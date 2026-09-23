import React, { useState, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import api from '../api/client';
import { User, Shield, Coins, Sparkles, Key, Check, Mail, Lock, Building, Save } from 'lucide-react';

export const Profile: React.FC = () => {
  const { user, refreshUser } = useAuth();
  const [fullName, setFullName] = useState('');
  const [email, setEmail] = useState('');
  const [brandName, setBrandName] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [saving, setSaving] = useState(false);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);

  useEffect(() => {
    if (user) {
      setFullName(user.full_name || '');
      setEmail(user.email || '');
      // Cargar nombre de marca
      api.get('/auth/me').then((res) => {
        if (res.data.brand_name) {
          setBrandName(res.data.brand_name);
        }
      });
    }
  }, [user]);

  const handleSaveProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setSuccessMsg(null);
    try {
      await api.put('/auth/profile', {
        full_name: fullName,
        email: email,
        brand_name: brandName,
        new_password: newPassword || null,
      });
      await refreshUser();
      setNewPassword('');
      setSuccessMsg('¡Tus datos y nombre de marca fueron actualizados con éxito!');
      setTimeout(() => setSuccessMsg(null), 4000);
    } catch (err: any) {
      alert(err.response?.data?.detail || 'Error al actualizar perfil.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="p-8 max-w-4xl mx-auto space-y-8">
      <div className="space-y-1">
        <h1 className="text-2xl font-extrabold text-white">Mi Perfil & Cuenta</h1>
        <p className="text-sm text-slate-400">Editá tus datos personales, marca asociada y credenciales de acceso.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* User Card */}
        <div className="p-8 rounded-3xl glass-card space-y-4 text-center flex flex-col items-center">
          <div className="w-20 h-20 rounded-full bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white text-2xl font-bold shadow-xl shadow-cyan-500/20">
            {fullName?.charAt(0) || email?.charAt(0).toUpperCase() || 'U'}
          </div>
          <div>
            <h2 className="text-lg font-bold text-white">{fullName || 'Usuario'}</h2>
            <p className="text-xs text-slate-400">{email}</p>
            {brandName && (
              <p className="text-xs text-cyan-300 font-semibold mt-1">🏷️ {brandName}</p>
            )}
          </div>
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-cyan-300 text-xs font-semibold uppercase tracking-wider">
            Rol: {user?.role}
          </div>
        </div>

        {/* Subscription & Credits */}
        <div className="p-8 rounded-3xl glass-card space-y-4 col-span-2 flex flex-col justify-between">
          <div className="space-y-3">
            <div className="flex items-center justify-between">
              <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Plan Actual</span>
              <span className="px-3 py-1 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold uppercase tracking-wider">
                Plan {user?.plan_tier}
              </span>
            </div>
            <div className="text-3xl font-extrabold text-white flex items-center gap-2">
              <Coins className="w-7 h-7 text-amber-400" />
              {user?.credits_balance} <span className="text-sm font-normal text-slate-400">créditos disponibles</span>
            </div>
            <p className="text-xs text-slate-300">
              Cada crédito te permite generar o regenerar 1 lámina individual de alta resolución con IA.
            </p>
          </div>

          <div className="pt-4 border-t border-slate-800 flex items-center justify-between">
            <span className="text-xs text-slate-500">Renovación automática mensual</span>
            <a
              href="/app/billing"
              className="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors"
            >
              Comprar Más Créditos
            </a>
          </div>
        </div>
      </div>

      {/* Editable Form */}
      <form onSubmit={handleSaveProfile} className="p-8 rounded-3xl glass-card space-y-6">
        <h2 className="text-lg font-bold text-white flex items-center gap-2">
          <User className="w-5 h-5 text-cyan-400" /> Editar Información Personal & Marca
        </h2>

        {successMsg && (
          <div className="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
            <Check className="w-4 h-4 text-emerald-400" />
            <span>{successMsg}</span>
          </div>
        )}

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              Nombre Completo / Titular
            </label>
            <input
              type="text"
              required
              value={fullName}
              onChange={(e) => setFullName(e.target.value)}
              placeholder="Ej: Dra. Jessica"
              className="w-full p-3.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-cyan-400 focus:outline-none"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              Nombre de la Marca Principal
            </label>
            <div className="relative">
              <Building className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                required
                value={brandName}
                onChange={(e) => setBrandName(e.target.value)}
                placeholder="Ej: JM Odontología Integral"
                className="w-full pl-10 pr-4 py-3.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-cyan-400 focus:outline-none"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              Correo Electrónico
            </label>
            <div className="relative">
              <Mail className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input
                type="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="correo@ejemplo.com"
                className="w-full pl-10 pr-4 py-3.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-cyan-400 focus:outline-none"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              Cambiar Contraseña (Opcional)
            </label>
            <div className="relative">
              <Lock className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input
                type="password"
                value={newPassword}
                onChange={(e) => setNewPassword(e.target.value)}
                placeholder="Dejar vacío para mantener la actual"
                className="w-full pl-10 pr-4 py-3.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-cyan-400 focus:outline-none"
              />
            </div>
          </div>
        </div>

        <div className="pt-4 border-t border-slate-800 flex justify-end">
          <button
            type="submit"
            disabled={saving}
            className="px-8 py-3.5 rounded-2xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/25 flex items-center gap-2 text-sm disabled:opacity-50"
          >
            <Save className="w-4 h-4" /> {saving ? 'Guardando...' : 'Guardar Cambios de Perfil'}
          </button>
        </div>
      </form>
    </div>
  );
};
