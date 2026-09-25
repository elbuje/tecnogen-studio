import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { ThemeProvider } from './contexts/ThemeContext';
import { AppLayout } from './components/layout/AppLayout';
import { OpsLayout } from './components/layout/OpsLayout';

// Client Pages
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
import { SystemLogs } from './pages/SystemLogs';

// Ops / SuperAdmin Pages
import { OpsOverview } from './pages/ops/OpsOverview';
import { OpsClients } from './pages/ops/OpsClients';
import { OpsQueue } from './pages/ops/OpsQueue';
import { OpsAgents } from './pages/ops/OpsAgents';
import { OpsFinops } from './pages/ops/OpsFinops';
import { OpsTeam } from './pages/ops/OpsTeam';
import { OpsLogs } from './pages/ops/OpsLogs';

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

const OpsProtectedLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user, loading, isOps } = useAuth();

  if (loading) {
    return <div className="min-h-screen bg-[#03060d] flex items-center justify-center text-purple-400 font-mono">Cargando HQ Operativo...</div>;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (!isOps) {
    return <Navigate to="/app" replace />;
  }

  return <OpsLayout>{children}</OpsLayout>;
};

export const App: React.FC = () => {
  return (
    <ThemeProvider>
      <AuthProvider>
        <BrowserRouter>
          <Routes>
            {/* Public */}
            <Route path="/" element={<Landing />} />
            <Route path="/login" element={<Login />} />

            {/* Client Studio Portal (/app/*) */}
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
            <Route
              path="/app/logs"
              element={
                <ProtectedLayout>
                  <SystemLogs />
                </ProtectedLayout>
              }
            />

            {/* Ops / SuperAdmin & Support Portal (/ops/*) */}
            <Route
              path="/ops"
              element={
                <OpsProtectedLayout>
                  <OpsOverview />
                </OpsProtectedLayout>
              }
            />
            <Route
              path="/ops/clients"
              element={
                <OpsProtectedLayout>
                  <OpsClients />
                </OpsProtectedLayout>
              }
            />
            <Route
              path="/ops/queue"
              element={
                <OpsProtectedLayout>
                  <OpsQueue />
                </OpsProtectedLayout>
              }
            />
            <Route
              path="/ops/agents"
              element={
                <OpsProtectedLayout>
                  <OpsAgents />
                </OpsProtectedLayout>
              }
            />
            <Route
              path="/ops/finops"
              element={
                <OpsProtectedLayout>
                  <OpsFinops />
                </OpsProtectedLayout>
              }
            />
            <Route
              path="/ops/team"
              element={
                <OpsProtectedLayout>
                  <OpsTeam />
                </OpsProtectedLayout>
              }
            />
            <Route
              path="/ops/logs"
              element={
                <OpsProtectedLayout>
                  <OpsLogs />
                </OpsProtectedLayout>
              }
            />

            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </ThemeProvider>
  );
};
