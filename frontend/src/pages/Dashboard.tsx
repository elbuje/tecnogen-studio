import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import { 
  Sparkles, 
  Plus, 
  Layers, 
  Eye, 
  Clock, 
  CheckCircle2, 
  AlertCircle, 
  Coins, 
  ArrowRight, 
  Palette,
  Video,
  FileImage,
  Play,
  FileSpreadsheet,
  RefreshCw
} from 'lucide-react';

export const Dashboard: React.FC = () => {
  const { user, refreshUser } = useAuth();
  const [brands, setBrands] = useState<any[]>([]);
  const [contents, setContents] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [generating, setGenerating] = useState(false);
  const [showModal, setShowModal] = useState(false);
  const navigate = useNavigate();

  // Generator form
  const [selectedBrandId, setSelectedBrandId] = useState('');
  const [title, setTitle] = useState('');
  const [contentType, setContentType] = useState<'video_reel' | 'carousel' | 'single_post'>('video_reel');
  const [totalSlides, setTotalSlides] = useState(4);
  const [subjectPresence, setSubjectPresence] = useState('portada-y-cierre');

  const fetchData = async () => {
    try {
      setLoading(true);
      const [brandsRes, contentsRes] = await Promise.all([
        api.get('/brands'),
        api.get('/contents'),
      ]);
      setBrands(brandsRes.data);
      if (brandsRes.data.length > 0 && !selectedBrandId) {
        setSelectedBrandId(brandsRes.data[0].id);
      }
      setContents(contentsRes.data);
    } catch (e) {
      console.error('Error fetching dashboard data:', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleOpenModalWithFormat = (type: 'video_reel' | 'carousel' | 'single_post') => {
    setContentType(type);
    if (type === 'video_reel') {
      setTotalSlides(4);
    } else if (type === 'single_post') {
      setTotalSlides(1);
    } else {
      setTotalSlides(6);
    }
    setShowModal(true);
  };

  const handleGenerate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedBrandId || !title) return;
    try {
      setGenerating(true);
      const res = await api.post('/contents/generate', {
        brand_id: selectedBrandId,
        title,
        type: contentType,
        total_slides: Number(totalSlides),
        subject_presence: subjectPresence,
      });
      await refreshUser();
      setShowModal(false);
      navigate(`/app/viewer/${res.data.content_id}`);
    } catch (err: any) {
      alert(err.response?.data?.detail?.message || err.response?.data?.detail || 'Error al iniciar generación.');
    } finally {
      setGenerating(false);
    }
  };

  const [syncingSheet, setSyncingSheet] = useState(false);
  const handleSyncSheet = async () => {
    const brandId = selectedBrandId || (brands.length > 0 ? brands[0].id : null);
    if (!brandId) {
      alert('No hay una marca configurada para sincronizar.');
      return;
    }
    try {
      setSyncingSheet(true);
      const res = await api.post('/integrations/sync-sheet', { brand_id: brandId });
      alert(res.data.detail || `¡Sincronización exitosa! Pendientes procesados: ${res.data.pending_count || 0}`);
      await fetchData();
    } catch (err: any) {
      alert(err.response?.data?.detail || 'Error al sincronizar Google Sheet. Verificá que la URL esté configurada en Integraciones.');
    } finally {
      setSyncingSheet(false);
    }
  };

  return (
    <div className="p-8 space-y-8 max-w-7xl mx-auto">
      {/* Welcome Banner & Quick Action */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 p-8 rounded-3xl glass-panel relative overflow-hidden">
        <div className="space-y-2 relative z-10">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 text-xs font-semibold">
            <Sparkles className="w-3.5 h-3.5" /> Generador de Reels, Videos y Carruseles con IA
          </div>
          <h1 className="text-3xl font-extrabold text-white tracking-tight">
            Hola, {user?.full_name || 'Creador'}
          </h1>
          <p className="text-sm text-slate-300 max-w-xl">
            Tenés <strong className="text-amber-300 font-bold">{user?.credits_balance} créditos</strong> disponibles.
            Generá videos verticales (Reels), guiones con avatar y carruseles editoriales con tu identidad de marca en minutos.
          </p>
        </div>

        <div className="flex flex-wrap items-center gap-3 relative z-10">
          <button
            onClick={handleSyncSheet}
            disabled={syncingSheet}
            className="px-5 py-3 rounded-2xl font-bold bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 shadow-xl shadow-emerald-500/10 transition-all flex items-center gap-2 text-sm disabled:opacity-40"
          >
            <RefreshCw className={`w-4 h-4 ${syncingSheet ? 'animate-spin' : ''}`} />
            {syncingSheet ? 'Sincronizando...' : 'Sincronizar Google Sheet'}
          </button>
          <button
            onClick={() => handleOpenModalWithFormat('video_reel')}
            className="px-5 py-3 rounded-2xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white shadow-xl shadow-purple-600/25 transition-all flex items-center gap-2 text-sm"
          >
            <Video className="w-4 h-4" /> Crear Reel / Video IA
          </button>
          <button
            onClick={() => handleOpenModalWithFormat('carousel')}
            className="px-5 py-3 rounded-2xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/25 transition-all flex items-center gap-2 text-sm"
          >
            <Plus className="w-4 h-4" /> Crear Carrusel
          </button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div className="p-6 rounded-2xl glass-card space-y-2">
          <div className="flex items-center justify-between text-slate-400">
            <span className="text-xs uppercase font-semibold tracking-wider">Saldo de Créditos</span>
            <Coins className="w-5 h-5 text-amber-400" />
          </div>
          <div className="text-3xl font-extrabold text-white">{user?.credits_balance}</div>
          <div className="text-xs text-slate-400">Plan {user?.plan_tier}</div>
        </div>

        <div className="p-6 rounded-2xl glass-card space-y-2">
          <div className="flex items-center justify-between text-slate-400">
            <span className="text-xs uppercase font-semibold tracking-wider">Marcas Activas</span>
            <Palette className="w-5 h-5 text-cyan-400" />
          </div>
          <div className="text-3xl font-extrabold text-white">{brands.length}</div>
          <div className="text-xs text-slate-400">Identidades configuradas</div>
        </div>

        <div className="p-6 rounded-2xl glass-card space-y-2">
          <div className="flex items-center justify-between text-slate-400">
            <span className="text-xs uppercase font-semibold tracking-wider">Contenidos Creados</span>
            <Layers className="w-5 h-5 text-blue-400" />
          </div>
          <div className="text-3xl font-extrabold text-white">{contents.length}</div>
          <div className="text-xs text-slate-400">Reels & Carruseles en estudio</div>
        </div>
      </div>

      {/* Content List */}
      <div className="space-y-4">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-bold text-white tracking-tight flex items-center gap-2">
            <Layers className="w-5 h-5 text-cyan-400" />
            <span>Mis Creaciones Recientes</span>
          </h2>
          <Link
            to="/app/library"
            className="text-xs font-semibold text-cyan-400 hover:text-cyan-300 flex items-center gap-1"
          >
            Ver toda la biblioteca <ArrowRight className="w-3.5 h-3.5" />
          </Link>
        </div>

        {loading ? (
          <div className="p-12 text-center text-slate-400 glass-card rounded-2xl">
            Cargando tus proyectos...
          </div>
        ) : contents.length === 0 ? (
          <div className="p-12 text-center glass-card rounded-2xl space-y-4 border border-slate-800">
            <div className="w-16 h-16 rounded-3xl bg-slate-800/80 text-cyan-400 flex items-center justify-center mx-auto">
              <Sparkles className="w-8 h-8" />
            </div>
            <div className="space-y-1">
              <h3 className="text-base font-bold text-white">Todavía no tenés contenidos generados</h3>
              <p className="text-xs text-slate-400 max-w-sm mx-auto">
                Seleccioná tu marca y creá tu primer Reel en formato vertical o un carrusel de alta conversión.
              </p>
            </div>
            <button
              onClick={() => handleOpenModalWithFormat('video_reel')}
              className="px-5 py-2.5 rounded-xl text-xs font-bold bg-purple-600 hover:bg-purple-500 text-white transition-all shadow-md shadow-purple-600/30"
            >
              Comenzar con un Reel Vertical
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {contents.map((item) => (
              <div
                key={item.id}
                className="p-5 rounded-2xl glass-card hover:border-cyan-500/40 transition-all flex flex-col justify-between space-y-4 group"
              >
                <div className="space-y-3">
                  <div className="flex items-center justify-between">
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-300 border border-slate-700/60">
                      {item.type === 'video_reel' ? <Video className="w-3 h-3 text-purple-400" /> : <Layers className="w-3 h-3 text-cyan-400" />}
                      <span>{item.type === 'video_reel' ? 'Video Reel 9:16' : 'Carrusel'}</span>
                    </span>

                    <span
                      className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold ${
                        item.status === 'ready_for_review' || item.status === 'approved'
                          ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                          : item.status === 'generating'
                          ? 'bg-amber-500/10 text-amber-300 border border-amber-500/20 animate-pulse'
                          : item.status === 'failed'
                          ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                          : 'bg-slate-800 text-slate-400'
                      }`}
                    >
                      {item.status === 'ready_for_review' && <CheckCircle2 className="w-3 h-3" />}
                      {item.status === 'generating' && <Clock className="w-3 h-3" />}
                      {item.status === 'failed' && <AlertCircle className="w-3 h-3" />}
                      <span>{item.status}</span>
                    </span>
                  </div>

                  <h3 className="font-bold text-white text-base leading-snug line-clamp-2">
                    {item.title}
                  </h3>
                  <p className="text-xs text-slate-400 line-clamp-2">
                    {item.hook_text || item.caption_copy}
                  </p>
                </div>

                <div className="pt-4 border-t border-slate-800/80 flex items-center justify-between">
                  <span className="text-[11px] text-slate-500">
                    {new Date(item.created_at).toLocaleDateString()}
                  </span>
                  <Link
                    to={`/app/viewer/${item.id}`}
                    className="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800/80 hover:bg-cyan-500/20 hover:text-cyan-300 text-slate-200 border border-slate-700/60 transition-colors flex items-center gap-1.5"
                  >
                    <Eye className="w-3.5 h-3.5" /> Abrir Visor
                  </Link>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Modal: Crear Nuevo Contenido */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-lg rounded-3xl glass-panel p-8 space-y-6 border border-slate-700/80 shadow-2xl relative">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center border border-purple-500/20">
                  {contentType === 'video_reel' ? <Video className="w-5 h-5" /> : <Sparkles className="w-5 h-5" />}
                </div>
                <div>
                  <h3 className="text-lg font-bold text-white">
                    {contentType === 'video_reel' ? 'Generar Reel / Video con IA' : 'Generar Carrusel con IA'}
                  </h3>
                  <p className="text-xs text-slate-400">
                    {contentType === 'video_reel' ? '4 escenas audiovisuales con guion y avatar' : '1 crédito por cada slide generado'}
                  </p>
                </div>
              </div>
              <button
                onClick={() => setShowModal(false)}
                className="text-slate-400 hover:text-white text-sm"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleGenerate} className="space-y-4">
              {/* Formato Selector Tabs */}
              <div>
                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                  Formato de la Pieza
                </label>
                <div className="grid grid-cols-3 gap-2">
                  <button
                    type="button"
                    onClick={() => { setContentType('video_reel'); setTotalSlides(4); }}
                    className={`py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all ${
                      contentType === 'video_reel'
                        ? 'bg-purple-600/30 text-purple-300 border border-purple-500/50'
                        : 'bg-slate-900 text-slate-400 border border-slate-800'
                    }`}
                  >
                    <Video className="w-3.5 h-3.5" />
                    <span>Reel 9:16</span>
                  </button>

                  <button
                    type="button"
                    onClick={() => { setContentType('carousel'); setTotalSlides(6); }}
                    className={`py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all ${
                      contentType === 'carousel'
                        ? 'bg-cyan-600/30 text-cyan-300 border border-cyan-500/50'
                        : 'bg-slate-900 text-slate-400 border border-slate-800'
                    }`}
                  >
                    <Layers className="w-3.5 h-3.5" />
                    <span>Carrusel</span>
                  </button>

                  <button
                    type="button"
                    onClick={() => { setContentType('single_post'); setTotalSlides(1); }}
                    className={`py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all ${
                      contentType === 'single_post'
                        ? 'bg-blue-600/30 text-blue-300 border border-blue-500/50'
                        : 'bg-slate-900 text-slate-400 border border-slate-800'
                    }`}
                  >
                    <FileImage className="w-3.5 h-3.5" />
                    <span>Post 1:1</span>
                  </button>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                  Marca Destino
                </label>
                <select
                  value={selectedBrandId}
                  onChange={(e) => setSelectedBrandId(e.target.value)}
                  className="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-purple-400 focus:outline-none"
                >
                  {brands.map((b) => (
                    <option key={b.id} value={b.id}>
                      {b.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                  Tema o Título del Video / Pieza
                </label>
                <input
                  type="text"
                  required
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  placeholder={contentType === 'video_reel' ? "Ej: 3 secretos para una sonrisa perfecta sin brackets" : "Ej: 5 señales de que tu diente necesita conducto"}
                  className="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-purple-400 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    {contentType === 'video_reel' ? 'Escenas / Secciones' : 'Cantidad de Slides'}
                  </label>
                  <select
                    value={totalSlides}
                    onChange={(e) => setTotalSlides(Number(e.target.value))}
                    className="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-purple-400 focus:outline-none"
                  >
                    {contentType === 'single_post' ? (
                      <option value={1}>1 Imagen (1 crédito)</option>
                    ) : contentType === 'video_reel' ? (
                      <>
                        <option value={3}>3 Escenas (3 créditos)</option>
                        <option value={4}>4 Escenas - Gancho, Valor, Caso, CTA (4 créditos)</option>
                        <option value={5}>5 Escenas Completas (5 créditos)</option>
                      </>
                    ) : (
                      [3, 4, 5, 6, 7, 8, 10].map((n) => (
                        <option key={n} value={n}>
                          {n} láminas ({n} créditos)
                        </option>
                      ))
                    )}
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Presencia de Sujeto / Avatar
                  </label>
                  <select
                    value={subjectPresence}
                    onChange={(e) => setSubjectPresence(e.target.value)}
                    className="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-purple-400 focus:outline-none"
                  >
                    <option value="portada-y-cierre">Portada y Cierre</option>
                    <option value="todas">Todas las escenas</option>
                    <option value="solo-portada">Solo Portada</option>
                    <option value="ninguna">Solo Diseño / Motion</option>
                  </select>
                </div>
              </div>

              <div className="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  disabled={generating || !title}
                  className="px-6 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white shadow-lg shadow-purple-600/25 disabled:opacity-50 flex items-center gap-2"
                >
                  {generating ? 'Generando...' : `Generar ${contentType === 'video_reel' ? 'Reel' : 'Carrusel'} (${totalSlides} créditos)`}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
