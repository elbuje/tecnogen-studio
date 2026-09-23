import React from 'react';
import { NavLink } from 'react-router-dom';
import { LayoutDashboard, Palette, Sparkles, Sliders, CreditCard, ExternalLink } from 'lucide-react';
import { useAuth } from '../../contexts/AuthContext';

export const Sidebar: React.FC = () => {
  const { user } = useAuth();

  const navItems = [
    { to: '/app', label: 'Dashboard', icon: LayoutDashboard, end: true },
    { to: '/app/brands', label: 'Mis Marcas & Kits', icon: Palette },
    { to: '/app/ai-settings', label: 'Modelos & IA', icon: Sliders, adminOnly: true },
    { to: '/app/billing', label: 'Créditos & Pagos', icon: CreditCard },
  ];

  return (
    <aside className="w-64 border-r border-slate-800/80 bg-[#070D1E]/60 backdrop-blur-md flex flex-col justify-between p-4 hidden md:flex">
      <div className="space-y-6">
        <div className="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">
          Menú Principal
        </div>
        <nav className="space-y-1.5">
          {navItems.map((item) => {
            if (item.adminOnly && user?.role !== 'admin') return null;
            const Icon = item.icon;
            return (
              <NavLink
                key={item.to}
                to={item.to}
                end={item.end}
                className={({ isActive }) =>
                  `flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all ${
                    isActive
                      ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 shadow-sm'
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

      <div className="p-4 rounded-2xl glass-card space-y-3">
        <div className="flex items-center justify-between text-xs text-slate-400">
          <span>Servidor Dev</span>
          <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        </div>
        <div className="text-[11px] text-slate-400 leading-tight">
          API: <code className="text-cyan-300">:8018</code> | Frontend: <code className="text-cyan-300">:5192</code>
        </div>
      </div>
    </aside>
  );
};
