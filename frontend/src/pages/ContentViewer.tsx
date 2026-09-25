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
  RotateCw,
  ExternalLink,
  Copy,
  Check,
  Eye,
  FileText,
  Sliders,
  FolderDown
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
  const [copiedCaption, setCopiedCaption] = useState(false);
  const [copiedPrompt, setCopiedPrompt] = useState(false);
  const [copiedText, setCopiedText] = useState(false);

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

  useEffect(() => {
    if (content && content.status !== 'generating' && pollingRef.current) {
      clearInterval(pollingRef.current);
    }
  }, [content?.status]);

  // Keyboard navigation
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'ArrowLeft') handlePrev();
      if (e.key === 'ArrowRight') handleNext();
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [currentIndex, content?.slides?.length]);

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

  // Descargar lámina actual
  const handleDownloadCurrent = () => {
    if (!currentSlide?.image_url) return;
    const a = document.createElement('a');
    a.href = currentSlide.image_url;
    a.download = `${content.title.replace(/\s+/g, '_')}_Slide_${currentIndex + 1}.png`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  };

  // Descargar todas las láminas
  const handleDownloadAll = () => {
    if (!slides.length) return;
    slides.forEach((s: any, idx: number) => {
      if (s.image_url) {
        setTimeout(() => {
          const a = document.createElement('a');
          a.href = s.image_url;
          a.download = `${content.title.replace(/\s+/g, '_')}_Slide_${idx + 1}.png`;
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        }, idx * 250);
      }
    });
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
      alert(`Lámina #${currentSlide.slide_number} regenerada con éxito.`);
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

      if (pollingRef.current) clearInterval(pollingRef.current);
      pollingRef.current = setInterval(async () => {
        const data = await fetchContent(true);
        if (data && data.status !== 'generating') {
          clearInterval(pollingRef.current);
        }
      }, 3000);

      alert(`Regeneración completa iniciada (${content.total_slides} créditos). Procesando con IA...`);
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
      alert('¡Carrusel aprobado exitosamente!');
    } catch (e) {
      alert('Error al aprobar carrusel.');
    }
  };

  const driveOutputFolder = content?.brand?.gdrive_output_folder_id || 'https://drive.google.com/drive/folders/10yYnkHa-l0oiotNxq8aalRUwRDv6dOJi?usp=sharing';

  if (loading && !content) {
    return (
      <div className="min-h-[70vh] flex flex-col items-center justify-center text-slate-400 space-y-4">
        <RefreshCw className="w-8 h-8 animate-spin text-cyan-400" />
        <span className="text-sm font-semibold">Cargando visor de estudio...</span>
      </div>
    );
  }

  if (!content) {
    return (
      <div className="p-12 text-center space-y-4">
        <div className="text-rose-400 font-bold text-lg">Contenido no encontrado</div>
        <p className="text-xs text-slate-400">Es posible que el contenido pertenezca a otra marca o se haya eliminado.</p>
        <Link to="/app/library" className="inline-block px-5 py-2.5 rounded-xl bg-cyan-500 text-black font-bold text-xs">
          Ir a Mi Biblioteca
        </Link>
      </div>
    );
  }

  return (
    <div className="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
      {/* Top Header Bar */}
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-slate-800">
        <div className="flex items-center gap-4">
          <Link
            to="/app/library"
            className="p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 transition-colors"
            title="Volver a Mi Biblioteca"
          >
            <ArrowLeft className="w-4 h-4" />
          </Link>
          <div>
            <div className="flex items-center gap-2.5 flex-wrap">
              <h1 className="text-xl font-bold text-white tracking-tight">{content.title}</h1>
              {content.status === 'generating' && (
                <span className="flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-[11px] font-bold animate-pulse">
                  <RotateCw className="w-3 h-3 animate-spin" /> Procesando con IA...
                </span>
              )}
              <span
                className={`px-3 py-1 rounded-full text-[11px] font-bold ${
                  content.status === 'ready_for_review'
                    ? 'bg-amber-500/15 text-amber-300 border border-amber-500/40'
                    : content.status === 'approved'
                    ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/40'
                    : content.status === 'failed'
                    ? 'bg-rose-500/15 text-rose-300 border border-rose-500/40'
                    : 'bg-slate-800 text-slate-300'
                }`}
              >
                {content.status === 'ready_for_review'
                  ? 'Listo para Revisar'
                  : content.status === 'approved'
                  ? 'Aprobado Oficial'
                  : content.status === 'failed'
                  ? 'Error'
                  : 'Generando...'}
              </span>
            </div>
            <div className="flex items-center gap-3 text-xs text-slate-400 mt-1">
              <span className="font-semibold text-cyan-400">{content.brand?.name || 'JM Odontología Integral'}</span>
              <span>•</span>
              <span>{content.total_slides} Láminas</span>
              <span>•</span>
              <span className="capitalize text-slate-300">Formato Instagram 4:5 HD</span>
            </div>
          </div>
        </div>

        {/* Global Action Buttons */}
        <div className="flex items-center gap-2.5 flex-wrap">
          {/* Abrir en Google Drive */}
          <a
            href={driveOutputFolder}
            target="_blank"
            rel="noopener noreferrer"
            className="px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-500/15 hover:bg-blue-500/25 text-blue-300 border border-blue-500/30 flex items-center gap-1.5 transition-all shadow-sm"
          >
            <ExternalLink className="w-3.5 h-3.5" /> Carpeta en Drive
          </a>

          {/* Descargar Todas */}
          <button
            onClick={handleDownloadAll}
            className="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition-all"
            title="Descargar todos los PNGs"
          >
            <FolderDown className="w-3.5 h-3.5 text-cyan-400" /> Descargar Todos los PNG
          </button>

          {/* Regenerar Todo */}
          <button
            onClick={() => setShowRegenerateAllModal(true)}
            className="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition-all"
          >
            <RefreshCw className="w-3.5 h-3.5 text-amber-400" /> Regenerar Todo
          </button>

          {/* Aprobar */}
          {content.status !== 'approved' && (
            <button
              onClick={handleApproveAll}
              className="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-black flex items-center gap-1.5 transition-all shadow-lg shadow-emerald-500/20"
            >
              <CheckCircle2 className="w-4 h-4" /> Aprobar Carrusel
            </button>
          )}

          {/* Eliminar */}
          <button
            onClick={handleDelete}
            disabled={deleting}
            title="Eliminar carrusel"
            className="p-2 rounded-xl text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/30 transition-colors"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>
      </div>

      {/* Main Studio Viewport & Inspector */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {/* Left Column: Big Stage + Filmstrip Strip */}
        <div className="lg:col-span-7 flex flex-col items-center space-y-5">
          {/* Main Stage Canvas (1080x1350 proportion) */}
          <div className="relative w-full max-w-[440px] aspect-[4/5] rounded-3xl overflow-hidden glass-panel border border-slate-700/80 shadow-2xl flex items-center justify-center bg-[#0B1E38] group">
            {currentSlide?.image_url ? (
              <img
                src={currentSlide.image_url}
                alt={`Slide ${currentSlide.slide_number}`}
                className="w-full h-full object-contain select-none"
              />
            ) : (
              <div className="text-center p-8 space-y-4">
                <Sparkles className="w-10 h-10 text-cyan-400 mx-auto animate-spin" />
                <div className="text-sm font-bold text-slate-200">
                  {currentSlide?.status === 'failed' ? (
                    <span className="text-rose-400">Error: {currentSlide?.feedback}</span>
                  ) : (
                    `Renderizando Lámina #${currentIndex + 1} de ${slides.length}...`
                  )}
                </div>
                <p className="text-xs text-slate-400 max-w-xs">
                  Componiendo fondo limpio, foto del sujeto, logo oficial y tipografía en español...
                </p>
              </div>
            )}

            {/* Top Indicator Badge */}
            {slides.length > 0 && (
              <div className="absolute top-4 right-4 px-3.5 py-1 rounded-full bg-black/75 backdrop-blur-md text-xs font-bold text-white border border-white/15 flex items-center gap-1.5 shadow-lg">
                <span className="text-cyan-400">Lámina {currentIndex + 1}</span> de {slides.length}
              </div>
            )}

            {/* Quick Download Overlay Button */}
            {currentSlide?.image_url && (
              <button
                onClick={handleDownloadCurrent}
                className="absolute bottom-4 right-4 p-2.5 rounded-xl bg-black/70 hover:bg-cyan-500 hover:text-black text-white border border-white/20 backdrop-blur-md text-xs font-bold transition-all opacity-0 group-hover:opacity-100 flex items-center gap-1.5 cursor-pointer shadow-lg"
                title="Descargar este PNG"
              >
                <Download className="w-3.5 h-3.5" /> Descargar PNG
              </button>
            )}
          </div>

          {/* Navigation Arrows & Counter */}
          {slides.length > 0 && (
            <div className="flex items-center gap-4">
              <button
                onClick={handlePrev}
                disabled={currentIndex === 0}
                className="p-3 rounded-2xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-white disabled:opacity-30 transition-all cursor-pointer"
                title="Anterior (Flecha izquierda)"
              >
                <ChevronLeft className="w-5 h-5" />
              </button>

              <div className="text-xs font-bold text-slate-300 tracking-wide">
                <span className="text-cyan-400 text-sm font-extrabold">{currentIndex + 1}</span> / {slides.length}
              </div>

              <button
                onClick={handleNext}
                disabled={currentIndex === slides.length - 1}
                className="p-3 rounded-2xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-white disabled:opacity-30 transition-all cursor-pointer"
                title="Siguiente (Flecha derecha)"
              >
                <ChevronRight className="w-5 h-5" />
              </button>
            </div>
          )}

          {/* Filmstrip Carousel Thumbnails */}
          {slides.length > 0 && (
            <div className="w-full max-w-[480px] p-3 rounded-2xl bg-slate-900/90 border border-slate-800/90 flex gap-2.5 overflow-x-auto">
              {slides.map((s: any, idx: number) => (
                <button
                  key={s.id || idx}
                  onClick={() => setCurrentIndex(idx)}
                  className={`relative flex-shrink-0 w-14 h-18 rounded-xl overflow-hidden border-2 transition-all cursor-pointer ${
                    idx === currentIndex
                      ? 'border-cyan-400 shadow-lg shadow-cyan-500/25 scale-105'
                      : 'border-slate-800 hover:border-slate-700 opacity-60 hover:opacity-100'
                  }`}
                >
                  {s.image_url ? (
                    <img src={s.image_url} alt="" className="w-full h-full object-cover" />
                  ) : (
                    <div className="w-full h-full bg-slate-950 flex items-center justify-center text-[10px] text-slate-500">
                      #{idx + 1}
                    </div>
                  )}
                  <span className="absolute bottom-0.5 right-0.5 px-1 py-0.2 rounded bg-black/80 text-[9px] font-bold text-white">
                    {idx + 1}
                  </span>
                </button>
              ))}
            </div>
          )}
        </div>

        {/* Right Column: Slide Inspector, Guion & Prompt Audit */}
        <div className="lg:col-span-5 space-y-5">
          {/* Slide Details Card */}
          <div className="p-6 rounded-3xl glass-card space-y-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="text-xs uppercase font-extrabold text-cyan-400 tracking-wider">
                  Lámina #{currentIndex + 1}
                </span>
                <span className="text-[11px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-semibold uppercase">
                  {currentSlide?.slide_type || 'content'}
                </span>
              </div>
              <span className="text-[11px] px-2.5 py-0.5 rounded-full bg-cyan-500/10 text-cyan-300 font-semibold border border-cyan-500/20">
                v{currentSlide?.version || 1}
              </span>
            </div>

            {/* Texto / Guión Oficial en Español */}
            <div className="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-white flex items-center gap-1.5">
                  <FileText className="w-3.5 h-3.5 text-cyan-400" /> Guión & Contenido en Español
                </span>
                <button
                  type="button"
                  onClick={() => {
                    if (currentSlide?.prompt_used) {
                      navigator.clipboard.writeText(currentSlide.prompt_used);
                      setCopiedText(true);
                      setTimeout(() => setCopiedText(false), 2000);
                    }
                  }}
                  className="text-[10px] text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1 cursor-pointer"
                >
                  {copiedText ? <Check className="w-3 h-3 text-emerald-400" /> : <Copy className="w-3 h-3" />}
                  {copiedText ? 'Copiado' : 'Copiar Texto'}
                </button>
              </div>
              <p className="text-xs text-slate-200 leading-relaxed font-sans">
                {currentSlide?.prompt_used || 'Generando contenido...'}
              </p>
            </div>

            {/* Auditoría de Prompt IA con Pre-Prompt */}
            <div className="p-4 rounded-2xl bg-slate-900/90 border border-indigo-500/30 space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-indigo-400 flex items-center gap-1.5">
                  <Sparkles className="w-3.5 h-3.5" /> Prompt Enviado a la IA
                </span>
                <button
                  type="button"
                  onClick={() => {
                    if (currentSlide?.prompt_used) {
                      navigator.clipboard.writeText(currentSlide.prompt_used);
                      setCopiedPrompt(true);
                      setTimeout(() => setCopiedPrompt(false), 2000);
                    }
                  }}
                  className="px-2.5 py-1 rounded-lg bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 text-[10px] font-semibold border border-indigo-500/30 transition-all flex items-center gap-1 cursor-pointer"
                >
                  {copiedPrompt ? <Check className="w-3 h-3 text-emerald-400" /> : <Share2 className="w-3 h-3" />}
                  {copiedPrompt ? 'Copiado' : 'Copiar Prompt'}
                </button>
              </div>
              <div className="p-3 rounded-xl bg-slate-950 border border-slate-800 text-[11px] text-slate-300 font-mono leading-relaxed max-h-36 overflow-y-auto">
                {currentSlide?.prompt_used || 'Generando prompt con OpenAI...'}
              </div>
            </div>

            {/* Granular Single Slide Regeneration */}
            <form onSubmit={handleRegenerateSingle} className="pt-3 border-t border-slate-800 space-y-3">
              <div className="flex items-center justify-between text-xs">
                <span className="font-bold text-slate-300 flex items-center gap-1.5">
                  <RefreshCw className="w-3.5 h-3.5 text-amber-400" /> Regenerar Solo Esta Lámina (#{currentIndex + 1})
                </span>
                <span className="text-amber-300 font-bold">1 crédito</span>
              </div>
              <textarea
                value={singleFeedback}
                onChange={(e) => setSingleFeedback(e.target.value)}
                placeholder="Directiva de ajuste para esta lámina (ej: cambiar iluminación, primer plano de consulta...)"
                rows={2}
                className="w-full p-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:border-cyan-400 focus:outline-none resize-none"
              />
              <button
                type="submit"
                disabled={regeneratingSingle || !singleFeedback.trim()}
                className="w-full py-2.5 rounded-xl font-bold bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black text-xs transition-all flex items-center justify-center gap-2 disabled:opacity-40 cursor-pointer"
              >
                {regeneratingSingle ? 'Regenerando con IA...' : `Rehacer Lámina #${currentIndex + 1} (1 Crédito)`}
              </button>
            </form>
          </div>

          {/* Social Caption & Copy Box */}
          <div className="p-6 rounded-3xl glass-card space-y-3">
            <div className="flex items-center justify-between">
              <h3 className="text-xs uppercase font-extrabold text-cyan-400 tracking-wider">
                Copy para Redes Sociales
              </h3>
              <button
                type="button"
                onClick={() => {
                  if (content.caption_copy) {
                    const full = `${content.caption_copy}\n\n${content.hashtags || ''}`;
                    navigator.clipboard.writeText(full);
                    setCopiedCaption(true);
                    setTimeout(() => setCopiedCaption(false), 2000);
                  }
                }}
                className="px-3 py-1 rounded-lg bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 text-xs font-semibold border border-cyan-500/30 transition-all flex items-center gap-1.5 cursor-pointer"
              >
                {copiedCaption ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
                {copiedCaption ? '¡Copiado!' : 'Copiar Copy Completo'}
              </button>
            </div>
            <div className="p-4 rounded-xl bg-slate-900/80 border border-slate-800 text-xs text-slate-200 leading-relaxed whitespace-pre-line max-h-48 overflow-y-auto">
              {content.caption_copy || 'Generando copy de Instagram...'}
            </div>
            {content.hashtags && (
              <div className="text-xs text-cyan-400 font-semibold font-mono">{content.hashtags}</div>
            )}
          </div>
        </div>
      </div>

      {/* Modal: Regenerar Todo el Carrusel */}
      {showRegenerateAllModal && (
        <div className="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-lg rounded-3xl glass-panel p-8 space-y-6 border border-slate-700 shadow-2xl">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20">
                  <RefreshCw className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="text-lg font-bold text-white">Regenerar Todo el Carrusel</h3>
                  <p className="text-xs text-slate-400">
                    Se recrearán las {content.total_slides} láminas con el motor determinista
                  </p>
                </div>
              </div>
              <button
                onClick={() => setShowRegenerateAllModal(false)}
                className="text-slate-400 hover:text-white cursor-pointer"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleRegenerateAll} className="space-y-4">
              <div className="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-xs text-slate-300 space-y-1">
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
                  Directiva Global / Ajuste Estético (Opcional)
                </label>
                <textarea
                  value={globalFeedback}
                  onChange={(e) => setGlobalFeedback(e.target.value)}
                  placeholder="Ej: Mayor iluminación natural, tonos más suaves, encuadre frontal en todas las láminas..."
                  rows={3}
                  className="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-xs focus:border-cyan-400 focus:outline-none resize-none"
                />
              </div>

              <div className="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setShowRegenerateAllModal(false)}
                  className="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white cursor-pointer"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  disabled={regeneratingAll}
                  className="px-5 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black shadow-lg shadow-amber-500/25 flex items-center gap-2 disabled:opacity-50 cursor-pointer"
                >
                  {regeneratingAll
                    ? 'Procesando...'
                    : `Confirmar y Regenerar (${content.total_slides} Créditos)`}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
