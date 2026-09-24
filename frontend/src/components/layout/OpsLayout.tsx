import React from 'react';
import { OpsNavbar } from './OpsNavbar';
import { OpsSidebar } from './OpsSidebar';
import { ImpersonationBanner } from './ImpersonationBanner';

export const OpsLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  return (
    <div className="min-h-screen bg-[#03060d] text-slate-100 flex flex-col font-sans selection:bg-purple-500 selection:text-white">
      <ImpersonationBanner />
      <OpsNavbar />
      <div className="flex-1 flex overflow-hidden">
        <OpsSidebar />
        <main className="flex-1 overflow-y-auto p-6 md:p-8 space-y-6">
          <div className="max-w-7xl mx-auto">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
};
