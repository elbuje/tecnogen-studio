import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { Sparkles, Coins, LogOut, User as UserIcon, Settings as SettingsIcon, Link2, Terminal } from 'lucide-react';
import { ThemeSwitcher } from '../common/ThemeSwitcher';

export const Navbar: React.FC = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <header className="h-16 border-b border-slate-800/80 bg-[#070D1E]/90 backdrop-blur-md sticky top-0 z-40 px-6 flex items-center justify-between">
      <div className="flex items-center gap-3">
        <Link to="/app" className="flex items-center gap-2">
          <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/20">
            <Sparkles className="w-5 h-5" />
          </div>
          <div>
            <span className="font-bold text-lg tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
              TecnoGen <span className="text-cyan-400 font-extrabold">Studio</span>
            </span>
          </div>
        </Link>
      </div>

      <div className="flex items-center gap-4">
        {user && (
          <>
            {/* Credit Badge */}
            <Link
              to="/app/billing"
              className="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 hover:bg-slate-800 border border-slate-700/60 text-sm transition-colors"
            >
              <Coins className="w-4 h-4 text-amber-400 animate-pulse" />
              <span className="font-semibold text-white">{user.credits_balance}</span>
              <span className="text-slate-400 text-xs uppercase tracking-wider font-medium">créditos</span>
            </Link>

            {/* Plan Badge */}
            <div className="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md bg-cyan-950/60 border border-cyan-800/50 text-cyan-300 text-xs font-semibold uppercase tracking-wider">
              {user.role === 'superadmin' ? 'SuperAdmin' : user.role === 'support' ? 'Soporte HQ' : `Plan ${user.plan_tier}`}
            </div>

            {/* Ops HQ Switch for SuperAdmin & Support */}
            {(user.role === 'superadmin' || user.role === 'admin' || user.role === 'support') && (
              <Link
                to="/ops"
                className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-purple-600/30 to-indigo-600/30 hover:from-purple-600/40 hover:to-indigo-600/40 border border-purple-500/40 text-purple-200 text-xs font-bold transition-all shadow-sm"
              >
                <span className="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></span>
                <span>Panel Ops / HQ</span>
              </Link>
            )}

            {/* Logs & Debugger Direct Shortcut */}
            <Link
              to="/app/logs"
              title="Consola de Logs & Debugger"
              className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 text-cyan-300 text-xs font-semibold transition-colors"
            >
              <Terminal className="w-3.5 h-3.5 text-cyan-400" />
              <span className="hidden sm:inline">Logs</span>
            </Link>

            {/* Theme / Look & Feel Switcher */}
            <ThemeSwitcher />

            {/* Profile Direct Button */}
            <Link
              to="/app/profile"
              className="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-800/60 hover:bg-slate-700/60 border border-slate-700/60 transition-colors"
            >
              <div className="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-300 flex items-center justify-center text-xs font-bold">
                {user.full_name?.charAt(0) || 'U'}
              </div>
              <span className="text-xs font-semibold text-slate-200 hidden md:inline">Mi Perfil</span>
            </Link>

            {/* Logout Button */}
            <button
              onClick={handleLogout}
              title="Cerrar Sesión"
              className="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-slate-800/60 transition-colors"
            >
              <LogOut className="w-4 h-4" />
            </button>
          </>
        )}
      </div>
    </header>

  );
};
