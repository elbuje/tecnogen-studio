module.exports = {
  apps: [
    {
      name: 'tecnogen-api',
      script: './backend/venv/bin/uvicorn',
      args: 'app.main:app --host 127.0.0.1 --port 8028',
      cwd: '/home/ploi/studio.tecnogen.ar',
      env: {
        PYTHONPATH: 'backend',
        ENVIRONMENT: 'production',
        PORT: '8028'
      },
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M'
    },
    {
      name: 'tecnogen-web',
      script: 'server.js',
      cwd: '/home/ploi/studio.tecnogen.ar',
      env: {
        NODE_ENV: 'production',
        PORT: 3000,
        BACKEND_URL: 'http://127.0.0.1:8028'
      },
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '300M'
    }
  ]
};
