import React, { useState, useEffect } from 'react';
import api from '../../api/client';
import { 
  DollarSign, 
  TrendingUp, 
  CreditCard, 
  Cpu, 
  CheckCircle2, 
  AlertTriangle, 
  RefreshCw,
  PieChart,
  Layers
} from 'lucide-react';

interface FinopsProvider {
  provider: string;
  service_type: string;
  cost_this_month_usd: number;
  quota_info: string;
  status: string;
  last_updated: string;
}

interface FinopsData {
  gross_revenue_mrr_usd: number;
  total_api_cost_usd: number;
  net_margin_usd: number;
  margin_percentage: number;
  providers: FinopsProvider[];
}

export const OpsFinops: React.FC = () => {
  const [data, setData] = useState<FinopsData | null>(null);
  const [loading, setLoading] = useState(true);

  const fetchFinops = async () => {
    try {
      const res = await api.get('/ops/finops');
      setData(res.data);
    } catch (e) {
      console.error("Error al cargar finops:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchFinops();
  }, []);

  return (
    <div className="space-y-8">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-white flex items-center gap-2.5">
            <DollarSign className="w-6 h-6 text-purple-400" />
            <span>FinOps & Consumo de Proveedores de IA</span>
          </h1>
          <p className="text-slate-400 text-sm mt-1">
            Análisis de costos reales en APIs (OpenAI, HeyGen, ElevenLabs, Cloudinary) vs ingresos de suscripciones y margen neto.
          </p>
        </div>

        <button
          onClick={fetchFinops}
          className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/80 text-xs font-semibold text-slate-200 transition-all shadow-sm"
        >
          <RefreshCw className="w-3.5 h-3.5 text-purple-400" />
          <span>Actualizar Costos</span>
        </button>
      </div>

      {/* Financial Overview Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {/* Ingresos Brutos */}
        <div className="p-5 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-2">
          <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">Ingreso Bruto (MRR)</span>
          <div className="text-3xl font-black text-white">
            ${data ? data.gross_revenue_mrr_usd.toFixed(2) : '---'}
          </div>
          <div className="text-xs text-emerald-400 font-semibold flex items-center gap-1">
            <TrendingUp className="w-3.5 h-3.5" />
            <span>Facturación recurrente activa</span>
          </div>
        </div>

        {/* Costo Total APIs */}
        <div className="p-5 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-2">
          <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">Costo Total de APIs (IA)</span>
          <div className="text-3xl font-black text-rose-400">
            ${data ? data.total_api_cost_usd.toFixed(2) : '---'}
          </div>
          <div className="text-xs text-slate-400">Consumo agregado del mes</div>
        </div>

        {/* Margen Neto */}
        <div className="p-5 rounded-2xl bg-gradient-to-br from-purple-950/50 to-slate-900/90 border border-purple-900/50 space-y-2">
          <span className="text-xs font-bold text-purple-300 uppercase tracking-wider">Margen Operativo Neto</span>
          <div className="text-3xl font-black text-purple-200">
            ${data ? data.net_margin_usd.toFixed(2) : '---'}
          </div>
          <div className="text-xs text-purple-400 font-semibold">Ganancia bruta mensual</div>
        </div>

        {/* % Margen */}
        <div className="p-5 rounded-2xl bg-gradient-to-br from-emerald-950/40 to-slate-900/90 border border-emerald-900/40 space-y-2">
          <span className="text-xs font-bold text-emerald-300 uppercase tracking-wider">Rentabilidad / Margen</span>
          <div className="text-3xl font-black text-emerald-400">
            {data ? `${data.margin_percentage}%` : '---'}
          </div>
          <div className="text-xs text-emerald-300/80">Margen saludable SaaS B2B</div>
        </div>
      </div>

      {/* Provider Details Table */}
      <div className="space-y-4">
        <h3 className="text-base font-bold text-white flex items-center gap-2">
          <Cpu className="w-4 h-4 text-purple-400" />
          <span>Desglose por Proveedor de Inteligencia Artificial</span>
        </h3>

        <div className="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden shadow-xl">
          <table className="w-full text-left border-collapse text-xs">
            <thead>
              <tr className="border-b border-slate-800 bg-slate-950/70 text-slate-400 uppercase tracking-wider font-bold text-[11px]">
                <th className="py-3.5 px-4">Proveedor / Plataforma</th>
                <th className="py-3.5 px-4">Servicio & Uso</th>
                <th className="py-3.5 px-4">Consumo Reportado</th>
                <th className="py-3.5 px-4">Gasto este Mes</th>
                <th className="py-3.5 px-4">Estado / Saldo</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/80">
              {data?.providers.map((p, idx) => (
                <tr key={idx} className="hover:bg-slate-800/40 transition-colors">
                  <td className="py-4 px-4">
                    <div className="font-bold text-white text-sm">{p.provider}</div>
                    <div className="text-[11px] text-slate-400">{p.last_updated}</div>
                  </td>
                  <td className="py-4 px-4 text-slate-300 font-medium">
                    {p.service_type}
                  </td>
                  <td className="py-4 px-4 font-mono text-purple-300">
                    {p.quota_info}
                  </td>
                  <td className="py-4 px-4 font-bold text-white text-sm">
                    ${p.cost_this_month_usd.toFixed(2)} <span className="text-[10px] text-slate-400 font-normal">USD</span>
                  </td>
                  <td className="py-4 px-4">
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold">
                      <CheckCircle2 className="w-3 h-3" />
                      <span>Saldo Activo</span>
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
