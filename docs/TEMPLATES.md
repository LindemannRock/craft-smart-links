# Smart Links Template Guide

## Custom Redirect Templates

You can override the default redirect template to customize the landing page appearance.

### Creating a Custom Template

1. Create a template file in your `templates/` directory (e.g., `templates/smart-links/redirect.twig`)
2. Configure the template path in Settings → General → Custom Redirect Template
3. Or set it in `config/smart-links.php`:

```php
return [
    'redirectTemplate' => 'smart-links/redirect',
];
```

### Template Variables

The following variables are available in your redirect template:

- `smartLink` - The SmartLink element
- `redirectUrl` - The URL to redirect to (determined by device detection)
- `device` - Device information object
- `language` - Detected language code

### Registering Tracking

To enable analytics tracking in your custom template, add this to your template's head block:

```twig
{% block head %}
    {{ parent() }}
    {% do craft.smartLinks.registerTracking(smartLink, redirectUrl) %}
{% endblock %}
```

This single line:
- Registers the tracking JavaScript asset bundle (`redirect-tracking.js`)
- Configures tracking for redirects, QR code scans, and button clicks
- Handles mobile auto-redirect using client-side device detection
- Works with CDN/static page caching (like Servd, Cloudflare)
- Only tracks meaningful interactions (mobile redirects, QR scans, button clicks)

**Debug Mode:**

Enable console logging for troubleshooting:

```twig
{% do craft.smartLinks.registerTracking(smartLink, redirectUrl, {debug: true}) %}
```

### Button Tracking

Buttons are automatically tracked if they have the `.smartlink-btn` or `.trackable-link` CSS class:

```twig
<a href="{{ smartLink.iosUrl }}" class="smartlink-btn">
    App Store
</a>
```

You can specify a custom platform name using the `data-platform` attribute:

```twig
<a href="{{ smartLink.fallbackUrl }}"
   class="trackable-link"
   data-platform="fallback">
    Continue to Website
</a>
```

Without `data-platform`, the button text is used (e.g., "App Store" → "app-store").

### What Gets Tracked

The tracking system records three types of interactions:

1. **Mobile Auto-Redirects** - When mobile users are automatically redirected
   - Type: `redirect`
   - Source: `direct`

2. **QR Code Scans** - When users visit with `?src=qr` parameter
   - Type: `redirect`
   - Source: `qr`

3. **Button Clicks** - When users click platform buttons
   - Type: `button`
   - Source: `landing`
   - Platform: Derived from button text or `data-platform` attribute

**Note:** Desktop page loads without the QR parameter are NOT tracked - the page simply displays without counting as an interaction.

### Example Custom Template

```twig
{% extends "_layouts/base.twig" %}

{% block title %}
    {{ smartLink.title }} - {{ siteName }}
{% endblock %}

{% block head %}
    {{ parent() }}
    {% do craft.smartLinks.registerTracking(smartLink, redirectUrl) %}
{% endblock %}

{% block content %}
    <div class="smart-link-page">
        <h1>{{ smartLink.title }}</h1>

        {% if smartLink.description %}
            <p>{{ smartLink.description }}</p>
        {% endif %}

        <div class="app-buttons">
            {% if smartLink.iosUrl %}
                <a href="{{ smartLink.iosUrl }}" class="smartlink-btn">
                    Download on App Store
                </a>
            {% endif %}

            {% if smartLink.androidUrl %}
                <a href="{{ smartLink.androidUrl }}" class="smartlink-btn">
                    Get it on Google Play
                </a>
            {% endif %}

            {% if smartLink.fallbackUrl %}
                <a href="{{ smartLink.fallbackUrl }}"
                   class="trackable-link"
                   data-platform="fallback">
                    Visit Website
                </a>
            {% endif %}
        </div>

        {% if smartLink.qrCodeEnabled %}
            <div class="qr-code">
                <img src="{{ smartLink.getQrCodeUrl() }}" alt="QR Code">
            </div>
        {% endif %}
    </div>
{% endblock %}
```

## Custom QR Code Templates

You can also customize the QR code display page:

```php
return [
    'qrTemplate' => 'smart-links/qr',
];
```

Available variables:
- `smartLink` - The SmartLink element
- `qrCodeSvg` - SVG markup (if format is SVG)
- `qrCodeData` - Base64 encoded image data (if format is PNG)
- `size` - QR code size
- `format` - QR code format (png or svg)

## Direct Template Usage

You can also fetch and display smart links directly in any template:

```twig
{# Get a single smart link by slug #}
{% set link = craft.smartLinks.slug('my-app').one() %}

{# Get all active smart links #}
{% set links = craft.smartLinks.active().all() %}

{# Get smart link by ID #}
{% set link = craft.smartLinks.getById(123) %}
```

## Template Hooks

The plugin provides template hooks for extending functionality:

- `cp.smartLinks.edit.content` - Add content to smart link edit page
- `cp.smartLinks.edit.settings` - Add settings to smart link edit page
- `cp.smartLinks.edit.meta` - Add meta information to smart link edit page