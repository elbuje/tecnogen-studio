import React from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { ShieldAlert, ArrowLeft, UserCheck } from 'lucide-react';

export const ImpersonationBanner: React.FC = () => {
  const { user, isImpersonating, stopImpersonation } = useAuth();

  if (!isImpersonating) return null;

  return (
    <div className="bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 text-white px-4 py-2 text-xs font-medium flex items-center justify-between shadow-lg sticky top-0 z-50 border-b border-amber-400/40">
      <div className="flex items-center gap-2">
        <ShieldAlert className="w-4 h-4 animate-bounce" />
        <span>
          <strong>MODO IMPERSONACIÓN ACTIVO:</strong> Estás navegando como el cliente <span className="underline font-bold">{user?.email}</span> ({user?.full_name || 'Sin nombre'}).
        </span>
      </div>
      <button
        onClick={stopImpersonation}
        className="flex items-center gap-1.5 px-3 py-1 bg-black/40 hover:bg-black/60 rounded-lg text-white font-semibold transition-all border border-white/20 shadow-sm"
      >
        <ArrowLeft className="w-3.5 h-3.5" />
        Salir y Volver al Panel Operativo (/ops)
      </button>
    </div>
  );
};
