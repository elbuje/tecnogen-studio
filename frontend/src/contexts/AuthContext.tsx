import React, { createContext, useContext, useState, useEffect } from 'react';
import api from '../api/client';

export interface User {
  id: string;
  email: string;
  full_name: string;
  role: string;
  plan_tier: string;
  credits_balance: number;
  commercial_status?: string;
  plan_name?: string;
  brand_name?: string;
  impersonated_by?: string;
}

interface AuthContextType {
  user: User | null;
  token: string | null;
  isOps: boolean;
  isSuperAdmin: boolean;
  isImpersonating: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
  impersonate: (token: string, clientUser: User) => void;
  stopImpersonation: () => void;
  refreshUser: () => Promise<void>;
  loading: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(() => {
    const saved = localStorage.getItem('user');
    return saved ? JSON.parse(saved) : null;
  });
  const [token, setToken] = useState<string | null>(() => localStorage.getItem('token'));
  const [isImpersonating, setIsImpersonating] = useState<boolean>(() => {
    return !!localStorage.getItem('original_ops_token');
  });
  const [loading, setLoading] = useState<boolean>(true);

  const isOps = user ? ['superadmin', 'admin', 'support'].includes(user.role) : false;
  const isSuperAdmin = user ? ['superadmin', 'admin'].includes(user.role) : false;

  const refreshUser = async () => {
    try {
      if (localStorage.getItem('token')) {
        const res = await api.get('/auth/me');
        setUser(res.data);
        localStorage.setItem('user', JSON.stringify(res.data));
      }
    } catch (e) {
      console.error("Error al actualizar usuario:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    refreshUser();
  }, []);

  const login = async (email: string, password: string) => {
    const res = await api.post('/auth/login', { email, password });
    const { access_token, user: userData } = res.data;
    localStorage.setItem('token', access_token);
    localStorage.setItem('user', JSON.stringify(userData));
    localStorage.removeItem('original_ops_token');
    localStorage.removeItem('original_ops_user');
    setToken(access_token);
    setUser(userData);
    setIsImpersonating(false);
  };

  const impersonate = (clientToken: string, clientUser: User) => {
    const currentToken = localStorage.getItem('token');
    const currentUser = localStorage.getItem('user');
    if (currentToken && !localStorage.getItem('original_ops_token')) {
      localStorage.setItem('original_ops_token', currentToken);
      if (currentUser) localStorage.setItem('original_ops_user', currentUser);
    }
    localStorage.setItem('token', clientToken);
    localStorage.setItem('user', JSON.stringify(clientUser));
    setToken(clientToken);
    setUser(clientUser);
    setIsImpersonating(true);
  };

  const stopImpersonation = () => {
    const originalToken = localStorage.getItem('original_ops_token');
    const originalUser = localStorage.getItem('original_ops_user');
    if (originalToken && originalUser) {
      localStorage.setItem('token', originalToken);
      localStorage.setItem('user', originalUser);
      localStorage.removeItem('original_ops_token');
      localStorage.removeItem('original_ops_user');
      setToken(originalToken);
      setUser(JSON.parse(originalUser));
      setIsImpersonating(false);
      window.location.href = '/ops';
    } else {
      logout();
    }
  };

  const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    localStorage.removeItem('original_ops_token');
    localStorage.removeItem('original_ops_user');
    setToken(null);
    setUser(null);
    setIsImpersonating(false);
  };

  return (
    <AuthContext.Provider value={{
      user,
      token,
      isOps,
      isSuperAdmin,
      isImpersonating,
      login,
      logout,
      impersonate,
      stopImpersonation,
      refreshUser,
      loading
    }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
