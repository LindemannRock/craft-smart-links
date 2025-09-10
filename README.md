# Smart Links Plugin for Craft CMS

Intelligent URL shortening and redirect management plugin for Craft CMS 5.x with device detection, QR codes, and analytics.

## Features

- **URL Shortening**: Create memorable short URLs that redirect to any destination
- **Device-Specific Redirects**: Different URLs for iOS, Android, Windows, macOS, and desktop users
- **QR Code Generation**: Automatic QR codes for each smart link with customizable colors
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

## Installation

### Via Composer (Development)

Until published on Packagist, install directly from the repository:

```bash
cd /path/to/project
composer config repositories.smart-links vcs https://github.com/LindemannRock/smart-links
composer require lindemannrock/smart-links:dev-main
./craft plugin/install smart-links
```

### Via Composer (Production - Coming Soon)

Once published on Packagist:

```bash
cd /path/to/project
composer require lindemannrock/smart-links
./craft plugin/install smart-links
```

### Via Plugin Store (Future)

1. Go to the Plugin Store in your Craft control panel
2. Search for "Smart Links"  
3. Click "Install"

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
    'enableAnalytics' => true,
    'analyticsRetention' => 90, // days (0 for unlimited)
    'includeDisabledInExport' => false,
    'defaultQrSize' => 256,
    'defaultQrColor' => '#000000',
    'defaultQrBgColor' => '#FFFFFF',
    'defaultQrFormat' => 'png', // or 'svg'
    'itemsPerPage' => 100,
    'languageDetectionMethod' => 'browser', // 'browser', 'ip', or 'both'
    'notFoundRedirectUrl' => '/',
    // Multi-environment support
    'production' => [
        'enableAnalytics' => true,
        'cacheDeviceDetection' => true,
        'deviceDetectionCacheDuration' => 3600,
    ],
];
```

See [Configuration Documentation](docs/CONFIGURATION.md) for all available options.

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
- `https://yourdomain.com/go/[slug]`
- `https://yourdomain.com/qr/[slug]` (QR code image)

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

### Available Properties

```twig
smartLink.id
smartLink.name
smartLink.slug
smartLink.desktopUrl
smartLink.mobileUrl
smartLink.tabletUrl
smartLink.enabled
smartLink.trackAnalytics
smartLink.qrCodeEnabled
smartLink.qrCodeSize
smartLink.qrCodeColor
smartLink.qrCodeBgColor
smartLink.clicks
smartLink.dateCreated
smartLink.dateUpdated
```

### Methods

```twig
{# Get the appropriate redirect URL for current device #}
smartLink.getRedirectUrl()

{# Get QR code URL #}
smartLink.getQrCodeUrl(size = 200)

{# Get full URL #}
smartLink.getUrl()
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
# List all smart links
./craft smart-links/links/list

# Create a smart link
./craft smart-links/links/create --name="Promo" --slug="promo" --url="https://example.com"

# Delete old analytics data
./craft smart-links/analytics/cleanup --days=90

# Update missing country data for analytics
./craft smart-links/analytics/update-countries

# Update missing city data for analytics
./craft smart-links/analytics/update-cities
```

## Troubleshooting

### QR Codes Not Generating
- Ensure GD or ImageMagick is installed
- Check file permissions on `storage/runtime/`
- Verify QR codes are enabled in settings

### Redirects Not Working
- Check `.htaccess` or nginx config allows `/go/` URLs
- Ensure smart link is enabled
- Verify URLs are properly formatted

### Analytics Not Tracking
- Confirm analytics is enabled in settings
- Check browser isn't blocking JavaScript
- Verify database migrations ran successfully

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

- **Documentation**: [https://github.com/LindemannRock/smart-links](https://github.com/LindemannRock/smart-links)
- **Issues**: [https://github.com/LindemannRock/smart-links/issues](https://github.com/LindemannRock/smart-links/issues)
- **Email**: [support@lindemannrock.com](mailto:support@lindemannrock.com)

## License

This plugin is licensed under the MIT License. See [LICENSE](LICENSE) for details.

---

Developed by [LindemannRock](https://lindemannrock.com)