import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import { Coins, Plus, History, CheckCircle2, ShieldCheck, ArrowRight } from 'lucide-react';

export const Billing: React.FC = () => {
  const { user } = useAuth();
  const [balanceData, setBalanceData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  const fetchBalance = async () => {
    try {
      setLoading(true);
      const res = await api.get('/billing/balance');
      setBalanceData(res.data);
    } catch (e) {
      console.error('Error fetching billing:', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBalance();
  }, []);

  return (
    <div className="p-8 max-w-5xl mx-auto space-y-8">
      <div>
        <h1 className="text-2xl font-extrabold text-white">Créditos & Facturación</h1>
        <p className="text-sm text-slate-400">Historial transparente de movimientos y recargas.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="p-6 rounded-3xl glass-card space-y-2 border border-cyan-500/30">
          <div className="text-xs uppercase font-semibold text-slate-400">Saldo Disponible</div>
          <div className="text-4xl font-extrabold text-white flex items-center gap-2">
            <Coins className="w-7 h-7 text-amber-400" />
            {balanceData?.credits_balance || user?.credits_balance}
          </div>
          <div className="text-xs text-slate-400">Plan {user?.plan_tier}</div>
        </div>

        <div className="p-6 rounded-3xl glass-card space-y-3 col-span-2 flex flex-col justify-between">
          <div className="space-y-1">
            <div className="text-sm font-bold text-white">Packs de Créditos Adicionales (Mercado Pago / Stripe)</div>
            <p className="text-xs text-slate-400">Comprá créditos que nunca vencen para picos de producción.</p>
          </div>
          <div className="flex gap-3">
            <button
              onClick={() => alert('Pasarela de pagos en modo Dev.')}
              className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition-colors"
            >
              +25 créditos ($9 USD)
            </button>
            <button
              onClick={() => alert('Pasarela de pagos en modo Dev.')}
              className="px-4 py-2 rounded-xl bg-cyan-500/20 hover:bg-cyan-500/30 border border-cyan-500/40 text-xs font-bold text-cyan-300 transition-colors"
            >
              +50 créditos ($15 USD)
            </button>
          </div>
        </div>
      </div>

      {/* History Ledger */}
      <div className="p-8 rounded-3xl glass-card space-y-4">
        <h2 className="text-base font-bold text-white flex items-center gap-2">
          <History className="w-4 h-4 text-cyan-400" /> Libro Contable de Auditoría (Credit Ledger)
        </h2>

        {balanceData?.history?.length === 0 ? (
          <div className="text-slate-400 text-xs py-4">No hay movimientos registrados todavía.</div>
        ) : (
          <div className="divide-y divide-slate-800 text-xs">
            {balanceData?.history?.map((item: any) => (
              <div key={item.id} className="py-3 flex items-center justify-between">
                <div className="space-y-0.5">
                  <div className="font-semibold text-slate-200">{item.description}</div>
                  <div className="text-[10px] text-slate-500 font-mono">
                    {new Date(item.created_at).toLocaleString()} • {item.action_type}
                  </div>
                </div>
                <div
                  className={`font-bold font-mono text-sm ${
                    item.amount > 0 ? 'text-emerald-400' : 'text-rose-400'
                  }`}
                >
                  {item.amount > 0 ? `+${item.amount}` : item.amount} créd.
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};
