import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { Navbar } from './components/layout/Navbar';
import { Sidebar } from './components/layout/Sidebar';
import { Landing } from './pages/Landing';
import { Login } from './pages/Login';
import { Dashboard } from './pages/Dashboard';
import { ContentViewer } from './pages/ContentViewer';
import { BrandKit } from './pages/BrandKit';
import { AISettings } from './pages/AISettings';
import { Billing } from './pages/Billing';

const ProtectedLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user, loading } = useAuth();

  if (loading) {
    return <div className="min-h-screen bg-[#070D1E] flex items-center justify-center text-slate-400">Cargando TecnoGen Studio...</div>;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  return (
    <div className="min-h-screen flex flex-col bg-[#070D1E]">
      <Navbar />
      <div className="flex flex-1">
        <Sidebar />
        <main className="flex-1 overflow-y-auto bg-[#070D1E]/40">{children}</main>
      </div>
    </div>
  );
};

export const App: React.FC = () => {
  return (
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
  );
};
