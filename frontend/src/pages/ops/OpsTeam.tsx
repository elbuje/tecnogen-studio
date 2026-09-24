import React, { useState, useEffect } from 'react';
import api from '../../api/client';
import { Headphones, UserPlus, Shield, CheckCircle, Clock, X, Plus } from 'lucide-react';

interface TeamMember {
  id: string;
  email: string;
  full_name: string | null;
  role: string;
  created_at: string;
}

export const OpsTeam: React.FC = () => {
  const [team, setTeam] = useState<TeamMember[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [newMember, setNewMember] = useState({
    email: '',
    password: '',
    full_name: '',
    role: 'support'
  });

  const fetchTeam = async () => {
    try {
      const res = await api.get('/ops/team');
      setTeam(res.data);
    } catch (e) {
      console.error("Error al cargar equipo:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchTeam();
  }, []);

  const handleCreateMember = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await api.post('/ops/team', newMember);
      setShowModal(false);
      setNewMember({
        email: '',
        password: '',
        full_name: '',
        role: 'support'
      });
      fetchTeam();
    } catch (e: any) {
      alert("Error creando miembro del equipo: " + (e.response?.data?.detail || e.message));
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-white flex items-center gap-2.5">
            <Headphones className="w-6 h-6 text-purple-400" />
            <span>Equipo de Soporte & Administradores</span>
          </h1>
          <p className="text-slate-400 text-sm mt-1">
            Gestión de roles de soporte técnico y accesos para asistencia a clientes.
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs shadow-lg shadow-purple-600/30 transition-all"
        >
          <UserPlus className="w-4 h-4" />
          <span>Registrar Agente de Soporte</span>
        </button>
      </div>

      {/* Team List */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        {team.map((member) => (
          <div key={member.id} className="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
            <div className="flex items-center justify-between">
              <div className="w-10 h-10 rounded-xl bg-purple-600/20 text-purple-300 flex items-center justify-center font-bold text-sm border border-purple-500/30">
                {member.full_name?.charAt(0) || 'U'}
              </div>
              <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${
                member.role === 'superadmin'
                  ? 'bg-purple-900/60 text-purple-300 border border-purple-500/50'
                  : 'bg-indigo-900/60 text-indigo-300 border border-indigo-500/50'
              }`}>
                {member.role}
              </span>
            </div>

            <div>
              <div className="font-bold text-white text-sm">{member.full_name || 'Sin Nombre'}</div>
              <div className="text-xs text-slate-400 font-mono mt-0.5">{member.email}</div>
            </div>

            <div className="text-[11px] text-slate-500 flex items-center gap-1.5 pt-2 border-t border-slate-800/80">
              <Clock className="w-3.5 h-3.5" />
              <span>Miembro desde: {new Date(member.created_at).toLocaleDateString()}</span>
            </div>
          </div>
        ))}
      </div>

      {/* Modal Registrar Miembro */}
      {showModal && (
        <div className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <form onSubmit={handleCreateMember} className="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-5 shadow-2xl">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <h3 className="text-lg font-bold text-white flex items-center gap-2">
                <UserPlus className="w-5 h-5 text-purple-400" />
                <span>Registrar Agente del Equipo</span>
              </h3>
              <button type="button" onClick={() => setShowModal(false)} className="text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="space-y-3 text-xs">
              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Email *</label>
                <input
                  type="email"
                  required
                  placeholder="soporte@tecnobrain.com.ar"
                  value={newMember.email}
                  onChange={(e) => setNewMember({ ...newMember, email: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Contraseña *</label>
                <input
                  type="password"
                  required
                  placeholder="••••••••"
                  value={newMember.password}
                  onChange={(e) => setNewMember({ ...newMember, password: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Nombre Completo *</label>
                <input
                  type="text"
                  required
                  placeholder="Agente Soporte N1"
                  value={newMember.full_name}
                  onChange={(e) => setNewMember({ ...newMember, full_name: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="space-y-1">
                <label className="text-slate-300 font-semibold">Rol Asignado</label>
                <select
                  value={newMember.role}
                  onChange={(e) => setNewMember({ ...newMember, role: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-purple-500"
                >
                  <option value="support">Soporte Técnico (Asistencia & Renders)</option>
                  <option value="admin">Administrador Operativo</option>
                  <option value="superadmin">SuperAdmin (Acceso Total)</option>
                </select>
              </div>
            </div>

            <div className="flex justify-end gap-3 pt-3 border-t border-slate-800">
              <button
                type="button"
                onClick={() => setShowModal(false)}
                className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold"
              >
                Cancelar
              </button>
              <button
                type="submit"
                className="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-md shadow-purple-600/30"
              >
                <Plus className="w-3.5 h-3.5" />
                <span>Crear Usuario</span>
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
};
