import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { Sparkles, Coins, LogOut, User as UserIcon, Settings as SettingsIcon } from 'lucide-react';

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
            <div className="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/60 text-sm">
              <Coins className="w-4 h-4 text-amber-400 animate-pulse" />
              <span className="font-semibold text-white">{user.credits_balance}</span>
              <span className="text-slate-400 text-xs uppercase tracking-wider font-medium">créditos</span>
            </div>

            {/* Plan Badge */}
            <div className="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md bg-cyan-950/60 border border-cyan-800/50 text-cyan-300 text-xs font-semibold uppercase tracking-wider">
              Plan {user.plan_tier}
            </div>

            {/* User Dropdown / Info */}
            <div className="flex items-center gap-3 pl-2 border-l border-slate-800">
              <div className="text-right hidden md:block">
                <div className="text-xs font-semibold text-slate-200">{user.full_name || user.email}</div>
                <div className="text-[10px] text-slate-400">{user.email}</div>
              </div>
              <button
                onClick={handleLogout}
                title="Cerrar Sesión"
                className="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800/60 transition-colors"
              >
                <LogOut className="w-4 h-4" />
              </button>
            </div>
          </>
        )}
      </div>
    </header>
  );
};
