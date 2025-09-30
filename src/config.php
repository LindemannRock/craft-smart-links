<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * Configuration file template
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

/**
 * Smart Links config.php
 *
 * This file exists only as a template for the Smart Links settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'smart-links.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    // Plugin Settings
    'pluginName' => 'Smart Links',

    // Site Settings
    'enabledSites' => [], // Array of site IDs where Smart Links should be enabled (empty = all sites)

    // Analytics Settings
    'enableAnalytics' => true,
    'analyticsRetention' => 90, // Days to keep analytics data (0 = unlimited, max 3650)

    // Analytics Export Options
    'includeDisabledInExport' => false, // Include disabled smart links in analytics exports
    'includeExpiredInExport' => false,  // Include expired smart links in analytics exports

    // QR Code Appearance Settings
    'defaultQrSize' => 256,            // Size in pixels (100-1000)
    'defaultQrColor' => '#000000',     // Foreground color
    'defaultQrBgColor' => '#FFFFFF',   // Background color
    'defaultQrFormat' => 'png',        // Format: 'png' or 'svg'
    'defaultQrMargin' => 4,            // White space around QR code (0-10 modules)

    // QR Code Technical Options
    'defaultQrErrorCorrection' => 'M', // Error correction level: L, M, Q, H
    'qrModuleStyle' => 'square',       // Module shape: 'square', 'rounded', 'dots'
    'qrEyeStyle' => 'square',          // Eye shape: 'square', 'rounded', 'leaf'
    'qrEyeColor' => null,              // Eye color (null = use main color)

    // QR Code Logo Settings
    'enableQrLogo' => false,           // Enable logo overlay in center of QR codes
    'qrLogoSize' => 20,                // Logo size as percentage (10-30%)
    // 'qrLogoVolumeUid' => null,      // Asset volume UID for logo selection
    'defaultQrLogoId' => null,         // Default logo asset ID

    // QR Code Download Settings
    'enableQrDownload' => true,        // Allow users to download QR codes
    'qrDownloadFilename' => '{slug}-qr-{size}', // Pattern with {slug}, {size}, {format}

    // Template Settings
    'redirectTemplate' => null,        // Custom redirect landing page template path
    'qrTemplate' => null,              // Custom QR code display page template path

    // Geographic & Device Detection
    'enableGeoDetection' => false,     // Detect user location for analytics
    'cacheDeviceDetection' => true,    // Cache device detection results
    'deviceDetectionCacheDuration' => 3600, // Device detection cache in seconds

    // Language & Redirect Settings
    'languageDetectionMethod' => 'browser', // Options: 'browser', 'ip', 'both'
    'notFoundRedirectUrl' => '/',      // Where to redirect for 404/disabled links

    // Caching Settings
    'qrCodeCacheDuration' => 86400,    // QR code cache duration in seconds (24 hours)

    // Interface Settings
    'itemsPerPage' => 100,             // Number of smart links per page (10-500)

    // Asset Settings
    // 'imageVolumeUid' => null,       // Asset volume UID for Smart Link images

    // Multi-environment example
    '*' => [
        // Default settings for all environments
    ],
    'dev' => [
        'enableAnalytics' => true,
        'analyticsRetention' => 30,    // Keep less data in dev
        'cacheDeviceDetection' => false,
    ],
    'staging' => [
        'enableAnalytics' => true,
        'analyticsRetention' => 90,
    ],
    'production' => [
        'enableAnalytics' => true,
        'analyticsRetention' => 365,   // Keep more data in production
        'cacheDeviceDetection' => true,
        'deviceDetectionCacheDuration' => 7200, // Longer cache in production
    ],
];
