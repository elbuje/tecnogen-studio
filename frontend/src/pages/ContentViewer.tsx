import React, { useState, useEffect, useRef } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import api from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import {
  ChevronLeft,
  ChevronRight,
  ArrowLeft,
  CheckCircle2,
  RefreshCw,
  Download,
  Calendar,
  Sparkles,
  AlertCircle,
  Share2,
  Trash2,
  Layers,
  RotateCw
} from 'lucide-react';

export const ContentViewer: React.FC = () => {
  const { contentId } = useParams<{ contentId: string }>();
  const { user, refreshUser } = useAuth();
  const navigate = useNavigate();
  const [content, setContent] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [currentIndex, setCurrentIndex] = useState(0);

  // Single slide feedback
  const [singleFeedback, setSingleFeedback] = useState('');
  const [regeneratingSingle, setRegeneratingSingle] = useState(false);

  // All slides feedback & modal
  const [showRegenerateAllModal, setShowRegenerateAllModal] = useState(false);
  const [globalFeedback, setGlobalFeedback] = useState('');
  const [regeneratingAll, setRegeneratingAll] = useState(false);

  // Metricool modal
  const [showScheduleModal, setShowScheduleModal] = useState(false);

  // Deleting state
  const [deleting, setDeleting] = useState(false);

  const pollingRef = useRef<any>(null);

  const fetchContent = async (silent = false) => {
    try {
      if (!silent) setLoading(true);
      const res = await api.get(`/contents/${contentId}`);
      setContent(res.data);
      return res.data;
    } catch (e) {
      console.error('Error fetching content:', e);
      return null;
    } finally {
      if (!silent) setLoading(false);
    }
  };

  useEffect(() => {
    fetchContent();

    // Auto-polling cada 3 segundos si el contenido se está generando
    pollingRef.current = setInterval(async () => {
      const data = await fetchContent(true);
      if (data && data.status !== 'generating') {
        clearInterval(pollingRef.current);
      }
    }, 3000);

    return () => {
      if (pollingRef.current) clearInterval(pollingRef.current);
    };
  }, [contentId]);

  // Si el estado cambia a listo, detener polling
  useEffect(() => {
    if (content && content.status !== 'generating' && pollingRef.current) {
      clearInterval(pollingRef.current);
    }
  }, [content?.status]);

  const slides = content?.slides || [];
  const currentSlide = slides[currentIndex];

  const handleNext = () => {
    if (currentIndex < slides.length - 1) {
      setCurrentIndex(currentIndex + 1);
    }
  };

  const handlePrev = () => {
    if (currentIndex > 0) {
      setCurrentIndex(currentIndex - 1);
    }
  };

  // 1. Regenerar Solo este Slide (1 crédito)
  const handleRegenerateSingle = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!currentSlide || !singleFeedback.trim()) return;

    try {
      setRegeneratingSingle(true);
      await api.post(`/contents/${contentId}/slides/${currentSlide.slide_number}/regenerate`, {
        feedback: singleFeedback,
      });
      await refreshUser();
      setSingleFeedback('');
      await fetchContent(false);
      alert(`Lámina #${currentSlide.slide_number} regenerada con éxito con OpenAI (1 crédito consumido).`);
    } catch (err: any) {
      alert(err.response?.data?.detail?.message || err.response?.data?.detail || 'Error al regenerar slide.');
    } finally {
      setRegeneratingSingle(false);
    }
  };

  // 2. Regenerar Todo el Carrusel (N créditos)
  const handleRegenerateAll = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      setRegeneratingAll(true);
      await api.post(`/contents/${contentId}/regenerate-all`, {
        global_feedback: globalFeedback || null,
      });
      await refreshUser();
      setShowRegenerateAllModal(false);
      setGlobalFeedback('');
      await fetchContent(false);

      // Reiniciar polling para esperar la regeneración completa
      if (pollingRef.current) clearInterval(pollingRef.current);
      pollingRef.current = setInterval(async () => {
        const data = await fetchContent(true);
        if (data && data.status !== 'generating') {
          clearInterval(pollingRef.current);
        }
      }, 3000);

      alert(`Regeneración completa iniciada (${content.total_slides} créditos consumidos). Procesando con OpenAI...`);
    } catch (err: any) {
      alert(err.response?.data?.detail?.message || err.response?.data?.detail || 'Error al regenerar todo el carrusel.');
    } finally {
      setRegeneratingAll(false);
    }
  };

  // 3. Eliminar Carrusel
  const handleDelete = async () => {
    if (!confirm(`¿Estás seguro de que querés eliminar el carrusel "${content.title}"? Esta acción no se puede deshacer.`)) {
      return;
    }
    try {
      setDeleting(true);
      await api.delete(`/contents/${contentId}`);
      await refreshUser();
      navigate('/app/library');
    } catch (e) {
      alert('Error al eliminar carrusel.');
    } finally {
      setDeleting(false);
    }
  };

  const handleApproveAll = async () => {
    try {
      await api.post(`/contents/${contentId}/approve`);
      await fetchContent(false);
      alert('¡Carrusel aprobado exitosamente! Ahora podés descargarlo o programarlo en Metricool.');
    } catch (e) {
      alert('Error al aprobar carrusel.');
    }
  };

  if (loading && !content) {
    return (
      <div className="min-h-[60vh] flex items-center justify-center text-slate-400">
        <RefreshCw className="w-6 h-6 animate-spin text-cyan-400 mr-2" /> Cargando visor interactivo...
      </div>
    );
  }

  if (!content) {
    return (
      <div className="p-8 text-center space-y-4">
        <div className="text-rose-400 font-bold">Contenido no encontrado</div>
        <Link to="/app/library" className="text-cyan-400 underline text-sm">Ir a Mi Biblioteca</Link>
      </div>
    );
  }

  return (
    <div className="p-6 max-w-7xl mx-auto space-y-6">
      {/* Top Bar Navigation & Actions */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div className="flex items-center gap-4">
          <Link
            to="/app/library"
            className="p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 transition-colors"
            title="Volver a Mi Biblioteca"
          >
            <ArrowLeft className="w-4 h-4" />
          </Link>
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-lg font-bold text-white tracking-tight">{content.title}</h1>
              {content.status === 'generating' && (
                <span className="flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-[11px] font-semibold animate-pulse">
                  <RotateCw className="w-3 h-3 animate-spin" /> Procesando con OpenAI...
                </span>
              )}
            </div>
            <div className="flex items-center gap-2 text-xs text-slate-400 mt-0.5">
              <span>{content.total_slides} slides</span>
              <span>•</span>
              <span className="capitalize text-cyan-300">{content.type}</span>
              <span>•</span>
              <span
                className={`px-2 py-0.5 rounded-full text-[10px] font-semibold ${
                  content.status === 'ready_for_review'
                    ? 'bg-amber-500/10 text-amber-300 border border-amber-500/30'
                    : content.status === 'approved'
                    ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30'
                    : content.status === 'failed'
                    ? 'bg-rose-500/10 text-rose-300 border border-rose-500/30'
                    : 'bg-slate-800 text-slate-300'
                }`}
              >
                {content.status === 'ready_for_review'
                  ? 'Listo para Revisar'
                  : content.status === 'approved'
                  ? 'Aprobado'
                  : content.status === 'failed'
                  ? 'Error'
                  : 'Generando...'}
              </span>
            </div>
          </div>
        </div>

        <div className="flex items-center gap-3 flex-wrap">
          {/* Botón Refrescar Manual */}
          <button
            onClick={() => fetchContent(false)}
            title="Actualizar estado"
            className="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition-colors"
          >
            <RotateCw className="w-4 h-4" />
          </button>

          {/* Botón Regenerar Todo */}
          <button
            onClick={() => setShowRegenerateAllModal(true)}
            className="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all flex items-center gap-1.5"
          >
            <RefreshCw className="w-3.5 h-3.5 text-cyan-400" /> Regenerar Todo el Carrusel
          </button>

          {/* Aprobar */}
          {content.status !== 'approved' && (
            <button
              onClick={handleApproveAll}
              className="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-black flex items-center gap-1.5 transition-all shadow-lg shadow-emerald-500/20"
            >
              <CheckCircle2 className="w-4 h-4" /> Aprobar Todo
            </button>
          )}

          {/* Programar */}
          <button
            onClick={() => setShowScheduleModal(true)}
            className="px-4 py-2 rounded-xl text-xs font-bold bg-cyan-500 hover:bg-cyan-400 text-black flex items-center gap-1.5 transition-all shadow-lg shadow-cyan-500/20"
          >
            <Calendar className="w-4 h-4" /> Programar Metricool
          </button>

          {/* Eliminar Carrusel */}
          <button
            onClick={handleDelete}
            disabled={deleting}
            title="Eliminar este carrusel por completo"
            className="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/30 transition-colors"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>
      </div>

      {/* Main Studio Viewer Area */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {/* Left: 1024x1536 Viewport Stage */}
        <div className="lg:col-span-7 flex flex-col items-center space-y-4">
          <div className="relative w-full max-w-[420px] aspect-[2/3] rounded-3xl overflow-hidden glass-panel border border-slate-700/80 shadow-2xl flex items-center justify-center bg-[#0B1E38]">
            {currentSlide?.image_url ? (
              <img
                src={currentSlide.image_url}
                alt={`Slide ${currentSlide.slide_number}`}
                className="w-full h-full object-contain select-none"
              />
            ) : (
              <div className="text-center p-6 space-y-3">
                <Sparkles className="w-8 h-8 text-cyan-400 mx-auto animate-spin" />
                <div className="text-sm font-semibold text-slate-300">
                  {currentSlide?.status === 'failed' ? (
                    <span className="text-rose-400">Error en OpenAI: {currentSlide?.feedback}</span>
                  ) : (
                    `Generando Slide #${currentIndex + 1}...`
                  )}
                </div>
              </div>
            )}

            {/* Slide Number Overlay */}
            {slides.length > 0 && (
              <div className="absolute top-4 right-4 px-3 py-1 rounded-full bg-black/60 backdrop-blur-md text-[11px] font-bold text-white border border-white/10">
                Slide {currentIndex + 1} de {slides.length} (v{currentSlide?.version || 1})
              </div>
            )}
          </div>

          {/* Navigation Controls */}
          {slides.length > 0 && (
            <div className="flex items-center gap-6">
              <button
                onClick={handlePrev}
                disabled={currentIndex === 0}
                className="p-3 rounded-2xl glass-card hover:bg-slate-800 text-white disabled:opacity-30 transition-all"
              >
                <ChevronLeft className="w-5 h-5" />
              </button>

              {/* Dot indicators */}
              <div className="flex items-center gap-2">
                {slides.map((s: any, idx: number) => (
                  <button
                    key={s.id || idx}
                    onClick={() => setCurrentIndex(idx)}
                    className={`w-3 h-3 rounded-full transition-all ${
                      idx === currentIndex
                        ? 'bg-cyan-400 w-8'
                        : 'bg-slate-700 hover:bg-slate-600'
                    }`}
                  />
                ))}
              </div>

              <button
                onClick={handleNext}
                disabled={currentIndex === slides.length - 1}
                className="p-3 rounded-2xl glass-card hover:bg-slate-800 text-white disabled:opacity-30 transition-all"
              >
                <ChevronRight className="w-5 h-5" />
              </button>
            </div>
          )}
        </div>

        {/* Right: Individual Slide Controls & Regeneration */}
        <div className="lg:col-span-5 space-y-6">
          {/* Slide Details & Prompt info */}
          <div className="p-6 rounded-3xl glass-card space-y-4">
            <div className="flex items-center justify-between">
              <span className="text-xs uppercase font-semibold text-slate-400 tracking-wider">
                Lámina #{currentIndex + 1} ({currentSlide?.slide_type || 'content'})
              </span>
              <span className="text-xs px-2.5 py-0.5 rounded-full bg-cyan-500/10 text-cyan-300 font-semibold border border-cyan-500/20">
                Versión {currentSlide?.version || 1}
              </span>
            </div>

            <div className="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 text-xs text-slate-300 font-mono leading-relaxed max-h-40 overflow-y-auto">
              <span className="text-slate-500 font-sans block text-[10px] uppercase font-bold mb-1">
                Prompt Enviado a OpenAI:
              </span>
              {currentSlide?.prompt_used || 'Generando prompt con OpenAI...'}
            </div>

            {/* Granular Regeneration Form (1 Credit) */}
            <form onSubmit={handleRegenerateSingle} className="pt-3 border-t border-slate-800 space-y-3">
              <div className="flex items-center justify-between text-xs">
                <span className="font-semibold text-slate-300 flex items-center gap-1.5">
                  <RefreshCw className="w-3.5 h-3.5 text-cyan-400" /> Regenerar solo esta lámina (#{currentIndex + 1})
                </span>
                <span className="text-amber-300 font-bold">1 crédito</span>
              </div>
              <textarea
                value={singleFeedback}
                onChange={(e) => setSingleFeedback(e.target.value)}
                placeholder="Ej: Cambiar el fondo por más iluminado, o mostrar primer plano de sonrisa..."
                rows={3}
                className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-xs focus:border-cyan-400 focus:outline-none resize-none"
              />
              <button
                type="submit"
                disabled={regeneratingSingle || !singleFeedback.trim()}
                className="w-full py-2.5 rounded-xl font-bold bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 border border-cyan-500/40 text-xs transition-all flex items-center justify-center gap-2 disabled:opacity-40"
              >
                {regeneratingSingle ? 'Regenerando con OpenAI...' : `Regenerar Lámina #${currentIndex + 1} (1 Crédito)`}
              </button>
            </form>
          </div>

          {/* Social Caption & Copy */}
          <div className="p-6 rounded-3xl glass-card space-y-3">
            <h3 className="text-xs uppercase font-semibold text-slate-400 tracking-wider">
              Copy para Redes Sociales
            </h3>
            <div className="p-4 rounded-xl bg-slate-900/80 border border-slate-800 text-sm text-slate-200 leading-relaxed whitespace-pre-line">
              {content.caption_copy}
            </div>
            <div className="text-xs text-cyan-400 font-semibold">{content.hashtags}</div>
          </div>
        </div>
      </div>

      {/* Modal: Regenerar Todo el Carrusel */}
      {showRegenerateAllModal && (
        <div className="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-lg rounded-3xl glass-panel p-8 space-y-6 border border-slate-700 shadow-2xl">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center border border-cyan-500/20">
                  <RefreshCw className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="text-lg font-bold text-white">Regenerar Todo el Carrusel</h3>
                  <p className="text-xs text-slate-400">
                    Se generarán nuevamente las {content.total_slides} láminas con OpenAI
                  </p>
                </div>
              </div>
              <button
                onClick={() => setShowRegenerateAllModal(false)}
                className="text-slate-400 hover:text-white"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleRegenerateAll} className="space-y-4">
              <div className="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 text-xs text-slate-300 space-y-1">
                <div className="font-semibold text-white flex items-center justify-between">
                  <span>Costo de Regeneración:</span>
                  <span className="text-amber-300 font-bold text-sm">{content.total_slides} créditos</span>
                </div>
                <div className="text-slate-400">
                  Saldo actual: {user?.credits_balance} créditos
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                  Directiva Global / Feedback (Opcional)
                </label>
                <textarea
                  value={globalFeedback}
                  onChange={(e) => setGlobalFeedback(e.target.value)}
                  placeholder="Ej: Usar tono más minimalista, fotos con luz natural y colores más pasteles en todas las láminas..."
                  rows={3}
                  className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-xs focus:border-cyan-400 focus:outline-none resize-none"
                />
              </div>

              <div className="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setShowRegenerateAllModal(false)}
                  className="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  disabled={regeneratingAll}
                  className="px-5 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25 flex items-center gap-2 disabled:opacity-50"
                >
                  {regeneratingAll
                    ? 'Iniciando...'
                    : `Confirmar y Regenerar (${content.total_slides} Créditos)`}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Metricool Scheduling Modal */}
      {showScheduleModal && (
        <div className="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md rounded-3xl glass-panel p-8 space-y-6 border border-slate-700 shadow-2xl">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center border border-cyan-500/20">
                  <Calendar className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="text-lg font-bold text-white">Programar en Metricool</h3>
                  <p className="text-xs text-slate-400">Mejores horarios recomendados por IA</p>
                </div>
              </div>
              <button
                onClick={() => setShowScheduleModal(false)}
                className="text-slate-400 hover:text-white"
              >
                ✕
              </button>
            </div>

            <div className="space-y-3">
              <div className="text-xs font-semibold text-slate-300">Mejores horarios de audiencia:</div>
              <div className="space-y-2">
                <label className="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-cyan-500/40 cursor-pointer">
                  <div className="flex items-center gap-2">
                    <input type="radio" name="slot" defaultChecked className="text-cyan-500" />
                    <span className="text-sm text-white font-medium">Hoy 19:30</span>
                  </div>
                  <span className="px-2 py-0.5 rounded-md bg-cyan-500/20 text-cyan-300 text-xs font-bold">
                    96% Actividad
                  </span>
                </label>
                <label className="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-slate-800 cursor-pointer hover:border-slate-700">
                  <div className="flex items-center gap-2">
                    <input type="radio" name="slot" className="text-cyan-500" />
                    <span className="text-sm text-white font-medium">Mañana 13:00</span>
                  </div>
                  <span className="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 text-xs font-bold">
                    91% Actividad
                  </span>
                </label>
              </div>
            </div>

            <div className="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
              <button
                onClick={() => setShowScheduleModal(false)}
                className="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400"
              >
                Cancelar
              </button>
              <button
                onClick={() => {
                  setShowScheduleModal(false);
                  alert('¡Contenido enviado a la cola de Metricool con éxito!');
                }}
                className="px-5 py-2 rounded-xl text-xs font-bold bg-cyan-500 hover:bg-cyan-400 text-black shadow-lg shadow-cyan-500/20"
              >
                Confirmar y Programar
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
