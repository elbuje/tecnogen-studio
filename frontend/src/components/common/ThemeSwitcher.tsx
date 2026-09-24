import React, { useState, useRef, useEffect } from 'react';
import { useTheme, AVAILABLE_THEMES, ThemeName } from '../../contexts/ThemeContext';
import { Palette, Check, Sparkles, Wand2, Coffee, Bot } from 'lucide-react';

export const ThemeSwitcher: React.FC<{ compact?: boolean }> = ({ compact = false }) => {
  const { theme, setTheme, themeInfo } = useTheme();
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const getThemeIcon = (id: ThemeName) => {
    switch (id) {
      case 'heygen':
        return <Wand2 className="w-4 h-4 text-purple-400" />;
      case 'claude':
        return <Coffee className="w-4 h-4 text-amber-400" />;
      case 'chatgpt':
        return <Bot className="w-4 h-4 text-emerald-400" />;
      default:
        return <Sparkles className="w-4 h-4 text-cyan-400" />;
    }
  };

  return (
    <div className="relative" ref={dropdownRef}>
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-700/70 bg-slate-800/60 hover:bg-slate-700/60 transition-all text-xs font-semibold text-slate-200 shadow-sm"
        title="Cambiar Look & Feel / Tema Visual"
      >
        <div
          className="w-3 h-3 rounded-full flex-shrink-0 shadow-sm"
          style={{ backgroundColor: themeInfo.accentColor }}
        />
        {getThemeIcon(theme)}
        <span className="hidden sm:inline">{themeInfo.name}</span>
        <span className="text-[10px] px-1.5 py-0.5 rounded bg-slate-900/80 text-slate-400 font-mono">
          {themeInfo.badge}
        </span>
      </button>

      {isOpen && (
        <div className="absolute right-0 mt-2 w-72 rounded-2xl glass-panel p-2 shadow-2xl border border-slate-700/80 z-50 animate-in fade-in zoom-in-95 duration-150">
          <div className="px-3 py-2 border-b border-slate-800/80 mb-1">
            <div className="text-xs font-bold text-white flex items-center gap-1.5">
              <Palette className="w-3.5 h-3.5 text-cyan-400" /> Seleccionar Look & Feel
            </div>
            <p className="text-[11px] text-slate-400 mt-0.5">
              Cambiá el estilo visual de la plataforma en tiempo real.
            </p>
          </div>

          <div className="space-y-1">
            {AVAILABLE_THEMES.map((item) => {
              const isSelected = theme === item.id;
              return (
                <button
                  key={item.id}
                  onClick={() => {
                    setTheme(item.id);
                    setIsOpen(false);
                  }}
                  className={`w-full text-left p-2.5 rounded-xl transition-all flex items-center justify-between group ${
                    isSelected
                      ? 'bg-slate-800/90 border border-slate-700 shadow-sm'
                      : 'hover:bg-slate-800/50 text-slate-300'
                  }`}
                >
                  <div className="flex items-center gap-2.5">
                    <div
                      className="w-7 h-7 rounded-lg flex items-center justify-center border border-slate-700/80 flex-shrink-0"
                      style={{ backgroundColor: item.bgPreview }}
                    >
                      <div
                        className="w-3 h-3 rounded-full"
                        style={{ backgroundColor: item.accentColor }}
                      />
                    </div>
                    <div>
                      <div className="text-xs font-bold text-white flex items-center gap-1.5">
                        {item.name}
                      </div>
                      <div className="text-[10px] text-slate-400 line-clamp-1">
                        {item.tagline}
                      </div>
                    </div>
                  </div>

                  {isSelected && (
                    <Check
                      className="w-4 h-4 flex-shrink-0"
                      style={{ color: item.accentColor }}
                    />
                  )}
                </button>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
};
