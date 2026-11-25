/**
 * Custom Error Class for Battery Connect Automation Errors
 */

class AutomationError extends Error {
    constructor(code, message, technicalDetails = null) {
        super(message);
        this.name = 'AutomationError';
        this.code = code;
        this.technicalDetails = technicalDetails;
    }
}

// Error Codes Registry
const ERROR_CODES = {
    AUTH_REQUIRED: 'AUTH_REQUIRED',
    BATTERY_NOT_FOUND: 'BATTERY_NOT_FOUND',
    SEARCH_TIMEOUT: 'SEARCH_TIMEOUT',
    BATTERY_CARD_NOT_FOUND: 'BATTERY_CARD_NOT_FOUND',
    BATTERY_MATCH_FAILED: 'BATTERY_MATCH_FAILED',
    DETAIL_PAGE_NOT_LOADED: 'DETAIL_PAGE_NOT_LOADED',
    PRODUCT_DETAILS_NOT_FOUND: 'PRODUCT_DETAILS_NOT_FOUND',
    PRODUCT_JOURNEY_NOT_FOUND: 'PRODUCT_JOURNEY_NOT_FOUND',
    CAPTURE_FAILED: 'CAPTURE_FAILED',
    BATTERY_CONNECT_UNAVAILABLE: 'BATTERY_CONNECT_UNAVAILABLE',
    LAYOUT_CHANGED: 'LAYOUT_CHANGED',
    AUTOMATION_ERROR: 'AUTOMATION_ERROR'
};

module.exports = {
    AutomationError,
    ERROR_CODES
};
