import React from 'react';
import { NavLink, Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../../contexts/AuthContext';
import { ThemeSwitcher } from '../../common/ThemeSwitcher';
import {
  LayoutDashboard,
  Layers,
  Palette,
  Sliders,
  CreditCard,
  Link2,
  User,
  PlusCircle,
  Coins,
  LogOut,
  Sparkles,
  FolderSync,
  Wand2
} from 'lucide-react';

export const HeyGenLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  const navRailItems = [
    { to: '/app', label: 'Estudio', icon: LayoutDashboard, end: true },
    { to: '/app/library', label: 'Biblioteca', icon: Layers },
    { to: '/app/brands', label: 'Kits Marca', icon: Palette },
    { to: '/app/integrations', label: 'Drive & APIs', icon: Link2 },
    { to: '/app/ai-settings', label: 'Modelos IA', icon: Sliders },
    { to: '/app/billing', label: 'Créditos', icon: CreditCard },
    { to: '/app/profile', label: 'Mi Perfil', icon: User },
  ];

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const getPageTitle = () => {
    const p = location.pathname;
    if (p === '/app') return 'Estudio Creativo / Generador';
    if (p === '/app/library') return 'Biblioteca de Carruseles & Assets';
    if (p === '/app/brands') return 'Brand Kits & Layouts';
    if (p === '/app/integrations') return 'Integraciones Cloud & Storage';
    if (p === '/app/ai-settings') return 'Motor de IA & Modelos';
    if (p === '/app/billing') return 'Créditos & Facturación';
    if (p === '/app/profile') return 'Mi Perfil & Ajustes';
    if (p.startsWith('/app/viewer')) return 'Editor de Carrusel / Render';
    return 'Creative Studio';
  };

  return (
    <div className="min-h-screen flex bg-[#0A0A0E] text-slate-100 font-sans">
      {/* Far-Left Icon Rail (HeyGen/Canva style) */}
      <aside className="w-[72px] bg-[#121218] border-r border-[#242432] flex flex-col items-center py-4 justify-between z-30 shrink-0">
        <div className="flex flex-col items-center gap-6 w-full">
          {/* Studio Brand Icon */}
          <Link
            to="/app"
            className="w-11 h-11 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-purple-600/30 hover:scale-105 transition-transform"
            title="TecnoGen Studio"
          >
            <Sparkles className="w-5 h-5 text-white" />
          </Link>

          {/* Quick Create Action */}
          <Link
            to="/app"
            className="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-300 border border-purple-500/40 flex items-center justify-center hover:bg-purple-500 hover:text-white transition-all shadow-sm"
            title="Nuevo Carrusel"
          >
            <PlusCircle className="w-5 h-5" />
          </Link>

          {/* Icon Navigation Stack */}
          <nav className="flex flex-col items-center gap-2.5 w-full px-2">
            {navRailItems.map((item) => {
              const Icon = item.icon;
              return (
                <NavLink
                  key={item.to}
                  to={item.to}
                  end={item.end}
                  className={({ isActive }) =>
                    `group relative w-12 h-12 rounded-xl flex flex-col items-center justify-center transition-all ${
                      isActive
                        ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30'
                        : 'text-slate-400 hover:text-white hover:bg-[#1E1E28]'
                    }`
                  }
                >
                  <Icon className="w-5 h-5" />
                  <span className="text-[9px] mt-0.5 tracking-tight font-medium scale-90">
                    {item.label}
                  </span>

                  {/* Tooltip */}
                  <div className="absolute left-[78px] px-2.5 py-1 rounded-lg bg-[#1E1E28] text-white text-xs font-semibold whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity shadow-xl border border-slate-700 z-50">
                    {item.label}
                  </div>
                </NavLink>
              );
            })}
          </nav>
        </div>

        {/* Bottom User Avatar */}
        <div className="flex flex-col items-center gap-3">
          <Link
            to="/app/profile"
            className="w-9 h-9 rounded-full bg-purple-600/30 border border-purple-500/40 text-purple-300 flex items-center justify-center text-xs font-bold hover:scale-105 transition-transform"
            title="Mi Perfil"
          >
            {user?.full_name?.charAt(0) || 'U'}
          </Link>
          <button
            onClick={handleLogout}
            title="Cerrar Sesión"
            className="w-8 h-8 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-slate-800/50 flex items-center justify-center transition-colors"
          >
            <LogOut className="w-4 h-4" />
          </button>
        </div>
      </aside>

      {/* Main Studio Viewport */}
      <div className="flex-1 flex flex-col min-w-0 bg-[#0B0B10]">
        {/* Top Studio Control Bar */}
        <header className="h-14 bg-[#121218]/90 backdrop-blur-md border-b border-[#222230] px-6 flex items-center justify-between z-20">
          <div className="flex items-center gap-3">
            <span className="text-xs font-bold text-purple-400 uppercase tracking-wider px-2 py-0.5 rounded bg-purple-950/60 border border-purple-800/50">
              Canva / HeyGen Studio Mode
            </span>
            <div className="h-4 w-px bg-slate-800" />
            <span className="text-sm font-semibold text-slate-200">
              {getPageTitle()}
            </span>
          </div>

          <div className="flex items-center gap-3">
            {/* Quick Action */}
            <Link
              to="/app"
              className="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold shadow-md shadow-purple-600/25 transition-all"
            >
              <Wand2 className="w-3.5 h-3.5" /> + Generar Contenido
            </Link>

            {/* Credits Counter */}
            <Link
              to="/app/billing"
              className="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#1A1A24] border border-[#2D2D3E] text-xs font-medium text-slate-200 hover:border-purple-500/40 transition-colors"
            >
              <Coins className="w-3.5 h-3.5 text-amber-400" />
              <span className="font-bold text-white">{user?.credits_balance || 0}</span>
              <span className="text-slate-400 text-[10px]">créditos</span>
            </Link>

            {/* Theme / Look Switcher */}
            <ThemeSwitcher />
          </div>
        </header>

        {/* Studio Canvas Area with Frame */}
        <main className="flex-1 overflow-y-auto p-4 md:p-6 bg-[#0E0E14] relative">
          <div className="max-w-7xl mx-auto">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
};
