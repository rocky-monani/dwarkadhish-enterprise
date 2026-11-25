/**
 * Centralized DOM selectors for official Battery Connect website.
 * Target page: https://batteryconnect.com/amaron/app/warranty/register
 * 
 * Update these selectors if Battery Connect website frontend changes in the future.
 */
module.exports = {
    // Official target URL
    targetUrl: 'https://batteryconnect.com/amaron/app/warranty/register',
    loginUrlPattern: /\/login/i,

    // Authentication verification selectors
    authenticatedNav: '.navbar, .brand-title, .core-menu, .menu-component, li.item.active',
    loginForm: 'input[name="username"], input[name="userId"], input[type="password"], form#loginForm',

    // Serial Number Input
    serialInput: 'form#warrantyStatusForm input[name="serialNumber"], .tab-pane.active input[name="serialNumber"], #status-panel input[name="serialNumber"], input[name="serialNumber"], input[formcontrolname="serialNumber"], input[placeholder*="Serial"]',

    // Check Status Button (READ-ONLY button — DO NOT click Register/Submit buttons)
    checkStatusButton: 'form#warrantyStatusForm button[type="submit"], form#warrantyStatusForm button, .tab-pane.active button:has-text("Check Status"), #status-panel button, button:has-text("Check Status"), button[type="submit"].arbl-btn, button.btn-secondary',

    // Product Status Tab Locator
    statusTab: 'a.nav-link:has-text("Product Status"), [role="tab"]:has-text("Product Status"), a#status, a#checkBatteryStatus, .nav-link:has-text("Product Status"), :has-text("Product Status"), #ngb-nav-0',

    // Expected Result Container Selectors (Matches ONLY actual warranty detail cards)
    resultsContainer: 'app-warranty-register-details, .card:has-text("Product Details"), .card:has-text("PRODUCT DETAILS"), .card:has-text("Product Journey"), .card:has-text("PRODUCT JOURNEY")',
    batteryCards: '.card, app-warranty-register-details .card, .product-card, div.card',

    // Product Details Section & Collapse/Expand Toggles
    productDetailsSection: '.card:has-text("Product Details"), .card:has-text("PRODUCT DETAILS"), div:has(> h5:has-text("Product Details")), div:has(> h4:has-text("Product Details"))',
    productDetailsContent: '.card:has-text("Product Details") .card-body, .card:has-text("PRODUCT DETAILS") .card-body, div:has-text("Product Segment")',
    productDetailsToggle: '.card:has-text("Product Details") .card-header, .card:has-text("Product Details") [data-toggle], .card:has-text("Product Details") i, .card:has-text("PRODUCT DETAILS") .card-header',

    // Product Journey & Warranty History Section & Toggles
    productJourneySection: '.card:has-text("Product Journey"), .card:has-text("PRODUCT JOURNEY"), .card:has-text("Warranty History"), div:has(> h5:has-text("Product Journey"))',
    productJourneyContent: '.card:has-text("Product Journey") .card-body, .card:has-text("PRODUCT JOURNEY") .card-body, div:has-text("Transaction Date")',
    productJourneyToggle: '.card:has-text("Product Journey") .card-header, .card:has-text("PRODUCT JOURNEY") [data-toggle], .card:has-text("PRODUCT JOURNEY") i, .card:has-text("Product Journey") .card-header',

    // Common Parent Container suitable for combined screenshot boundary
    commonParentContainer: 'app-warranty-register-details, div.main-inner-container, form#warrantyStatusForm, .content-wrapper, main, app-root',

    // Floating UI noise to hide during screenshot
    unwantedNoiseElements: 'footer, .gst-button, .chat-widget, #cf-turnstile-response, .navbar-toggler'
};
