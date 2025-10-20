# Smart Links Plugin for Craft CMS

[![Latest Version](https://img.shields.io/packagist/v/lindemannrock/craft-smart-links.svg)](https://packagist.org/packages/lindemannrock/craft-smart-links)
[![Craft CMS](https://img.shields.io/badge/Craft%20CMS-5.0+-orange.svg)](https://craftcms.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![License](https://img.shields.io/packagist/l/lindemannrock/craft-smart-links.svg)](LICENSE)

Intelligent URL shortening and redirect management plugin for Craft CMS 5.x with device detection, QR codes, and analytics.

## Features

- **URL Shortening**: Create memorable short URLs that redirect to any destination
- **Device-Specific Redirects**: Different URLs for iOS, Android, Huawei, Amazon, Windows, macOS, and desktop users using accurate DeviceDetector library
- **Cache-Safe Device Detection**: Works with CDN/static page caching (Servd, Cloudflare) by fetching fresh device detection via uncached endpoint
- **Image Management**: Upload and configure images with multiple size options (xl, lg, md, sm)
- **QR Code Generation**: Automatic QR codes for each smart link with customizable colors, styles, and logo overlay
- **Landing Page Customization**: Hide titles on landing pages, custom layouts, and template override support
- **Advanced Analytics**:
  - Geographic tracking with country and city-level data
  - Peak usage hours visualization
  - Device, browser, and platform breakdown
  - Mobile usage insights by location
  - Real-time interaction tracking (auto-redirects and button clicks)
  - Last interaction type and destination URL tracking
  - Source tracking (QR code scan, landing page visit, or direct access)
  - Button click tracking by platform (App Store, Google Play, etc.)
  - Configurable analytics retention and export options
- **Smart Link Field**: Integrate smart links into your entries and elements
- **Multi-Site Support**: Different URLs per language/site (coming soon)
- **User-Friendly CP**: Clean interface matching Craft's design standards

## Requirements

- Craft CMS 5.0 or greater
- PHP 8.2 or greater
- [Logging Library](https://github.com/LindemannRock/craft-logging-library) 5.0 or greater (installed automatically as dependency)

## Installation

### Via Composer

```bash
cd /path/to/project
composer require lindemannrock/craft-smart-links
./craft plugin/install smart-links
```

### Using DDEV

```bash
cd /path/to/project
ddev composer require lindemannrock/craft-smart-links
ddev craft plugin/install smart-links
```

### Via Control Panel

In the Control Panel, go to Settings → Plugins and click "Install" for Smart Links.

## Configuration

### Settings

Navigate to **Settings → Smart Links** in the control panel to configure:

- **Enable Analytics**: Global toggle to enable/disable all analytics functionality (disables all tracking and hides analytics UI when off)
- **Analytics Retention**: How many days to keep analytics data (0 for unlimited)
- **Export Settings**: Include/exclude disabled smart links in analytics exports
- **QR Code Settings**: Default size, colors, and format (individual links can override)
- **Redirect Settings**: Language detection method and 404 redirect URL
- **Interface Settings**: Items per page in element index

### Config File

Create a `config/smart-links.php` file to override default settings:

```php
<?php
return [
    // Plugin Settings
    'pluginName' => 'Smart Links',

    // Logging Settings
    'logLevel' => 'error', // error, warning, info, or debug (debug requires devMode)

    // URL Settings
    'slugPrefix' => 'go',  // URL prefix for smart links (e.g., 'go' creates /go/your-link)
    'qrPrefix' => 'qr',    // URL prefix for QR code pages (e.g., 'qr' creates /qr/your-link)

    // Analytics Settings
    'enableAnalytics' => true,
    'analyticsRetention' => 90, // days (0 for unlimited)
    'includeDisabledInExport' => false,
    'includeExpiredInExport' => false,
    'enableGeoDetection' => false,

    // QR Code Settings
    'defaultQrSize' => 256,
    'defaultQrColor' => '#000000',
    'defaultQrBgColor' => '#FFFFFF',
    'defaultQrFormat' => 'png', // or 'svg'
    'defaultQrErrorCorrection' => 'M', // L, M, Q, H
    'defaultQrMargin' => 4,
    'qrModuleStyle' => 'square', // square, rounded, dots
    'qrEyeStyle' => 'square', // square, rounded, leaf
    'qrEyeColor' => null, // null = use main color
    'enableQrLogo' => false,
    'qrLogoSize' => 20, // percentage (10-30%)
    'defaultQrLogoId' => null,
    'enableQrDownload' => true,
    'qrDownloadFilename' => '{slug}-qr-{size}',
    'qrCodeCacheDuration' => 86400, // seconds

    // Template Settings
    'redirectTemplate' => null, // e.g., 'smart-links/redirect'
    'qrTemplate' => null, // e.g., 'smart-links/qr'

    // Device Detection & Caching
    'cacheDeviceDetection' => true,
    'deviceDetectionCacheDuration' => 3600, // seconds

    // Language & Redirect Settings
    'languageDetectionMethod' => 'browser', // 'browser', 'ip', or 'both'
    'notFoundRedirectUrl' => '/',

    // Interface Settings
    'itemsPerPage' => 100,

    // Site Selection
    'enabledSites' => [], // Array of site IDs, empty = all sites

    // Multi-environment support
    'dev' => [
        'logLevel' => 'debug', // More verbose in dev
        'enableAnalytics' => true,
        'analyticsRetention' => 30,
    ],
    'production' => [
        'logLevel' => 'error', // Only errors in production
        'enableAnalytics' => true,
        'analyticsRetention' => 365,
        'cacheDeviceDetection' => true,
        'deviceDetectionCacheDuration' => 7200,
    ],
];
```

**Important:** After changing `slugPrefix` or `qrPrefix`, clear Craft's routes cache:
```bash
php craft clear-caches/compiled-templates
```

See [Configuration Documentation](docs/CONFIGURATION.md) for all available options.

### Read-Only Mode

Smart Links respects Craft's `allowAdminChanges` setting for production environments. When `allowAdminChanges` is disabled:

- All settings pages display in read-only mode with a notice banner
- Field layout designer is disabled
- Save actions return 403 Forbidden errors
- Config file settings override database settings

**Enable read-only mode in your `.env`:**
```bash
CRAFT_ALLOW_ADMIN_CHANGES=false
```

This ensures settings and field layouts can only be modified through:
1. Project config (synced across environments)
2. Configuration files (`config/smart-links.php`)

**Best Practice:** Use `allowAdminChanges=false` in staging/production to prevent direct CP modifications and ensure consistency across environments.

## Multi-Site Management

Smart Links supports restricting functionality to specific sites in multi-site installations.

### Site Selection

Configure which sites Smart Links should be enabled for:

**Via Control Panel:**
- Go to **Settings → Plugins → Smart Links → General**
- Check the sites where Smart Links should be available
- Leave empty to enable for all sites

**Via Configuration File:**
```php
// config/smart-links.php
return [
    'enabledSites' => [1, 2], // Only enable for sites 1 and 2

    // Environment-specific overrides
    'dev' => [
        'enabledSites' => [1], // Only main site in development
    ],
    'production' => [
        'enabledSites' => [1, 2, 3], // All sites in production
    ],
];
```

**Behavior:**
- **CP Navigation**: Smart Links only appears in sidebar for enabled sites
- **Site Switcher**: Only enabled sites appear in the site dropdown
- **Access Control**: Direct access to disabled sites returns 403 Forbidden
- **Backwards Compatibility**: Empty selection enables all sites

**Important Notes:**
- If the primary site is not included in `enabledSites`, Smart Links will not appear in the main CP navigation at all, as the navigation uses the primary site context. Ensure you include your primary site ID if you want Smart Links accessible from the main menu.
- You can still access Smart Links on enabled non-primary sites via direct URLs, but the main navigation will be hidden.

## Usage

### Creating Smart Links

1. Navigate to **Smart Links** in the control panel
2. Click **"New smart link"**
3. Fill in the required fields:
   - **Name**: Internal reference name
   - **Slug**: The short URL path (e.g., `promo-2024`)
   - **Desktop URL**: Where desktop users go
   - **Mobile URL**: Where mobile users go (optional)
   - **Tablet URL**: Where tablet users go (optional)

### Smart Link URLs

Your smart links will be accessible at:
- `https://yourdomain.com/go/[slug]` - Redirect URL
- `https://yourdomain.com/qr/[slug]` - QR code image
- `https://yourdomain.com/qr/[slug]/view` - QR code display page

**Customizable URL Prefixes:**
You can customize the `/go/` and `/qr/` prefixes via Settings → General or Settings → QR Code:
- Change `slugPrefix` from `go` to `link`, `s`, or any custom prefix
- Change `qrPrefix` from `qr` to `qrcode`, `code`, or any custom prefix
- Only letters, numbers, hyphens, and underscores are allowed
- After changing, clear routes cache: `php craft clear-caches/compiled-templates`

### Using Smart Link Field

Add a Smart Link field to any element:

1. Go to **Settings → Fields**
2. Create a new **Smart Link** field
3. Add it to your field layout

In templates:
```twig
{# Get the smart link #}
{% set smartLink = entry.mySmartLinkField.one() %}

{# Output the redirect URL #}
<a href="{{ smartLink.getRedirectUrl() }}">{{ smartLink.name }}</a>

{# Get the QR code URL #}
<img src="{{ smartLink.getQrCodeUrl() }}" alt="QR Code">

{# Check if QR is enabled #}
{% if smartLink.qrCodeEnabled %}
    <img src="{{ siteUrl('qr/' ~ smartLink.slug) }}" alt="QR Code">
{% endif %}
```

### Analytics

Smart Links provides comprehensive analytics dashboard with interaction tracking:

#### Main Analytics View
Navigate to **Smart Links → Analytics** to see:
- Total interactions (auto-redirects and button clicks)
- Active links and links used percentage
- Daily interaction trends chart organized by sections:
  - **Traffic Overview**: Daily interactions visualization
  - **Device & Platform Analytics**: Separate charts for device types and operating systems
  - **Geographic Distribution**: Top countries and cities side by side
  - **Usage Patterns**: Peak hours and behavioral insights
- **Top Smart Links** table showing:
  - Total interactions count
  - Last interaction timestamp and type (redirect or button click)
  - Last destination URL (truncated to 25 characters)
  - QR code scans vs direct visits breakdown
- **Latest Interactions** table with detailed tracking:
  - Interaction type (redirect or button click)
  - Button type (App Store, Google Play, etc.) for button clicks
  - Source (QR scan, landing page, or direct access)
  - Destination URL, device info, OS, and location

#### Geographic Analytics
- **Top Countries**: See which countries your traffic comes from
- **Top Cities**: City-level breakdown with click percentages
- **View Geographic Details**: Comprehensive modal showing all countries and cities

#### Advanced Insights
- **Peak Usage Hours**: Hourly bar chart showing when users are most active
- **Mobile Usage by City**: See mobile vs desktop preferences by location
- **Browser Preferences**: Most popular browsers by country

#### Features
- Date range filtering with AJAX updates (Last 7 days, 30 days, 90 days, All time)
- Export analytics data to CSV with configurable options
- Real-time interaction tracking (auto-redirects and button clicks only)
- Privacy-focused IP hashing
- Automatic geographic detection using ip-api.com
- Global analytics toggle to disable all tracking
- Per-link analytics control with confirmation prompts

### Third-Party Integrations

Smart Links can integrate with third-party analytics and tracking services to push click events beyond its built-in analytics.

#### SEOmatic Integration

When [SEOmatic](https://plugins.craftcms.com/seomatic) is installed, Smart Links can push click events to Google Tag Manager's data layer for tracking in GTM and Google Analytics.

**Setup:**
1. Install and configure SEOmatic plugin with GTM or Google Analytics
2. Navigate to **Settings → Smart Links → Analytics**
3. Scroll to **Third-Party Integrations** section
4. Enable **SEOmatic Integration**
5. Select which events to track (redirects, button clicks, QR scans)
6. Customize the event prefix if needed (default: `smart_links`)
7. Save settings

**GTM Event Structure:**

Events are pushed to `window.dataLayer` with the following structure:

```javascript
{
  event: "smart_links_redirect",
  smart_link: {
    slug: "promo-2024",
    title: "Summer Promo",
    destination_url: "https://app.example.com/promo",
    platform: "ios",              // ios, android, windows, macos, other
    source: "qr",                 // qr, landing, direct
    device_type: "mobile",        // mobile, tablet, desktop
    os: "iOS 17",
    browser: "Safari",
    country: "United States",
    city: "New York",
    click_type: "button"          // button or redirect
  }
}
```

**Event Types:**
- `smart_links_redirect` - Auto-redirects (mobile users automatically redirected)
- `smart_links_button_click` - Button clicks (manual platform selection)
- `smart_links_qr_scan` - QR code scans (accessed via `?src=qr` parameter)

**GTM Trigger Setup:**

Create triggers in Google Tag Manager to listen for these events:

1. **Trigger Type**: Custom Event
2. **Event Name**: `smart_links_redirect` (or your custom prefix)
3. **Use regex matching** to catch all Smart Links events: `smart_links_.*`

**GA4 Event Example:**

Forward Smart Links events to Google Analytics 4:

```
Event Name: smart_link_click
Parameters:
  - link_slug: {{smart_link.slug}}
  - link_platform: {{smart_link.platform}}
  - link_source: {{smart_link.source}}
  - device_type: {{smart_link.device_type}}
```

**Configuration via Config File:**

```php
// config/smart-links.php
return [
    'enabledIntegrations' => ['seomatic'],
    'seomaticTrackingEvents' => ['redirect', 'button_click', 'qr_scan'],
    'seomaticEventPrefix' => 'smart_links',
];
```

**Important Notes:**
- Events are only sent when analytics tracking is enabled (globally and per-link)
- Requires SEOmatic plugin to be installed and enabled
- GTM or Google Analytics must be configured in SEOmatic
- Events include all analytics data Smart Links already tracks
- No additional external API calls or performance impact

**Template Usage:**

Add the tracking method to your templates to enable client-side event tracking:

```twig
{# templates/smart-links/redirect.twig #}
<!DOCTYPE html>
<html>
<head>
    <title>{{ smartLink.title }}</title>

    {# Render SEOmatic tracking script (outputs JavaScript if enabled, null if disabled) #}
    {{ smartLink.renderSeomaticTracking('redirect') }}
</head>
<body>
    {# Your template content #}
</body>
</html>
```

For QR code templates:
```twig
{# templates/smart-links/qr.twig #}
{{ smartLink.renderSeomaticTracking('qr_scan') }}
```

**Event Types:**
- `'redirect'` - Use for landing pages with buttons and auto-redirects
- `'qr_scan'` - Use for QR code display pages

**How It Works:**
- The method returns client-side JavaScript that pushes events to `window.dataLayer`
- Returns `null` if SEOmatic is not installed or disabled (no output)
- No need for `|raw` filter (returns `\Twig\Markup` automatically)
- Button clicks are intercepted with 300ms delay to ensure tracking completes
- Works with debug mode: add `?debug=1` to test tracking without redirects

### QR Codes

Each smart link automatically generates a QR code:

- Access at: `https://yourdomain.com/qr/[slug]`
- Customize size: `?size=300`
- Customize colors: `?color=FF0000&bg=FFFFFF`
- Returns PNG or SVG image based on settings
- Configurable default colors and size in settings
- Per-link QR code customization with reset button
- Live preview in edit page (280px, bottom-right corner)
- Logo overlay support (PNG format only, not available with SVG)
- Advanced styling options (module style, eye style, eye color)

### Individual Smart Link Settings

Each smart link has its own settings:

#### QR Code Customization
- Custom size, colors per link
- Reset to defaults button in sidebar
- Live preview in edit page

#### Analytics Control
- Toggle analytics tracking per link
- Confirmation prompt when disabling
- Respects global analytics setting

### Trashed Smart Links

When smart links are trashed:
- They are no longer accessible via their URLs
- They cannot be edited until restored
- They appear in the trashed status filter
- Analytics data is preserved until permanent deletion

## Templating

### Template Requirements

**Smart Links requires custom templates** for the redirect landing page and QR code display. These templates must be created in your project's `templates/` directory.

#### Default Template Paths

When `redirectTemplate` and `qrTemplate` are not configured (set to `null`), the plugin looks for:
- **Redirect landing page:** `templates/smart-links/redirect.twig`
- **QR code display:** `templates/smart-links/qr.twig`

**Important:** If these templates don't exist, visitors will see a "Unable to find template" error when accessing your smart links.

#### Quick Start: Copy Example Templates

The plugin includes example templates you can copy to get started:

```bash
# Create templates directory
mkdir -p templates/smart-links

# Copy example templates
cp vendor/lindemannrock/smart-links/src/templates/redirect.twig templates/smart-links/
cp vendor/lindemannrock/smart-links/src/templates/qr.twig templates/smart-links/

# Customize the templates to match your site's design
```

#### Custom Template Paths

You can use different template paths by configuring them:

**Via Config File:**
```php
// config/smart-links.php
return [
    'redirectTemplate' => 'my-custom/landing', // Path relative to templates/
    'qrTemplate' => 'my-custom/qr-display',    // Path relative to templates/
];
```

**Via Control Panel:**
Settings → Redirect Settings → Custom Redirect Template
Settings → QR Code → Custom QR Code Template

**Basic Template Example:**
```twig
{# templates/smart-links/redirect.twig #}
<!DOCTYPE html>
<html>
<head>
    <title>{{ smartLink.title }}</title>

    {# SEOmatic tracking integration (if enabled) #}
    {{ smartLink.renderSeomaticTracking('redirect') }}

    <script>
        // Client-side mobile detection for auto-redirect (works with cached pages)
        (function() {
            fetch('{{ actionUrl('smart-links/redirect/refresh-csrf')|raw }}', {
                credentials: 'same-origin',
                cache: 'no-store'
            })
            .then(r => r.json())
            .then(data => {
                if (data.isMobile) {
                    window.location.replace('{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'auto'})|raw }}');
                }
            })
            .catch(err => {
                console.error('Device detection failed:', err);
            });
        })();
    </script>
</head>
<body>
    <h1>{{ smartLink.title }}</h1>

    {# Platform-specific buttons that track clicks via redirect controller #}
    {% if smartLink.iosUrl %}
        <a href="{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'ios', site: smartLink.site.handle}) }}">Download on App Store</a>
    {% endif %}

    {% if smartLink.androidUrl %}
        <a href="{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'android', site: smartLink.site.handle}) }}">Get it on Google Play</a>
    {% endif %}

    {# Fallback button #}
    <a href="{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'fallback', site: smartLink.site.handle}) }}">Continue to Website</a>

    {# QR Code #}
    {% if smartLink.qrCodeEnabled %}
        <img src="{{ smartLink.getQrCodeUrl() }}" alt="QR Code">
    {% endif %}
</body>
</html>
```

**How Tracking Works:**

The tracking system uses the redirect controller to log all interactions:
- **Mobile auto-redirects**: JavaScript detects mobile and redirects via `platform: 'auto'`
- **Button clicks**: All buttons use `actionUrl('smart-links/redirect/go')` which tracks before redirecting
- **QR code scans**: QR codes include `?src=qr` parameter for source tracking
- **Works with CDN caching**: Device detection happens client-side via uncached endpoint
- **Desktop page loads**: Not tracked unless a button is clicked

**Available Template Variables:**
- `smartLink` - The SmartLink element
- `device` - DeviceInfo object with detection results
- `redirectUrl` - The calculated redirect URL for current device
- `language` - Detected language code

#### Custom QR Code Template

Create a custom QR code display page:

**Template Example:**
```twig
{# templates/smart-links/qr.twig #}
<!DOCTYPE html>
<html lang="{{ currentSite.language }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ smartLink.title }} - QR Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="flex justify-center items-center max-w-lg mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-lg max-w-lg w-full p-8 text-center">
            <h1 class="text-3xl font-semibold mb-4">{{ smartLink.title }}</h1>

            {% if smartLink.description %}
                <p class="text-gray-600 mb-8">{{ smartLink.description }}</p>
            {% endif %}

            {# QR Code display #}
            <div class="my-8 mx-auto">
                <img src="{{ smartLink.getQrCodeUrl({ size: size ?? 300 }) }}"
                     alt="{{ smartLink.title }} QR Code"
                     class="mx-auto">
            </div>

            <p class="text-sm text-gray-600">Scan with your phone's camera</p>
        </div>
    </div>
</body>
</html>
```

**Available QR Template Variables:**
- `smartLink` - The SmartLink element
- `size` - QR code size from URL parameter
- `format` - QR code format from URL parameter
- `color` - QR code color from URL parameter
- `bg` - QR code background color from URL parameter

### Available Properties

```twig
smartLink.id
smartLink.name
smartLink.slug
smartLink.title
smartLink.description
smartLink.fallbackUrl
smartLink.iosUrl
smartLink.androidUrl
smartLink.huaweiUrl
smartLink.amazonUrl
smartLink.windowsUrl
smartLink.macUrl
smartLink.enabled           {# Per-site status managed by Craft's element system #}
smartLink.trackAnalytics
smartLink.qrCodeEnabled
smartLink.hideTitle
smartLink.getImage()
smartLink.imageSize
smartLink.clicks            {# Dynamically calculated from analytics data #}
smartLink.dateCreated
smartLink.dateUpdated
```

**Note:**
- `enabled` is a per-site property managed by Craft CMS's element system (stored in `elements_sites` table)
- `clicks` is dynamically calculated by counting records in the `smartlinks_analytics` table

### Device Detection Properties

```twig
device.isMobile
device.isTablet
device.isDesktop
device.platform  {# ios, android, huawei, windows, macos, linux, other #}
device.deviceType
device.brand
device.osName
device.osVersion
device.browser
device.language
```

### Methods

```twig
{# Get the appropriate redirect URL for current device #}
smartLink.getRedirectUrl()

{# Get QR code URL #}
smartLink.getQrCodeUrl(size = 200)

{# Get QR code display page URL #}
smartLink.getQrCodeDisplayUrl()

{# Get full URL #}
smartLink.getUrl()

{# Get image asset #}
smartLink.getImage()

{# Render SEOmatic tracking script (returns Twig\Markup or null) #}
smartLink.renderSeomaticTracking(eventType = 'qr_scan')
{# Event types: 'redirect' for landing pages, 'qr_scan' for QR code pages #}
```

### GraphQL Support

```graphql
query {
  smartLinks {
    id
    name
    slug
    desktopUrl
    mobileUrl
    enabled
    clicks
  }
}
```

## Events

```php
use lindemannrock\smartlinks\events\RedirectEvent;
use lindemannrock\smartlinks\services\RedirectService;
use yii\base\Event;

Event::on(
    RedirectService::class,
    RedirectService::EVENT_BEFORE_REDIRECT,
    function(RedirectEvent $event) {
        // Modify redirect URL
        if ($event->device === 'mobile') {
            $event->url = 'https://m.example.com';
        }
    }
);
```

## Console Commands

```bash
# Update missing country data for analytics
./craft smart-links/analytics/update-countries

# Update missing city data for analytics
./craft smart-links/analytics/update-cities
```

## Logging

Smart Links uses the [LindemannRock Logging Library](https://github.com/LindemannRock/craft-logging-library) for centralized, structured logging across all LindemannRock plugins.

### Log Levels
- **Error**: Critical errors only (default)
- **Warning**: Errors and warnings
- **Info**: General information
- **Debug**: Detailed debugging (includes performance metrics, requires devMode)

### Configuration
```php
// config/smart-links.php
return [
    'logLevel' => 'error', // error, warning, info, or debug
];
```

**Note:** Debug level requires Craft's `devMode` to be enabled. If set to debug with devMode disabled, it automatically falls back to info level.

### Log Files
- **Location**: `storage/logs/smart-links-YYYY-MM-DD.log`
- **Retention**: 30 days (automatic cleanup via Logging Library)
- **Format**: Structured JSON logs with context data
- **Web Interface**: View and filter logs in CP at Smart Links → Logs

### Log Management
Access logs through the Control Panel:
1. Navigate to Smart Links → Logs
2. Filter by date, level, or search terms
3. Download log files for external analysis
4. View file sizes and entry counts
5. Auto-cleanup after 30 days (configurable via Logging Library)

**Requires:** `lindemannrock/logginglibrary` plugin (installed automatically as dependency)

See [docs/LOGGING.md](docs/LOGGING.md) for detailed logging documentation.

## Troubleshooting

### QR Codes Not Generating
- Ensure GD or ImageMagick is installed
- Check file permissions on `storage/runtime/`
- Verify QR codes are enabled in settings

### Redirects Not Working
- Check `.htaccess` or nginx config allows `/go/` URLs
- Ensure smart link is enabled
- Verify URLs are properly formatted

### How Mobile Redirects Work
Mobile users will briefly see the landing page before being automatically redirected:
1. All users (mobile and desktop) see the landing page with JavaScript
2. JavaScript fetches fresh device detection from `/smart-links/redirect/refresh-csrf` (uncached endpoint)
3. If mobile device is detected, JavaScript redirects via `actionUrl('smart-links/redirect/go', {platform: 'auto'})`
4. Desktop users stay on the landing page with platform buttons

This client-side approach ensures tracking works correctly even when pages are cached by CDN (Servd, Cloudflare).

**Troubleshooting:**
- Ensure mobile detection script is in your template (fetches from `refresh-csrf` endpoint)
- Ensure `/smart-links/redirect/refresh-csrf` endpoint is not being cached
- Check browser console for errors
- The brief landing page flash is normal and necessary for tracking to work with page caching

### Analytics Not Tracking
- Confirm analytics is enabled globally in Settings → General
- Verify per-link analytics is enabled for the smart link
- Ensure buttons use `actionUrl('smart-links/redirect/go')` instead of direct URLs
- Check browser isn't blocking JavaScript
- Check browser console for errors
- Desktop page loads without `?src=qr` parameter are intentionally NOT tracked

### Wrong Location in Local Development
When running locally (DDEV, localhost), analytics will default to Saudi Arabia because local IPs can't be geolocated. To set your actual location for testing:

```bash
# Add to your .env file:
SMART_LINKS_DEFAULT_COUNTRY=AE
SMART_LINKS_DEFAULT_CITY=Dubai
```

Supported locations:
- UAE: Dubai, Abu Dhabi
- Saudi Arabia: Riyadh, Jeddah

## Multi-Site Considerations

Multi-site support is coming soon. Future features will include:
- Different URLs per site/language
- Separate slugs for each locale
- Per-site analytics tracking
- Propagation settings

## Support

- **Documentation**: [https://github.com/LindemannRock/craft-smart-links](https://github.com/LindemannRock/craft-smart-links)
- **Issues**: [https://github.com/LindemannRock/craft-smart-links/issues](https://github.com/LindemannRock/craft-smart-links/issues)
- **Email**: [support@lindemannrock.com](mailto:support@lindemannrock.com)

## License

This plugin is licensed under the MIT License. See [LICENSE](LICENSE) for details.

---

Developed by [LindemannRock](https://lindemannrock.com)
