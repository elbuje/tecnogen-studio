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
  FolderTree,
  ChevronRight,
  BookOpen,
  Settings2,
  PanelLeftClose,
  PanelLeft
} from 'lucide-react';

export const ClaudeLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [sidebarOpen, setSidebarOpen] = useState(true);

  const navSections = [
    {
      title: 'Espacios de Trabajo',
      items: [
        { to: '/app', label: 'Estudio de Redacción & IA', icon: Sparkles, end: true },
        { to: '/app/library', label: 'Proyectos & Biblioteca', icon: Layers },
        { to: '/app/brands', label: 'Manuales de Marca & Kits', icon: Palette },
      ],
    },
    {
      title: 'Configuraciones & Conectividad',
      items: [
        { to: '/app/integrations', label: 'Drive & Conectores', icon: Link2 },
        { to: '/app/ai-settings', label: 'Modelos & Llaves API', icon: Sliders },
        { to: '/app/billing', label: 'Créditos & Suscripción', icon: CreditCard },
        { to: '/app/profile', label: 'Ajustes de Cuenta', icon: User },
      ],
    },
  ];

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const getPageTitle = () => {
    const p = location.pathname;
    if (p === '/app') return 'Nuevo Carrusel & Generación';
    if (p === '/app/library') return 'Biblioteca de Documentos';
    if (p === '/app/brands') return 'Kits de Marca & Identidad';
    if (p === '/app/integrations') return 'Integraciones Cloud';
    if (p === '/app/ai-settings') return 'Ajustes del Modelo';
    if (p === '/app/billing') return 'Créditos de Uso';
    if (p === '/app/profile') return 'Perfil de Usuario';
    if (p.startsWith('/app/viewer')) return 'Visor de Contenido';
    return 'Espacio de Trabajo';
  };

  return (
    <div className="min-h-screen flex bg-[#171614] text-[#E5E0D8] font-sans antialiased">
      {/* Claude Style Left Workspace Sidebar */}
      <aside
        className={`${
          sidebarOpen ? 'w-64' : 'w-0 -translate-x-full'
        } transition-all duration-300 bg-[#1C1B17] border-r border-[#2A2823] flex flex-col justify-between overflow-hidden shrink-0 z-30`}
      >
        <div className="p-4 flex flex-col gap-6">
          {/* Anthropic / Claude Cowork Header */}
          <div className="flex items-center justify-between pb-2 border-b border-[#2A2823]">
            <Link to="/app" className="flex items-center gap-2 text-[#E5E0D8]">
              <div className="w-7 h-7 rounded-lg bg-[#D97706]/20 border border-[#D97706]/40 flex items-center justify-center text-[#F59E0B]">
                <Sparkles className="w-4 h-4" />
              </div>
              <span className="font-semibold text-sm tracking-tight text-[#E5E0D8]">
                Claude <span className="text-[#D97706] font-serif italic">Cowork</span>
              </span>
            </Link>
            <button
              onClick={() => setSidebarOpen(false)}
              className="text-[#9C9488] hover:text-[#E5E0D8] p-1 rounded-md hover:bg-[#25241F]"
              title="Ocultar barra lateral"
            >
              <PanelLeftClose className="w-4 h-4" />
            </button>
          </div>

          {/* New Project CTA (Claude style) */}
          <Link
            to="/app"
            className="flex items-center gap-2.5 px-3.5 py-2 rounded-xl bg-[#282620] hover:bg-[#323028] border border-[#3E3C33] text-[#E5E0D8] text-xs font-semibold shadow-sm transition-all"
          >
            <Plus className="w-4 h-4 text-[#D97706]" />
            <span>Iniciar Nuevo Carrusel</span>
          </Link>

          {/* Navigation Sections */}
          <div className="space-y-6">
            {navSections.map((section) => (
              <div key={section.title} className="space-y-1.5">
                <div className="px-2 text-[11px] font-medium text-[#8C8477] uppercase tracking-wider">
                  {section.title}
                </div>
                <div className="space-y-0.5">
                  {section.items.map((item) => {
                    const Icon = item.icon;
                    return (
                      <NavLink
                        key={item.to}
                        to={item.to}
                        end={item.end}
                        className={({ isActive }) =>
                          `flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-medium transition-colors ${
                            isActive
                              ? 'bg-[#D97706]/15 text-[#F59E0B] font-semibold'
                              : 'text-[#B5ADA0] hover:text-[#E5E0D8] hover:bg-[#24231E]'
                          }`
                        }
                      >
                        <Icon className="w-3.5 h-3.5" />
                        <span>{item.label}</span>
                      </NavLink>
                    );
                  })}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* User Card at bottom */}
        <div className="p-3 border-t border-[#2A2823] bg-[#191814] flex items-center justify-between">
          <Link to="/app/profile" className="flex items-center gap-2.5 min-w-0">
            <div className="w-7 h-7 rounded-full bg-[#D97706]/20 border border-[#D97706]/30 text-[#F59E0B] flex items-center justify-center text-xs font-bold shrink-0">
              {user?.full_name?.charAt(0) || 'U'}
            </div>
            <div className="truncate">
              <div className="text-xs font-medium text-[#E5E0D8] truncate">{user?.full_name || 'Usuario'}</div>
              <div className="text-[10px] text-[#8C8477] truncate">Plan {user?.plan_tier}</div>
            </div>
          </Link>
          <button
            onClick={handleLogout}
            title="Cerrar Sesión"
            className="text-[#8C8477] hover:text-rose-400 p-1.5 rounded-lg hover:bg-[#25241F]"
          >
            <LogOut className="w-3.5 h-3.5" />
          </button>
        </div>
      </aside>

      {/* Main Editorial Document Canvas */}
      <div className="flex-1 flex flex-col min-w-0">
        {/* Top Minimal Header */}
        <header className="h-12 border-b border-[#2A2823] px-6 flex items-center justify-between bg-[#171614]/90 backdrop-blur-md sticky top-0 z-20">
          <div className="flex items-center gap-3">
            {!sidebarOpen && (
              <button
                onClick={() => setSidebarOpen(true)}
                className="text-[#9C9488] hover:text-[#E5E0D8] p-1 rounded-md hover:bg-[#25241F] mr-1"
                title="Abrir barra lateral"
              >
                <PanelLeft className="w-4 h-4" />
              </button>
            )}
            <div className="flex items-center gap-2 text-xs text-[#9C9488]">
              <span>TecnoGen Cowork</span>
              <ChevronRight className="w-3 h-3 text-[#6E675C]" />
              <span className="text-[#E5E0D8] font-medium">{getPageTitle()}</span>
            </div>
          </div>

          <div className="flex items-center gap-3">
            {/* Credits Counter */}
            <Link
              to="/app/billing"
              className="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#22201B] border border-[#333029] text-xs text-[#D97706]"
            >
              <Coins className="w-3 h-3 text-[#F59E0B]" />
              <span className="font-semibold">{user?.credits_balance || 0}</span>
              <span className="text-[#8C8477] text-[10px]">créditos</span>
            </Link>

            {/* Theme / Look Switcher */}
            <ThemeSwitcher />
          </div>
        </header>

        {/* Content Body */}
        <main className="flex-1 overflow-y-auto p-4 md:p-8">
          <div className="max-w-5xl mx-auto">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
};
