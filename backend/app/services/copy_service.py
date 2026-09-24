import logging
from typing import List, Dict, Any, Optional

logger = logging.getLogger(__name__)

def generate_carousel_slides_copy(
    topic: str,
    total_slides: int = 6,
    brand_name: str = "JM Odontología Integral",
    openai_client = None,
    text_model: str = "gpt-4o-mini"
) -> List[Dict[str, Any]]:
    """
    Genera el copy estructurado para cada slide del carrusel.
    Si hay OpenAI client configurado, utiliza GPT-4o-mini.
    Si no, genera una estructura de alto impacto editorial y médica/profesional adaptada al tema.
    """
    if openai_client:
        try:
            prompt = f"""
Sos un copywriter experto en redes sociales para marcas profesionales y clínicas.
Crea el contenido para un carrusel de Instagram/LinkedIn de {total_slides} slides sobre el tema: "{topic}".
Marca: {brand_name}.

Devuelve EXACTAMENTE un JSON array con {total_slides} objetos:
[
  {{
    "slide_number": 1,
    "slide_type": "cover",
    "badge": "TEMA CLAVE",
    "title": "Título gancho en mayúsculas/minúsculas potente",
    "subtitle": "Subtítulo intrigante que invite a deslizar",
    "body": ""
  }},
  {{
    "slide_number": 2,
    "slide_type": "content",
    "badge": "01. CONCEPTO",
    "title": "Subtítulo principal del slide",
    "subtitle": "",
    "body": "Texto explicativo claro, conciso y profesional de 2 a 3 líneas."
  }},
  ...
  {{
    "slide_number": {total_slides},
    "slide_type": "cta",
    "badge": "CONCLUSIÓN & ACCIÓN",
    "title": "Llamado a la acción claro",
    "subtitle": "{brand_name}",
    "body": "Guardá este carrusel o agendá tu consulta hoy mismo."
  }}
]
"""
            response = openai_client.chat.completions.create(
                model=text_model,
                messages=[{"role": "user", "content": prompt}],
                response_format={"type": "json_object"}
            )
            import json
            data = json.loads(response.choices[0].message.content)
            if isinstance(data, list):
                return data
            elif "slides" in data:
                return data["slides"]
        except Exception as e:
            logger.error(f"Error generando copy con GPT: {e}")

    # Generación Algorítmica Inteligente (Fallback editorial de alta calidad)
    slides = []
    
    # 1. Portada
    slides.append({
        "slide_number": 1,
        "slide_type": "cover",
        "badge": "CASO CLÍNICO & SALUD DENTAL",
        "title": topic.title(),
        "subtitle": "Lo que necesitás saber para cuidar tu sonrisa y prevenir complicaciones.",
        "body": "Deslizá para ver el paso a paso 👉",
        "highlight_word": topic.split()[0] if topic else "Salud"
    })

    # Puntos intermedios adaptados
    concepts = [
        ("01. Diagnóstico de Precisión", "La evaluación clínica y radiográfica temprana permite salvar piezas dentales que antes se daban por perdidas."),
        ("02. Tratamiento Conservador", "Tecnología moderna y técnicas indoloras para eliminar infecciones y regenerar tejidos dañados."),
        ("03. Reconstrucción Estética", "Restauramos la anatomía y función natural de cada pieza para una mordida perfecta y sonrisa armónica."),
        ("04. Cuidado & Mantenimiento", "Hábitos de higiene específicos y controles periódicos aseguran resultados duraderos en el tiempo."),
        ("05. Prevención Integral", "El mejor tratamiento siempre es el que evita llegar a situaciones de dolor agudo o urgencia."),
        ("06. Innovación en Odontología", "Materiales biocompatibles de última generación que garantizan máxima durabilidad y naturalidad.")
    ]

    for i in range(2, total_slides):
        idx = (i - 2) % len(concepts)
        c_title, c_body = concepts[idx]
        slides.append({
            "slide_number": i,
            "slide_type": "content",
            "badge": f"PASO 0{i - 1}",
            "title": c_title,
            "subtitle": "",
            "body": c_body,
            "highlight_word": c_title.split()[1] if len(c_title.split()) > 1 else ""
        })

    # Último slide: CTA
    slides.append({
        "slide_number": total_slides,
        "slide_type": "cta",
        "badge": "TU SONRISA IMPORTA",
        "title": "¿Tenés dudas sobre tu salud dental?",
        "subtitle": brand_name,
        "body": "Dejanos tu consulta en comentarios o agendá tu turno de evaluación con nuestro equipo.",
        "highlight_word": "Consulta"
    })

    return slides

def generate_post_caption(
    topic: str,
    brand_name: str = "JM Odontología Integral",
    slides: Optional[List[Dict[str, Any]]] = None,
    openai_client = None,
    text_model: str = "gpt-4o-mini"
) -> Dict[str, str]:
    """
    Genera el caption completo y hashtags profesionales para Instagram/LinkedIn.
    """
    if openai_client:
        try:
            prompt = f"""
Sos un copywriter profesional de alto nivel en salud, medicina y odontología.
Crea un texto persuasivo y de alto valor para acompañar un post de carrusel/video en Instagram y LinkedIn.
Tema: "{topic}"
Clínica/Marca: "{brand_name}"

Estructura requerida:
1. Gancho inicial intrigante con emoji.
2. Desarrollo de valor médico/profesional explicando por qué importa el tema (2 párrafos claros).
3. Resumen de puntos clave o consejos prácticos.
4. Llamado a la acción (CTA) cálido y profesional invitando a agendar o comentar.
5. 6 a 10 hashtags relevantes y específicos.

Devuelve EXACTAMENTE un JSON con:
{{
  "caption": "Texto completo formateado con saltos de línea y emojis",
  "hashtags": "#saluddental #odontologia ..."
}}
"""
            response = openai_client.chat.completions.create(
                model=text_model,
                messages=[{"role": "user", "content": prompt}],
                response_format={"type": "json_object"}
            )
            import json
            data = json.loads(response.choices[0].message.content)
            if "caption" in data:
                return {
                    "caption": data.get("caption", ""),
                    "hashtags": data.get("hashtags", "#odontologia #saludbucal #esteticadental")
                }
        except Exception as e:
            logger.error(f"Error generando caption con GPT: {e}")

    # Fallback
    caption = (
        f"🦷 {topic.capitalize()}\n\n"
        f"La salud bucal y la estética de tu sonrisa van de la mano. Un diagnóstico a tiempo y el uso de técnicas modernas "
        f"marcan la diferencia en la durabilidad y naturalidad de cada tratamiento.\n\n"
        f"En {brand_name} priorizamos tu bienestar con atención personalizada y tecnología de vanguardia.\n\n"
        f"💬 ¿Cuándo fue tu último control? Dejanos tu consulta o escribinos por mensaje directo para coordinar tu cita."
    )
    hashtags = "#OdontologiaIntegral #SaludBucal #EsteticaDental #SonrisaSaludable #ImplantesDentales #PrevencionDental"
    return {"caption": caption, "hashtags": hashtags}

