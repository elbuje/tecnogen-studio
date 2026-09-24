const express = require('express');
const { createProxyMiddleware } = require('http-proxy-middleware');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = process.env.PORT || 3000;
const BACKEND_URL = process.env.BACKEND_URL || 'http://127.0.0.1:8028';

// ACME challenge support for Let's Encrypt
app.use('/.well-known', express.static(path.join(__dirname, 'public', '.well-known')));

// Proxy API requests to FastAPI Backend
app.use(
  '/api',
  createProxyMiddleware({
    target: BACKEND_URL,
    changeOrigin: true,
    logLevel: 'warn',
    onProxyReq: (proxyReq, req, res) => {
      // Preserve client IP and host
      proxyReq.setHeader('X-Forwarded-Host', req.headers.host || '');
    },
  })
);

// Serve static frontend build
const distPath = path.join(__dirname, 'frontend', 'dist');
app.use(express.static(distPath));

// SPA fallback: return index.html for all other routes
app.get('*', (req, res) => {
  const indexPath = path.join(distPath, 'index.html');
  if (fs.existsSync(indexPath)) {
    res.sendFile(indexPath);
  } else {
    res.status(503).send('TecnoGen Studio is compiling assets. Please refresh in a moment.');
  }
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 TecnoGen Studio Web Server running on port ${PORT}`);
  console.log(`📡 Proxying /api to ${BACKEND_URL}`);
});
