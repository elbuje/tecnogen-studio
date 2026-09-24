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
    def parse_script_to_slides(script_text: str) -> List[Dict[str, Any]]:
        """
        Parsea el guion de texto proveniente de la columna 'GUION'
        en láminas estructuradas respetando número, tipo y texto.
        """
        if not script_text:
            return []
        lines = [l.strip() for l in script_text.split('\n') if l.strip()]
        slides = []
        for line in lines:
            m = re.match(r'^(\d+)[\.\)]\s*(?:(Portada|Cierre|Paso\s*\d+|Consejo\s*\d+|Mito\s*\d+):?\s*)?(.*)$', line, re.IGNORECASE)
            if m:
                num = int(m.group(1))
                tag = (m.group(2) or '').strip()
                content = m.group(3).strip(' "\'')
                slide_type = 'cover' if (num == 1 or tag.lower() == 'portada') else ('cta' if tag.lower() == 'cierre' else 'content')
                slides.append({
                    "slide_number": num,
                    "slide_type": slide_type,
                    "badge": tag.upper() if tag else f"PASO {num}",
                    "title": content,
                    "body": content
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

    def get_doctor_photos(self, doctor_ref: str, subjects_folder_id: Optional[str] = None) -> List[Dict[str, Any]]:
        """
        Encuentra las fotos de la doctora indicada (por link de Drive o por nombre en subjects_folder).
        """
        if not self.is_ready or not doctor_ref:
            return []

        folder_id = self.extract_id(doctor_ref)
        if not folder_id and subjects_folder_id:
            # Buscar subcarpeta por nombre
            parent_id = self.extract_id(subjects_folder_id)
            if parent_id:
                q = f"'{parent_id}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false"
                res = self.drive_service.files().list(q=q, fields="files(id, name)").execute()
                for f in res.get("files", []):
                    if f["name"].lower() in doctor_ref.lower():
                        folder_id = f["id"]
                        break

        if not folder_id:
            return []

        # Listar fotos de la carpeta
        q = f"'{folder_id}' in parents and trashed = false"
        res = self.drive_service.files().list(q=q, fields="files(id, name, mimeType)").execute()
        return res.get("files", [])
