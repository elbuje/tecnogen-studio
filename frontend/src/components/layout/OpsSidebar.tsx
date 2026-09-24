import React from 'react';
import { NavLink, Link } from 'react-router-dom';
import { 
  LayoutDashboard, 
  Users, 
  Layers, 
  Bot, 
  DollarSign, 
  ShieldCheck, 
  FileText,
  Sparkles,
  ArrowUpRight,
  Headphones
} from 'lucide-react';
import { useAuth } from '../../contexts/AuthContext';

export const OpsSidebar: React.FC = () => {
  const { user, isSuperAdmin } = useAuth();

  const navItems = [
    { to: '/ops', label: 'Executive Overview', icon: LayoutDashboard, end: true },
    { to: '/ops/clients', label: 'Clientes & Planes', icon: Users },
    { to: '/ops/queue', label: 'Cola de Renders', icon: Layers },
    { to: '/ops/agents', label: 'Agentes & Sheets', icon: Bot },
    ...(isSuperAdmin ? [{ to: '/ops/finops', label: 'FinOps & APIs', icon: DollarSign }] : []),
    ...(isSuperAdmin ? [{ to: '/ops/team', label: 'Equipo & Soporte', icon: Headphones }] : []),
    { to: '/ops/logs', label: 'Auditoría & Logs', icon: FileText },
  ];

  return (
    <aside className="w-64 border-r border-purple-900/30 bg-[#060914] flex flex-col justify-between p-4 hidden md:flex">
      <div className="space-y-6">
        {/* Ops Tag */}
        <div className="flex items-center justify-between px-3 py-2 rounded-xl bg-purple-950/40 border border-purple-800/40 text-xs">
          <div className="flex items-center gap-2">
            <ShieldCheck className="w-4 h-4 text-purple-400" />
            <span className="font-bold text-purple-200">
              {user?.role === 'superadmin' ? 'SUPERADMIN HQ' : 'SOPORTE PORTAL'}
            </span>
          </div>
          <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        </div>

        <div className="px-3 text-[11px] font-bold text-purple-400/80 uppercase tracking-widest">
          Control Operativo
        </div>

        <nav className="space-y-1.5">
          {navItems.map((item) => {
            const Icon = item.icon;
            return (
              <NavLink
                key={item.to}
                to={item.to}
                end={item.end}
                className={({ isActive }) =>
                  `flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all ${
                    isActive
                      ? 'bg-purple-600/20 text-purple-300 border border-purple-500/40 shadow-lg shadow-purple-900/20 font-semibold'
                      : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50'
                  }`
                }
              >
                <Icon className="w-4 h-4" />
                <span>{item.label}</span>
              </NavLink>
            );
          })}
        </nav>
      </div>

      <div className="space-y-3">
        {/* Switch back to Studio App */}
        <Link
          to="/app"
          className="flex items-center justify-between p-3 rounded-xl bg-gradient-to-r from-cyan-950/40 to-blue-950/40 border border-cyan-800/40 text-cyan-300 hover:text-cyan-200 hover:bg-cyan-900/40 transition-all text-xs font-semibold group shadow-sm"
        >
          <div className="flex items-center gap-2">
            <Sparkles className="w-4 h-4 text-cyan-400" />
            <span>Volver a TecnoGen Studio</span>
          </div>
          <ArrowUpRight className="w-3.5 h-3.5 text-cyan-400 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" />
        </Link>

        {/* Server & Node Status */}
        <div className="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 text-[11px] text-slate-400 space-y-1">
          <div className="flex items-center justify-between">
            <span>VPS KVM 4 (antig)</span>
            <span className="text-emerald-400 font-mono font-semibold">:8018</span>
          </div>
          <div className="flex items-center justify-between text-slate-500">
            <span>Agentes Orquestador</span>
            <span className="text-purple-300 font-mono">tecnobrain.ar</span>
          </div>
        </div>
      </div>
    </aside>
  );
};
