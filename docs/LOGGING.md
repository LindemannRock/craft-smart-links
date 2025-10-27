# Smart Links Logging

Smart Links uses the [LindemannRock Logging Library](https://github.com/LindemannRock/craft-logging-library) for centralized, structured logging across all LindemannRock plugins.

## Log Levels

- **Error**: Critical errors only (default)
- **Warning**: Errors and warnings
- **Info**: General information
- **Debug**: Detailed debugging (includes performance metrics, requires devMode)

## Configuration

### Control Panel

1. Navigate to **Settings → Smart Links → General**
2. Scroll to **Logging Settings**
3. Select desired log level from dropdown
4. Click **Save**

### Config File

```php
// config/smart-links.php
return [
    'pluginName' => 'Links',     // Optional: Customize plugin name shown in logs interface
    'logLevel' => 'error',       // error, warning, info, or debug
];
```

**Notes:**
- The `pluginName` setting customizes how the plugin name appears in the log viewer interface (page title, breadcrumbs, etc.). If not set, it defaults to "Smart Links".
- Debug level requires Craft's `devMode` to be enabled. If set to debug with devMode disabled, it automatically falls back to info level.

## Log Files

- **Location**: `storage/logs/smart-links-YYYY-MM-DD.log`
- **Retention**: 30 days (automatic cleanup via Logging Library)
- **Format**: Structured JSON logs with context data
- **Web Interface**: View and filter logs in CP at Smart Links → Logs

## What's Logged

The plugin logs meaningful events using context arrays for structured data. All logs include user context when available.

### Analytics Operations (AnalyticsController)

- **[INFO]** `Analytics getData called` - Analytics data retrieval
  - Context: `type`, `dateRange`, `smartLinkId`
- **[ERROR]** `Analytics getData error` - Analytics query failure
  - Context: `error` (exception message), `trace` (stack trace)
- **[ERROR]** `Failed to get analytics data` - Analytics fetch error
  - Context: `error` (exception message)

### Smart Link Operations (SmartLinksController)

- **[ERROR]** `Smart link save failed` - Smart link validation errors
  - Context: `errors` (validation errors array)
- **[ERROR]** `Smart link save error` - Smart link save exception
  - Context: `error` (exception message), `trace` (stack trace)

### QR Code Operations (QrCodeController)

#### Display Operations
- **[INFO]** `SmartLink redirect URL (display)` - Redirect URL for QR display
  - Context: `url`
- **[INFO]** `Full URL for QR` - Full URL including domain
  - Context: `fullUrl`
- **[ERROR]** `Failed to generate QR code` - QR code generation failure (display)
  - Context: `error` (exception message)

#### Generation Operations
- **[INFO]** `SmartLink redirect URL (generate)` - Redirect URL for QR generation
  - Context: `url`
- **[INFO]** `Full URL for QR` - Full URL for QR code
  - Context: `fullUrl`
- **[ERROR]** `Failed to generate QR code` - QR code generation failure (generate)
  - Context: `error` (exception message)

### QR Code Service (QrCodeService)

- **[ERROR]** `Failed to add logo to QR code` - Logo overlay failure
  - Context: `error` (exception message)

### Analytics Service (AnalyticsService)

#### Date Filtering
- **[INFO]** `Today filter` - Today's date range filter applied
  - Context: `start` (start date), `end` (end date)

#### Save Operations
- **[INFO]** `Saving Smart Link analytics` - Analytics save initiated
  - Context: `linkId`
- **[ERROR]** `Failed to hash IP address` - IP hashing failed
  - Context: `error` (exception message)
- **[ERROR]** `IP hash salt not configured - analytics tracking disabled` - Missing IP hash salt
  - Context: `ip` (always 'hidden'), `saltValue` (NULL or unparsed string)
  - **Solution**: Run `php craft smart-links/security/generate-salt`
- **[ERROR]** `Failed to save analytics` - Analytics save failure (multiple contexts)
  - Context variations:
    - `error` (exception message)
    - `error`, `linkId`, `data`, `trace` (detailed failure context)

#### Geolocation
- **[WARNING]** `Failed to get location from IP` - IP geolocation lookup failed
  - Context: `error` (exception message)

### Smart Links Service (SmartLinksService)

- **[INFO]** `Smart link not saved due to validation errors` - Validation failure notice
  - Context: `errors` (validation errors array)

### Settings Controller (SettingsController)

#### Field Layout Configuration
- **[DEBUG]** `Field Layout debug info` - Field layout debugging information
  - Context: `uid`, `currentFieldLayout`, `fromConfig`, `forElement`
- **[DEBUG]** `actionFieldLayout called` - Field layout action called
  - Context: `smartLinkUid`, `layoutUid`

#### Settings Save Operations
- **[DEBUG]** `Settings data received` - Settings data posted
  - Context: `settingsData` (posted settings)
- **[DEBUG]** `imageVolumeUid debug` - Image volume UID debugging
  - Context: `posted`, `fromSettings`, `fromGetBodyParam`
- **[DEBUG]** `All POST data` - Complete POST data dump
  - Context: `bodyParams` (all POST parameters)
- **[DEBUG]** `Auto-setting qrLogoVolumeUid to match imageVolumeUid` - QR logo volume auto-set
  - Context: `uid` (volume UID)
- **[DEBUG]** `Settings after updates` - Settings state after updates
  - Context: `enabledSites` (enabled sites array)
- **[ERROR]** `Settings validation failed` - Settings validation errors
  - Context: `errors` (validation errors array)

### Settings Model (Settings)

#### Log Level Adjustments
- **[WARNING]** `Log level "debug" from config file changed to "info" because devMode is disabled` - Debug level auto-corrected from config file
  - Context: `configFile` (path to config file)
- **[WARNING]** `Log level automatically changed from "debug" to "info" because devMode is disabled` - Debug level auto-corrected from database setting

#### Loading Operations
- **[ERROR]** `Failed to load settings from database` - Database query error
  - Context: `error` (exception message)
- **[WARNING]** `No settings found in database` - No settings record exists in database

#### Validation & Save Operations
- **[ERROR]** `Settings validation failed` - Settings model validation errors
  - Context: `errors` (validation errors array)
- **[DEBUG]** `Attempting to save settings` - Settings save operation initiated
  - Context: `attributes` (settings being saved)
- **[DEBUG]** `Database update result` - Database update operation result
  - Context: `result` (update result)
- **[INFO]** `Settings saved successfully to database` - Settings saved
- **[ERROR]** `Database update returned false` - Database update operation returned false
- **[ERROR]** `Failed to save Smart Links settings` - Settings save exception
  - Context: `error` (exception message)

### Redirect Controller (RedirectController)

- **[INFO]** `Smart link 404 handled by Redirect Manager` - 404 forwarded to Redirect Manager plugin
  - Context: `slug`, `hasRedirectManager`
- **[INFO]** `SEOmatic client-side tracking: {clickType} event for '{slug}'` - SEOmatic tracking event
  - Context: `linkId`, `clickType`, `slug`

### Integration Service (IntegrationService)

- **[DEBUG]** `Integrations loaded` - Integrations loaded successfully
  - Context: `count` (number of integrations), `names` (integration names)
- **[ERROR]** `Failed to load integrations` - Integration loading failed
  - Context: `error` (exception message)
- **[DEBUG]** `Registered integration` - Integration registered
  - Context: `name` (integration name), `class` (integration class)
- **[ERROR]** `Integration failed` - Integration execution error
  - Context: `name` (integration name), `error` (exception message)
- **[ERROR]** `Failed to render SEOmatic tracking` - SEOmatic tracking render error
  - Context: `error` (exception message)

### Cleanup Analytics Job (CleanupAnalyticsJob)

- **[INFO]** `Cleaned up analytics records` - Scheduled analytics cleanup completed
  - Context: `deleted` (number of records deleted), `retentionDays`

### Track Analytics Job (TrackAnalyticsJob)

- **[ERROR]** `Failed to save analytics for link` - Analytics tracking job failed
  - Context: `linkId`

### Base Integration (BaseIntegration)

- **[WARNING]** `Unknown event type: {eventType}` - Unknown event type received
- **[WARNING]** `Missing required fields for {eventType}: {fields}` - Required fields missing for event

### SEOmatic Integration (SeomaticIntegration)

#### Plugin Availability
- **[DEBUG]** `SEOmatic plugin not available` - SEOmatic plugin not installed
- **[DEBUG]** `SEOmatic integration not enabled` - SEOmatic integration disabled in settings
- **[DEBUG]** `Event type '{eventType}' not configured for tracking` - Event type not configured

#### Event Tracking
- **[INFO]** `Event '{eventType}' queued successfully` - Event queued for tracking
  - Context: `eventType`, `eventData`
- **[ERROR]** `Failed to push event` - Event push failed
  - Context: `error` (exception message), `eventType`

#### Data Layer Injection
- **[DEBUG]** `SEOmatic script service not available` - SEOmatic script service unavailable
- **[DEBUG]** `Event injected into GTM data layer` - GTM event injected
  - Context: `event` (event data)
- **[DEBUG]** `Event injected into gtag data layer` - Google Analytics event injected
  - Context: `event` (event data)
- **[DEBUG]** `No active tracking scripts found in SEOmatic` - No tracking scripts active
- **[ERROR]** `Failed to inject data layer event` - Data layer injection failed
  - Context: `error` (exception message)

#### Event Listeners
- **[DEBUG]** `Registered SEOmatic event listeners` - Event listeners registered
- **[DEBUG]** `Injected queued events` - Queued events injected into page
  - Context: `count` (number of events)
- **[ERROR]** `Error in AddDynamicMeta handler` - AddDynamicMeta event handler error
  - Context: `error` (exception message)
- **[ERROR]** `Error getting SEOmatic status` - SEOmatic status check error
  - Context: `error` (exception message)

### Main Plugin (SmartLinks)

- **[INFO]** `Scheduled initial analytics cleanup job` - Analytics cleanup job scheduled
  - Context: `interval` (cleanup interval)
- **[INFO]** `Applied Smart Links field layout from project config` - Field layout applied from project config
  - Context: `uid` (field layout UID)

## Log Management

### Via Control Panel

1. Navigate to **Smart Links → Logs**
2. Filter by date, level, or search terms
3. Download log files for external analysis
4. View file sizes and entry counts
5. Auto-cleanup after 30 days (configurable via Logging Library)

### Via Command Line

**View today's log**:

```bash
tail -f storage/logs/smart-links-$(date +%Y-%m-%d).log
```

**View specific date**:

```bash
cat storage/logs/smart-links-2025-01-15.log
```

**Search across all logs**:

```bash
grep "QR code" storage/logs/smart-links-*.log
```

**Filter by log level**:

```bash
grep "\[ERROR\]" storage/logs/smart-links-*.log
```

## Log Format

Each log entry follows structured JSON format with context data:

```json
{
  "timestamp": "2025-01-15 14:30:45",
  "level": "INFO",
  "message": "Analytics getData called",
  "context": {
    "type": "clicks",
    "dateRange": "7days",
    "smartLinkId": 123,
    "userId": 1
  },
  "category": "lindemannrock\\smartlinks\\controllers\\AnalyticsController"
}
```

## Using the Logging Trait

All services and controllers in Smart Links use the `LoggingTrait` from the LindemannRock Logging Library:

```php
use lindemannrock\logginglibrary\traits\LoggingTrait;

class MyService extends Component
{
    use LoggingTrait;

    public function myMethod()
    {
        // Info level - general operations
        $this->logInfo('Operation started', ['param' => $value]);

        // Warning level - important but non-critical
        $this->logWarning('Missing data', ['key' => $missingKey]);

        // Error level - failures and exceptions
        $this->logError('Operation failed', ['error' => $e->getMessage()]);

        // Debug level - detailed information
        $this->logDebug('Processing item', ['item' => $itemData]);
    }
}
```

## Best Practices

### 1. DO NOT Log in init() ⚠️

The `init()` method is called on **every request** (every page load, AJAX call, etc.). Logging there will flood your logs with duplicate entries.

```php
// ❌ BAD - Causes log flooding
public function init(): void
{
    parent::init();
    $this->logInfo('Plugin initialized');  // Called on EVERY request!
}

// ✅ GOOD - Log actual operations
public function handleRedirect($slug): void
{
    $this->logInfo('Smart link redirect processed', ['slug' => $slug]);
    // ... your logic
}
```

### 2. Always Use Context Arrays

Use the second parameter for variable data, not string concatenation:

```php
// ❌ BAD - Concatenating variables into message
$this->logError('QR generation failed: ' . $e->getMessage());
$this->logInfo('Processing link: ' . $slug);

// ✅ GOOD - Use context array for variables
$this->logError('QR generation failed', ['error' => $e->getMessage()]);
$this->logInfo('Processing link', ['slug' => $slug]);
```

**Why Context Arrays Are Better:**
- Structured data for log analysis tools
- Easier to search and filter in log viewer
- Consistent formatting across all logs
- Automatic JSON encoding with UTF-8 support

### 3. Use Appropriate Log Levels

- **debug**: Internal state, variable dumps (requires devMode)
- **info**: Normal operations, user actions
- **warning**: Unexpected but handled situations
- **error**: Actual errors that prevent operation

### 4. Security

- Never log passwords or sensitive data
- Be careful with user input in log messages
- Never log API keys, tokens, or credentials

## Performance Considerations

- **Error/Warning levels**: Minimal performance impact, suitable for production
  - Logs only failures and important warnings
  - Suitable for production environments
- **Info level**: Moderate logging, useful for tracking operations
  - Logs smart link saves and validation
  - Analytics operations
  - QR code generation
  - Integration events
  - Job completions
  - Settings saves
- **Debug level**: Extensive logging, use only in development (requires devMode)
  - Includes performance metrics
  - Logs detailed analytics data
  - Tracks redirect operations
  - Records device detection details
  - **Settings operations**: Full POST data dumps, volume UID debugging
  - **Field layout**: Configuration debugging with UIDs and layouts
  - **Integrations**: Loading, registration, and execution details
  - **SEOmatic**: Event tracking, data layer injection, script availability
  - **Database**: Update results and operation tracking

## Requirements

Smart Links logging requires:

- **lindemannrock/logginglibrary** plugin (installed automatically as dependency)
- Write permissions on `storage/logs` directory
- Craft CMS 5.x or later

## Troubleshooting

If logs aren't appearing:

1. **Check permissions**: Verify `storage/logs` directory is writable
2. **Verify library**: Ensure LindemannRock Logging Library is installed and enabled
3. **Check log level**: Confirm log level allows the messages you're looking for
4. **devMode for debug**: Debug level requires `devMode` enabled in `config/general.php`
5. **Check CP interface**: Use Smart Links → Logs to verify log files exist

## Common Scenarios

### QR Code Generation Issues

When QR codes fail to generate, check for:

```bash
grep "Failed to generate QR code" storage/logs/smart-links-*.log
```

Look for:
- Missing GD or ImageMagick library
- File permission issues
- Invalid QR code parameters
- Logo overlay failures

### Analytics Tracking Problems

Debug analytics issues:

```bash
grep "analytics" storage/logs/smart-links-*.log
```

Common issues:
- `IP hash salt not configured` - Salt missing from `.env` (run `php craft smart-links/security/generate-salt`)
- `Failed to save analytics` - Check database connectivity or salt configuration
- `Failed to get location from IP` - IP geolocation service issues
- `Analytics getData error` - Query or date range problems

### Smart Link Save Failures

Track smart link save issues:

```bash
grep "Smart link save" storage/logs/smart-links-*.log
```

Review validation errors in the context to identify:
- Missing required fields
- Invalid URLs
- Slug conflicts
- Database constraints

### Geolocation Issues

Monitor IP geolocation lookups:

```bash
grep "location from IP" storage/logs/smart-links-*.log
```

**Note**: In local development (DDEV, localhost), geolocation will fail because local IPs cannot be resolved. This is expected behavior. Set default location via environment variables:

```bash
SMART_LINKS_DEFAULT_COUNTRY=AE
SMART_LINKS_DEFAULT_CITY=Dubai
```

### SEOmatic Integration Issues

Debug SEOmatic tracking integration:

```bash
grep -i "seomatic" storage/logs/smart-links-*.log
```

Look for:

- `SEOmatic plugin not available` - SEOmatic not installed
- `SEOmatic integration not enabled` - Integration disabled in settings
- `Event type not configured for tracking` - Event type not enabled
- `Event queued successfully` - Event queued for tracking
- `Failed to push event` - Event push failed
- `Event injected into GTM data layer` - GTM event injected
- `Event injected into gtag data layer` - GA4 event injected
- `No active tracking scripts found` - No tracking configured

Common issues:

- SEOmatic plugin not installed or enabled
- Event tracking not enabled in Smart Links settings
- Specific event types not configured
- No tracking scripts configured in SEOmatic
- GTM or gtag configuration issues

### Settings Configuration Issues

Debug settings operations:

```bash
grep -i "settings" storage/logs/smart-links-*.log
```

Look for:

- `Settings data received` - Settings posted from form
- `Settings validation failed` - Validation errors
- `Settings saved successfully to database` - Successful save
- `Failed to save Smart Links settings` - Save exception

If settings fail to save:

- Check validation errors for specific fields
- Verify database connectivity
- Ensure database table exists (run migrations)
- Review config file overrides (may prevent saves)

### Integration System Issues

Monitor integration system:

```bash
grep -i "integration" storage/logs/smart-links-*.log
```

Look for:

- `Integrations loaded` - Integrations loaded successfully
- `Failed to load integrations` - Integration loading failed
- `Registered integration` - Integration registered
- `Integration failed` - Integration execution error

If integrations fail:

- Check integration configuration
- Verify required plugins installed
- Review integration-specific settings
- Check for missing required fields

### Analytics Job Issues

Monitor analytics job execution:

```bash
grep -i "analytics.*job\|cleanup.*analytics" storage/logs/smart-links-*.log
```

Look for:

- `Cleaned up analytics records` - Successful cleanup
- `Failed to save analytics for link` - Job execution failed

Common issues:

- Database connectivity problems
- Incorrect retention settings
- Queue not running
- Job execution timeout

## Development Tips

### Enable Debug Logging

For detailed troubleshooting during development:

```php
// config/smart-links.php
return [
    'dev' => [
        'logLevel' => 'debug',
    ],
];
```

This provides:

- Detailed redirect URL calculations
- Full analytics data payloads
- Device detection details
- Request headers and parameters
- Settings save operations with full context
- Field layout configuration debugging
- Integration loading and execution details
- SEOmatic event tracking and data layer injection
- Volume UID debugging for images and QR logos

### Monitor Specific Operations

Track specific operations using grep:

```bash
# Monitor all QR code operations
grep "QR" storage/logs/smart-links-*.log

# Watch analytics in real-time
tail -f storage/logs/smart-links-$(date +%Y-%m-%d).log | grep "analytics"

# Check all errors
grep "\[ERROR\]" storage/logs/smart-links-*.log

# Monitor SEOmatic integration
grep -i "seomatic" storage/logs/smart-links-*.log

# Track integration system
grep -i "integration" storage/logs/smart-links-*.log

# Watch settings operations
grep -i "settings" storage/logs/smart-links-*.log

# Monitor field layout operations
grep "Field Layout" storage/logs/smart-links-*.log

# Track analytics jobs
grep -i "cleanup.*analytics" storage/logs/smart-links-*.log

# Monitor Redirect Manager integration
grep "Redirect Manager" storage/logs/smart-links-*.log
```
