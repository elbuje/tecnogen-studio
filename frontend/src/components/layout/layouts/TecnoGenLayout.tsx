import React from 'react';
import { Navbar } from '../Navbar';
import { Sidebar } from '../Sidebar';

export const TecnoGenLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  return (
    <div className="min-h-screen flex flex-col bg-[var(--bg-app)] transition-colors duration-300">
      <Navbar />
      <div className="flex flex-1">
        <Sidebar />
        <main className="flex-1 overflow-y-auto bg-[var(--bg-app)]/40 p-1 md:p-2">{children}</main>
      </div>
    </div>
  );
};
