---
title: Configuration Reference
category: configuration
order: 2
description: Complete configuration file options, analytics settings, and QR code customization
keywords: config, settings, analytics, qr codes, device detection
relatedPages:
  - slug: config-overview
    title: Configuration Overview
  - slug: analytics-dashboard
    title: Analytics Dashboard
---

# Smart Links Configuration

## Configuration File

You can override plugin settings by creating a `smart-links.php` file in your `config/` directory.

### Basic Setup

1. Copy `vendor/lindemannrock/smart-links/src/config.php` to `config/smart-links.php`
2. Modify the settings as needed

### Available Settings

```php
<?php
use craft\helpers\App;

return [
    // Plugin settings
    'pluginName' => 'Smart Links',

    // Logging settings
    'logLevel' => 'error', // error, warning, info, or debug

    // IP Privacy Protection
    // Generate salt with: php craft smart-links/security/generate-salt
    'ipHashSalt' => App::env('SMART_LINKS_IP_SALT'),

    // URL Settings
    'slugPrefix' => 'go', // URL prefix for smart links (e.g., 'go' creates /go/your-link)
    'qrPrefix' => 'go/qr', // URL prefix for QR code pages (e.g., 'go/qr' creates /go/qr/your-link)

    // Template Settings
    'redirectTemplate' => null, // Custom redirect landing page template path
    'qrTemplate' => null, // Custom QR code display page template path

    // Site Settings
    'enabledSites' => [], // Array of site IDs where Smart Links should be enabled (empty = all sites)

    // Asset Settings
    'imageVolumeUid' => null, // Asset volume UID for smart link images

    // Redirect Settings
    'notFoundRedirectUrl' => '/', // 404 redirect URL
    'languageDetectionMethod' => 'browser', // browser, ip, or both

    // Analytics configuration
    'enableAnalytics' => true,
    'analyticsRetention' => 90, // days (0 = unlimited, max 3650)
    'anonymizeIpAddress' => false, // Mask IPs for maximum privacy
    'includeDisabledInExport' => false,
    'includeExpiredInExport' => false,
    'enableGeoDetection' => false,

    // QR Code defaults
    'defaultQrSize' => 256, // pixels (100-1000)
    'defaultQrColor' => '#000000',
    'defaultQrBgColor' => '#FFFFFF',
    'defaultQrFormat' => 'png', // png or svg
    'defaultQrErrorCorrection' => 'M', // L, M, Q, H
    'defaultQrMargin' => 4, // quiet zone (0-10)
    'qrModuleStyle' => 'square', // square, rounded, dots
    'qrEyeStyle' => 'square', // square, rounded, leaf
    'qrEyeColor' => null, // null = same as module color

    // QR Code logo overlay
    'enableQrLogo' => false,
    'qrLogoVolumeUid' => null, // Asset volume UID for logos
    'defaultQrLogoId' => null, // Default logo asset ID
    'qrLogoSize' => 20, // Logo size percentage (10-30)

    // QR Code downloads
    'enableQrDownload' => true,
    'qrDownloadFilename' => '{slug}-qr-{size}', // Filename pattern

    // Cache Settings
    'enableQrCodeCache' => true, // Cache generated QR codes
    'qrCodeCacheDuration' => 86400, // 24 hours
    'cacheDeviceDetection' => true,
    'deviceDetectionCacheDuration' => 3600, // 1 hour

    // Interface settings
    'itemsPerPage' => 100, // Items per page in CP (10-500)

    // Integration Settings
    'enabledIntegrations' => [], // Enabled integration handles (e.g., ['seomatic'])
    'seomaticTrackingEvents' => ['redirect', 'button_click', 'qr_scan'], // Event types to track
    'seomaticEventPrefix' => 'smart_links', // Event prefix for GTM/GA events (lowercase, numbers, underscores only)
];
```

### Multi-Environment Configuration

You can have different settings per environment:

```php
<?php
return [
    // Global settings
    '*' => [
        'pluginName' => 'Smart Links',
        'enableAnalytics' => true,
        'logLevel' => 'error',
    ],

    // Development environment
    'dev' => [
        'logLevel' => 'debug',
        'analyticsRetention' => 30,
        'cacheDeviceDetection' => false,
        'enableQrCodeCache' => false,
    ],

    // Staging environment
    'staging' => [
        'logLevel' => 'info',
        'analyticsRetention' => 90,
        'qrCodeCacheDuration' => 3600,
    ],

    // Production environment
    'production' => [
        'logLevel' => 'error',
        'analyticsRetention' => 365,
        'qrCodeCacheDuration' => 604800,
    ],
];
```

### Using Environment Variables

Use `App::env()` to read environment variables in config files:

```php
use craft\helpers\App;

return [
    'ipHashSalt' => App::env('SMART_LINKS_IP_SALT'),
    'enableAnalytics' => (bool)App::env('SMART_LINKS_ANALYTICS') ?: true,
    'analyticsRetention' => (int)App::env('SMART_LINKS_RETENTION') ?: 90,
    'slugPrefix' => App::env('SMART_LINKS_PREFIX') ?: 'go',
    'notFoundRedirectUrl' => App::env('NOT_FOUND_URL') ?: '/',
];
```

**Important:**
- ✅ Use `App::env('VAR_NAME')` - Craft 5 recommended approach
- ❌ Don't use `getenv('VAR_NAME')` - Not thread-safe
- ✅ Always import: `use craft\helpers\App;`

### Setting Descriptions

#### Plugin Settings

- **pluginName**: Display name for the plugin in Craft CP navigation

#### Logging Settings

- **logLevel**: What types of messages to log ('debug', 'info', 'warning', 'error')
  - **error**: Critical errors only (default, production recommended)
  - **warning**: Errors and warnings
  - **info**: General information and successful operations
  - **debug**: Detailed debugging information (development only, requires devMode)

#### URL Settings

- **slugPrefix**: URL prefix for smart links (e.g., 'go' creates /go/your-link)
  - **Type:** `string`
  - **Default:** `'go'`
- **qrPrefix**: URL prefix for QR code pages (e.g., 'go/qr' creates /go/qr/your-link). Supports nested patterns.
  - **Type:** `string`
  - **Default:** `'go/qr'`

#### Template Settings

- **redirectTemplate**: Custom redirect landing page template path
  - **Type:** `string|null`
  - **Default:** `null`
  - **Example:** `'smart-links/redirect'`
- **qrTemplate**: Custom QR code display page template path
  - **Type:** `string|null`
  - **Default:** `null`
  - **Example:** `'smart-links/qr'`

#### Site Settings

- **enabledSites**: Array of site IDs where Smart Links should be enabled
  - **Type:** `array`
  - **Default:** `[]` (empty = all sites enabled)
  - **Example:** `[1, 3, 5]` (only enable for specific sites)

#### Asset Settings

- **imageVolumeUid**: Asset volume UID for smart link images
  - **Type:** `string|null`
  - **Default:** `null` (all volumes)

#### IP Privacy Settings

- **ipHashSalt**: Secure salt for IP address hashing (stored in `.env`)
  - **Required** when analytics is enabled
  - Generate with: `php craft smart-links/security/generate-salt`
  - Config usage: `App::env('SMART_LINKS_IP_SALT')`
  - Never commit to version control
  - Use the SAME salt across all environments

#### Analytics Settings

- **enableAnalytics**: Enable/disable click tracking and analytics
- **analyticsRetention**: How many days to keep analytics data (0-3650, 0 = unlimited)
- **anonymizeIpAddress**: Mask IP addresses before storage for maximum privacy
  - `false` (default): Full IP hashed with salt (accurate unique visitors)
  - `true`: Subnet masked then hashed (IPv4: last octet, IPv6: last 80 bits)
  - Trade-off: Reduces unique visitor accuracy but provides extra privacy
- **includeDisabledInExport**: Include disabled smart links in CSV exports
- **includeExpiredInExport**: Include expired smart links in CSV exports

#### Redirect Settings

- **notFoundRedirectUrl**: URL to redirect when smart link not found
  - **Type:** `string`
  - **Default:** `'/'`
- **languageDetectionMethod**: Method for detecting user language
  - **Type:** `string`
  - **Options:** `'browser'` (Accept-Language header), `'ip'` (IP-based), `'both'` (combine both)
  - **Default:** `'browser'`

#### QR Code Settings

- **defaultQrSize**: Default size in pixels for generated QR codes (100-1000)
- **defaultQrColor**: Hex color for QR code foreground (default: #000000)
  - Individual smart links inherit this value
  - Colors matching the global default are stored as NULL
  - Changing this global setting automatically updates all smart links without custom colors
- **defaultQrBgColor**: Hex color for QR code background (default: #FFFFFF)
  - Same inheritance behavior as defaultQrColor
- **defaultQrFormat**: Output format (png or svg)
- **defaultQrErrorCorrection**: Error correction level
  - `L` - ~7% correction
  - `M` - ~15% correction (default)
  - `Q` - ~25% correction
  - `H` - ~30% correction
- **defaultQrMargin**: White space margin around QR code (0-10)
- **qrModuleStyle**: QR code module style (square, rounded, dots)
- **qrEyeStyle**: QR code eye/finder pattern style (square, rounded, leaf)
- **qrEyeColor**: Custom color for eye patterns (null = same as module color)
- **qrCodeCacheDuration**: QR code cache duration in seconds

#### QR Code Logo Settings

- **enableQrLogo**: Enable logo overlay on QR codes
- **qrLogoVolumeUid**: Asset volume UID for logo selection (null = all volumes)
- **defaultQrLogoId**: Default logo asset ID
  - Individual smart links inherit this default logo
  - Only explicitly set logos are saved; otherwise uses this global default
  - Changing this global default updates all smart links without custom logos
- **qrLogoSize**: Logo size as percentage of QR code (10-30)

#### QR Code Download Settings

- **enableQrDownload**: Enable QR code download functionality
- **qrDownloadFilename**: Filename pattern for downloads (supports {slug} and {size})

#### Geographic Settings

- **enableGeoDetection**: Enable geographic detection for analytics
  - **Type:** `bool`
  - **Default:** `false`

#### Caching Settings

- **enableQrCodeCache**: Cache generated QR codes for better performance
  - **Type:** `bool`
  - **Default:** `true`
- **qrCodeCacheDuration**: QR code cache duration in seconds
  - **Type:** `int`
  - **Default:** `86400` (24 hours)
- **cacheDeviceDetection**: Enable/disable caching of device detection results
  - **Type:** `bool`
  - **Default:** `true`
- **deviceDetectionCacheDuration**: Device detection cache duration in seconds
  - **Type:** `int`
  - **Default:** `3600` (1 hour)

#### Interface Settings

- **itemsPerPage**: Number of items per page in CP element index (10-500)
  - **Type:** `int`
  - **Range:** `10-500`
  - **Default:** `100`

#### Integration Settings

- **enabledIntegrations**: Enabled integration handles
  - **Type:** `array`
  - **Default:** `[]` (no integrations enabled)
  - **Example:** `['seomatic']` (enable SEOmatic tracking)
- **seomaticTrackingEvents**: Event types to track in SEOmatic/Google Analytics
  - **Type:** `array`
  - **Default:** `['redirect', 'button_click', 'qr_scan']`
  - **Options:** `'redirect'` (link clicks), `'button_click'` (CTA button clicks), `'qr_scan'` (QR code views)
- **seomaticEventPrefix**: Event prefix for GTM/GA events
  - **Type:** `string`
  - **Default:** `'smart_links'`
  - **Format:** Lowercase, numbers, underscores only
  - **Example:** Events sent as `smart_links_redirect`, `smart_links_button_click`, etc.

### Precedence

Settings are loaded in this order (later overrides earlier):

1. Default plugin settings
2. Database-stored settings (from CP)
3. Config file settings
4. Environment-specific config settings

**Note:** Config file settings always override database settings, making them ideal for production environments where you want to enforce specific values.

## Read-Only Mode & Production Environments

Smart Links fully supports Craft's `allowAdminChanges` setting for production deployments.

### Enabling Read-Only Mode

Add to your `.env` file:

```bash
CRAFT_ALLOW_ADMIN_CHANGES=false
```

### What Happens in Read-Only Mode

When `allowAdminChanges` is disabled:

1. **Settings Pages** - Display with a read-only notice banner
2. **Form Fields** - All inputs are disabled (can view but not edit)
3. **Field Layout Designer** - Completely disabled, no drag-and-drop
4. **Save Actions** - Return 403 Forbidden HTTP errors
5. **Config Overrides** - Config file settings remain the source of truth

### Best Practices

**Development Environment:**
```bash
# .env
CRAFT_ALLOW_ADMIN_CHANGES=true
```

Configure settings through the Control Panel, which saves to the database.

**Staging/Production Environments:**
```bash
# .env
CRAFT_ALLOW_ADMIN_CHANGES=false
```

Use `config/smart-links.php` to manage settings:

```php
<?php
return [
    'production' => [
        'enableAnalytics' => true,
        'analyticsRetention' => 365,
        'slugPrefix' => 'go',
        'qrPrefix' => 'go/qr',
        'enableQrCodeCache' => true,
        'qrCodeCacheDuration' => 604800,
        'logLevel' => 'error',
    ],
];
```

### Field Layout Management

Field layouts are stored in project config and sync across environments:

- **Location:** `config/project/smart-links/fieldLayouts/{uid}.yaml`
- **Syncing:** Automatically applied when project config is synced
- **Read-Only:** Cannot be modified in CP when `allowAdminChanges=false`

To modify field layouts in production:
1. Make changes in development environment
2. Commit the updated YAML files in `config/project/`
3. Deploy to production
4. Run `php craft project-config/apply` if needed

### Performance Recommendations

For production environments:

```php
'production' => [
    'logLevel' => 'error',
    'analyticsRetention' => 365,
    'enableQrCodeCache' => true,
    'qrCodeCacheDuration' => 604800,  // 7 days
    'cacheDeviceDetection' => true,
    'deviceDetectionCacheDuration' => 7200,  // 2 hours
],
```

### Security Recommendations

```php
use craft\helpers\App;

// IP Privacy (Required)
'ipHashSalt' => App::env('SMART_LINKS_IP_SALT'), // Required for analytics
'anonymizeIpAddress' => true, // Extra privacy for EU/GDPR compliance

// Restrict QR code generation
'defaultQrSize' => 256, // Default size (100-1000 pixels)
'defaultQrFormat' => 'png', // PNG is safer than SVG for user input
```

### SEOmatic Integration

When SEOmatic plugin is installed and enabled, Smart Links can automatically track events to Google Analytics/GTM:

```php
'enabledIntegrations' => ['seomatic'],
'seomaticTrackingEvents' => ['redirect', 'button_click', 'qr_scan'],
'seomaticEventPrefix' => 'smart_links',
```

**Events tracked:**
- `smart_links_redirect` - When user clicks a smart link
- `smart_links_button_click` - When user clicks a CTA button in the smart link page
- `smart_links_qr_scan` - When user views the QR code page

**Event data includes:**
- Smart link slug
- Destination URL
- Device type (mobile, desktop, tablet)
- Browser information
- Geographic data (if enabled)


### IP Privacy Configuration

#### Required Setup

1. **Generate Salt (Local/Dev Only):**
   ```bash
   php craft smart-links/security/generate-salt
   ```

2. **Add to `.env`:**
   ```bash
   SMART_LINKS_IP_SALT="generated-64-character-salt"
   ```

3. **Copy to Other Environments:**
   - Manually add the SAME salt to `staging/.env` and `production/.env`
   - Never regenerate in staging/production

#### IP Anonymization Options

**Default (Salted Hash Only):**
```php
use craft\helpers\App;

'ipHashSalt' => App::env('SMART_LINKS_IP_SALT'),
'anonymizeIpAddress' => false,
```
- Full IP hashed with salt
- Accurate unique visitor tracking
- Rainbow-table proof
- Geo-location works normally

**Maximum Privacy (Anonymization + Salt):**
```php
use craft\helpers\App;

'ipHashSalt' => App::env('SMART_LINKS_IP_SALT'),
'anonymizeIpAddress' => true,
```
- IP masked before hashing
  - IPv4: `192.168.1.123` → `192.168.1.0`
  - IPv6: Masks last 80 bits
- Less accurate unique visitors (subnet-level)
- Extra privacy layer (even salt leak reveals only subnet)
- Geo-location still works
- Recommended for: EU, healthcare, government, high-privacy requirements