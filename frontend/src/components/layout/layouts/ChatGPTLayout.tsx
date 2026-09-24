import React, { useState } from 'react';
import { NavLink, Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../../contexts/AuthContext';
import { ThemeSwitcher } from '../../common/ThemeSwitcher';
import {
  Sparkles,
  Layers,
  Palette,
  Sliders,
  CreditCard,
  Link2,
  User,
  Plus,
  Coins,
  LogOut,
  ChevronDown,
  Bot,
  PanelLeftClose,
  PanelLeft,
  FolderOpen
} from 'lucide-react';

export const ChatGPTLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [sidebarOpen, setSidebarOpen] = useState(true);

  const navItems = [
    { to: '/app', label: 'Nuevo Carrusel', icon: Plus, end: true },
    { to: '/app/library', label: 'Biblioteca & Generados', icon: Layers },
    { to: '/app/brands', label: 'Brand Kits & Layouts', icon: Palette },
    { to: '/app/integrations', label: 'Google Drive & APIs', icon: Link2 },
    { to: '/app/ai-settings', label: 'Configuración de Modelos', icon: Sliders },
    { to: '/app/billing', label: 'Planes & Créditos', icon: CreditCard },
    { to: '/app/profile', label: 'Mi Perfil', icon: User },
  ];

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <div className="min-h-screen flex bg-[#0D0D0D] text-[#ECECEC] font-sans antialiased">
      {/* OpenAI / ChatGPT Style Left Sidebar */}
      <aside
        className={`${
          sidebarOpen ? 'w-64' : 'w-0 -translate-x-full'
        } transition-all duration-200 bg-[#171717] flex flex-col justify-between overflow-hidden shrink-0 z-30 border-r border-[#212121]`}
      >
        <div className="p-3 flex flex-col gap-4">
          {/* Header & Collapse Toggle */}
          <div className="flex items-center justify-between px-2 pt-1 pb-1">
            <Link to="/app" className="flex items-center gap-2">
              <div className="w-6 h-6 rounded-full bg-[#10A37F] flex items-center justify-center text-white font-bold text-xs">
                <Bot className="w-3.5 h-3.5" />
              </div>
              <span className="font-semibold text-sm tracking-tight text-white">
                ChatGPT <span className="text-[#10A37F] font-normal text-xs">Canvas</span>
              </span>
            </Link>
            <button
              onClick={() => setSidebarOpen(false)}
              className="text-[#9B9B9B] hover:text-white p-1 rounded-md hover:bg-[#212121]"
              title="Cerrar panel"
            >
              <PanelLeftClose className="w-4 h-4" />
            </button>
          </div>

          {/* New Chat / Project Button */}
          <Link
            to="/app"
            className="flex items-center justify-between px-3 py-2 rounded-lg bg-[#212121] hover:bg-[#2F2F2F] text-white text-xs font-semibold border border-[#303030] transition-colors"
          >
            <span className="flex items-center gap-2">
              <Plus className="w-4 h-4 text-[#10A37F]" />
              Nuevo Carrusel
            </span>
            <span className="text-[10px] text-[#8E8E8E] font-mono">⌘K</span>
          </Link>

          {/* Navigation Links */}
          <div className="space-y-1">
            <div className="px-2 text-[10px] font-semibold text-[#666] uppercase tracking-wider mb-2">
              Navegación & Herramientas
            </div>
            {navItems.map((item) => {
              const Icon = item.icon;
              return (
                <NavLink
                  key={item.to}
                  to={item.to}
                  end={item.end}
                  className={({ isActive }) =>
                    `flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors ${
                      isActive
                        ? 'bg-[#212121] text-white font-semibold'
                        : 'text-[#B4B4B4] hover:text-white hover:bg-[#1E1E1E]'
                    }`
                  }
                >
                  <Icon className="w-4 h-4 text-[#9B9B9B]" />
                  <span>{item.label}</span>
                </NavLink>
              );
            })}
          </div>
        </div>

        {/* User Footer Profile */}
        <div className="p-3 border-t border-[#212121] bg-[#141414]">
          <div className="flex items-center justify-between">
            <Link to="/app/profile" className="flex items-center gap-2.5 min-w-0">
              <div className="w-8 h-8 rounded-full bg-[#10A37F]/20 text-[#10A37F] border border-[#10A37F]/40 flex items-center justify-center text-xs font-bold shrink-0">
                {user?.full_name?.charAt(0) || 'U'}
              </div>
              <div className="truncate">
                <div className="text-xs font-medium text-white truncate">{user?.full_name || 'Usuario'}</div>
                <div className="text-[10px] text-[#8E8E8E] truncate">TecnoGen Pro</div>
              </div>
            </Link>
            <button
              onClick={handleLogout}
              title="Cerrar Sesión"
              className="text-[#8E8E8E] hover:text-rose-400 p-1.5 rounded-lg hover:bg-[#212121]"
            >
              <LogOut className="w-4 h-4" />
            </button>
          </div>
        </div>
      </aside>

      {/* Main Focus Area */}
      <div className="flex-1 flex flex-col min-w-0 bg-[#0D0D0D]">
        {/* Top Floating Model Selector Bar */}
        <header className="h-12 border-b border-[#212121] px-4 md:px-6 flex items-center justify-between bg-[#0D0D0D]/90 backdrop-blur-md sticky top-0 z-20">
          <div className="flex items-center gap-3">
            {!sidebarOpen && (
              <button
                onClick={() => setSidebarOpen(true)}
                className="text-[#9B9B9B] hover:text-white p-1 rounded-md hover:bg-[#212121] mr-1"
                title="Abrir panel"
              >
                <PanelLeft className="w-4 h-4" />
              </button>
            )}

            {/* OpenAI Model Pill */}
            <div className="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#171717] border border-[#262626] text-xs font-semibold text-white hover:bg-[#212121] cursor-pointer">
              <Sparkles className="w-3.5 h-3.5 text-[#10A37F]" />
              <span>TecnoGen Studio 4.0</span>
              <ChevronDown className="w-3 h-3 text-[#8E8E8E]" />
            </div>
          </div>

          <div className="flex items-center gap-3">
            {/* Credits Counter */}
            <Link
              to="/app/billing"
              className="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#171717] border border-[#2A2A2A] text-xs font-medium text-white hover:border-[#10A37F]/50 transition-colors"
            >
              <Coins className="w-3.5 h-3.5 text-amber-400" />
              <span className="font-bold">{user?.credits_balance || 0}</span>
              <span className="text-[#8E8E8E] text-[10px]">créditos</span>
            </Link>

            {/* Theme / Look Switcher */}
            <ThemeSwitcher />
          </div>
        </header>

        {/* Main Canvas Viewport */}
        <main className="flex-1 overflow-y-auto p-4 md:p-6 bg-[#0D0D0D]">
          <div className="max-w-6xl mx-auto">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
};
