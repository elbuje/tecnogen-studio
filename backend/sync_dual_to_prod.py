import os
import sys
import json
import urllib.request
import urllib.error

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app.database import SessionLocal
from app.models.content import Content, Slide

def sync():
    db = SessionLocal()
    # Buscar los dos contenidos generados
    contents = db.query(Content).filter(
        Content.title.ilike("%3 frases sobre sacarte una muela%")
    ).order_by(Content.created_at.desc()).all()
    
    print(f"Encontrados {len(contents)} contenidos para sincronizar a producción:")
    
    payload_contents = []
    for c in contents:
        slides = db.query(Slide).filter(Slide.content_id == c.id).order_by(Slide.slide_number).all()
        print(f" - [{c.title}] ID={c.id}, Total slides={len(slides)}")
        
        slide_list = []
        for s in slides:
            slide_list.append({
                "id": s.id,
                "content_id": s.content_id,
                "slide_number": s.slide_number,
                "slide_type": s.slide_type,
                "image_url": s.image_url,
                "headline": s.headline,
                "body_text": s.body_text,
                "badge": s.badge,
                "prompt_used": s.prompt_used,
                "gdrive_file_id": s.gdrive_file_id,
                "status": s.status
            })
            
        payload_contents.append({
            "id": c.id,
            "title": c.title,
            "type": c.type or "carousel",
            "status": c.status or "ready_for_review",
            "total_slides": c.total_slides,
            "caption_copy": c.caption_copy,
            "hashtags": c.hashtags,
            "hook_text": c.hook_text,
            "slides": slide_list
        })
        
    if not payload_contents:
        print("No hay contenidos para enviar.")
        return

    # Sincronizar en lotes o individualmente a producción
    prod_url = "https://studio.tecnogen.ar/api/v1/contents/sync-import"
    print(f"\nEnviando datos a {prod_url}...")
    
    for c_data in payload_contents:
        req_data = json.dumps({"contents": [c_data]}).encode("utf-8")
        req = urllib.request.Request(
            prod_url,
            data=req_data,
            headers={"Content-Type": "application/json"}
        )
        try:
            with urllib.request.urlopen(req) as response:
                res_body = response.read().decode("utf-8")
                print(f"✅ Sincronizado en producción: '{c_data['title']}' -> {res_body}")
                print(f"   Viewer: https://studio.tecnogen.ar/app/viewer/{c_data['id']}")
        except urllib.error.HTTPError as e:
            print(f"❌ Error HTTP {e.code} sincronizando '{c_data['title']}': {e.read().decode('utf-8')}")
        except Exception as e:
            print(f"❌ Error sincronizando '{c_data['title']}': {e}")

if __name__ == "__main__":
    sync()
