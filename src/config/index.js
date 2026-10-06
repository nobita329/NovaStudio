const path = require('path');
require('dotenv').config();

const ROOT_DIR = path.resolve(__dirname, '../../');

module.exports = {
  panel: {
    name: process.env.PANEL_NAME || 'Nova',
    port: parseInt(process.env.PANEL_PORT || '3001', 10),
    url: process.env.PANEL_URL || 'http://localhost:3001',
    env: process.env.NODE_ENV || 'production',
  },
  api: {
    port: parseInt(process.env.API_PORT || '3003', 10),
  },
  sftp: {
    port: parseInt(process.env.SFTP_PORT || '3004', 10),
    host: process.env.SFTP_HOST || '0.0.0.0',
  },
  db: {
    host: process.env.DB_HOST || '127.0.0.1',
    port: parseInt(process.env.DB_PORT || '3002', 10),
    user: process.env.DB_USER || 'Nova',
    password: process.env.DB_PASSWORD || 'NovaStudio',
    database: process.env.DB_NAME || 'Nova',
  },
  serverDb: {
    host: process.env.SERVER_DB_HOST || '127.0.0.1',
    port: parseInt(process.env.SERVER_DB_PORT || '3005', 10),
    user: process.env.SERVER_DB_USER || 'root',
    password: process.env.SERVER_DB_PASSWORD || 'YourStrongPassword',
  },
  paths: {
    root: ROOT_DIR,
    instances: path.join(ROOT_DIR, 'Instance'),
    servers: path.join(ROOT_DIR, 'Instance/server'),
    archives: path.join(ROOT_DIR, 'Instance/Archive'),
    backups: path.join(ROOT_DIR, 'Instance/Backup'),
    public: path.join(ROOT_DIR, 'public'),
    views: path.join(ROOT_DIR, 'views'),
  },
  security: {
    sessionSecret: process.env.SESSION_SECRET || 'nova-secret-key-super-secure-change-me',
    jwtSecret: process.env.JWT_SECRET || 'nova-jwt-secret-key-ultra-secure',
  },
  docker: {
    socketPath: process.env.DOCKER_SOCKET || '/var/run/docker.sock',
  }
};
