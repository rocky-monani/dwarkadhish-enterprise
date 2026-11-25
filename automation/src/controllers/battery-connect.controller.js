const batteryConnectService = require('../services/battery-connect.service');
const { launchHeadedLogin } = require('../browser/browser-manager');
const logger = require('../utils/logger');

/**
 * Controller handling requests from Laravel backend to Playwright service.
 */
class BatteryConnectController {
    /**
     * POST /battery-connect/check-status
     * Primary automation endpoint
     */
    async checkStatus(req, res) {
        try {
            const { serial_number, complaint_number } = req.body || {};

            if (!serial_number || typeof serial_number !== 'string' || !serial_number.trim()) {
                return res.status(400).json({
                    success: false,
                    code: 'INVALID_INPUT',
                    message: 'Battery serial_number is required.'
                });
            }

            logger.info(`Received check-status request: serial=${serial_number}, complaint=${complaint_number}`);

            const result = await batteryConnectService.checkBatteryStatus(serial_number.trim(), complaint_number);

            if (result.success) {
                return res.status(200).json(result);
            }

            // Map error codes to HTTP status
            const httpStatus = (result.code === 'AUTH_REQUIRED') ? 401 :
                               (result.code === 'BATTERY_NOT_FOUND') ? 404 : 422;

            return res.status(httpStatus).json(result);

        } catch (err) {
            logger.error('Unhandled Exception in checkStatus Controller', { error: err.message, stack: err.stack });
            return res.status(500).json({
                success: false,
                code: 'AUTOMATION_ERROR',
                message: `Server Error: ${err.message}`
            });
        }
    }

    /**
     * POST /battery-connect/open-login
     * Step 30 — Manual Browser Mode
     */
    async openLogin(req, res) {
        try {
            logger.info('Received open-login request from Laravel.');
            
            // Launch headed browser in background
            launchHeadedLogin().catch(err => {
                logger.error('Error launching headed browser in background', { error: err.message });
            });

            return res.status(200).json({
                status: 'SUCCESS',
                success: true,
                code: 'BROWSER_OPENED',
                message: 'Headed browser opened. Operator can log in or enter OTP manually on screen.'
            });
        } catch (err) {
            logger.error('Error in openLogin Controller', { error: err.message });
            return res.status(500).json({
                success: false,
                code: 'AUTOMATION_ERROR',
                message: `Could not launch browser: ${err.message}`
            });
        }
    }

    /**
     * GET /health
     */
    async health(req, res) {
        return res.status(200).json({
            success: true,
            status: 'UP',
            service: 'Amaron Battery Connect Playwright Service',
            timestamp: new Date().toISOString()
        });
    }
}

module.exports = new BatteryConnectController();
