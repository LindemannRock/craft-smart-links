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
    // Analytics settings
    'enableAnalytics' => true,
    'analyticsRetentionDays' => 90, // 0-3650 days (0 = forever)
    'trackBotClicks' => false,

    // QR Code defaults
    'qrCodeSize' => 300,
    'qrCodeMargin' => 4,
    'qrCodeForegroundColor' => '#000000',
    'qrCodeBackgroundColor' => '#FFFFFF',
    'qrCodeErrorCorrection' => 'M', // L, M, Q, H
    'qrCodeFormat' => 'svg', // png, svg

    // Caching
    'cacheEnabled' => true,
    'cacheDuration' => 3600, // seconds

    // Device detection
    'deviceDetectionMethod' => 'user-agent', // user-agent, client-hints
    'fallbackDevice' => 'desktop', // desktop, mobile, tablet

    // Language detection
    'languageDetectionMethod' => 'site', // site, browser, query
    'fallbackLanguage' => 'en',

    // Multi-environment example
    // '*' => [
    //     'enableAnalytics' => true,
    // ],
    // 'dev' => [
    //     'enableAnalytics' => false,
    //     'cacheEnabled' => false,
    // ],
    // 'production' => [
    //     'enableAnalytics' => true,
    //     'analyticsRetentionDays' => 180,
    //     'cacheEnabled' => true,
    // ],
];
