import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/client';
import { 
  Layers, 
  RefreshCw, 
  RotateCcw, 
  CheckCircle2, 
  Clock, 
  AlertCircle, 
  Eye, 
  Bot, 
  Sparkles,
  Filter
} from 'lucide-react';

interface QueueItem {
  content_id: string;
  client_id: string;
  client_name: string;
  client_email: string;
  brand_name: string;
  title: string;
  status: string;
  type: string;
  source: string;
  sheet_row_ref: string | null;
  created_at: string;
  updated_at: string;
}

export const OpsQueue: React.FC = () => {
  const [queue, setQueue] = useState<QueueItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [retryingId, setRetryingId] = useState<string | null>(null);
  const [statusFilter, setStatusFilter] = useState('all');

  const fetchQueue = async () => {
    try {
      const res = await api.get('/ops/queue');
      setQueue(res.data);
    } catch (e) {
      console.error("Error al cargar cola de renders:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchQueue();
    // Auto refresh every 10 seconds for real-time monitoring
    const interval = setInterval(fetchQueue, 10000);
    return () => clearInterval(interval);
  }, []);

  const handleRetry = async (contentId: string) => {
    setRetryingId(contentId);
    try {
      await api.post(`/ops/queue/${contentId}/retry`);
      fetchQueue();
    } catch (e: any) {
      alert("Error al reintentar render: " + (e.response?.data?.detail || e.message));
    } finally {
      setRetryingId(null);
    }
  };

  const filteredQueue = queue.filter(item => {
    if (statusFilter === 'all') return true;
    if (statusFilter === 'processing') return ['generating', 'draft', 'copy_approved'].includes(item.status);
    if (statusFilter === 'failed') return item.status === 'failed';
    if (statusFilter === 'completed') return ['ready_for_review', 'approved', 'published'].includes(item.status);
    return true;
  });

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-white flex items-center gap-2.5">
            <Layers className="w-6 h-6 text-purple-400" />
            <span>Cola de Renders & Pipelines en Tiempo Real</span>
          </h1>
          <p className="text-slate-400 text-sm mt-1">
            Supervisión del pipeline de generación de video, síntesis de voz, renderizado de avatares y reintentos.
          </p>
        </div>

        <button
          onClick={fetchQueue}
          className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/80 text-xs font-semibold text-slate-200 transition-all shadow-sm"
        >
          <RefreshCw className="w-3.5 h-3.5 text-purple-400" />
          <span>Actualizar Ahora</span>
        </button>
      </div>

      {/* Filter Tabs */}
      <div className="flex items-center gap-2 p-1.5 rounded-2xl bg-slate-900/80 border border-slate-800 w-fit text-xs">
        <button
          onClick={() => setStatusFilter('all')}
          className={`px-3 py-1.5 rounded-xl font-semibold transition-all ${
            statusFilter === 'all' ? 'bg-purple-600/30 text-purple-300 border border-purple-500/40' : 'text-slate-400 hover:text-white'
          }`}
        >
          Todos ({queue.length})
        </button>
        <button
          onClick={() => setStatusFilter('processing')}
          className={`px-3 py-1.5 rounded-xl font-semibold transition-all ${
            statusFilter === 'processing' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'text-slate-400 hover:text-white'
          }`}
        >
          En Proceso ({queue.filter(q => ['generating', 'draft', 'copy_approved'].includes(q.status)).length})
        </button>
        <button
          onClick={() => setStatusFilter('failed')}
          className={`px-3 py-1.5 rounded-xl font-semibold transition-all ${
            statusFilter === 'failed' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'text-slate-400 hover:text-white'
          }`}
        >
          Fallidos ({queue.filter(q => q.status === 'failed').length})
        </button>
        <button
          onClick={() => setStatusFilter('completed')}
          className={`px-3 py-1.5 rounded-xl font-semibold transition-all ${
            statusFilter === 'completed' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'text-slate-400 hover:text-white'
          }`}
        >
          Completados / Listos ({queue.filter(q => ['ready_for_review', 'approved', 'published'].includes(q.status)).length})
        </button>
      </div>

      {/* Queue Table */}
      <div className="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden shadow-xl">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs">
            <thead>
              <tr className="border-b border-slate-800 bg-slate-950/70 text-slate-400 uppercase tracking-wider font-bold text-[11px]">
                <th className="py-3.5 px-4">Contenido / Título</th>
                <th className="py-3.5 px-4">Cliente & Marca</th>
                <th className="py-3.5 px-4">Origen / Trigger</th>
                <th className="py-3.5 px-4">Estado del Pipeline</th>
                <th className="py-3.5 px-4">Fecha / Hora</th>
                <th className="py-3.5 px-4 text-right">Acción</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/80">
              {filteredQueue.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-8 text-center text-slate-500 text-sm">
                    No hay trabajos registrados en la cola para este filtro.
                  </td>
                </tr>
              ) : (
                filteredQueue.map((item) => {
                  const isFailed = item.status === 'failed';
                  const isGenerating = item.status === 'generating' || item.status === 'draft';
                  const isDone = ['ready_for_review', 'approved', 'published'].includes(item.status);

                  return (
                    <tr key={item.content_id} className="hover:bg-slate-800/40 transition-colors">
                      {/* Titulo */}
                      <td className="py-4 px-4 max-w-xs">
                        <div className="font-bold text-white text-sm truncate">{item.title}</div>
                        <div className="text-slate-500 font-mono text-[10px] truncate">ID: {item.content_id}</div>
                      </td>

                      {/* Cliente */}
                      <td className="py-4 px-4">
                        <div className="font-semibold text-slate-200">{item.client_name}</div>
                        <div className="text-[11px] text-purple-300 font-medium">{item.brand_name}</div>
                      </td>

                      {/* Origen */}
                      <td className="py-4 px-4">
                        <div className="flex items-center gap-1.5">
                          {item.source === 'google_sheet' ? (
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-950/80 border border-indigo-700/50 text-indigo-300 font-semibold text-[10px]">
                              <Bot className="w-3 h-3" />
                              Sheet Fila {item.sheet_row_ref || '#'}
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 font-semibold text-[10px]">
                              <Sparkles className="w-3 h-3" />
                              Manual Studio
                            </span>
                          )}
                        </div>
                      </td>

                      {/* Estado */}
                      <td className="py-4 px-4">
                        <div className="flex items-center gap-2">
                          <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold ${
                            isDone
                              ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                              : isGenerating
                              ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30 animate-pulse'
                              : 'bg-rose-500/20 text-rose-400 border border-rose-500/30'
                          }`}>
                            {isDone && <CheckCircle2 className="w-3 h-3" />}
                            {isGenerating && <Clock className="w-3 h-3 animate-spin" />}
                            {isFailed && <AlertCircle className="w-3 h-3" />}
                            <span>{item.status.toUpperCase()}</span>
                          </span>
                        </div>
                      </td>

                      {/* Fecha */}
                      <td className="py-4 px-4 text-slate-400 text-[11px] font-mono">
                        {new Date(item.created_at).toLocaleString()}
                      </td>

                      {/* Acciones */}
                      <td className="py-4 px-4 text-right">
                        <div className="flex items-center justify-end gap-2">
                          {/* Reintentar */}
                          <button
                            onClick={() => handleRetry(item.content_id)}
                            disabled={retryingId === item.content_id}
                            title="Forzar reintento de renderizado"
                            className="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-purple-950/60 hover:bg-purple-900 border border-purple-700/50 text-purple-300 text-[11px] font-bold transition-all"
                          >
                            <RotateCcw className={`w-3 h-3 ${retryingId === item.content_id ? 'animate-spin' : ''}`} />
                            <span>Reintentar</span>
                          </button>

                          {/* Ver en Studio */}
                          <Link
                            to={`/app/viewer/${item.content_id}`}
                            className="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors"
                            title="Abrir visor"
                          >
                            <Eye className="w-3.5 h-3.5" />
                          </Link>
                        </div>
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
