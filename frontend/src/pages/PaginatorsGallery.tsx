import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { PAGINATORS_CATALOG, PaginatorStyle } from '../data/paginators';
import { PaginatorMiniPreview } from '../components/common/PaginatorMiniPreview';
import { Bookmark, Check, Sparkles, Filter, Search, ArrowRight, ExternalLink, RefreshCw } from 'lucide-react';
import { Link } from 'react-router-dom';

export const PaginatorsGallery: React.FC = () => {
  const [brands, setBrands] = useState<any[]>([]);
  const [selectedBrand, setSelectedBrand] = useState<any>(null);
  const [activePaginator, setActivePaginator] = useState<string>('01. Numeración simple (01 / 07)');
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [selectedCategory, setSelectedCategory] = useState<string>('Todas');
  const [saving, setSaving] = useState(false);

  const categories = [
    'Todas',
    'Numeración & Tipografía',
    'Barras & Timelines',
    'Puntos & UI Móvil',
    'Cápsulas & Badges',
    'Pasos & Storytelling',
    'Direccionales & Dinámicos',
    'Conceptuales & Panorámicos',
  ];

  useEffect(() => {
    const fetchBrands = async () => {
      try {
        const res = await api.get('/brands');
        setBrands(res.data);
        if (res.data.length > 0) {
          const b = res.data[0];
          setSelectedBrand(b);
          // Si la marca tiene configurado un paginador en brand_rules
          const currentRule = b.brand_rules?.default_paginator || b.brand_rules?.estilo_paginador || '01. Numeración simple (01 / 07)';
          setActivePaginator(currentRule);
        }
      } catch (e) {
        console.error('Error fetching brands for paginators:', e);
      }
    };
    fetchBrands();
  }, []);

  const handleBrandSelect = (brandId: string) => {
    const b = brands.find((item) => item.id === brandId);
    if (b) {
      setSelectedBrand(b);
      const currentRule = b.brand_rules?.default_paginator || b.brand_rules?.estilo_paginador || '01. Numeración simple (01 / 07)';
      setActivePaginator(currentRule);
    }
  };

  const handleSelectPaginator = async (pag: PaginatorStyle) => {
    setActivePaginator(pag.sheetLabel);
    if (!selectedBrand) return;

    try {
      setSaving(true);
      const updatedRules = {
        ...(selectedBrand.brand_rules || {}),
        default_paginator: pag.sheetLabel,
        estilo_paginador: pag.sheetLabel,
        paginator_id: pag.id
      };

      await api.put(`/brands/${selectedBrand.id}`, {
        primary_color: selectedBrand.primary_color,
        accent_color: selectedBrand.accent_color,
        bg_color: selectedBrand.bg_color,
        font_style_title: selectedBrand.font_style_title,
        font_style_body: selectedBrand.font_style_body,
        layout_preset: selectedBrand.layout_preset,
        logo_position: selectedBrand.logo_position,
        logo_width_px: selectedBrand.logo_width_px,
        brand_rules: updatedRules
      });

      setSelectedBrand({
        ...selectedBrand,
        brand_rules: updatedRules
      });
    } catch (e) {
      console.error('Error saving default paginator:', e);
    } finally {
      setSaving(false);
    }
  };

  const filteredPaginators = PAGINATORS_CATALOG.filter((pag) => {
    const matchesSearch =
      pag.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      pag.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
      pag.sheetLabel.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesCat = selectedCategory === 'Todas' || pag.category === selectedCategory;
    return matchesSearch && matchesCat;
  });

  const primaryCol = selectedBrand?.primary_color || '#16345F';
  const accentCol = selectedBrand?.accent_color || '#38BDF8';
  const bgCol = selectedBrand?.bg_color || '#070D1E';

  return (
    <div className="space-y-8 max-w-7xl mx-auto pb-16">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-6">
        <div>
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 shadow-md">
              <Bookmark className="w-5 h-5" />
            </div>
            <div>
              <h1 className="text-2xl font-black text-white tracking-tight flex items-center gap-3">
                Biblioteca de Paginadores
                <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                  {PAGINATORS_CATALOG.length} Estilos
                </span>
              </h1>
              <p className="text-sm text-slate-400">
                Seleccioná el estilo por defecto de tu marca o elegí cualquiera desde la columna <code className="text-cyan-300 font-mono font-bold">ESTILO PAGINADOR</code> en Google Sheets.
              </p>
            </div>
          </div>
        </div>

        {/* Brand Selector */}
        <div className="flex items-center gap-3">
          {brands.length > 1 && (
            <select
              value={selectedBrand?.id || ''}
              onChange={(e) => handleBrandSelect(e.target.value)}
              className="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-semibold"
            >
              {brands.map((b) => (
                <option key={b.id} value={b.id}>
                  {b.name}
                </option>
              ))}
            </select>
          )}

          <Link
            to="/app/integrations"
            className="px-4 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-800 border border-slate-700 text-xs font-bold text-slate-300 flex items-center gap-2 transition-all"
          >
            <span>Ver Google Sheet</span>
            <ExternalLink className="w-3.5 h-3.5 text-cyan-400" />
          </Link>
        </div>
      </div>

      {/* Banner de Sincronización con Google Sheet */}
      <div className="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-cyan-950/20 to-slate-900 border border-cyan-500/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div className="flex items-center gap-3.5">
          <div className="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-sm">
            ✓
          </div>
          <div>
            <h4 className="text-sm font-bold text-white flex items-center gap-2">
              Sincronizado con Columna U del Google Sheet
              <span className="text-[11px] font-normal text-slate-400">
                (Validación de datos ONE_OF_LIST activa con 50 opciones)
              </span>
            </h4>
            <p className="text-xs text-slate-400 mt-0.5">
              Estilo actualmente seleccionado para <b>{selectedBrand?.name || 'tu marca'}</b>:{' '}
              <span className="text-cyan-300 font-mono font-bold">{activePaginator}</span>
            </p>
          </div>
        </div>

        <div className="text-xs text-slate-400 flex items-center gap-2">
          {saving && (
            <span className="flex items-center gap-1.5 text-cyan-400 font-medium">
              <RefreshCw className="w-3.5 h-3.5 animate-spin" /> Guardando preferencia...
            </span>
          )}
        </div>
      </div>

      {/* Filters & Search */}
      <div className="space-y-4">
        <div className="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
          {/* Search */}
          <div className="relative flex-1 max-w-md">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder="Buscar por nombre, número o estilo (ej: barra, story, dots, 03)..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-cyan-500/60"
            />
          </div>

          <div className="text-xs text-slate-400 font-medium self-center">
            Mostrando <b>{filteredPaginators.length}</b> de {PAGINATORS_CATALOG.length} paginadores
          </div>
        </div>

        {/* Categories Pills */}
        <div className="flex items-center gap-1.5 overflow-x-auto pb-2 scrollbar-thin">
          {categories.map((cat) => {
            const isActive = selectedCategory === cat;
            return (
              <button
                key={cat}
                onClick={() => setSelectedCategory(cat)}
                className={`px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all ${
                  isActive
                    ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 shadow-sm'
                    : 'bg-slate-900/60 text-slate-400 hover:text-slate-200 border border-slate-800'
                }`}
              >
                {cat}
              </button>
            );
          })}
        </div>
      </div>

      {/* Grid of 49 Paginators + Random */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        {filteredPaginators.map((pag) => {
          const isSelected = activePaginator === pag.sheetLabel || (pag.id === 'random' && activePaginator === 'Aleatorio');

          return (
            <div
              key={pag.id}
              onClick={() => handleSelectPaginator(pag)}
              className={`group p-3.5 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between relative ${
                isSelected
                  ? 'bg-slate-900/90 border-cyan-500/80 shadow-lg shadow-cyan-500/10 ring-1 ring-cyan-500/30'
                  : 'bg-slate-900/40 hover:bg-slate-900/70 border-slate-800/80 hover:border-slate-700'
              }`}
            >
              {/* Badge de seleccionado */}
              {isSelected && (
                <div className="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-cyan-500 text-slate-950 flex items-center justify-center text-xs font-black shadow-md z-10">
                  <Check className="w-3.5 h-3.5 stroke-[3]" />
                </div>
              )}

              {/* Encabezado de la tarjeta */}
              <div className="space-y-2 mb-3">
                <div className="flex items-center justify-between gap-1">
                  <span className="text-[10px] font-bold uppercase tracking-wider text-cyan-400 bg-cyan-950/60 px-2 py-0.5 rounded border border-cyan-900/60 truncate">
                    {pag.category}
                  </span>
                  <span className="text-[10px] font-mono text-slate-500 font-semibold">
                    #{pag.number}
                  </span>
                </div>

                <h3 className="text-xs font-bold text-white group-hover:text-cyan-300 transition-colors line-clamp-1">
                  {pag.name}
                </h3>
              </div>

              {/* Vista previa miniatura interactiva */}
              <div className="my-1.5">
                <PaginatorMiniPreview
                  type={pag.previewType}
                  primaryColor={primaryCol}
                  accentColor={accentCol}
                  bgColor={bgCol}
                />
              </div>

              {/* Descripción & Etiqueta de Sheet */}
              <div className="space-y-2 mt-2 pt-2 border-t border-slate-800/60">
                <p className="text-[11px] text-slate-400 leading-tight line-clamp-2">
                  {pag.description}
                </p>

                <div className="flex items-center justify-between pt-1">
                  <span className="text-[10px] text-slate-500 font-mono truncate max-w-[170px]" title={pag.sheetLabel}>
                    Sheet: <span className="text-slate-300 font-semibold">{pag.sheetLabel}</span>
                  </span>

                  <button
                    onClick={(e) => {
                      e.stopPropagation();
                      handleSelectPaginator(pag);
                    }}
                    className={`text-[10px] font-bold px-2 py-1 rounded-lg transition-all ${
                      isSelected
                        ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/40'
                        : 'bg-slate-800 hover:bg-slate-700 text-slate-300'
                    }`}
                  >
                    {isSelected ? 'Activo' : 'Elegir'}
                  </button>
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};
