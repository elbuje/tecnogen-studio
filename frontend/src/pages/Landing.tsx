import React from 'react';
import { Link } from 'react-router-dom';
import { Sparkles, ArrowRight, Layers, RefreshCw, HardDrive, CheckCircle2, ShieldCheck, Zap } from 'lucide-react';

export const Landing: React.FC = () => {
  return (
    <div className="min-h-screen bg-[#070D1E] text-slate-100 selection:bg-cyan-500 selection:text-black">
      {/* Header */}
      <header className="border-b border-slate-800/80 bg-[#070D1E]/80 backdrop-blur-md sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/25">
              <Sparkles className="w-6 h-6" />
            </div>
            <span className="text-xl font-extrabold tracking-tight">
              TecnoGen <span className="text-cyan-400">Studio</span>
            </span>
          </div>
          <div className="flex items-center gap-4">
            <Link
              to="/login"
              className="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-800/60 transition-colors"
            >
              Iniciar Sesión
            </Link>
            <Link
              to="/login"
              className="px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25 transition-all flex items-center gap-2"
            >
              Probar Demo <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="py-24 px-6 relative overflow-hidden">
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-cyan-900/20 via-transparent to-transparent pointer-events-none"></div>
        <div className="max-w-5xl mx-auto text-center space-y-8 relative z-10">
          <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-panel text-cyan-300 text-xs font-semibold uppercase tracking-widest border border-cyan-500/20">
            <Sparkles className="w-4 h-4" /> Plataforma SaaS B2B de Generación Visual con IA
          </div>
          <h1 className="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white leading-[1.1]">
            El Estudio de Contenido con IA <br />
            <span className="bg-gradient-to-r from-cyan-400 via-sky-300 to-blue-500 bg-clip-text text-transparent">
              para Marcas y Agencias
            </span>
          </h1>
          <p className="text-lg sm:text-xl text-slate-300 max-w-3xl mx-auto font-normal leading-relaxed">
            Transformá ideas o guiones en carruseles profesionales listos para Instagram y LinkedIn.
            Composición de marca determinista, corrección granular por slide y arquitectura <strong className="text-cyan-300 font-semibold">Zero-Storage</strong> directo a tu Google Drive.
          </p>

          <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <Link
              to="/login"
              className="w-full sm:w-auto px-8 py-4 rounded-2xl text-base font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/30 transition-all flex items-center justify-center gap-3"
            >
              Comenzar Ahora <ArrowRight className="w-5 h-5" />
            </Link>
            <a
              href="#precios"
              className="w-full sm:w-auto px-8 py-4 rounded-2xl text-base font-semibold glass-panel hover:bg-slate-800/80 text-slate-200 transition-colors"
            >
              Ver Planes & Precios
            </a>
          </div>
        </div>
      </section>

      {/* Feature Grid */}
      <section className="py-20 px-6 max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
        <div className="p-8 rounded-3xl glass-card space-y-4 hover:border-cyan-500/40 transition-colors">
          <div className="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center border border-cyan-500/20">
            <HardDrive className="w-6 h-6" />
          </div>
          <h3 className="text-xl font-bold text-white">Zero-Storage</h3>
          <p className="text-sm text-slate-300 leading-relaxed">
            Tus fotos y carruseles generados viven en tu propio Google Drive. Privacidad total y sin almacenamiento de terceros.
          </p>
        </div>

        <div className="p-8 rounded-3xl glass-card space-y-4 hover:border-cyan-500/40 transition-colors">
          <div className="w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-400 flex items-center justify-center border border-sky-500/20">
            <RefreshCw className="w-6 h-6" />
          </div>
          <h3 className="text-xl font-bold text-white">Corrección Granular</h3>
          <p className="text-sm text-slate-300 leading-relaxed">
            ¿Un carrusel de 7 slides tiene 1 lámina a mejorar? Regenerá solo esa lámina con feedback exacto sin rehacer el carrusel entero.
          </p>
        </div>

        <div className="p-8 rounded-3xl glass-card space-y-4 hover:border-cyan-500/40 transition-colors">
          <div className="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20">
            <Zap className="w-6 h-6" />
          </div>
          <h3 className="text-xl font-bold text-white">Metricool Directo</h3>
          <p className="text-sm text-slate-300 leading-relaxed">
            Conectá tu cuenta de Metricool, obtené automáticamente los mejores horarios de publicación y programá en 1 clic.
          </p>
        </div>
      </section>

      {/* Pricing Section */}
      <section id="precios" className="py-24 px-6 max-w-6xl mx-auto space-y-12">
        <div className="text-center space-y-4">
          <h2 className="text-3xl sm:text-5xl font-extrabold text-white">Planes Transparentes</h2>
          <p className="text-slate-400">Elegí el plan adecuado para tu negocio o agencia.</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {/* Starter */}
          <div className="p-8 rounded-3xl glass-card border border-slate-700/60 space-y-6 flex flex-col justify-between">
            <div className="space-y-4">
              <h3 className="text-2xl font-bold text-white">Starter</h3>
              <div className="text-4xl font-extrabold text-white">$29 <span className="text-sm font-medium text-slate-400">/mes</span></div>
              <p className="text-sm text-slate-400">Para profesionales y negocios independientes.</p>
              <ul className="space-y-3 text-sm text-slate-300 pt-4 border-t border-slate-800">
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> 75 créditos / mes (~10 carruseles)</li>
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> 1 Marca</li>
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> Conexión Google Drive</li>
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> Descargas ZIP en alta resolución</li>
              </ul>
            </div>
            <Link to="/login" className="w-full py-3 rounded-xl font-semibold glass-panel hover:bg-slate-800 text-center transition-colors">
              Elegir Starter
            </Link>
          </div>

          {/* Growth */}
          <div className="p-8 rounded-3xl glass-card border-2 border-cyan-500 shadow-2xl shadow-cyan-500/20 space-y-6 flex flex-col justify-between relative">
            <div className="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-cyan-500 text-black text-xs font-bold uppercase tracking-wider">
              Más Popular
            </div>
            <div className="space-y-4">
              <h3 className="text-2xl font-bold text-white">Growth</h3>
              <div className="text-4xl font-extrabold text-cyan-400">$69 <span className="text-sm font-medium text-slate-400">/mes</span></div>
              <p className="text-sm text-slate-400">Para clínicas, franquicias y marcas en expansión.</p>
              <ul className="space-y-3 text-sm text-slate-300 pt-4 border-t border-slate-800">
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> 220 créditos / mes (~30 carruseles)</li>
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> Hasta 3 Marcas</li>
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> Integración Metricool (Programación)</li>
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> Soporte Chat Prioritario</li>
              </ul>
            </div>
            <Link to="/login" className="w-full py-3 rounded-xl font-bold bg-cyan-500 hover:bg-cyan-400 text-black text-center transition-colors shadow-lg shadow-cyan-500/20">
              Elegir Growth
            </Link>
          </div>

          {/* Agency */}
          <div className="p-8 rounded-3xl glass-card border border-slate-700/60 space-y-6 flex flex-col justify-between">
            <div className="space-y-4">
              <h3 className="text-2xl font-bold text-white">Agency</h3>
              <div className="text-4xl font-extrabold text-white">$149 <span className="text-sm font-medium text-slate-400">/mes</span></div>
              <p className="text-sm text-slate-400">Para agencias de marketing y creadores prolíficos.</p>
              <ul className="space-y-3 text-sm text-slate-300 pt-4 border-t border-slate-800">
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> 750 créditos / mes (~100 carruseles)</li>
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> Marcas Ilimitadas</li>
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> Acceso API / MCP Abierto</li>
                <li className="flex items-center gap-2"><CheckCircle2 className="w-4 h-4 text-cyan-400" /> 5 Usuarios de equipo</li>
              </ul>
            </div>
            <Link to="/login" className="w-full py-3 rounded-xl font-semibold glass-panel hover:bg-slate-800 text-center transition-colors">
              Elegir Agency
            </Link>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-slate-800/80 py-12 px-6 text-center text-sm text-slate-500">
        <p>© 2026 TecnoGen Studio (studio.tecnogen.ar). Todos los derechos reservados.</p>
      </footer>
    </div>
  );
};
