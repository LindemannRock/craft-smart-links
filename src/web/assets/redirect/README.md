# Redirect Tracking Assets

This directory contains the JavaScript assets for Smart Links redirect tracking.

## Files

- **redirect-tracking.js** - The main JavaScript file that handles redirect and QR code tracking
- **redirect-tracking.min.js** - Minified version (used in production)
- **RedirectAsset.php** - Asset bundle that registers the JS file

## How it Works

1. When a smart link page loads, the `RedirectAsset` bundle is registered
2. The template sets `window.smartLinksTracking` configuration object
3. The JavaScript reads the config and tracks the page load via API call
4. Tracking works even when pages are cached by CDN (like Servd)
5. For mobile devices, it auto-redirects after tracking

## Configuration

The template uses `craft.smartLinks.registerTracking()` which automatically sets the configuration:

```twig
{% do craft.smartLinks.registerTracking(smartLink, redirectUrl) %}
```

This creates:

```javascript
window.smartLinksTracking = {
    smartLinkId: 123,
    redirectUrl: 'https://...',
    trackAnalytics: true,
    trackingEndpoint: '/smart-links/redirect/track-button-click',
    csrfEndpoint: '/smart-links/redirect/refresh-csrf',
    debug: false  // Enable with {debug: true}
};
```

## What Gets Tracked

The script tracks THREE types of interactions:

1. **Mobile Auto-Redirects** - When mobile users visit and are automatically redirected
   - Type: `redirect`
   - Source: `direct` or `qr`

2. **QR Code Scans** - When anyone visits with `?src=qr` parameter (from scanning QR code)
   - Type: `redirect`
   - Source: `qr`

3. **Button Clicks** - When users click platform buttons (App Store, Google Play, etc.)
   - Type: `button`
   - Source: `landing`

**Desktop page loads WITHOUT QR parameter are NOT tracked** - the page just displays without counting as a click.

## How Tracking Works

1. Script fetches fresh device detection and CSRF token from uncached endpoint
2. Checks if should track (mobile device OR QR scan parameter)
3. If yes, sends tracking data via `navigator.sendBeacon()` (guaranteed delivery)
4. For mobile devices, redirects immediately after tracking
5. Also listens for button clicks on `.smartlink-btn` and `.trackable-link` elements

## Debug Mode

Enable console logging for troubleshooting:

```twig
{% do craft.smartLinks.registerTracking(smartLink, redirectUrl, {debug: true}) %}
```

This enables detailed console logs prefixed with `[Smart Links]`. Error logs are always shown regardless of debug mode.

**Note:** If using Terser's `drop_console: true` in production builds, all console logs will be removed during minification. The debug flag provides runtime control without needing to modify code.

## Development

To modify the assets:
1. Edit `redirect-tracking.js`
2. Run `npm run minify` to create `redirect-tracking.min.js`
3. Run `ddev craft clear-caches/all` to publish changes
4. Test in both dev mode (unminified) and production mode (minified)

The asset bundle automatically uses the appropriate version based on Craft's `devMode` setting.