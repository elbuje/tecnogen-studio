import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import { Sparkles, Plus, Layers, Eye, Clock, CheckCircle2, AlertCircle, Coins, ArrowRight, Palette } from 'lucide-react';

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
  const [totalSlides, setTotalSlides] = useState(6);
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

  const handleGenerate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedBrandId || !title) return;
    try {
      setGenerating(true);
      const res = await api.post('/contents/generate', {
        brand_id: selectedBrandId,
        title,
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

  return (
    <div className="p-8 space-y-8 max-w-7xl mx-auto">
      {/* Welcome Banner & Quick Action */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 p-8 rounded-3xl glass-panel relative overflow-hidden">
        <div className="space-y-2 relative z-10">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 text-xs font-semibold">
            <Sparkles className="w-3.5 h-3.5" /> Generador de Contenido Visual con IA
          </div>
          <h1 className="text-3xl font-extrabold text-white tracking-tight">
            Hola, {user?.full_name || 'Creador'}
          </h1>
          <p className="text-sm text-slate-300 max-w-xl">
            Tenés <strong className="text-amber-300 font-bold">{user?.credits_balance} créditos</strong> disponibles.
            Generá carruseles y láminas de alta fidelidad con tu identidad de marca en minutos.
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="px-6 py-3.5 rounded-2xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/25 transition-all flex items-center gap-2 text-sm self-start md:self-auto flex-shrink-0"
        >
          <Plus className="w-5 h-5" /> Crear Nuevo Carrusel
        </button>
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
            <span className="text-xs uppercase font-semibold tracking-wider">Carruseles Creados</span>
            <Layers className="w-5 h-5 text-blue-400" />
          </div>
          <div className="text-3xl font-extrabold text-white">{contents.length}</div>
          <div className="text-xs text-slate-400">Total en el estudio</div>
        </div>
      </div>

      {/* Recent Carousels List */}
      <div className="space-y-4">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-bold text-white">Carruseles & Contenidos Recientes</h2>
          <span className="text-xs text-slate-400">{contents.length} pieza(s)</span>
        </div>

        {contents.length === 0 ? (
          <div className="p-12 rounded-3xl glass-card text-center space-y-4 border border-dashed border-slate-700/80">
            <div className="w-12 h-12 rounded-2xl bg-slate-800 text-slate-400 flex items-center justify-center mx-auto">
              <Layers className="w-6 h-6" />
            </div>
            <div className="text-slate-300 font-medium">Aún no has generado ningún carrusel.</div>
            <button
              onClick={() => setShowModal(true)}
              className="px-5 py-2.5 rounded-xl font-semibold bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 border border-cyan-500/30 text-sm transition-colors"
            >
              Generar mi primer carrusel
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {contents.map((item) => (
              <div
                key={item.id}
                className="p-6 rounded-3xl glass-card space-y-4 border border-slate-800/80 hover:border-cyan-500/40 transition-all flex flex-col justify-between"
              >
                <div className="space-y-3">
                  <div className="flex items-center justify-between text-xs">
                    <span className="px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 font-semibold">
                      {item.total_slides} slides
                    </span>
                    <span
                      className={`px-2.5 py-1 rounded-full font-semibold ${
                        item.status === 'approved'
                          ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                          : item.status === 'ready_for_review'
                          ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30'
                          : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30'
                      }`}
                    >
                      {item.status === 'approved'
                        ? '✅ Aprobado'
                        : item.status === 'ready_for_review'
                        ? '🟡 Para Revisar'
                        : '⚡ Generando'}
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

      {/* Modal: Crear Nuevo Carrusel */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-lg rounded-3xl glass-panel p-8 space-y-6 border border-slate-700/80 shadow-2xl relative">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center border border-cyan-500/20">
                  <Sparkles className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="text-lg font-bold text-white">Generar Carrusel con IA</h3>
                  <p className="text-xs text-slate-400">1 crédito por cada slide generado</p>
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
              <div>
                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                  Marca Destino
                </label>
                <select
                  value={selectedBrandId}
                  onChange={(e) => setSelectedBrandId(e.target.value)}
                  className="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-cyan-400 focus:outline-none"
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
                  Tema o Título del Carrusel
                </label>
                <input
                  type="text"
                  required
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  placeholder="Ej: 5 señales de que tu diente necesita conducto"
                  className="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Cantidad de Slides
                  </label>
                  <select
                    value={totalSlides}
                    onChange={(e) => setTotalSlides(Number(e.target.value))}
                    className="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-cyan-400 focus:outline-none"
                  >
                    {[3, 4, 5, 6, 7, 8, 10].map((n) => (
                      <option key={n} value={n}>
                        {n} láminas ({n} créditos)
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Presencia de Sujeto / Foto
                  </label>
                  <select
                    value={subjectPresence}
                    onChange={(e) => setSubjectPresence(e.target.value)}
                    className="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-cyan-400 focus:outline-none"
                  >
                    <option value="portada-y-cierre">Portada y Cierre</option>
                    <option value="solo-portada">Solo Portada</option>
                    <option value="todas">Todas las láminas</option>
                    <option value="ninguna">Solo Diseño Gráfico</option>
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
                  className="px-6 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25 disabled:opacity-50 flex items-center gap-2"
                >
                  {generating ? 'Generando...' : `Generar (${totalSlides} créditos)`}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
