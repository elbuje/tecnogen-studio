import React, { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { User, Shield, Coins, Sparkles, Key, Check, Mail, Lock } from 'lucide-react';

export const Profile: React.FC = () => {
  const { user } = useAuth();
  const [copied, setCopied] = useState(false);

  return (
    <div className="p-8 max-w-4xl mx-auto space-y-8">
      <div className="space-y-1">
        <h1 className="text-2xl font-extrabold text-white">Mi Perfil & Cuenta</h1>
        <p className="text-sm text-slate-400">Administrá tus datos personales, plan de suscripción y seguridad.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* User Card */}
        <div className="p-8 rounded-3xl glass-card space-y-4 text-center flex flex-col items-center">
          <div className="w-20 h-20 rounded-full bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white text-2xl font-bold shadow-xl shadow-cyan-500/20">
            {user?.full_name?.charAt(0) || user?.email?.charAt(0).toUpperCase() || 'U'}
          </div>
          <div>
            <h2 className="text-lg font-bold text-white">{user?.full_name || 'Usuario'}</h2>
            <p className="text-xs text-slate-400">{user?.email}</p>
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
            <button
              onClick={() => alert('Para cambiar de plan, podés hacerlo desde la pestaña de Facturación.')}
              className="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors"
            >
              Mejorar Plan
            </button>
          </div>
        </div>
      </div>

      {/* Account Details Form */}
      <div className="p-8 rounded-3xl glass-card space-y-6">
        <h2 className="text-lg font-bold text-white flex items-center gap-2">
          <User className="w-5 h-5 text-cyan-400" /> Información Personal
        </h2>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              Nombre Completo
            </label>
            <input
              type="text"
              readOnly
              value={user?.full_name || ''}
              className="w-full p-3 rounded-xl bg-slate-900/80 border border-slate-700/80 text-white text-sm"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              Correo Electrónico
            </label>
            <input
              type="email"
              readOnly
              value={user?.email || ''}
              className="w-full p-3 rounded-xl bg-slate-900/80 border border-slate-700/80 text-white text-sm"
            />
          </div>
        </div>
      </div>
    </div>
  );
};
