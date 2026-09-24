import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/client';
import { 
  Users, 
  DollarSign, 
  Video, 
  Layers, 
  Bot, 
  AlertCircle, 
  CheckCircle2, 
  ArrowUpRight, 
  RefreshCw,
  Clock,
  Sparkles
} from 'lucide-react';

interface OverviewStats {
  total_clients: number;
  active_clients: number;
  suspended_clients: number;
  trial_clients: number;
  mrr_total_usd: number;
  total_videos_month: number;
  total_avatar_minutes_month: number;
  queue_pending: number;
  queue_failed: number;
  agents_autonomous_active: number;
}

export const OpsOverview: React.FC = () => {
  const [stats, setStats] = useState<OverviewStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchStats = async () => {
    try {
      const res = await api.get('/ops/overview');
      setStats(res.data);
    } catch (e) {
      console.error("Error al cargar métricas de Ops:", e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchStats();
  }, []);

  const handleRefresh = () => {
    setRefreshing(true);
    fetchStats();
  };

  return (
    <div className="space-y-8">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl md:text-3xl font-black tracking-tight text-white flex items-center gap-3">
            <span>Executive Overview & Control</span>
            <span className="px-2.5 py-0.5 rounded-full bg-purple-900/60 border border-purple-500/50 text-purple-300 text-xs font-semibold uppercase tracking-wider">
              HQ Live
            </span>
          </h1>
          <p className="text-slate-400 text-sm mt-1">
            Panel de supervisión global de TecnoGen Studio: finanzas, clientes, colas de render y agentes autónomos.
          </p>
        </div>

        <div className="flex items-center gap-3">
          <button
            onClick={handleRefresh}
            disabled={refreshing}
            className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/80 text-xs font-semibold text-slate-200 transition-all shadow-sm"
          >
            <RefreshCw className={`w-3.5 h-3.5 text-purple-400 ${refreshing ? 'animate-spin' : ''}`} />
            <span>Actualizar Métricas</span>
          </button>
          <Link
            to="/ops/clients"
            className="flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold transition-all shadow-lg shadow-purple-600/30"
          >
            <span>Gestionar Clientes</span>
            <ArrowUpRight className="w-3.5 h-3.5" />
          </Link>
        </div>
      </div>

      {/* KPI Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {/* MRR Card */}
        <div className="p-5 rounded-2xl bg-gradient-to-br from-slate-900/90 to-purple-950/30 border border-purple-900/40 space-y-3 relative overflow-hidden group">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-purple-300 uppercase tracking-wider">MRR Proyectado</span>
            <div className="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-300 flex items-center justify-center">
              <DollarSign className="w-4 h-4" />
            </div>
          </div>
          <div className="flex items-baseline gap-2">
            <span className="text-3xl font-black text-white">
              ${stats ? stats.mrr_total_usd.toLocaleString() : '---'}
            </span>
            <span className="text-xs text-purple-400 font-semibold">USD / mes</span>
          </div>
          <div className="text-xs text-slate-400 flex items-center gap-1.5">
            <span className="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span>Facturación de clientes activos</span>
          </div>
        </div>

        {/* Clientes Activos */}
        <div className="p-5 rounded-2xl bg-gradient-to-br from-slate-900/90 to-slate-900/50 border border-slate-800 space-y-3 relative overflow-hidden">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">Clientes & Tenants</span>
            <div className="w-8 h-8 rounded-xl bg-cyan-500/20 text-cyan-300 flex items-center justify-center">
              <Users className="w-4 h-4" />
            </div>
          </div>
          <div className="flex items-baseline gap-2">
            <span className="text-3xl font-black text-white">{stats ? stats.total_clients : '---'}</span>
            <span className="text-xs text-slate-400">totales</span>
          </div>
          <div className="flex items-center gap-2 text-xs">
            <span className="text-emerald-400 font-bold">{stats ? stats.active_clients : 0} Activos</span>
            <span className="text-slate-600">•</span>
            <span className="text-rose-400 font-bold">{stats ? stats.suspended_clients : 0} Suspendidos</span>
          </div>
        </div>

        {/* Videos y Render */}
        <div className="p-5 rounded-2xl bg-gradient-to-br from-slate-900/90 to-slate-900/50 border border-slate-800 space-y-3 relative overflow-hidden">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">Videos este Mes</span>
            <div className="w-8 h-8 rounded-xl bg-blue-500/20 text-blue-300 flex items-center justify-center">
              <Video className="w-4 h-4" />
            </div>
          </div>
          <div className="flex items-baseline gap-2">
            <span className="text-3xl font-black text-white">{stats ? stats.total_videos_month : '---'}</span>
            <span className="text-xs text-blue-400 font-semibold">generados</span>
          </div>
          <div className="text-xs text-slate-400">
            {stats ? stats.total_avatar_minutes_month : 0} mins consumidos de avatar
          </div>
        </div>

        {/* Agentes Autónomos */}
        <div className="p-5 rounded-2xl bg-gradient-to-br from-slate-900/90 to-indigo-950/30 border border-indigo-900/40 space-y-3 relative overflow-hidden">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-indigo-300 uppercase tracking-wider">Agentes Autónomos</span>
            <div className="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-300 flex items-center justify-center">
              <Bot className="w-4 h-4" />
            </div>
          </div>
          <div className="flex items-baseline gap-2">
            <span className="text-3xl font-black text-white">{stats ? stats.agents_autonomous_active : '---'}</span>
            <span className="text-xs text-indigo-300 font-semibold">conectados</span>
          </div>
          <div className="text-xs text-indigo-300/80 flex items-center gap-1.5">
            <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>agentes.tecnobrain.ar sincronizado</span>
          </div>
        </div>
      </div>

      {/* Operations Quick Status & Queues */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Render Queue Alert & Quick Access */}
        <div className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-base font-bold text-white flex items-center gap-2">
              <Layers className="w-4 h-4 text-purple-400" />
              <span>Estado de la Cola de Renders</span>
            </h2>
            <Link to="/ops/queue" className="text-xs text-purple-400 hover:text-purple-300 font-semibold flex items-center gap-1">
              <span>Ver Cola</span>
              <ArrowUpRight className="w-3 h-3" />
            </Link>
          </div>

          <div className="space-y-3">
            <div className="flex items-center justify-between p-3 rounded-xl bg-slate-950/60 border border-slate-800/80">
              <div className="flex items-center gap-2.5">
                <Clock className="w-4 h-4 text-amber-400" />
                <span className="text-xs text-slate-300 font-medium">Trabajos en Procesamiento</span>
              </div>
              <span className="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 text-xs font-bold">
                {stats ? stats.queue_pending : 0}
              </span>
            </div>

            <div className="flex items-center justify-between p-3 rounded-xl bg-slate-950/60 border border-slate-800/80">
              <div className="flex items-center gap-2.5">
                <AlertCircle className="w-4 h-4 text-rose-400" />
                <span className="text-xs text-slate-300 font-medium">Errores / Requieren Reintento</span>
              </div>
              <span className={`px-2 py-0.5 rounded-md text-xs font-bold ${stats && stats.queue_failed > 0 ? 'bg-rose-500/20 text-rose-300 animate-pulse' : 'bg-slate-800 text-slate-400'}`}>
                {stats ? stats.queue_failed : 0}
              </span>
            </div>
          </div>
        </div>

        {/* System Health / External APIs */}
        <div className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-base font-bold text-white flex items-center gap-2">
              <Sparkles className="w-4 h-4 text-cyan-400" />
              <span>Salud de Proveedores IA</span>
            </h2>
            <Link to="/ops/finops" className="text-xs text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
              <span>FinOps</span>
              <ArrowUpRight className="w-3 h-3" />
            </Link>
          </div>

          <div className="space-y-2.5 text-xs">
            <div className="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
              <span className="text-slate-300 font-medium">OpenAI / GPT-4o-mini (Copy)</span>
              <span className="flex items-center gap-1 text-emerald-400 font-semibold">
                <CheckCircle2 className="w-3.5 h-3.5" /> 100% Operativo
              </span>
            </div>
            <div className="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
              <span className="text-slate-300 font-medium">HeyGen Avatar API</span>
              <span className="flex items-center gap-1 text-emerald-400 font-semibold">
                <CheckCircle2 className="w-3.5 h-3.5" /> Saldo OK
              </span>
            </div>
            <div className="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60">
              <span className="text-slate-300 font-medium">ElevenLabs Voice AI</span>
              <span className="flex items-center gap-1 text-emerald-400 font-semibold">
                <CheckCircle2 className="w-3.5 h-3.5" /> Latencia 210ms
              </span>
            </div>
          </div>
        </div>

        {/* Primary Client Spotlight */}
        <div className="p-6 rounded-2xl bg-gradient-to-br from-purple-950/40 via-slate-900/80 to-slate-900/70 border border-purple-900/50 space-y-4">
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold text-purple-300 uppercase tracking-wider">Cliente Principal Activo</span>
            <span className="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold">Activo</span>
          </div>

          <div>
            <div className="text-sm font-bold text-white">Dra. Jessica / JM Odontología</div>
            <div className="text-xs text-slate-400 font-mono">mmujica@tecnobrain.com.ar</div>
          </div>

          <div className="p-3 rounded-xl bg-slate-950/80 border border-slate-800 space-y-1 text-xs">
            <div className="flex justify-between text-slate-300">
              <span>Plan:</span>
              <span className="font-semibold text-purple-300">Growth Pro ($150/mes)</span>
            </div>
            <div className="flex justify-between text-slate-300">
              <span>Cuota Videos:</span>
              <span className="font-semibold text-white">6 / 30 videos</span>
            </div>
            <div className="flex justify-between text-slate-300">
              <span>Modo Sheet:</span>
              <span className="font-semibold text-cyan-300">Copiloto Asistido</span>
            </div>
          </div>

          <Link
            to="/ops/clients"
            className="w-full flex items-center justify-center gap-2 py-2 rounded-xl bg-purple-600/30 hover:bg-purple-600/50 text-purple-200 border border-purple-500/40 text-xs font-semibold transition-all"
          >
            <span>Ver Ficha Completa del Cliente</span>
            <ArrowUpRight className="w-3.5 h-3.5" />
          </Link>
        </div>
      </div>
    </div>
  );
};
