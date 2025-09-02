# Smart Links Configuration

## Configuration File

You can override plugin settings by creating a `smart-links.php` file in your `config/` directory.

### Basic Setup

1. Copy `vendor/lindemannrock/smart-links/src/config.php` to `config/smart-links.php`
2. Modify the settings as needed

### Available Settings

```php
<?php
return [
    // Analytics configuration
    'enableAnalytics' => true,
    'analyticsRetentionDays' => 90, // 0 = forever, max 3650
    'trackBotClicks' => false,
    
    // QR Code defaults
    'qrCodeSize' => 300, // pixels
    'qrCodeMargin' => 4,
    'qrCodeForegroundColor' => '#000000',
    'qrCodeBackgroundColor' => '#FFFFFF',
    'qrCodeErrorCorrection' => 'M', // L, M, Q, H
    'qrCodeFormat' => 'png', // png or svg
    
    // Caching
    'cacheEnabled' => true,
    'cacheDuration' => 3600, // seconds
    
    // Device detection
    'deviceDetectionMethod' => 'user-agent', // user-agent or client-hints
    'fallbackDevice' => 'desktop', // desktop, mobile, or tablet
    
    // Language detection  
    'languageDetectionMethod' => 'site', // site, browser, or query
    'fallbackLanguage' => 'en',
];
```

### Multi-Environment Configuration

You can have different settings per environment:

```php
<?php
return [
    // Global settings
    '*' => [
        'qrCodeSize' => 300,
        'qrCodeForegroundColor' => '#000000',
    ],
    
    // Development environment
    'dev' => [
        'enableAnalytics' => false,
        'cacheEnabled' => false,
    ],
    
    // Staging environment
    'staging' => [
        'enableAnalytics' => true,
        'analyticsRetentionDays' => 30,
    ],
    
    // Production environment
    'production' => [
        'enableAnalytics' => true,
        'analyticsRetentionDays' => 180,
        'cacheEnabled' => true,
        'cacheDuration' => 7200,
    ],
];
```

### Using Environment Variables

All settings support environment variables:

```php
return [
    'enableAnalytics' => getenv('SMART_LINKS_ANALYTICS') === 'true',
    'analyticsRetentionDays' => (int)getenv('SMART_LINKS_RETENTION') ?: 90,
    'qrCodeSize' => (int)getenv('QR_CODE_SIZE') ?: 300,
];
```

### Setting Descriptions

#### Analytics Settings

- **enableAnalytics**: Enable/disable click tracking and analytics
- **analyticsRetentionDays**: How many days to keep analytics data (0-3650, 0 = forever)
- **trackBotClicks**: Whether to track clicks from bots/crawlers

#### QR Code Settings

- **qrCodeSize**: Default size in pixels for generated QR codes
- **qrCodeMargin**: White space margin around QR code (quiet zone)
- **qrCodeForegroundColor**: Hex color for QR code foreground
- **qrCodeBackgroundColor**: Hex color for QR code background
- **qrCodeErrorCorrection**: Error correction level
  - `L` - ~7% correction
  - `M` - ~15% correction (default)
  - `Q` - ~25% correction
  - `H` - ~30% correction
- **qrCodeFormat**: Output format (png or svg)

#### Caching Settings

- **cacheEnabled**: Enable/disable caching of device detection and QR codes
- **cacheDuration**: Cache duration in seconds

#### Device Detection Settings

- **deviceDetectionMethod**: Method for detecting device type
  - `user-agent` - Parse User-Agent header (default)
  - `client-hints` - Use Client Hints API (modern browsers)
- **fallbackDevice**: Default device type when detection fails

#### Language Detection Settings

- **languageDetectionMethod**: Method for detecting user language
  - `site` - Use current Craft site language
  - `browser` - Use Accept-Language header
  - `query` - Use query parameter (?lang=xx)
- **fallbackLanguage**: Default language code when detection fails

### Precedence

Settings are loaded in this order (later overrides earlier):

1. Default plugin settings
2. Database-stored settings (from CP)
3. Config file settings
4. Environment-specific config settings

### Performance Recommendations

For production environments:

```php
'production' => [
    'enableAnalytics' => true,
    'analyticsRetentionDays' => 90, // Balance data vs storage
    'cacheEnabled' => true,
    'cacheDuration' => 7200, // 2 hours
    'trackBotClicks' => false, // Reduce noise in analytics
],
```

### Security Recommendations

```php
// Restrict QR code generation
'qrCodeSize' => min((int)getenv('QR_CODE_SIZE') ?: 300, 1000), // Max 1000px
'qrCodeFormat' => 'png', // PNG is safer than SVG for user input
```