import React, { useState, useEffect } from 'react';
import api from '../../api/client';
import { useAuth, User } from '../../contexts/AuthContext';
import { 
  Users, 
  Search, 
  Plus, 
  Eye, 
  Edit, 
  ShieldAlert, 
  CheckCircle, 
  PauseCircle, 
  XCircle, 
  Bot, 
  Video, 
  Coins, 
  Sparkles,
  ExternalLink,
  Shield,
  Save,
  X
} from 'lucide-react';

interface ClientItem {
  id: string;
  email: string;
  full_name: string | null;
  role: string;
  commercial_status: string; // 'active', 'trial', 'suspended_payment', 'suspended_manual', 'cancelled'
  plan_tier: string;
  plan_name: string | null;
  plan_price_monthly: number | null;
  monthly_video_limit: number;
  videos_generated_this_month: number;
  avatar_minutes_quota: number;
  avatar_minutes_used: number;
  credits_balance: number;
  auto_mode_enabled: boolean;
  sheet_url: string | null;
  sheet_auto_mode: string;
  sheet_last_sync_at: string | null;
  brands_count: number;
  created_at: string;
}

export const OpsClients: React.FC = () => {
  const { impersonate, isSuperAdmin } = useAuth();
  const [clients, setClients] = useState<ClientItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  
  // Modals
  const [editingClient, setEditingClient] = useState<ClientItem | null>(null);
  const [showCreateModal, setShowCreateModal] = useState(false);
  
  // New Client Form
  const [newClient, setNewClient] = useState({
    email: '',
    password: '',
    full_name: '',
    brand_name: '',
    plan_tier: 'growth',
    plan_name: 'Plan Growth Pro (30 Videos/Mes)',
    plan_price_monthly: 150,
    monthly_video_limit: 30,
    avatar_minutes_quota: 60,
    credits_balance: 220,
    auto_mode_enabled: true,
    sheet_url: '',
    sheet_auto_mode: 'copilot',
    notes: ''
  });

  const fetchClients = async () => {
    try {
      const res = await api.get('/ops/clients');
      setClients(res.data);
    } catch (e) {
      console.error("Error al cargar clientes:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchClients();
  }, []);

  const handleImpersonate = async (clientId: string) => {
    try {
      const res = await api.post(`/ops/clients/${clientId}/impersonate`);
      const { access_token, client_user } = res.data;
      impersonate(access_token, client_user as User);
      window.location.href = '/app';
    } catch (e: any) {
      alert("Error al iniciar impersonación: " + (e.response?.data?.detail || e.message));
    }
  };

  const handleQuickStatusChange = async (client: ClientItem, newStatus: string) => {
    try {
      await api.put(`/ops/clients/${client.id}`, {
        commercial_status: newStatus
      });
      fetchClients();
    } catch (e: any) {
      alert("Error actualizando estado comercial: " + (e.response?.data?.detail || e.message));
    }
  };

  const handleSaveEdit = async () => {
    if (!editingClient) return;
    try {
      await api.put(`/ops/clients/${editingClient.id}`, {
        full_name: editingClient.full_name,
        commercial_status: editingClient.commercial_status,
        plan_name: editingClient.plan_name,
        plan_tier: editingClient.plan_tier,
        plan_price_monthly: Number(editingClient.plan_price_monthly),
        monthly_video_limit: Number(editingClient.monthly_video_limit),
        avatar_minutes_quota: Number(editingClient.avatar_minutes_quota),
        credits_balance: Number(editingClient.credits_balance),
        auto_mode_enabled: editingClient.auto_mode_enabled,
        sheet_url: editingClient.sheet_url,
        sheet_auto_mode: editingClient.sheet_auto_mode
      });
      setEditingClient(null);
      fetchClients();
    } catch (e: any) {
      alert("Error al guardar cambios: " + (e.response?.data?.detail || e.message));
    }
  };

  const handleCreateClient = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await api.post('/ops/clients', newClient);
      setShowCreateModal(false);
      setNewClient({
        email: '',
        password: '',
        full_name: '',
        brand_name: '',
        plan_tier: 'growth',
        plan_name: 'Plan Growth Pro (30 Videos/Mes)',
        plan_price_monthly: 150,
        monthly_video_limit: 30,
        avatar_minutes_quota: 60,
        credits_balance: 220,
        auto_mode_enabled: true,
        sheet_url: '',
        sheet_auto_mode: 'copilot',
        notes: ''
      });
      fetchClients();
    } catch (e: any) {
      alert("Error al crear cliente: " + (e.response?.data?.detail || e.message));
    }
  };

  const filteredClients = clients.filter(c => {
    const matchesSearch = (c.email + ' ' + (c.full_name || '') + ' ' + (c.plan_name || '')).toLowerCase().includes(search.toLowerCase());
    const matchesStatus = statusFilter === 'all' ? true : c.commercial_status.includes(statusFilter);
    return matchesSearch && matchesStatus;
  });

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-white flex items-center gap-2.5">
            <Users className="w-6 h-6 text-purple-400" />
            <span>Gestión Comercial de Clientes & Tenants</span>
          </h1>
          <p className="text-slate-400 text-sm mt-1">
            Altas, bajas, suspensiones comerciales por mora, cuotas de videos, planes y acceso de soporte asistido.
          </p>
        </div>

        {isSuperAdmin && (
          <button
            onClick={() => setShowCreateModal(true)}
            className="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs shadow-lg shadow-purple-600/30 transition-all"
          >
            <Plus className="w-4 h-4" />
            <span>Dar de Alta Cliente / Tenant</span>
          </button>
        )}
      </div>

      {/* Filter and Search Bar */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
        <div className="relative flex-1 max-w-md">
          <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Buscar por email, nombre o plan..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 transition-colors"
          />
        </div>

        <div className="flex items-center gap-2">
          {['all', 'active', 'suspended', 'trial'].map(filter => (
            <button
              key={filter}
              onClick={() => setStatusFilter(filter)}
              className={`px-3 py-1.5 rounded-xl text-xs font-semibold capitalize transition-all ${
                statusFilter === filter
                  ? 'bg-purple-600/30 text-purple-300 border border-purple-500/50'
                  : 'text-slate-400 hover:bg-slate-800'
              }`}
            >
              {filter === 'all' ? 'Todos' : filter === 'active' ? 'Activos' : filter === 'suspended' ? 'Suspendidos' : 'Trial'}
            </button>
          ))}
        </div>
      </div>

      {/* Clients Table */}
      <div className="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden shadow-xl">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs">
            <thead>
              <tr className="border-b border-slate-800 bg-slate-950/70 text-slate-400 uppercase tracking-wider font-bold text-[11px]">
                <th className="py-3.5 px-4">Cliente / Email</th>
                <th className="py-3.5 px-4">Plan & Tarifa</th>
                <th className="py-3.5 px-4">Estado Comercial</th>
                <th className="py-3.5 px-4">Consumo Mensual</th>
                <th className="py-3.5 px-4">Automatización Sheet</th>
                <th className="py-3.5 px-4 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/80">
              {filteredClients.map((client) => {
                const isSuspended = client.commercial_status.includes('suspended');
                const videoPct = Math.min(100, Math.round((client.videos_generated_this_month / (client.monthly_video_limit || 1)) * 100));

                return (
                  <tr key={client.id} className="hover:bg-slate-800/40 transition-colors">
                    {/* Cliente */}
                    <td className="py-4 px-4">
                      <div className="font-bold text-white text-sm">{client.full_name || 'Sin nombre'}</div>
                      <div className="text-slate-400 font-mono text-[11px]">{client.email}</div>
                      <div className="text-[10px] text-slate-500 mt-0.5">{client.brands_count} Marca(s) activa(s)</div>
                    </td>

                    {/* Plan */}
                    <td className="py-4 px-4">
                      <div className="font-semibold text-purple-300">{client.plan_name || client.plan_tier}</div>
                      <div className="text-emerald-400 font-bold text-xs mt-0.5">
                        ${client.plan_price_monthly || 0} <span className="text-[10px] text-slate-400 font-normal">USD/mes</span>
                      </div>
                      <div className="text-[10px] text-slate-400 mt-0.5">{client.credits_balance} créditos de saldo</div>
                    </td>

                    {/* Estado Comercial */}
                    <td className="py-4 px-4">
                      <div className="flex items-center gap-2">
                        <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold ${
                          client.commercial_status === 'active'
                            ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                            : isSuspended
                            ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30'
                            : 'bg-amber-500/20 text-amber-400 border border-amber-500/30'
                        }`}>
                          {client.commercial_status === 'active' && <CheckCircle className="w-3 h-3" />}
                          {isSuspended && <XCircle className="w-3 h-3" />}
                          {client.commercial_status === 'active' ? 'Activo' : client.commercial_status === 'suspended_payment' ? 'Mora / Falta Pago' : client.commercial_status === 'suspended_manual' ? 'Suspendido Manual' : 'En Trial'}
                        </span>

                        {/* Quick switch dropdown for status */}
                        <select
                          value={client.commercial_status}
                          onChange={(e) => handleQuickStatusChange(client, e.target.value)}
                          className="bg-slate-950 border border-slate-800 text-[10px] text-slate-300 rounded px-1.5 py-1 focus:outline-none focus:border-purple-500"
                        >
                          <option value="active">Activar</option>
                          <option value="suspended_payment">Suspender (Mora)</option>
                          <option value="suspended_manual">Suspender (Manual)</option>
                          <option value="trial">Trial</option>
                        </select>
                      </div>
                    </td>

                    {/* Consumo */}
                    <td className="py-4 px-4">
                      <div className="space-y-1 max-w-[140px]">
                        <div className="flex justify-between text-[11px]">
                          <span className="text-slate-300 font-medium">Videos:</span>
                          <span className="font-bold text-white">{client.videos_generated_this_month} / {client.monthly_video_limit}</span>
                        </div>
                        <div className="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                          <div 
                            className={`h-full rounded-full ${videoPct > 90 ? 'bg-rose-500' : 'bg-purple-500'}`}
                            style={{ width: `${videoPct}%` }}
                          ></div>
                        </div>
                        <div className="text-[10px] text-slate-400">
                          Avatar: {client.avatar_minutes_used}/{client.avatar_minutes_quota} mins
                        </div>
                      </div>
                    </td>

                    {/* Sheet Auto Mode */}
                    <td className="py-4 px-4">
                      <div className="space-y-1">
                        <div className="flex items-center gap-1.5">
                          <Bot className="w-3.5 h-3.5 text-indigo-400" />
                          <span className={`font-semibold text-[11px] ${
                            client.sheet_auto_mode === 'autonomous'
                              ? 'text-emerald-400'
                              : client.sheet_auto_mode === 'copilot'
                              ? 'text-cyan-400'
                              : 'text-slate-500'
                          }`}>
                            {client.sheet_auto_mode === 'autonomous' ? '🟢 Piloto Auto' : client.sheet_auto_mode === 'copilot' ? '🟡 Copiloto' : '⚪ Desactivado'}
                          </span>
                        </div>
                        {client.sheet_url && (
                          <a
                            href={client.sheet_url}
                            target="_blank"
                            rel="noreferrer"
                            className="text-[10px] text-slate-400 hover:text-cyan-300 truncate max-w-[120px] block underline"
                          >
                            Ver Sheet Conectado
                          </a>
                        )}
                      </div>
                    </td>

                    {/* Acciones */}
                    <td className="py-4 px-4 text-right">
                      <div className="flex items-center justify-end gap-2">
                        {/* Impersonate button */}
                        <button
                          onClick={() => handleImpersonate(client.id)}
                          title="Impersonar / Entrar como este cliente"
                          className="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-indigo-950/60 hover:bg-indigo-900/80 border border-indigo-700/50 text-indigo-300 text-[11px] font-bold transition-all shadow-sm"
                        >
                          <Eye className="w-3 h-3" />
                          <span>Impersonar</span>
                        </button>

                        {/* Edit Button */}
                        <button
                          onClick={() => setEditingClient(client)}
                          title="Editar cuotas y plan"
                          className="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors"
                        >
                          <Edit className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

      {/* MODAL: Editar Cliente / Cuotas */}
      {editingClient && (
        <div className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-700 rounded-2xl max-w-xl w-full p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <h3 className="text-lg font-bold text-white flex items-center gap-2">
                <Edit className="w-5 h-5 text-purple-400" />
                <span>Editar Cliente & Plan: {editingClient.email}</span>
              </h3>
              <button onClick={() => setEditingClient(null)} className="text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Nombre Completo</label>
                <input
                  type="text"
                  value={editingClient.full_name || ''}
                  onChange={(e) => setEditingClient({ ...editingClient, full_name: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Estado Comercial</label>
                <select
                  value={editingClient.commercial_status}
                  onChange={(e) => setEditingClient({ ...editingClient, commercial_status: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                >
                  <option value="active">Activo (Al día)</option>
                  <option value="trial">Trial de Prueba</option>
                  <option value="suspended_payment">Suspendido (Falta de Pago / Mora)</option>
                  <option value="suspended_manual">Suspendido (Manual)</option>
                  <option value="cancelled">Cancelado</option>
                </select>
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Nombre del Plan</label>
                <input
                  type="text"
                  value={editingClient.plan_name || ''}
                  onChange={(e) => setEditingClient({ ...editingClient, plan_name: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Precio Mensual (USD)</label>
                <input
                  type="number"
                  value={editingClient.plan_price_monthly || 0}
                  onChange={(e) => setEditingClient({ ...editingClient, plan_price_monthly: Number(e.target.value) })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Límite Mensual de Videos</label>
                <input
                  type="number"
                  value={editingClient.monthly_video_limit}
                  onChange={(e) => setEditingClient({ ...editingClient, monthly_video_limit: Number(e.target.value) })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Cuota Minutos Avatar</label>
                <input
                  type="number"
                  value={editingClient.avatar_minutes_quota}
                  onChange={(e) => setEditingClient({ ...editingClient, avatar_minutes_quota: Number(e.target.value) })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Créditos de Saldo</label>
                <input
                  type="number"
                  value={editingClient.credits_balance}
                  onChange={(e) => setEditingClient({ ...editingClient, credits_balance: Number(e.target.value) })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Modo Sheet Automatizado</label>
                <select
                  value={editingClient.sheet_auto_mode}
                  onChange={(e) => setEditingClient({ ...editingClient, sheet_auto_mode: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                >
                  <option value="autonomous">🟢 Piloto Automático (Genera y Renderiza Directo)</option>
                  <option value="copilot">🟡 Modo Copiloto (Genera Borrador y Pide Aprobación)</option>
                  <option value="disabled">⚪ Desactivado</option>
                </select>
              </div>

              <div className="sm:col-span-2 space-y-1">
                <label className="text-slate-300 font-semibold">URL de Google Sheets / Planilla</label>
                <input
                  type="text"
                  value={editingClient.sheet_url || ''}
                  placeholder="https://docs.google.com/spreadsheets/d/..."
                  onChange={(e) => setEditingClient({ ...editingClient, sheet_url: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>
            </div>

            <div className="flex justify-end gap-3 pt-3 border-t border-slate-800">
              <button
                onClick={() => setEditingClient(null)}
                className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold"
              >
                Cancelar
              </button>
              <button
                onClick={handleSaveEdit}
                className="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-md shadow-purple-600/30"
              >
                <Save className="w-3.5 h-3.5" />
                <span>Guardar Cambios</span>
              </button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL: Alta de Nuevo Cliente */}
      {showCreateModal && (
        <div className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <form onSubmit={handleCreateClient} className="bg-slate-900 border border-slate-700 rounded-2xl max-w-xl w-full p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <h3 className="text-lg font-bold text-white flex items-center gap-2">
                <Plus className="w-5 h-5 text-purple-400" />
                <span>Dar de Alta Nuevo Cliente / Tenant</span>
              </h3>
              <button type="button" onClick={() => setShowCreateModal(false)} className="text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Email del Cliente *</label>
                <input
                  type="email"
                  required
                  placeholder="cliente@empresa.com"
                  value={newClient.email}
                  onChange={(e) => setNewClient({ ...newClient, email: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Contraseña Inicial *</label>
                <input
                  type="password"
                  required
                  placeholder="••••••••"
                  value={newClient.password}
                  onChange={(e) => setNewClient({ ...newClient, password: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Nombre / Contacto *</label>
                <input
                  type="text"
                  required
                  placeholder="Dra. Jessica"
                  value={newClient.full_name}
                  onChange={(e) => setNewClient({ ...newClient, full_name: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Nombre de Marca / Clínica</label>
                <input
                  type="text"
                  placeholder="JM Odontología"
                  value={newClient.brand_name}
                  onChange={(e) => setNewClient({ ...newClient, brand_name: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Plan Comercial</label>
                <input
                  type="text"
                  value={newClient.plan_name}
                  onChange={(e) => setNewClient({ ...newClient, plan_name: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Precio Mensual (USD)</label>
                <input
                  type="number"
                  value={newClient.plan_price_monthly}
                  onChange={(e) => setNewClient({ ...newClient, plan_price_monthly: Number(e.target.value) })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Límite Videos / Mes</label>
                <input
                  type="number"
                  value={newClient.monthly_video_limit}
                  onChange={(e) => setNewClient({ ...newClient, monthly_video_limit: Number(e.target.value) })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Modo de Agente</label>
                <select
                  value={newClient.sheet_auto_mode}
                  onChange={(e) => setNewClient({ ...newClient, sheet_auto_mode: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                >
                  <option value="copilot">🟡 Copiloto (Aprobación Requerida)</option>
                  <option value="autonomous">🟢 Piloto 100% Autónomo</option>
                  <option value="disabled">⚪ Desactivado</option>
                </select>
              </div>

              <div className="sm:col-span-2 space-y-1">
                <label className="text-slate-300 font-semibold">URL Planilla Google Sheets (Opcional)</label>
                <input
                  type="text"
                  placeholder="https://docs.google.com/spreadsheets/d/..."
                  value={newClient.sheet_url}
                  onChange={(e) => setNewClient({ ...newClient, sheet_url: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>
            </div>

            <div className="flex justify-end gap-3 pt-3 border-t border-slate-800">
              <button
                type="button"
                onClick={() => setShowCreateModal(false)}
                className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold"
              >
                Cancelar
              </button>
              <button
                type="submit"
                className="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-md shadow-purple-600/30"
              >
                <Plus className="w-3.5 h-3.5" />
                <span>Confirmar Alta</span>
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
};
