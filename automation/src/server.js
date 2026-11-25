require('dotenv').config();
const express = require('express');
const cors = require('cors');
const logger = require('./utils/logger');
const routes = require('./routes/battery-connect.routes');

const app = express();
const PORT = process.env.PORT || 3000;
const SECRET_KEY = process.env.AUTOMATION_SECRET || 'amaron_local_secret_key_2026';

app.use(cors());
app.use(express.json());

// Local Security Middleware
app.use((req, res, next) => {
    const remoteIp = req.socket.remoteAddress || '';
    if (!remoteIp.includes('127.0.0.1') && !remoteIp.includes('::1') && !remoteIp.includes('localhost')) {
        return res.status(403).json({ success: false, code: 'FORBIDDEN', message: 'Access denied. Local requests only.' });
    }
    
    const clientSecret = req.headers['x-automation-secret'];
    if (SECRET_KEY && clientSecret && clientSecret !== SECRET_KEY) {
        return res.status(401).json({ success: false, code: 'UNAUTHORIZED', message: 'Unauthorized automation request.' });
    }

    next();
});

// Register modular routes
app.use('/', routes);

app.listen(PORT, '127.0.0.1', () => {
    logger.info(`Playwright Automation Service running locally on http://127.0.0.1:${PORT}`);
});
