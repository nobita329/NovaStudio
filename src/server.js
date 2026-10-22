const express = require('express');
const http = require('http');
const path = require('path');
const session = require('express-session');
const cookieParser = require('cookie-parser');
const cors = require('cors');

const config = require('./config');
const { initDb, getDb } = require('./database');
const { applySchema } = require('./database/schema');
const SqlSessionStore = require('./middleware/sessionStore');
const { renderPhp } = require('./middleware/phpEngine');
const { attachSettings, requireAuth, requireAdmin, requireServerAccess } = require('./middleware/auth');
const { setupConsoleWs } = require('./websocket/consoleWs');
const { startSftpServer } = require('./sftp/sftpServer');
const { initCronService } = require('./services/cronService');

const authRoutes = require('./routes/authRoutes');
const userRoutes = require('./routes/userRoutes');
const serverRoutes = require('./routes/serverRoutes');
const adminRoutes = require('./routes/adminRoutes');
const apiRoutes = require('./routes/apiRoutes');
const themeService = require('./services/themeService');

async function bootstrap() {
  // 1. Initialize DB & seed
  const db = await initDb();
  await applySchema(db);
  await themeService.init();

  // 2. Initialize background services
  await initCronService();
  startSftpServer();

  // 3. Setup Express Application
  const app = express();

  // Configure PHP View Engine
  app.engine('php', renderPhp);
  app.set('view engine', 'php');
  app.set('views', config.paths.views);

  // Middlewares
  app.use(cors());
  app.use(express.json());
  app.use(express.urlencoded({ extended: true }));
  app.use(cookieParser());
  app.use(session({
    store: new SqlSessionStore(getDb),
    secret: config.security.sessionSecret,
    resave: false,
    saveUninitialized: false,
    cookie: {
      maxAge: 1000 * 60 * 60 * 24 * 7, // 7 days
      httpOnly: true,
      secure: false
    }
  }));

  // Public assets
  app.use(express.static(config.paths.public));

  // Global settings & context
  app.use(attachSettings);

  // Maintenance mode check
  app.use((req, res, next) => {
    const isMaintenance = res.locals.settings && res.locals.settings.maintenance_mode === '1';
    const isAuthOrAdmin = req.path.startsWith('/auth') || req.path.startsWith('/admin') || req.path.startsWith('/assets') || (req.session.user && req.session.user.root_admin === 1);
    if (isMaintenance && !isAuthOrAdmin) {
      return res.status(503).render('errors/maintenance', {
        message: res.locals.settings.maintenance_message || 'Nova Panel is undergoing scheduled maintenance.'
      });
    }
    next();
  });

  // Base redirect
  app.get('/', (req, res) => {
    if (req.session && req.session.user) {
      res.redirect('/dashboard');
    } else {
      res.redirect('/auth/login');
    }
  });

  // Mount Route Groups
  app.use('/auth', authRoutes);
  app.use('/api', apiRoutes);
  app.use('/', requireAuth, userRoutes);
  app.use('/server/:server', requireAuth, requireServerAccess(), serverRoutes);
  app.use('/admin', requireAuth, requireAdmin, adminRoutes);

  // 404 Handler
  app.use((req, res) => {
    res.status(404).render('errors/404', { message: 'Page or resource not found.' });
  });

  // 500 Handler
  app.use((err, req, res, next) => {
    console.error('[Nova Error]', err);
    res.status(500).render('errors/500', { message: err.message });
  });

  // 4. Start Primary Web Server (Port 3001)
  const httpServer = http.createServer(app);
  setupConsoleWs(httpServer);

  httpServer.listen(config.panel.port, '0.0.0.0', () => {
    console.log(`=======================================================`);
    console.log(`  🚀 Nova Panel running at: http://localhost:${config.panel.port}`);
    console.log(`  🔌 API Service active at: http://localhost:${config.api.port}`);
    console.log(`  📁 SFTP Service active on port: ${config.sftp.port}`);
    console.log(`=======================================================`);
  });

  // 5. Start Dedicated API Server (Port 3003) as per spec
  if (config.api.port !== config.panel.port) {
    const apiApp = express();
    apiApp.use(cors());
    apiApp.use(express.json());
    apiApp.use('/api', apiRoutes);
    apiApp.use('/', apiRoutes);
    apiApp.listen(config.api.port, '0.0.0.0', () => {
      console.log(`  ⚡ Dedicated REST API listening on port ${config.api.port}`);
    });
  }
}

bootstrap().catch((err) => {
  console.error('Fatal initialization error:', err);
  process.exit(1);
});
