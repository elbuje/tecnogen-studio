import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { Shield, Sparkles, LogOut, Terminal, Bell } from 'lucide-react';

export const OpsNavbar: React.FC = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <header className="h-16 border-b border-purple-900/30 bg-[#060914]/90 backdrop-blur-md sticky top-0 z-40 px-6 flex items-center justify-between">
      <div className="flex items-center gap-3">
        <Link to="/ops" className="flex items-center gap-2.5">
          <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-purple-600/30">
            <Shield className="w-5 h-5" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <span className="font-black text-lg tracking-tight bg-gradient-to-r from-purple-200 via-white to-purple-400 bg-clip-text text-transparent">
                TecnoGen <span className="text-purple-400 font-extrabold">OPS</span>
              </span>
              <span className="text-[10px] px-2 py-0.5 rounded-full bg-purple-900/60 border border-purple-600/50 text-purple-300 font-bold tracking-widest uppercase">
                HQ Master
              </span>
            </div>
          </div>
        </Link>
      </div>

      <div className="flex items-center gap-4">
        {/* Switch to Client App */}
        <Link
          to="/app"
          className="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-cyan-950/40 hover:bg-cyan-900/40 border border-cyan-700/50 text-cyan-300 text-xs font-semibold transition-all shadow-sm"
        >
          <Sparkles className="w-3.5 h-3.5 text-cyan-400" />
          <span>Espacio Studio</span>
        </Link>

        {/* User Badge */}
        {user && (
          <div className="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-900/80 border border-slate-800">
            <div className="w-6 h-6 rounded-full bg-purple-600/30 text-purple-300 flex items-center justify-center text-xs font-bold border border-purple-500/40">
              {user.full_name?.charAt(0) || 'A'}
            </div>
            <div className="text-left hidden sm:block">
              <div className="text-xs font-semibold text-slate-200 leading-none">{user.full_name || user.email}</div>
              <div className="text-[10px] text-purple-400 font-mono mt-0.5">{user.role.toUpperCase()}</div>
            </div>
          </div>
        )}

        {/* Logout Button */}
        <button
          onClick={handleLogout}
          title="Cerrar Sesión"
          className="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-slate-800/60 transition-colors"
        >
          <LogOut className="w-4 h-4" />
        </button>
      </div>
    </header>
  );
};
