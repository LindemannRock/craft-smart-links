<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

/**
 * English Translations
 *
 * @since 1.0.0
 */

return [
    // Plugin Meta
    'Smart Links' => 'Smart Links',
    '{name} plugin loaded' => '{name} plugin loaded',

    // Element Names
    'Smart Link' => 'Smart Link',
    'smart link' => 'smart link',
    'smart links' => 'smart links',
    'New smart link' => 'New smart link',

    // Permissions
    'View smart links' => 'View smart links',
    'Create smart links' => 'Create smart links',
    'Edit smart links' => 'Edit smart links',
    'Delete smart links' => 'Delete smart links',
    'View analytics' => 'View analytics',
    'Manage settings' => 'Manage settings',

    // Navigation
    'Analytics' => 'Analytics',
    'Settings' => 'Settings',
    'General' => 'General',
    'QR Code' => 'QR Code',
    'Redirect' => 'Redirect',
    'Export' => 'Export',
    'Advanced' => 'Advanced',
    'Interface' => 'Interface',

    // General Settings
    'Plugin Name' => 'Plugin Name',
    'The name of the plugin as it appears in the Control Panel menu' => 'The name of the plugin as it appears in the Control Panel menu',
    'Plugin Settings' => 'Plugin Settings',

    // Site Settings
    'Site Settings' => 'Site Settings',
    'Enabled Sites' => 'Enabled Sites',
    'Select which sites Smart Links should be enabled for. Leave empty to enable for all sites.' => 'Select which sites Smart Links should be enabled for. Leave empty to enable for all sites.',

    // URL Settings
    'URL Settings' => 'URL Settings',
    'Smart Link URL Prefix' => 'Smart Link URL Prefix',
    'QR Code URL Prefix' => 'QR Code URL Prefix',
    'The URL prefix for smart links (e.g., \'go\' creates /go/your-link)' => 'The URL prefix for smart links (e.g., \'go\' creates /go/your-link)',
    'The URL prefix for QR code pages (e.g., \'qr\' creates /qr/your-link)' => 'The URL prefix for QR code pages (e.g., \'qr\' creates /qr/your-link)',
    'Only letters, numbers, hyphens, and underscores are allowed.' => 'Only letters, numbers, hyphens, and underscores are allowed.',
    'This is being overridden by the <code>slugPrefix</code> setting in <code>config/smart-links.php</code>. Clear routes cache after changing this.' => 'This is being overridden by the <code>slugPrefix</code> setting in <code>config/smart-links.php</code>. Clear routes cache after changing this.',
    'This is being overridden by the <code>qrPrefix</code> setting in <code>config/smart-links.php</code>. Clear routes cache after changing this.' => 'This is being overridden by the <code>qrPrefix</code> setting in <code>config/smart-links.php</code>. Clear routes cache after changing this.',
    'Clear routes cache after changing this (php craft clear-caches/compiled-templates).' => 'Clear routes cache after changing this (php craft clear-caches/compiled-templates).',

    // Smart Link Fields
    'Title' => 'Title',
    'The title of this smart link' => 'The title of this smart link',
    'Description' => 'Description',
    'A brief description of this smart link' => 'A brief description of this smart link',
    'Icon' => 'Icon',
    'Icon identifier or URL for this smart link' => 'Icon identifier or URL for this smart link',

    // Image Settings
    'Image' => 'Image',
    'Select an image for this smart link' => 'Select an image for this smart link',
    'Image Size' => 'Image Size',
    'Select the size for the smart link image' => 'Select the size for the smart link image',
    'Extra Large (2048px)' => 'Extra Large (2048px)',
    'Large (1024px)' => 'Large (1024px)',
    'Medium (512px)' => 'Medium (512px)',
    'Small (256px)' => 'Small (256px)',
    'Hide Title on Landing Pages' => 'Hide Title on Landing Pages',
    'Hide the smart link title on both redirect and QR code landing pages' => 'Hide the smart link title on both redirect and QR code landing pages',

    // URL Fields
    'Destination URL' => 'Destination URL',
    'Last Destination URL' => 'Last Destination URL',
    'Fallback URL' => 'Fallback URL',
    'The URL to redirect to when no platform-specific URL is available' => 'The URL to redirect to when no platform-specific URL is available',
    'iOS URL' => 'iOS URL',
    'App Store URL for iOS devices' => 'App Store URL for iOS devices',
    'Android URL' => 'Android URL',
    'Google Play Store URL for Android devices' => 'Google Play Store URL for Android devices',
    'Huawei URL' => 'Huawei URL',
    'AppGallery URL for Huawei devices' => 'AppGallery URL for Huawei devices',
    'Amazon URL' => 'Amazon URL',
    'Amazon Appstore URL' => 'Amazon Appstore URL',
    'Windows URL' => 'Windows URL',
    'Microsoft Store URL for Windows devices' => 'Microsoft Store URL for Windows devices',
    'Mac URL' => 'Mac URL',
    'Mac App Store URL' => 'Mac App Store URL',
    'App Store URLs' => 'App Store URLs',
    'Enter the store URLs for each platform. The system will automatically redirect users to the appropriate store based on their device.' => 'Enter the store URLs for each platform. The system will automatically redirect users to the appropriate store based on their device.',

    // Display Settings
    'Display Settings' => 'Display Settings',

    // QR Code Settings
    'QR Code Settings' => 'QR Code Settings',
    'Enable QR Code' => 'Enable QR Code',
    'Default QR Code Size' => 'Default QR Code Size',
    'Default size in pixels for generated QR codes' => 'Default size in pixels for generated QR codes',
    'Default QR Code Color' => 'Default QR Code Color',
    'Color' => 'Color',
    'Default QR Background Color' => 'Default QR Background Color',
    'Background' => 'Background',
    'Background Color' => 'Background Color',
    'Default QR Code Format' => 'Default QR Code Format',
    'Default format for generated QR codes' => 'Default format for generated QR codes',
    'Override the default QR code format' => 'Override the default QR code format',
    'Format' => 'Format',
    'Use Default ({format|upper})' => 'Use Default ({format|upper})',
    'QR Code Cache Duration (seconds)' => 'QR Code Cache Duration (seconds)',
    'How long to cache generated QR codes (in seconds)' => 'How long to cache generated QR codes (in seconds)',
    'Cache duration in seconds' => 'Cache duration in seconds',
    'Caching' => 'Caching',

    // QR Code Technical Options
    'Technical Options' => 'Technical Options',
    'Error Correction Level' => 'Error Correction Level',
    'Higher levels work better if QR code is damaged but create denser patterns' => 'Higher levels work better if QR code is damaged but create denser patterns',
    'QR Code Margin' => 'QR Code Margin',
    'Margin Size' => 'Margin Size',
    'White space around QR code (0-10 modules)' => 'White space around QR code (0-10 modules)',
    'Module Style' => 'Module Style',
    'Shape of the QR code modules' => 'Shape of the QR code modules',
    'Eye Style' => 'Eye Style',
    'Shape of the position markers (corners)' => 'Shape of the position markers (corners)',
    'Eye Color' => 'Eye Color',
    'Color for position markers (leave empty to use main color)' => 'Color for position markers (leave empty to use main color)',

    // QR Code Appearance
    'Appearance & Style' => 'Appearance & Style',

    // QR Code Logo Settings
    'Logo Settings' => 'Logo Settings',
    'Enable QR Code Logo' => 'Enable QR Code Logo',
    'Enable Logo Overlay' => 'Enable Logo Overlay',
    'Add a logo in the center of QR codes' => 'Add a logo in the center of QR codes',
    'Logo Volume' => 'Logo Volume',
    'Logo Asset Volume' => 'Logo Asset Volume',
    'Which asset volume contains QR code logos. Save settings after changing this to update the logo selection below.' => 'Which asset volume contains QR code logos. Save settings after changing this to update the logo selection below.',
    'Default Logo' => 'Default Logo',
    'Default logo to use for QR codes (can be overridden per smart link)' => 'Default logo to use for QR codes (can be overridden per smart link)',
    'Default logo is required when logo overlay is enabled.' => 'Default logo is required when logo overlay is enabled.',
    'Logo Size (%)' => 'Logo Size (%)',
    'Logo Size' => 'Logo Size',
    'Logo size as percentage of QR code (10-30%)' => 'Logo size as percentage of QR code (10-30%)',
    'Logo' => 'Logo',
    'Logo overlay only works with PNG format. SVG format does not support logos.' => 'Logo overlay only works with PNG format. SVG format does not support logos.',
    'Logo requires PNG format' => 'Logo requires PNG format',
    'Using default logo from settings (click to override)' => 'Using default logo from settings (click to override)',

    // QR Code Download Settings
    'Download Settings' => 'Download Settings',
    'Enable QR Code Downloads' => 'Enable QR Code Downloads',
    'Allow users to download QR codes' => 'Allow users to download QR codes',
    'Download Filename Pattern' => 'Download Filename Pattern',
    'Available variables: {slug}, {size}, {format}' => 'Available variables: {slug}, {size}, {format}',
    'Download QR Code' => 'Download QR Code',

    // QR Code Actions
    'QR Code Actions' => 'QR Code Actions',
    'Reset to Defaults' => 'Reset to Defaults',
    'Reset QR code settings to plugin defaults?' => 'Reset QR code settings to plugin defaults?',
    'QR code settings reset to defaults' => 'QR code settings reset to defaults',
    'Live Preview' => 'Live Preview',
    'Preview' => 'Preview',
    'Click to view QR code page' => 'Click to view QR code page',
    'Toggle preview' => 'Toggle preview',
    'Please save to apply the volume change' => 'Please save to apply the volume change',
    'Size' => 'Size',
    'Custom Size...' => 'Custom Size...',
    'Enter custom size (100-4096 pixels):' => 'Enter custom size (100-4096 pixels):',
    'Please enter a valid size between 100 and 4096 pixels' => 'Please enter a valid size between 100 and 4096 pixels',

    // Asset Settings
    'Asset Settings' => 'Asset Settings',
    'Image Volume' => 'Image Volume',
    'Smart Link Image Volume' => 'Smart Link Image Volume',
    'Which asset volume should be used for Smart Link images' => 'Which asset volume should be used for Smart Link images',
    'All asset volumes' => 'All asset volumes',

    // Analytics Settings
    'Analytics Settings' => 'Analytics Settings',
    'Enable Analytics' => 'Enable Analytics',
    'Track Analytics' => 'Track Analytics',
    'Track clicks and visitor data for smart links' => 'Track clicks and visitor data for smart links',
    'When enabled, Smart Links will track visitor interactions, device types, geographic data, and other analytics information.' => 'When enabled, Smart Links will track visitor interactions, device types, geographic data, and other analytics information.',
    'Are you sure you want to disable analytics tracking for this smart link? This smart link will no longer collect visitor data and interactions.' => 'Are you sure you want to disable analytics tracking for this smart link? This smart link will no longer collect visitor data and interactions.',
    'Analytics Retention (days)' => 'Analytics Retention (days)',
    'Analytics Retention' => 'Analytics Retention',
    'How many days to keep analytics data (0 for unlimited, max 3650)' => 'How many days to keep analytics data (0 for unlimited, max 3650)',
    'Data Retention' => 'Data Retention',
    'Analytics Cleanup' => 'Analytics Cleanup',
    'Clean Up Now' => 'Clean Up Now',
    'Are you sure you want to clean up old analytics data now?' => 'Are you sure you want to clean up old analytics data now?',
    'Analytics cleanup job queued' => 'Analytics cleanup job queued',
    'Failed to queue cleanup job' => 'Failed to queue cleanup job',
    'Scheduled initial analytics cleanup job to run in 5 minutes' => 'Scheduled initial analytics cleanup job to run in 5 minutes',
    'Analytics cleanup job already scheduled, skipping' => 'Analytics cleanup job already scheduled, skipping',
    'Analytics cleanup settings updated' => 'Analytics cleanup settings updated',
    'Unlimited Retention Warning' => 'Unlimited Retention Warning',
    'Analytics data will be retained indefinitely. This could result in large database size, slower performance, and increased storage costs over time. Consider setting a retention period (recommended: 90-365 days) for production sites.' => 'Analytics data will be retained indefinitely. This could result in large database size, slower performance, and increased storage costs over time. Consider setting a retention period (recommended: 90-365 days) for production sites.',

    // Geographic Detection
    'Enable Geographic Detection' => 'Enable Geographic Detection',
    'Detect user location for analytics' => 'Detect user location for analytics',
    'Geographic Detection' => 'Geographic Detection',
    'Geographic Analytics' => 'Geographic Analytics',
    'Geographic Distribution' => 'Geographic Distribution',
    'View Geographic Details' => 'View Geographic Details',
    'Loading geographic data...' => 'Loading geographic data...',

    // Device Detection
    'Cache Device Detection' => 'Cache Device Detection',
    'Cache device detection results for better performance' => 'Cache device detection results for better performance',
    'Device Detection Cache Duration (seconds)' => 'Device Detection Cache Duration (seconds)',

    // Language Detection
    'Language Detection Method' => 'Language Detection Method',
    'How to detect user language preference' => 'How to detect user language preference',
    'Language Detection' => 'Language Detection',
    'Enable automatic language detection to redirect users based on their browser or location' => 'Enable automatic language detection to redirect users based on their browser or location',

    // Analytics Export
    'Analytics Export Options' => 'Analytics Export Options',
    'Export Settings' => 'Export Settings',
    'Include Disabled Links in Export' => 'Include Disabled Links in Export',
    'Include Disabled Smart Links in Export' => 'Include Disabled Smart Links in Export',
    'When enabled, analytics exports will include data from disabled smart links' => 'When enabled, analytics exports will include data from disabled smart links',
    'Include Expired Links in Export' => 'Include Expired Links in Export',
    'Include Expired Smart Links in Export' => 'Include Expired Smart Links in Export',
    'When enabled, analytics exports will include data from expired smart links' => 'When enabled, analytics exports will include data from expired smart links',
    'Export as CSV' => 'Export as CSV',

    // Redirect Settings
    'Custom Redirect Template' => 'Custom Redirect Template',
    'Path to custom template in your templates/ folder (e.g., smart-links/redirect)' => 'Path to custom template in your templates/ folder (e.g., smart-links/redirect)',
    'Custom QR Code Template' => 'Custom QR Code Template',
    'Path to custom template in your templates/ folder (e.g., smart-links/qr)' => 'Path to custom template in your templates/ folder (e.g., smart-links/qr)',
    'Redirect Settings' => 'Redirect Settings',
    'Redirect Behavior' => 'Redirect Behavior',
    '404 Redirect URL' => '404 Redirect URL',
    'Where to redirect when a smart link is not found or disabled' => 'Where to redirect when a smart link is not found or disabled',
    'Can be a relative path (/) or full URL (https://example.com)' => 'Can be a relative path (/) or full URL (https://example.com)',

    // Interface Settings
    'Interface Settings' => 'Interface Settings',
    'Items Per Page' => 'Items Per Page',
    'Number of smart links to show per page' => 'Number of smart links to show per page',
    'Allow Multiple' => 'Allow Multiple',
    'Whether to allow multiple smart links to be selected' => 'Whether to allow multiple smart links to be selected',

    // Advanced Settings
    'Advanced Settings' => 'Advanced Settings',

    // Analytics Dashboard
    'Smart Links Overview' => 'Smart Links Overview',
    'View Analytics' => 'View Analytics',
    'Traffic Overview' => 'Traffic Overview',
    'Total Links' => 'Total Links',
    'Active Links' => 'Active Links',
    'Total Clicks' => 'Total Clicks',
    'total clicks' => 'total clicks',
    'Clicks' => 'Clicks',
    'Unique Visitors' => 'Unique Visitors',
    'Top Smart Links' => 'Top Smart Links',
    'Top Performing Links (Last 7 Days)' => 'Top Performing Links (Last 7 Days)',
    'Top Countries' => 'Top Countries',
    'Top Cities' => 'Top Cities',
    'Top Cities Worldwide' => 'Top Cities Worldwide',
    'Device Breakdown' => 'Device Breakdown',
    'Device Types' => 'Device Types',
    'Device Brands' => 'Device Brands',
    'Operating Systems' => 'Operating Systems',
    'Browser Usage' => 'Browser Usage',
    'Daily Clicks' => 'Daily Clicks',
    'Usage Patterns' => 'Usage Patterns',
    'Peak Usage Hours' => 'Peak Usage Hours',
    'Peak usage at {hour}' => 'Peak usage at {hour}',
    'Avg. Clicks/Day' => 'Avg. Clicks/Day',
    'Engagement Rate' => 'Engagement Rate',
    'No analytics data yet' => 'No analytics data yet',
    'Analytics will appear here once your smart link starts receiving clicks.' => 'Analytics will appear here once your smart link starts receiving clicks.',
    'Failed to load analytics data' => 'Failed to load analytics data',
    'Failed to load countries data' => 'Failed to load countries data',
    'No data for selected period' => 'No data for selected period',
    'No country data available' => 'No country data available',
    'No city data available' => 'No city data available',

    // Time Periods
    'Today' => 'Today',
    'Yesterday' => 'Yesterday',
    'Last 7 days' => 'Last 7 days',
    'Last 30 days' => 'Last 30 days',
    'Last 90 days' => 'Last 90 days',
    'All time' => 'All time',

    // Analytics Data
    'Date' => 'Date',
    'Device' => 'Device',
    'Location' => 'Location',
    'Country' => 'Country',
    'Countries' => 'Countries',
    'City' => 'City',
    'Site' => 'Site',
    'Source' => 'Source',
    'Type' => 'Type',
    'OS' => 'OS',
    'Operating System' => 'Operating System',
    'Device Analytics' => 'Device Analytics',
    'Interactions' => 'Interactions',
    'Total Interactions' => 'Total Interactions',
    'Latest Interactions' => 'Latest Interactions',
    'No interactions recorded yet' => 'No interactions recorded yet',
    'Last Interaction' => 'Last Interaction',
    'Last Interaction Type' => 'Last Interaction Type',
    'Last Click' => 'Last Click',
    'Device information not available' => 'Device information not available',
    'OS information not available' => 'OS information not available',

    // Interaction Types
    'Direct' => 'Direct',
    'Direct Visits' => 'Direct Visits',
    'QR' => 'QR',
    'QR Scans' => 'QR Scans',
    'Button' => 'Button',
    'Landing' => 'Landing',

    // Actions
    'Actions' => 'Actions',
    'Save Settings' => 'Save Settings',
    'Manage Smart Links' => 'Manage Smart Links',

    // Messages
    'Loading...' => 'Loading...',
    'Error' => 'Error',
    'Name' => 'Name',
    'Percentage' => 'Percentage',

    // Dynamic Plugin Name Strings (with parameters)
    'Integrate {pluginName} with third-party analytics and tracking services to push click events to Google Tag Manager, Google Analytics, and other platforms.' => 'Integrate {pluginName} with third-party analytics and tracking services to push click events to Google Tag Manager, Google Analytics, and other platforms.',
    'Push {pluginName} events to SEOmatic\'s Google Tag Manager data layer for tracking in GTM and Google Analytics.' => 'Push {pluginName} events to SEOmatic\'s Google Tag Manager data layer for tracking in GTM and Google Analytics.',
    'Scripts receiving {pluginName} events' => 'Scripts receiving {pluginName} events',
    'Select which {pluginName} events to send to SEOmatic' => 'Select which {pluginName} events to send to SEOmatic',
    'Are you sure you want to clear all {pluginName} caches?' => 'Are you sure you want to clear all {pluginName} caches?',

    // Utilities
    'Monitor link performance, track analytics, and manage cache for your {singularName} redirects and QR codes.' => 'Monitor link performance, track analytics, and manage cache for your {singularName} redirects and QR codes.',
    'Active {pluginName}' => 'Active {pluginName}',

    // Smart Link Fields
    'The title of this {singularName}' => 'The title of this {singularName}',
    'A brief description of this {singularName}' => 'A brief description of this {singularName}',
    'Select an image for this {singularName}' => 'Select an image for this {singularName}',
    'Select the size for the {singularName} image' => 'Select the size for the {singularName} image',
    'Hide the {singularName} title on both redirect and QR code landing pages' => 'Hide the {singularName} title on both redirect and QR code landing pages',
    'Icon identifier or URL for this {singularName}' => 'Icon identifier or URL for this {singularName}',

    // Field Layout
    'Add custom fields to {singularName} elements. Any fields you add here will appear in the {singularName} edit screen.' => 'Add custom fields to {singularName} elements. Any fields you add here will appear in the {singularName} edit screen.',

    // Analytics Settings
    'Track clicks and visitor data for {pluginName}' => 'Track clicks and visitor data for {pluginName}',
    'When enabled, {pluginName} will track visitor interactions, device types, geographic data, and other analytics information.' => 'When enabled, {pluginName} will track visitor interactions, device types, geographic data, and other analytics information.',

    // Export Settings
    'Include Disabled {pluginName} in Export' => 'Include Disabled {pluginName} in Export',
    'When enabled, analytics exports will include data from disabled {pluginName}' => 'When enabled, analytics exports will include data from disabled {pluginName}',
    'Include Expired {pluginName} in Export' => 'Include Expired {pluginName} in Export',
    'When enabled, analytics exports will include data from expired {pluginName}' => 'When enabled, analytics exports will include data from expired {pluginName}',

    // Redirect Settings
    'Where to redirect when a {singularName} is not found or disabled' => 'Where to redirect when a {singularName} is not found or disabled',

    // General Settings
    '{singularName} URL Prefix' => '{singularName} URL Prefix',
    'The URL prefix for {pluginName} (e.g., \'go\' creates /go/your-link). Clear routes cache after changing (php craft clear-caches/compiled-templates).' => 'The URL prefix for {pluginName} (e.g., \'go\' creates /go/your-link). Clear routes cache after changing (php craft clear-caches/compiled-templates).',
    'Select which sites {pluginName} should be enabled for. Leave empty to enable for all sites.' => 'Select which sites {pluginName} should be enabled for. Leave empty to enable for all sites.',
    '{singularName} Image Volume' => '{singularName} Image Volume',
    'Which asset volume should be used for {singularName} images' => 'Which asset volume should be used for {singularName} images',

    // Interface Settings
    'Number of {pluginName} to show per page' => 'Number of {pluginName} to show per page',

    // Integration Settings
    '{pluginName} pushes events to GTM or GA4 dataLayer only' => '{pluginName} pushes events to GTM or GA4 dataLayer only',
    'Configure GTM triggers and tags to forward {pluginName} events to Facebook Pixel, LinkedIn, HubSpot, etc.' => 'Configure GTM triggers and tags to forward {pluginName} events to Facebook Pixel, LinkedIn, HubSpot, etc.',
    'Fathom, Matomo, and Plausible are shown above but do not receive events directly from {pluginName}' => 'Fathom, Matomo, and Plausible are shown above but do not receive events directly from {pluginName}',

    // Config Override Warnings
    'This is being overridden by the <code>pluginName</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>pluginName</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>enableAnalytics</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>enableAnalytics</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>analyticsRetention</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>analyticsRetention</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>includeDisabledInExport</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>includeDisabledInExport</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>includeExpiredInExport</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>includeExpiredInExport</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>defaultQrSize</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>defaultQrSize</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>defaultQrColor</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>defaultQrColor</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>defaultQrBgColor</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>defaultQrBgColor</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>defaultQrFormat</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>defaultQrFormat</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>qrCodeCacheDuration</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>qrCodeCacheDuration</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>defaultQrErrorCorrection</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>defaultQrErrorCorrection</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>defaultQrMargin</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>defaultQrMargin</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>qrModuleStyle</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>qrModuleStyle</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>qrEyeStyle</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>qrEyeStyle</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>qrEyeColor</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>qrEyeColor</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>enableQrLogo</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>enableQrLogo</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>qrLogoVolumeUid</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>qrLogoVolumeUid</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>imageVolumeUid</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>imageVolumeUid</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>qrLogoSize</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>qrLogoSize</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>enableQrDownload</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>enableQrDownload</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>qrDownloadFilename</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>qrDownloadFilename</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>redirectTemplate</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>redirectTemplate</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>qrTemplate</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>qrTemplate</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>enableGeoDetection</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>enableGeoDetection</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>cacheDeviceDetection</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>cacheDeviceDetection</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>deviceDetectionCacheDuration</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>deviceDetectionCacheDuration</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>languageDetectionMethod</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>languageDetectionMethod</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>itemsPerPage</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>itemsPerPage</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>notFoundRedirectUrl</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>notFoundRedirectUrl</code> setting in <code>config/smart-links.php</code>.',
    'This is being overridden by the <code>enabledSites</code> setting in <code>config/smart-links.php</code>.' => 'This is being overridden by the <code>enabledSites</code> setting in <code>config/smart-links.php</code>.',
];
