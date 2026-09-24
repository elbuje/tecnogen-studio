import React, { useState, useEffect } from 'react';
import api from '../../api/client';
import { FileText, RefreshCw, Shield, Clock } from 'lucide-react';

interface AuditLogItem {
  id: string;
  user_email: string;
  target_email: string | null;
  action: string;
  details: string;
  created_at: string;
}

export const OpsLogs: React.FC = () => {
  const [logs, setLogs] = useState<AuditLogItem[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchLogs = async () => {
    try {
      const res = await api.get('/ops/audit-logs');
      setLogs(res.data);
    } catch (e) {
      console.error("Error al cargar logs de auditoría:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLogs();
  }, []);

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-white flex items-center gap-2.5">
            <FileText className="w-6 h-6 text-purple-400" />
            <span>Auditoría & Trazabilidad de Acciones</span>
          </h1>
          <p className="text-slate-400 text-sm mt-1">
            Registro inmutable de accesos de soporte, impersonaciones, modificaciones comerciales y disparos de agentes.
          </p>
        </div>

        <button
          onClick={fetchLogs}
          className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/80 text-xs font-semibold text-slate-200 transition-all shadow-sm"
        >
          <RefreshCw className="w-3.5 h-3.5 text-purple-400" />
          <span>Actualizar Logs</span>
        </button>
      </div>

      {/* Logs Table */}
      <div className="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden shadow-xl">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs">
            <thead>
              <tr className="border-b border-slate-800 bg-slate-950/70 text-slate-400 uppercase tracking-wider font-bold text-[11px]">
                <th className="py-3.5 px-4">Operador / Usuario</th>
                <th className="py-3.5 px-4">Acción</th>
                <th className="py-3.5 px-4">Cliente Afectado</th>
                <th className="py-3.5 px-4">Detalles</th>
                <th className="py-3.5 px-4">Timestamp</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/80 font-mono">
              {logs.length === 0 ? (
                <tr>
                  <td colSpan={5} className="py-8 text-center text-slate-500 font-sans text-sm">
                    No hay eventos de auditoría registrados todavía.
                  </td>
                </tr>
              ) : (
                logs.map((log) => (
                  <tr key={log.id} className="hover:bg-slate-800/40 transition-colors text-[11px]">
                    <td className="py-3.5 px-4 text-purple-300 font-bold">
                      {log.user_email}
                    </td>
                    <td className="py-3.5 px-4">
                      <span className="px-2 py-0.5 rounded bg-slate-800 text-slate-200 font-bold">
                        {log.action}
                      </span>
                    </td>
                    <td className="py-3.5 px-4 text-cyan-300">
                      {log.target_email || '-'}
                    </td>
                    <td className="py-3.5 px-4 text-slate-300 font-sans">
                      {log.details}
                    </td>
                    <td className="py-3.5 px-4 text-slate-400 text-[10px]">
                      {new Date(log.created_at).toLocaleString()}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
