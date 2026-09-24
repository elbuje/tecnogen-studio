import React from 'react';
import { useTheme } from '../../contexts/ThemeContext';
import { TecnoGenLayout } from './layouts/TecnoGenLayout';
import { HeyGenLayout } from './layouts/HeyGenLayout';
import { ClaudeLayout } from './layouts/ClaudeLayout';
import { ChatGPTLayout } from './layouts/ChatGPTLayout';

export const AppLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { theme } = useTheme();

  switch (theme) {
    case 'heygen':
      return <HeyGenLayout>{children}</HeyGenLayout>;
    case 'claude':
      return <ClaudeLayout>{children}</ClaudeLayout>;
    case 'chatgpt':
      return <ChatGPTLayout>{children}</ChatGPTLayout>;
    case 'tecnogen':
    default:
      return <TecnoGenLayout>{children}</TecnoGenLayout>;
  }
};
