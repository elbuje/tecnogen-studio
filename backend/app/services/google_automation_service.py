import os
import re
import logging
from typing import List, Dict, Any, Optional
from datetime import datetime
from google.oauth2 import service_account
from googleapiclient.discovery import build
from app.config import settings

logger = logging.getLogger(__name__)

class GoogleAutomationService:
    def __init__(self, key_path: Optional[str] = None):
        candidate_paths = [
            key_path,
            getattr(settings, "GOOGLE_SERVICE_ACCOUNT_KEY_PATH", None),
            "storage/google-service-account.json",
            "backend/storage/google-service-account.json",
            os.path.join(os.path.dirname(__file__), "../../../storage/google-service-account.json"),
            os.path.join(os.path.dirname(__file__), "../../storage/google-service-account.json"),
            os.path.join(os.path.dirname(__file__), "../storage/google-service-account.json"),
            "/home/ploi/studio.tecnogen.ar/backend/storage/google-service-account.json",
            "/home/ploi/studio.tecnogen.ar/storage/google-service-account.json"
        ]
        
        self.key_path = None
        for p in candidate_paths:
            if p and os.path.exists(p):
                self.key_path = p
                break

        self.creds = None
        self.drive_service = None
        self.sheets_service = None
        self.is_ready = False

        if self.key_path:
            try:
                scopes = [
                    "https://www.googleapis.com/auth/drive.readonly",
                    "https://www.googleapis.com/auth/spreadsheets"
                ]
                self.creds = service_account.Credentials.from_service_account_file(self.key_path, scopes=scopes)
                self.drive_service = build("drive", "v3", credentials=self.creds)
                self.sheets_service = build("sheets", "v4", credentials=self.creds)
                self.is_ready = True
            except Exception as e:
                logger.error(f"Error inicializando GoogleAutomationService: {e}")
        else:
            logger.error("No se encontró el archivo google-service-account.json en ninguna de las rutas esperadas.")

    @staticmethod
    def extract_id(url_or_id: Optional[str]) -> Optional[str]:
        if not url_or_id:
            return None
        m = re.search(r'folders/([a-zA-Z0-9_-]+)', url_or_id)
        if m:
            return m.group(1)
        m2 = re.search(r'd/([a-zA-Z0-9_-]+)', url_or_id)
        if m2:
            return m2.group(1)
        return url_or_id.split('?')[0].strip()

    @staticmethod
    def parse_script_to_slides(script_text: str, total_slides: int = 4, topic: str = "") -> List[Dict[str, Any]]:
        """
        Parsea el guion de texto proveniente de la columna 'GUION'
        en láminas estructuradas respetando número, tipo, headline y body_text.
        """
        if not script_text:
            # Fallback a láminas por defecto con el topic
            return [
                {"slide_number": 1, "order_index": 1, "slide_type": "cover", "headline": topic or "Portada", "body_text": topic},
                {"slide_number": 2, "order_index": 2, "slide_type": "content", "headline": "Información Clave", "body_text": topic},
                {"slide_number": 3, "order_index": 3, "slide_type": "content", "headline": "Consejo Profesional", "body_text": topic},
                {"slide_number": 4, "order_index": 4, "slide_type": "cta", "headline": "Agendá tu Consulta", "body_text": "Dejanos tu comentario o escribinos por DM."}
            ]

        lines = [l.strip() for l in script_text.split('\n') if l.strip()]
        slides = []
        for idx, line in enumerate(lines, start=1):
            m = re.match(r'^(\d+)[\.\)]\s*(?:(Portada|Cierre|Paso\s*\d+|Consejo\s*\d+|Mito\s*\d+):?\s*)?(.*)$', line, re.IGNORECASE)
            if m:
                num = int(m.group(1))
                tag = (m.group(2) or '').strip()
                content = m.group(3).strip(' "\'')
                slide_type = 'cover' if (num == 1 or tag.lower() == 'portada') else ('cta' if tag.lower() == 'cierre' else 'content')
                slides.append({
                    "slide_number": num,
                    "order_index": num,
                    "slide_type": slide_type,
                    "badge": tag.upper() if tag else f"PASO {num}",
                    "headline": content[:80] if content else (topic if num == 1 else f"Lámina #{num}"),
                    "title": content,
                    "body_text": content,
                    "body": content
                })
            else:
                slide_type = 'cover' if idx == 1 else ('cta' if idx == len(lines) else 'content')
                slides.append({
                    "slide_number": idx,
                    "order_index": idx,
                    "slide_type": slide_type,
                    "badge": f"PASO {idx}",
                    "headline": line[:80],
                    "title": line,
                    "body_text": line,
                    "body": line
                })

        return slides

    def read_sheet_jobs(self, sheet_url: str, tab_name: str = "Carruseles") -> List[Dict[str, Any]]:
        """
        Lee el Google Sheet y extrae las filas con su mapeo exacto de columnas.
        """
        if not self.is_ready:
            raise RuntimeError("Google Automation Service no está configurado o falta la credencial JSON.")

        sheet_id = self.extract_id(sheet_url)
        res = self.sheets_service.spreadsheets().values().get(
            spreadsheetId=sheet_id,
            range=f"{tab_name}!A1:Z100"
        ).execute()

        all_rows = res.get("values", [])
        if not all_rows:
            return []

        headers = [h.strip().upper() for h in all_rows[0]]
        
        # Mapear índices
        def get_idx(candidates: List[str]) -> int:
            for c in candidates:
                for idx, h in enumerate(headers):
                    if c in h:
                        return idx
            return -1

        idx_num = get_idx(["N°", "NUM", "NUMERO"])
        idx_tema = get_idx(["TEMA"])
        idx_titulo = get_idx(["TÍTULO / PORTADA", "TITULO", "PORTADA"])
        idx_guion = get_idx(["GUION", "GUÍON", "SCRIPT"])
        idx_subtitulos = get_idx(["SUBTÍTULOS", "PALABRAS CLAVE"])
        idx_instagram = get_idx(["INSTAGRAM", "COPY"])
        idx_fotos = get_idx(["FOTOS DOCTORA", "LINK FOTOS", "DOCTORA"])
        idx_estado = get_idx(["ESTADO"])
        idx_nota = get_idx(["NOTA", "NOTAS"])
        idx_id_content = get_idx(["ID_CONTENIDO"])
        idx_link_preview = get_idx(["LINK_PREVIEW"])
        idx_fecha = get_idx(["FECHA_PROCESADO"])

        jobs = []
        for row_num, row in enumerate(all_rows[1:], start=2):
            def val(idx):
                return row[idx].strip() if idx >= 0 and idx < len(row) else ""

            estado = val(idx_estado)
            content_id = val(idx_id_content)

            jobs.append({
                "row_number": row_num,
                "row_index": row_num,
                "nro": val(idx_num),
                "tema": val(idx_tema),
                "topic": val(idx_tema) or val(idx_titulo),
                "titulo": val(idx_titulo),
                "title": val(idx_titulo) or val(idx_tema),
                "guion": val(idx_guion),
                "script": val(idx_guion),
                "subtitulos": val(idx_subtitulos),
                "copy_instagram": val(idx_instagram),
                "doctora_ref": val(idx_fotos),
                "subject": val(idx_fotos),
                "estado": estado,
                "nota": val(idx_nota),
                "notes": val(idx_nota),
                "content_id": content_id,
                "link_preview": val(idx_link_preview),
                "fecha_procesado": val(idx_fecha),
                # Determinar si está listo para procesar
                "is_pending": "pendiente" in estado.lower() and not content_id
            })

        return jobs

    def update_sheet_row_status(
        self,
        sheet_url: str,
        row_number: int,
        estado: str,
        content_id: Optional[str] = None,
        preview_url: Optional[str] = None,
        tab_name: str = "Carruseles"
    ):
        """
        Actualiza el estado, ID de contenido y link de vista previa de una fila en el Sheet.
        Columna O = ESTADO (col 15)
        Columna Q = ID_CONTENIDO (col 17)
        Columna R = LINK_PREVIEW (col 18)
        Columna S = FECHA_PROCESADO (col 19)
        """
        if not self.is_ready:
            return

        sheet_id = self.extract_id(sheet_url)

        # 1. Actualizar Estado (Columna O)
        self.sheets_service.spreadsheets().values().update(
            spreadsheetId=sheet_id,
            range=f"{tab_name}!O{row_number}",
            valueInputOption="RAW",
            body={"values": [[estado]]}
        ).execute()

        # 2. Si hay ID y Preview, actualizar columnas Q, R, S
        if content_id or preview_url:
            now_str = datetime.now().strftime("%Y-%m-%d %H:%M")
            self.sheets_service.spreadsheets().values().update(
                spreadsheetId=sheet_id,
                range=f"{tab_name}!Q{row_number}:S{row_number}",
                valueInputOption="RAW",
                body={"values": [[content_id or "", preview_url or "", now_str]]}
            ).execute()

    def download_file_bytes(self, file_id: str) -> Optional[bytes]:
        """Descarga el contenido binario de un archivo en Google Drive."""
        if not self.is_ready or not file_id:
            return None
        try:
            req = self.drive_service.files().get_media(fileId=file_id)
            return req.execute()
        except Exception as e:
            logger.error(f"Error descargando archivo {file_id} de Drive: {e}")
            return None

    def get_brand_logo_bytes(self, brand) -> Optional[bytes]:
        """Obtiene el mejor logo de la marca desde la carpeta de Logos en Google Drive."""
        if not self.is_ready or not brand.gdrive_logos_folder_id:
            return None
        folder_id = self.extract_id(brand.gdrive_logos_folder_id)
        if not folder_id:
            return None
        try:
            q = f"'{folder_id}' in parents and trashed = false"
            res = self.drive_service.files().list(q=q, fields="files(id, name, mimeType)").execute()
            files = res.get("files", [])
            if not files:
                return None
            # Priorizar logos PNG transparentes o en blanco/negro/color
            png_files = [f for f in files if "png" in f.get("name", "").lower()]
            selected = png_files[0] if png_files else files[0]
            logger.info(f"Usando logo de Drive: {selected.get('name')} (id={selected.get('id')})")
            return self.download_file_bytes(selected.get("id"))
        except Exception as e:
            logger.error(f"Error obteniendo logo de Drive: {e}")
            return None

    def get_brand_subject_bytes(self, brand, doctor_ref: Optional[str] = None) -> Optional[bytes]:
        """Obtiene la foto del sujeto/doctora desde la subcarpeta de Personajes en Google Drive."""
        if not self.is_ready or not brand.gdrive_subjects_folder_id:
            return None
        parent_id = self.extract_id(brand.gdrive_subjects_folder_id)
        if not parent_id:
            return None
        try:
            # 1. Buscar subcarpeta del doctor/personaje
            q = f"'{parent_id}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false"
            res = self.drive_service.files().list(q=q, fields="files(id, name)").execute()
            folders = res.get("files", [])
            
            target_folder_id = None
            if doctor_ref:
                for f in folders:
                    if f.get("name", "").lower() in doctor_ref.lower() or doctor_ref.lower() in f.get("name", "").lower():
                        target_folder_id = f.get("id")
                        break
            if not target_folder_id and folders:
                target_folder_id = folders[0].get("id")
            if not target_folder_id:
                target_folder_id = parent_id

            q_files = f"'{target_folder_id}' in parents and mimeType != 'application/vnd.google-apps.folder' and trashed = false"
            res_files = self.drive_service.files().list(q=q_files, fields="files(id, name, mimeType)").execute()
            files = res_files.get("files", [])
            if not files:
                return None
            
            # Seleccionar foto (preferir HIF / PNG / JPG de Jessica/Doctora)
            selected_file = files[0]
            logger.info(f"Usando foto de sujeto de Drive: {selected_file.get('name')} (id={selected_file.get('id')})")
            return self.download_file_bytes(selected_file.get("id"))
        except Exception as e:
            logger.error(f"Error obteniendo foto de sujeto de Drive: {e}")
            return None
