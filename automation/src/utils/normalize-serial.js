/**
 * Utility functions for serial number normalization and comparison
 */

/**
 * Normalizes input serial number by trimming whitespace and removing newlines
 * while preserving actual character formatting.
 */
function normalizeSerial(serialNumber) {
    if (!serialNumber || typeof serialNumber !== 'string') {
        return '';
    }
    return serialNumber.trim().replace(/[\r\n]+/g, '');
}

/**
 * Aggressive normalization for string comparisons.
 * Removes spaces, dashes, slashes, underscores and converts to uppercase.
 * Example: "E JE02 31 A 6 44397" -> "EJE0231A644397"
 */
function comparisonNormalize(serialNumber) {
    if (!serialNumber || typeof serialNumber !== 'string') {
        return '';
    }
    return serialNumber.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
}

/**
 * Formats raw serial numbers into standard Amaron spaced format for Angular form validation.
 * Example: "EJE0231A644397" -> "E JE02 31 A 6 44397"
 */
function formatAmaronSerial(rawSerial) {
    if (!rawSerial || typeof rawSerial !== 'string') return '';
    const clean = rawSerial.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

    if (clean.length === 14) {
        return `${clean.slice(0, 1)} ${clean.slice(1, 5)} ${clean.slice(5, 7)} ${clean.slice(7, 8)} ${clean.slice(8, 9)} ${clean.slice(9)}`;
    }

    if (clean.length === 15) {
        return `${clean.slice(0, 1)} ${clean.slice(1, 5)} ${clean.slice(5, 7)} ${clean.slice(7, 8)} ${clean.slice(8, 9)} ${clean.slice(9)}`;
    }

    return rawSerial.trim();
}

/**
 * Checks if targetText contains the requested serial number accounting for visual spacing.
 */
function serialMatchesText(targetText, requestedSerial) {
    if (!targetText || !requestedSerial) return false;
    const cleanTarget = comparisonNormalize(targetText);
    const cleanRequested = comparisonNormalize(requestedSerial);
    return cleanTarget.includes(cleanRequested);
}

module.exports = {
    normalizeSerial,
    comparisonNormalize,
    formatAmaronSerial,
    serialMatchesText
};
