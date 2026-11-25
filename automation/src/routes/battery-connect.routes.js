const express = require('express');
const router = express.Router();
const batteryConnectController = require('../controllers/battery-connect.controller');

// Health Check
router.get('/health', (req, res) => batteryConnectController.health(req, res));

// Primary Check Status Automation (Input: serial_number, complaint_number)
router.post('/battery-connect/check-status', (req, res) => batteryConnectController.checkStatus(req, res));
router.post('/battery/lookup', (req, res) => batteryConnectController.checkStatus(req, res)); // Legacy route alias for compatibility

// Manual Browser Mode (Open persistent browser window)
router.post('/battery-connect/open-login', (req, res) => batteryConnectController.openLogin(req, res));
router.post('/battery/open-login', (req, res) => batteryConnectController.openLogin(req, res)); // Legacy route alias

module.exports = router;
