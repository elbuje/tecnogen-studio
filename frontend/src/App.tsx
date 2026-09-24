import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { ThemeProvider } from './contexts/ThemeContext';
import { AppLayout } from './components/layout/AppLayout';
import { Landing } from './pages/Landing';
import { Login } from './pages/Login';
import { Dashboard } from './pages/Dashboard';
import { ContentViewer } from './pages/ContentViewer';
import { BrandKit } from './pages/BrandKit';
import { AISettings } from './pages/AISettings';
import { Billing } from './pages/Billing';
import { Profile } from './pages/Profile';
import { Integrations } from './pages/Integrations';
import { Library } from './pages/Library';

const ProtectedLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user, loading } = useAuth();

  if (loading) {
    return <div className="min-h-screen bg-[var(--bg-app)] flex items-center justify-center text-slate-400">Cargando TecnoGen Studio...</div>;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  return <AppLayout>{children}</AppLayout>;
};

export const App: React.FC = () => {
  return (
    <ThemeProvider>
      <AuthProvider>
        <BrowserRouter>
          <Routes>
          <Route path="/" element={<Landing />} />
          <Route path="/login" element={<Login />} />
          <Route
            path="/app"
            element={
              <ProtectedLayout>
                <Dashboard />
              </ProtectedLayout>
            }
          />
          <Route
            path="/app/library"
            element={
              <ProtectedLayout>
                <Library />
              </ProtectedLayout>
            }
          />
          <Route
            path="/app/viewer/:contentId"
            element={
              <ProtectedLayout>
                <ContentViewer />
              </ProtectedLayout>
            }
          />
          <Route
            path="/app/brands"
            element={
              <ProtectedLayout>
                <BrandKit />
              </ProtectedLayout>
            }
          />
          <Route
            path="/app/integrations"
            element={
              <ProtectedLayout>
                <Integrations />
              </ProtectedLayout>
            }
          />
          <Route
            path="/app/profile"
            element={
              <ProtectedLayout>
                <Profile />
              </ProtectedLayout>
            }
          />
          <Route
            path="/app/ai-settings"
            element={
              <ProtectedLayout>
                <AISettings />
              </ProtectedLayout>
            }
          />
          <Route
            path="/app/billing"
            element={
              <ProtectedLayout>
                <Billing />
              </ProtectedLayout>
            }
          />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  </ThemeProvider>
);
};
