import React, { useState, useEffect, useRef } from 'react';
import api from '../api/client';
import {
  Terminal,
  RefreshCw,
  Trash2,
  AlertTriangle,
  AlertCircle,
  CheckCircle2,
  Info,
  Search,
  Filter,
  Download,
  Clock,
  Server,
  Database,
  Layers,
  ChevronDown,
  ChevronRight
} from 'lucide-react';

interface LogItem {
  id: string;
  timestamp: string;
  level: 'ERROR' | 'WARNING' | 'INFO' | 'SUCCESS';
  service: string;
  message: string;
  details?: string;
}

export const SystemLogs: React.FC = () => {
  const [logs, setLogs] = useState<LogItem[]>([]);
  const [loading, setLoading] = useState(false);
  const [autoRefresh, setAutoRefresh] = useState(true);
  const [levelFilter, setLevelFilter] = useState('ALL');
  const [serviceFilter, setServiceFilter] = useState('ALL');
  const [searchQuery, setSearchQuery] = useState('');
  const [expandedLogId, setExpandedLogId] = useState<string | null>(null);
  const [serverTime, setServerTime] = useState<string>('');
  const [dbStatus, setDbStatus] = useState<string>('ONLINE');

  const timerRef = useRef<any>(null);

  const fetchLogs = async () => {
    try {
      setLoading(true);
      const res = await api.get('/system/logs', {
        params: {
          level: levelFilter,
          service: serviceFilter,
          limit: 150,
        },
      });
      setLogs(res.data.logs || []);
      setServerTime(res.data.server_time || '');
      setDbStatus(res.data.database_status || 'ONLINE');
    } catch (err) {
      console.error('Error fetching system logs:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLogs();
  }, [levelFilter, serviceFilter]);

  useEffect(() => {
    if (autoRefresh) {
      timerRef.current = setInterval(() => {
        fetchLogs();
      }, 5000);
    } else {
      if (timerRef.current) clearInterval(timerRef.current);
    }
    return () => {
      if (timerRef.current) clearInterval(timerRef.current);
    };
  }, [autoRefresh, levelFilter, serviceFilter]);

  const handleClearLogs = async () => {
    if (window.confirm('¿Deseas limpiar los logs en memoria?')) {
      try {
        await api.delete('/system/logs');
        setLogs([]);
        await fetchLogs();
      } catch (e) {
        console.error('Error clearing logs:', e);
      }
    }
  };

  const handleExportLogs = () => {
    const dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(logs, null, 2));
    const downloadAnchor = document.createElement('a');
    downloadAnchor.setAttribute('href', dataStr);
    downloadAnchor.setAttribute('download', `tecnogen_logs_${new Date().toISOString().slice(0, 19)}.json`);
    document.body.appendChild(downloadAnchor);
    downloadAnchor.click();
    downloadAnchor.remove();
  };

  const filteredLogs = logs.filter((log) => {
    const q = searchQuery.toLowerCase();
    return (
      log.message.toLowerCase().includes(q) ||
      log.service.toLowerCase().includes(q) ||
      (log.details && log.details.toLowerCase().includes(q))
    );
  });

  const getLevelBadge = (level: string) => {
    switch (level) {
      case 'ERROR':
        return (
          <span className="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center gap-1">
            <AlertCircle className="w-3 h-3" /> ERROR
          </span>
        );
      case 'WARNING':
        return (
          <span className="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center gap-1">
            <AlertTriangle className="w-3 h-3" /> AVISO
          </span>
        );
      case 'SUCCESS':
        return (
          <span className="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
            <CheckCircle2 className="w-3 h-3" /> OK
          </span>
        );
      default:
        return (
          <span className="px-2 py-0.5 rounded-md text-[10px] font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 flex items-center gap-1">
            <Info className="w-3 h-3" /> INFO
          </span>
        );
    }
  };

  return (
    <div className="p-8 max-w-6xl mx-auto space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white flex items-center gap-2.5">
            <Terminal className="w-6 h-6 text-cyan-400" /> Logs del Sistema & Monitoreo
          </h1>
          <p className="text-sm text-slate-400 mt-1">
            Visor en tiempo real de llamadas a OpenAI, sincronizaciones con Google Sheets, subidas a Drive y errores de ejecución.
          </p>
        </div>

        {/* Top Controls */}
        <div className="flex items-center gap-2.5 flex-wrap">
          <button
            onClick={() => setAutoRefresh(!autoRefresh)}
            className={`px-3 py-2 rounded-xl text-xs font-semibold border flex items-center gap-1.5 transition-all ${
              autoRefresh
                ? 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30 shadow-sm'
                : 'bg-slate-900 text-slate-400 border-slate-800 hover:text-white'
            }`}
          >
            <RefreshCw className={`w-3.5 h-3.5 ${autoRefresh ? 'animate-spin' : ''}`} />
            <span>Auto-refresco {autoRefresh ? '(5s ON)' : '(Pausado)'}</span>
          </button>

          <button
            onClick={fetchLogs}
            disabled={loading}
            className="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800 flex items-center gap-1.5 transition-all"
          >
            <RefreshCw className={`w-3.5 h-3.5 ${loading ? 'animate-spin text-cyan-400' : ''}`} />
            <span>Refrescar</span>
          </button>

          <button
            onClick={handleExportLogs}
            disabled={!logs.length}
            className="px-3 py-2 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800 flex items-center gap-1.5 transition-all"
            title="Exportar JSON"
          >
            <Download className="w-3.5 h-3.5 text-cyan-400" />
            <span>Exportar</span>
          </button>

          <button
            onClick={handleClearLogs}
            className="p-2 rounded-xl bg-slate-900 hover:bg-rose-500/10 text-slate-400 hover:text-rose-400 border border-slate-800 hover:border-rose-500/30 transition-all"
            title="Limpiar logs"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>
      </div>

      {/* System Status Indicators */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div className="p-4 rounded-2xl bg-slate-900/90 border border-slate-800/90 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="p-2 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
              <Server className="w-4 h-4" />
            </div>
            <div>
              <div className="text-[11px] text-slate-400">Servidor Producción</div>
              <div className="text-xs font-bold text-slate-200 font-mono">studio.tecnogen.ar</div>
            </div>
          </div>
          <span className="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
        </div>

        <div className="p-4 rounded-2xl bg-slate-900/90 border border-slate-800/90 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              <Database className="w-4 h-4" />
            </div>
            <div>
              <div className="text-[11px] text-slate-400">Base de Datos</div>
              <div className="text-xs font-bold text-emerald-400 font-mono">{dbStatus}</div>
            </div>
          </div>
          <span className="text-[10px] text-slate-500">Auto-migrada</span>
        </div>

        <div className="p-4 rounded-2xl bg-slate-900/90 border border-slate-800/90 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="p-2 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20">
              <Clock className="w-4 h-4" />
            </div>
            <div>
              <div className="text-[11px] text-slate-400">Hora del Servidor</div>
              <div className="text-xs font-bold text-slate-200 font-mono">
                {serverTime ? new Date(serverTime).toLocaleTimeString() : '--:--:--'}
              </div>
            </div>
          </div>
          <span className="text-[10px] text-slate-500">{filteredLogs.length} eventos</span>
        </div>
      </div>

      {/* Filters Toolbar */}
      <div className="p-4 rounded-2xl bg-slate-900/90 border border-slate-800/90 flex flex-col md:flex-row items-center justify-between gap-3">
        {/* Search */}
        <div className="relative w-full md:w-80">
          <Search className="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Filtrar por texto, error o servicio..."
            className="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-cyan-400 focus:outline-none"
          />
        </div>

        {/* Level Filters */}
        <div className="flex items-center gap-1.5 flex-wrap self-start md:self-auto">
          {['ALL', 'ERROR', 'WARNING', 'SUCCESS', 'INFO'].map((lvl) => (
            <button
              key={lvl}
              onClick={() => setLevelFilter(lvl)}
              className={`px-3 py-1.5 rounded-xl text-xs font-medium transition-all ${
                levelFilter === lvl
                  ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30'
                  : 'bg-slate-950 text-slate-400 border border-slate-800 hover:text-white'
              }`}
            >
              {lvl === 'ALL' ? 'Todos' : lvl}
            </button>
          ))}
        </div>
      </div>

      {/* Logs Console Container */}
      <div className="rounded-3xl bg-slate-950 border border-slate-800/90 overflow-hidden shadow-2xl">
        <div className="px-6 py-3.5 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
          <div className="flex items-center gap-2 text-xs font-mono text-slate-400">
            <span className="w-3 h-3 rounded-full bg-rose-500/60 inline-block"></span>
            <span className="w-3 h-3 rounded-full bg-amber-500/60 inline-block"></span>
            <span className="w-3 h-3 rounded-full bg-emerald-500/60 inline-block"></span>
            <span className="ml-2 font-bold text-slate-300">Consola de Eventos y Diagnóstico</span>
          </div>
          <span className="text-[11px] font-mono text-slate-500">
            Mostrando {filteredLogs.length} registros
          </span>
        </div>

        <div className="divide-y divide-slate-800/60 max-h-[600px] overflow-y-auto font-mono text-xs">
          {filteredLogs.length === 0 ? (
            <div className="p-12 text-center text-slate-500 space-y-2">
              <Terminal className="w-8 h-8 text-slate-600 mx-auto" />
              <p>No hay eventos registrados que coincidan con los filtros seleccionados.</p>
            </div>
          ) : (
            filteredLogs.map((log) => {
              const isExpanded = expandedLogId === log.id;
              const dateObj = new Date(log.timestamp);
              const timeStr = !isNaN(dateObj.getTime())
                ? `${dateObj.toLocaleDateString()} ${dateObj.toLocaleTimeString()}`
                : log.timestamp;

              return (
                <div
                  key={log.id}
                  className={`p-4 hover:bg-slate-900/40 transition-colors ${
                    log.level === 'ERROR' ? 'bg-rose-950/10' : ''
                  }`}
                >
                  <div className="flex items-start justify-between gap-4">
                    <div className="flex items-start gap-3 flex-1 min-w-0">
                      <div className="pt-0.5">{getLevelBadge(log.level)}</div>

                      <div className="space-y-1 min-w-0 flex-1">
                        <div className="flex items-center gap-2 flex-wrap">
                          <span className="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-semibold text-[11px]">
                            {log.service}
                          </span>
                          <span className="text-[11px] text-slate-500">{timeStr}</span>
                        </div>

                        <p className={`text-slate-200 text-xs font-sans break-words ${
                          log.level === 'ERROR' ? 'text-rose-300 font-medium' : ''
                        }`}>
                          {log.message}
                        </p>

                        {/* Expandable Details */}
                        {log.details && (
                          <div className="pt-1.5">
                            <button
                              onClick={() => setExpandedLogId(isExpanded ? null : log.id)}
                              className="text-[11px] text-cyan-400 hover:text-cyan-300 flex items-center gap-1 transition-colors"
                            >
                              {isExpanded ? <ChevronDown className="w-3.5 h-3.5" /> : <ChevronRight className="w-3.5 h-3.5" />}
                              <span>{isExpanded ? 'Ocultar detalle técnico' : 'Ver detalle técnico / traza'}</span>
                            </button>

                            {isExpanded && (
                              <div className="mt-2 p-3 rounded-xl bg-slate-900/90 border border-slate-800 text-[11px] text-slate-300 whitespace-pre-wrap font-mono break-all">
                                {log.details}
                              </div>
                            )}
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              );
            })
          )}
        </div>
      </div>
    </div>
  );
};
