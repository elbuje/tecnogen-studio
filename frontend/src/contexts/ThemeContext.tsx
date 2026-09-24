import React, { createContext, useContext, useState, useEffect } from 'react';

export type ThemeName = 'tecnogen' | 'heygen' | 'claude' | 'chatgpt';

interface ThemeInfo {
  id: ThemeName;
  name: string;
  tagline: string;
  accentColor: string;
  bgPreview: string;
  badge: string;
}

export const AVAILABLE_THEMES: ThemeInfo[] = [
  {
    id: 'tecnogen',
    name: 'TecnoGen Cyber Glow',
    tagline: 'Estudio Neón y Neomórfico (Original)',
    accentColor: '#38BDF8',
    bgPreview: '#070D1E',
    badge: 'Cyber AI',
  },
  {
    id: 'heygen',
    name: 'HeyGen & Canva Studio',
    tagline: 'Suite Creativa Púrpura & Vidrio Mate',
    accentColor: '#8B5CF6',
    bgPreview: '#0F0F13',
    badge: 'Creative Studio',
  },
  {
    id: 'claude',
    name: 'Claude Cowork (Anthropic)',
    tagline: 'Minimalismo Cálido Editorial & Terracota',
    accentColor: '#D97706',
    bgPreview: '#171614',
    badge: 'Warm Editorial',
  },
  {
    id: 'chatgpt',
    name: 'ChatGPT Canvas (OpenAI)',
    tagline: 'Monocromo Ultra-Clean & Esmeralda',
    accentColor: '#10A37F',
    bgPreview: '#0D0D0D',
    badge: 'Clean Workspace',
  },
];

interface ThemeContextType {
  theme: ThemeName;
  setTheme: (theme: ThemeName) => void;
  themeInfo: ThemeInfo;
}

const ThemeContext = createContext<ThemeContextType | undefined>(undefined);

export const ThemeProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [theme, setThemeState] = useState<ThemeName>(() => {
    const saved = localStorage.getItem('tg_theme') as ThemeName;
    return saved && ['tecnogen', 'heygen', 'claude', 'chatgpt'].includes(saved) ? saved : 'tecnogen';
  });

  const setTheme = (newTheme: ThemeName) => {
    setThemeState(newTheme);
    localStorage.setItem('tg_theme', newTheme);
  };

  useEffect(() => {
    const root = document.documentElement;
    root.classList.remove('theme-tecnogen', 'theme-heygen', 'theme-claude', 'theme-chatgpt');
    root.classList.add(`theme-${theme}`);
  }, [theme]);

  const themeInfo = AVAILABLE_THEMES.find((t) => t.id === theme) || AVAILABLE_THEMES[0];

  return (
    <ThemeContext.Provider value={{ theme, setTheme, themeInfo }}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};
