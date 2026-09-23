import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import {
  Layers,
  Search,
  Trash2,
  Eye,
  CheckCircle2,
  Calendar,
  Sparkles,
  Plus,
  RefreshCw,
  AlertCircle
} from 'lucide-react';

export const Library: React.FC = () => {
  const { user, refreshUser } = useAuth();
  const [contents, setContents] = useState<any[]>([]);
  const [brands, setBrands] = useState<any[]>([]);
  const [selectedBrandId, setSelectedBrandId] = useState<string>('all');
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [loading, setLoading] = useState<boolean>(true);
  const [deletingId, setDeletingId] = useState<string | null>(null);

  const fetchContents = async () => {
    try {
      setLoading(true);
      const [contentsRes, brandsRes] = await Promise.all([
        api.get('/contents'),
        api.get('/brands'),
      ]);
      setContents(contentsRes.data);
      setBrands(brandsRes.data);
    } catch (e) {
      console.error('Error fetching library:', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchContents();
  }, []);

  const handleDelete = async (contentId: string, title: string) => {
    if (!confirm(`¿Estás seguro de que querés eliminar el carrusel "${title}"? Esta acción no se puede deshacer.`)) {
      return;
    }

    try {
      setDeletingId(contentId);
      await api.delete(`/contents/${contentId}`);
      await refreshUser();
      setContents(contents.filter((c) => c.id !== contentId));
    } catch (e) {
      alert('Error al eliminar carrusel.');
    } finally {
      setDeletingId(null);
    }
  };

  const filteredContents = contents.filter((item) => {
    const matchesBrand = selectedBrandId === 'all' || item.brand_id === selectedBrandId;
    const matchesStatus = statusFilter === 'all' || item.status === statusFilter;
    const matchesSearch = item.title.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesBrand && matchesStatus && matchesSearch;
  });

  return (
    <div className="p-8 max-w-7xl mx-auto space-y-8">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white flex items-center gap-2.5">
            <Layers className="w-6 h-6 text-cyan-400" /> Mi Biblioteca de Carruseles
          </h1>
          <p className="text-sm text-slate-400">
            Administrá, visualizá, aprobá y eliminá todas tus piezas generadas con IA.
          </p>
        </div>

        <Link
          to="/app"
          className="px-5 py-2.5 rounded-xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25 flex items-center gap-2 text-xs self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" /> Crear Nuevo
        </Link>
      </div>

      {/* Filter Bar */}
      <div className="p-4 rounded-2xl glass-card flex flex-col md:flex-row items-center justify-between gap-4">
        {/* Search */}
        <div className="relative w-full md:w-80">
          <Search className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Buscar por título o tema..."
            className="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-xs focus:border-cyan-400 focus:outline-none"
          />
        </div>

        {/* Dropdowns */}
        <div className="flex items-center gap-3 w-full md:w-auto">
          {brands.length > 1 && (
            <select
              value={selectedBrandId}
              onChange={(e) => setSelectedBrandId(e.target.value)}
              className="px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none"
            >
              <option value="all">Todas las marcas</option>
              {brands.map((b) => (
                <option key={b.id} value={b.id}>
                  {b.name}
                </option>
              ))}
            </select>
          )}

          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            className="px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none"
          >
            <option value="all">Todos los estados</option>
            <option value="ready_for_review">Para Revisar</option>
            <option value="approved">Aprobados</option>
            <option value="generating">Generando</option>
            <option value="failed">Fallidos</option>
          </select>
        </div>
      </div>

      {/* Grid of Carousels */}
      {loading ? (
        <div className="min-h-[40vh] flex items-center justify-center text-slate-400 text-sm">
          <RefreshCw className="w-5 h-5 animate-spin text-cyan-400 mr-2" /> Cargando biblioteca...
        </div>
      ) : filteredContents.length === 0 ? (
        <div className="p-16 rounded-3xl glass-card text-center space-y-4 border border-dashed border-slate-800">
          <div className="w-14 h-14 rounded-2xl bg-slate-800 text-slate-400 flex items-center justify-center mx-auto">
            <Layers className="w-7 h-7" />
          </div>
          <div className="text-base font-bold text-white">No se encontraron carruseles</div>
          <p className="text-xs text-slate-400 max-w-sm mx-auto">
            {searchQuery || statusFilter !== 'all' || selectedBrandId !== 'all'
              ? 'Probá ajustando los filtros de búsqueda.'
              : 'Comenzá creando tu primera pieza desde el generador.'}
          </p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredContents.map((item) => {
            const firstSlide = item.slides?.[0];
            return (
              <div
                key={item.id}
                className="rounded-3xl glass-card border border-slate-800/80 hover:border-cyan-500/40 transition-all flex flex-col justify-between overflow-hidden group shadow-lg"
              >
                {/* Thumbnail Preview Header */}
                <div className="relative aspect-[16/10] bg-[#0B1E38] overflow-hidden flex items-center justify-center border-b border-slate-800">
                  {firstSlide?.image_url ? (
                    <img
                      src={firstSlide.image_url}
                      alt={item.title}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                  ) : (
                    <div className="text-center p-4 space-y-2">
                      <Sparkles className="w-6 h-6 text-cyan-400 mx-auto animate-pulse" />
                      <span className="text-[11px] text-slate-400">
                        {item.status === 'generating' ? 'Generando láminas...' : 'Sin vista previa'}
                      </span>
                    </div>
                  )}

                  {/* Badges Over Thumbnail */}
                  <div className="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-black/70 backdrop-blur-md text-[10px] font-bold text-white border border-white/10">
                    {item.total_slides} slides
                  </div>

                  <div className="absolute top-3 right-3">
                    <span
                      className={`px-2.5 py-1 rounded-full text-[10px] font-bold backdrop-blur-md border ${
                        item.status === 'approved'
                          ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40'
                          : item.status === 'ready_for_review'
                          ? 'bg-amber-500/20 text-amber-300 border-amber-500/40'
                          : item.status === 'failed'
                          ? 'bg-rose-500/20 text-rose-300 border-rose-500/40'
                          : 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40'
                      }`}
                    >
                      {item.status === 'approved'
                        ? '✅ Aprobado'
                        : item.status === 'ready_for_review'
                        ? '🟡 Para Revisar'
                        : item.status === 'failed'
                        ? '❌ Fallido'
                        : '⚡ Generando'}
                    </span>
                  </div>
                </div>

                {/* Content Info */}
                <div className="p-6 space-y-4 flex-1 flex flex-col justify-between">
                  <div className="space-y-2">
                    <h3 className="font-bold text-white text-base leading-snug line-clamp-2">
                      {item.title}
                    </h3>
                    <p className="text-xs text-slate-400 line-clamp-2">
                      {item.caption_copy || item.hook_text}
                    </p>
                  </div>

                  {/* Actions Footer */}
                  <div className="pt-4 border-t border-slate-800 flex items-center justify-between gap-2">
                    <span className="text-[10px] text-slate-500">
                      {new Date(item.created_at).toLocaleDateString()}
                    </span>

                    <div className="flex items-center gap-2">
                      {/* Delete Button */}
                      <button
                        onClick={() => handleDelete(item.id, item.title)}
                        disabled={deletingId === item.id}
                        title="Eliminar Carrusel"
                        className="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/30 transition-colors disabled:opacity-40"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>

                      {/* Open Viewer */}
                      <Link
                        to={`/app/viewer/${item.id}`}
                        className="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 transition-colors flex items-center gap-1.5"
                      >
                        <Eye className="w-3.5 h-3.5" /> Abrir Visor
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
};
