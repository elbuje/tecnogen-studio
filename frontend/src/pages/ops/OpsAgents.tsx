import React, { useState, useEffect } from 'react';
import api from '../../api/client';
import { 
  Bot, 
  Play, 
  CheckCircle2, 
  ExternalLink, 
  FileSpreadsheet, 
  Code2, 
  Sparkles, 
  RefreshCw,
  Copy,
  Check,
  Send
} from 'lucide-react';

interface AgentConfig {
  client_id: string;
  client_email: string;
  client_name: string;
  auto_mode_enabled: boolean;
  sheet_url: string;
  sheet_auto_mode: string;
  sheet_last_sync_at: string | null;
  monthly_videos_done: string;
  status: string;
}

export const OpsAgents: React.FC = () => {
  const [agents, setAgents] = useState<AgentConfig[]>([]);
  const [loading, setLoading] = useState(true);
  const [copied, setCopied] = useState(false);

  // Manual Trigger Form
  const [testClientEmail, setTestClientEmail] = useState('mmujica@tecnobrain.com.ar');
  const [testTopic, setTestTopic] = useState('Implantes Dentales sin Dolor: Mitos y Realidades');
  const [testRowRef, setTestRowRef] = useState('102');
  const [testMode, setTestMode] = useState('copilot'); // 'autonomous' or 'copilot'
  const [triggering, setTriggering] = useState(false);
  const [triggerResult, setTriggerResult] = useState<any>(null);
  const [syncingEmail, setSyncingEmail] = useState<string | null>(null);

  const handleSyncSheet = async (email: string) => {
    try {
      setSyncingEmail(email);
      const res = await api.post(`/ops/agents/sync-sheet?client_email=${encodeURIComponent(email)}`);
      alert(res.data.message || 'Sincronización completada.');
      fetchAgents();
    } catch (e: any) {
      alert('Error sincronizando sheet: ' + (e.response?.data?.detail || e.message));
    } finally {
      setSyncingEmail(null);
    }
  };

  const fetchAgents = async () => {
    try {
      const res = await api.get('/ops/agents');
      setAgents(res.data);
    } catch (e) {
      console.error("Error al cargar agentes:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAgents();
  }, []);

  const handleTestTrigger = async (e: React.FormEvent) => {
    e.preventDefault();
    setTriggering(true);
    setTriggerResult(null);
    try {
      const res = await api.post('/ops/agents/trigger-job', {
        client_email: testClientEmail,
        topic: testTopic,
        sheet_row_ref: testRowRef,
        mode: testMode,
        format: 'carousel'
      });
      setTriggerResult(res.data);
      fetchAgents();
    } catch (e: any) {
      alert("Error al disparar agente: " + (e.response?.data?.detail || e.message));
    } finally {
      setTriggering(false);
    }
  };

  const curlSnippet = `curl -X POST "https://studio.tecnogen.nippur.cloud/api/v1/ops/agents/trigger-job" \\
  -H "Content-Type: application/json" \\
  -H "Authorization: Bearer <TU_API_TOKEN_MAESTRO>" \\
  -d '{
    "client_email": "mmujica@tecnobrain.com.ar",
    "topic": "Implantes Dentales en 24 Horas",
    "sheet_row_ref": "45",
    "mode": "autonomous",
    "format": "carousel"
  }'`;

  const copyToClipboard = () => {
    navigator.clipboard.writeText(curlSnippet);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="space-y-8">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-black text-white flex items-center gap-2.5">
          <Bot className="w-6 h-6 text-indigo-400" />
          <span>Orquestador de Agentes Autónomos & Google Sheets</span>
        </h1>
        <p className="text-slate-400 text-sm mt-1">
          Configuración y monitoreo de agentes en <code className="text-purple-300 font-mono">agentes.tecnobrain.ar</code> que leen planillas de clientes y disparan la generación automática o asistida.
        </p>
      </div>

      {/* Architecture Overview Banner */}
      <div className="p-6 rounded-2xl bg-gradient-to-r from-indigo-950/40 via-purple-950/30 to-slate-900 border border-indigo-800/40 space-y-4">
        <div className="flex items-center gap-2">
          <Sparkles className="w-5 h-5 text-indigo-400" />
          <h2 className="text-base font-bold text-white">Flujo de Ejecución de 2 Modos</h2>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
          <div className="p-4 rounded-xl bg-slate-950/70 border border-emerald-500/30 space-y-2">
            <div className="flex items-center justify-between">
              <span className="font-bold text-emerald-400 flex items-center gap-1.5">
                <span className="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                Modo 100% Autónomo (Piloto Automático)
              </span>
              <span className="text-[10px] bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded">Hands-free</span>
            </div>
            <p className="text-slate-300 leading-relaxed">
              El agente detecta la fila en el Google Sheet del cliente ➔ Genera guion, voz y render completo ➔ Guarda el video final y actualiza la fila con el enlace listo para publicar.
            </p>
          </div>

          <div className="p-4 rounded-xl bg-slate-950/70 border border-cyan-500/30 space-y-2">
            <div className="flex items-center justify-between">
              <span className="font-bold text-cyan-400 flex items-center gap-1.5">
                <span className="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                Modo Copiloto (Aprobación Requerida)
              </span>
              <span className="text-[10px] bg-cyan-500/20 text-cyan-300 px-2 py-0.5 rounded">Revisión Humana</span>
            </div>
            <p className="text-slate-300 leading-relaxed">
              El agente lee la novedad del Sheet ➔ Genera el borrador en TecnoGen Studio en estado <code className="text-cyan-300 font-mono">ready_for_review</code> ➔ Notifica al cliente para que revise y apruebe con 1 clic.
            </p>
          </div>
        </div>
      </div>

      {/* Connected Clients Watchers */}
      <div className="space-y-4">
        <h3 className="text-base font-bold text-white flex items-center gap-2">
          <FileSpreadsheet className="w-4 h-4 text-emerald-400" />
          <span>Planillas Conectadas por Cliente</span>
        </h3>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {agents.map((agent) => (
            <div key={agent.client_id} className="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
              <div className="flex items-center justify-between">
                <div>
                  <div className="font-bold text-white text-sm">{agent.client_name}</div>
                  <div className="text-xs text-slate-400 font-mono">{agent.client_email}</div>
                </div>
                <span className={`px-2.5 py-1 rounded-full text-xs font-bold ${
                  agent.sheet_auto_mode === 'autonomous'
                    ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
                    : 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/40'
                }`}>
                  {agent.sheet_auto_mode === 'autonomous' ? 'Piloto Auto' : 'Copiloto'}
                </span>
              </div>

              <div className="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-1.5 text-xs">
                <div className="flex justify-between text-slate-400">
                  <span>Videos generados mes:</span>
                  <span className="font-bold text-white">{agent.monthly_videos_done}</span>
                </div>
                <div className="flex justify-between text-slate-400">
                  <span>Última sincronización:</span>
                  <span className="text-purple-300 font-mono">{agent.sheet_last_sync_at ? new Date(agent.sheet_last_sync_at).toLocaleString() : 'Pendiente primer disparo'}</span>
                </div>
                <div className="truncate pt-1 border-t border-slate-900 flex items-center justify-between">
                  <a
                    href={agent.sheet_url}
                    target="_blank"
                    rel="noreferrer"
                    className="text-cyan-400 hover:underline flex items-center gap-1 text-[11px]"
                  >
                    <span>Abrir Planilla</span>
                    <ExternalLink className="w-3 h-3" />
                  </a>
                  <button
                    onClick={() => handleSyncSheet(agent.client_email)}
                    disabled={syncingEmail === agent.client_email}
                    className="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[11px] flex items-center gap-1 transition-all shadow-md shadow-indigo-600/20"
                  >
                    <RefreshCw className={`w-3 h-3 ${syncingEmail === agent.client_email ? 'animate-spin' : ''}`} />
                    <span>{syncingEmail === agent.client_email ? 'Sincronizando...' : 'Sincronizar Sheet'}</span>
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Manual Trigger / Test Lab */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Formulario de Disparo de Prueba */}
        <div className="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
          <div className="flex items-center gap-2">
            <Play className="w-4 h-4 text-purple-400" />
            <h3 className="text-base font-bold text-white">Disparador de Prueba / Simulación de Sheet</h3>
          </div>
          <p className="text-xs text-slate-400">
            Simula el llamado que hace el agente desde <code className="text-purple-300">agentes.tecnobrain.ar</code> al detectar una nueva fila.
          </p>

          <form onSubmit={handleTestTrigger} className="space-y-4 text-xs">
            <div className="space-y-1">
              <label className="text-slate-300 font-semibold">Cliente Destino</label>
              <select
                value={testClientEmail}
                onChange={(e) => setTestClientEmail(e.target.value)}
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
              >
                {agents.map(a => (
                  <option key={a.client_email} value={a.client_email}>{a.client_name} ({a.client_email})</option>
                ))}
              </select>
            </div>

            <div className="space-y-1">
              <label className="text-slate-300 font-semibold">Tema / Título de la Novedad (Columna Sheet)</label>
              <input
                type="text"
                required
                value={testTopic}
                onChange={(e) => setTestTopic(e.target.value)}
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
              />
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Fila Ref. (#)</label>
                <input
                  type="text"
                  value={testRowRef}
                  onChange={(e) => setTestRowRef(e.target.value)}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Modo de Ejecución</label>
                <select
                  value={testMode}
                  onChange={(e) => setTestMode(e.target.value)}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                >
                  <option value="copilot">🟡 Copiloto (Borrador Listo)</option>
                  <option value="autonomous">🟢 Autónomo (Render Completo)</option>
                </select>
              </div>
            </div>

            <button
              type="submit"
              disabled={triggering}
              className="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold transition-all shadow-lg shadow-purple-600/30"
            >
              <Send className={`w-4 h-4 ${triggering ? 'animate-bounce' : ''}`} />
              <span>{triggering ? 'Procesando con IA...' : 'Simular Disparo del Agente'}</span>
            </button>
          </form>

          {triggerResult && (
            <div className="p-4 rounded-xl bg-emerald-950/40 border border-emerald-700/50 space-y-2 text-xs">
              <div className="flex items-center gap-2 text-emerald-400 font-bold">
                <CheckCircle2 className="w-4 h-4" />
                <span>¡Generación Disparada Exitosamente!</span>
              </div>
              <div className="text-slate-300">
                <div><strong>Job ID:</strong> <span className="font-mono text-white">{triggerResult.job_id}</span></div>
                <div><strong>Modo:</strong> <span className="text-purple-300 capitalize">{triggerResult.mode_executed}</span></div>
              </div>
              <div className="pt-2">
                <a
                  href={triggerResult.viewer_url}
                  className="inline-flex items-center gap-1 text-cyan-400 hover:underline font-bold"
                >
                  <span>Ver Contenido Generado en Viewer</span>
                  <ExternalLink className="w-3 h-3" />
                </a>
              </div>
            </div>
          )}
        </div>

        {/* API Endpoint & Integration Spec */}
        <div className="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <Code2 className="w-4 h-4 text-cyan-400" />
              <h3 className="text-base font-bold text-white">Endpoint para agentes.tecnobrain.ar</h3>
            </div>
            <button
              onClick={copyToClipboard}
              className="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] transition-colors"
            >
              {copied ? <Check className="w-3 h-3 text-emerald-400" /> : <Copy className="w-3 h-3" />}
              <span>{copied ? 'Copiado' : 'Copiar cURL'}</span>
            </button>
          </div>

          <p className="text-xs text-slate-400">
            Pega este llamado en el script / cron / worker en Python de <code className="text-purple-300">agentes.tecnobrain.ar</code> para enviar las novedades de las filas del Google Sheet.
          </p>

          <pre className="p-4 rounded-xl bg-slate-950 border border-slate-800 font-mono text-[11px] text-purple-200 overflow-x-auto leading-relaxed">
            {curlSnippet}
          </pre>
        </div>
      </div>
    </div>
  );
};
