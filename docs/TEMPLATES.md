# Smart Links Template Guide

## Template Requirements

**Smart Links requires custom templates** to display redirect landing pages and QR code views. These templates are NOT optional - the plugin will throw errors if they don't exist.

### Default Template Paths

When template paths are not configured (set to `null` in config), the plugin expects templates at:

- **Redirect landing page:** `templates/smart-links/redirect.twig` (used by `/go/{slug}`)
- **QR code display:** `templates/smart-links/qr.twig` (used by `/qr/{slug}/view`)

**If these templates don't exist, visitors will get a "Unable to find template" error.**

### Quick Start

The plugin includes example templates in `vendor/lindemannrock/smart-links/src/templates/`. Copy them to get started:

```bash
# Create templates directory
mkdir -p templates/smart-links

# Copy example templates
cp vendor/lindemannrock/smart-links/src/templates/redirect.twig templates/smart-links/
cp vendor/lindemannrock/smart-links/src/templates/qr.twig templates/smart-links/

# Customize to match your site's design
```

## Custom Redirect Templates

You can customize the redirect landing page template to match your site's design.

### Creating a Custom Template

1. Create a template file in your `templates/` directory (e.g., `templates/smart-links/redirect.twig`)
2. Configure the template path in Settings → Redirect Settings → Custom Redirect Template
3. Or set it in `config/smart-links.php`:

```php
return [
    'redirectTemplate' => 'smart-links/redirect', // Defaults to this if null
];
```

**To use a different path:**
```php
return [
    'redirectTemplate' => 'my-custom/landing-page', // Custom path
];
```

### Template Variables

The following variables are available in your redirect template:

- `smartLink` - The SmartLink element
- `redirectUrl` - The URL to redirect to (determined by device detection)
- `device` - Device information object
- `language` - Detected language code

### Mobile Detection and Tracking

To enable mobile auto-redirect and analytics tracking, add this JavaScript to your template's head:

```twig
{% block head %}
    {{ parent() }}

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
{% endblock %}
```

This approach:
- Fetches fresh device detection from uncached endpoint
- Works with CDN/static page caching (Servd, Cloudflare, Blitz)
- Tracks mobile redirects automatically via the redirect controller
- Desktop users remain on the landing page

### Button Links

All buttons should use the redirect controller action to enable tracking:

```twig
<a href="{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'ios', site: smartLink.site.handle}) }}">
    Download on App Store
</a>

<a href="{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'android', site: smartLink.site.handle}) }}">
    Get it on Google Play
</a>

<a href="{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'fallback', site: smartLink.site.handle}) }}">
    Continue to Website
</a>
```

**Note:** Include `site: smartLink.site.handle` parameter for proper multi-site support.

The redirect controller tracks each click before redirecting to the appropriate URL.

### What Gets Tracked

The tracking system records these interaction types:

1. **Mobile Auto-Redirects** - When mobile users are automatically redirected via `platform: 'auto'`
2. **QR Code Scans** - When users visit with `?src=qr` parameter
3. **Button Clicks** - When users click platform buttons that use the redirect controller

**Note:** Desktop page loads without the QR parameter are NOT tracked - the page simply displays without counting as an interaction.

### Example Custom Template

```twig
{% extends "_layouts/base.twig" %}

{% block title %}
    {{ smartLink.title }} - {{ siteName }}
{% endblock %}

{% block head %}
    {{ parent() }}

    {# SEOmatic tracking integration (if enabled) #}
    {{ smartLink.renderSeomaticTracking('redirect') }}

    <script>
        // Client-side mobile detection for auto-redirect
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
{% endblock %}

{% block content %}
    <div class="smart-link-page">
        <h1>{{ smartLink.title }}</h1>

        {% if smartLink.description %}
            <p>{{ smartLink.description }}</p>
        {% endif %}

        <div class="app-buttons">
            {% if smartLink.iosUrl %}
                <a href="{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'ios', site: smartLink.site.handle}) }}">
                    Download on App Store
                </a>
            {% endif %}

            {% if smartLink.androidUrl %}
                <a href="{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'android', site: smartLink.site.handle}) }}">
                    Get it on Google Play
                </a>
            {% endif %}

            {% if smartLink.fallbackUrl %}
                <a href="{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'fallback', site: smartLink.site.handle}) }}">
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

The QR code display page (`/qr/{slug}/view`) requires a custom template.

### Default Template Path

When `qrTemplate` is not configured (set to `null`), the plugin looks for:
- **QR code display:** `templates/smart-links/qr.twig`

The plugin includes an example QR template you can copy (see Quick Start above).

### Creating a QR Template

Here's a basic example (`templates/smart-links/qr.twig`):

```twig
<!DOCTYPE html>
<html lang="{{ currentSite.language }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ smartLink.title }} - QR Code</title>

    {# SEOmatic tracking integration (if enabled) #}
    {{ smartLink.renderSeomaticTracking('qr_scan') }}
</head>
<body>
    <div class="qr-display">
        <h1>{{ smartLink.title }}</h1>

        {% if smartLink.description %}
            <p>{{ smartLink.description }}</p>
        {% endif %}

        <div class="qr-code">
            <img src="{{ smartLink.getQrCodeUrl({ size: size ?? 300 }) }}"
                 alt="{{ smartLink.title }} QR Code">
        </div>

        <p>Scan with your phone's camera</p>
    </div>
</body>
</html>
```

### Custom Template Path

```php
return [
    'qrTemplate' => 'my-custom/qr-display', // Custom path
];
```

### Available Variables

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

## SEOmatic Integration

Smart Links includes built-in SEOmatic integration for analytics tracking and custom SEO fields.

### Analytics Tracking Integration

When SEOmatic is installed and enabled in Settings → Analytics → Third-Party Integrations, Smart Links can push click events to Google Tag Manager's dataLayer.

**Template Method:**

Add the tracking method to your templates:

```twig
{# For redirect landing pages #}
{{ smartLink.renderSeomaticTracking('redirect') }}

{# For QR code display pages #}
{{ smartLink.renderSeomaticTracking('qr_scan') }}
```

**How It Works:**
- Returns client-side JavaScript when SEOmatic integration is enabled
- Returns `null` (no output) when disabled or not installed
- No need for `|raw` filter (returns `\Twig\Markup` automatically)
- Pushes events to `window.dataLayer` for GTM/GA4 tracking
- Button clicks intercepted with 300ms delay to ensure tracking completes
- Supports debug mode: add `?debug=1` to test without redirects

**Event Types:**
- `'redirect'` - Landing pages with buttons and auto-redirects
- `'qr_scan'` - QR code display pages

See the main README for full configuration options and GTM setup instructions.

### SEOmatic Field Integration

Smart Links also supports custom SEOmatic fields through field layouts for per-link SEO customization:

**Adding SEOmatic Field to Smart Links:**

1. Go to **Settings → Smart Links → Field Layout**
2. Drag an SEOmatic field into the layout
3. Save the field layout

**Template Usage:**

If you have SEOmatic fields in your Smart Links field layout, you can access them in templates:

```twig
{% extends "_layouts/base.twig" %}

{% block head %}
    {{ parent() }}

    {# SEOmatic tracking integration (if enabled) #}
    {{ smartLink.renderSeomaticTracking('redirect') }}

    {# Mobile detection for auto-redirect #}
    <script>
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
            });
        })();
    </script>

    {# SEOmatic meta tags for the Smart Link #}
    {% do seomatic.meta.get(smartLink) %}
{% endblock %}

{% block content %}
    <div class="smart-link-page">
        <h1>{{ smartLink.title }}</h1>

        {% if smartLink.description %}
            <p>{{ smartLink.description }}</p>
        {% endif %}

        {# Platform buttons #}
        <div class="app-buttons">
            {% if smartLink.iosUrl %}
                <a href="{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'ios', site: smartLink.site.handle}) }}">
                    Download on App Store
                </a>
            {% endif %}

            {% if smartLink.androidUrl %}
                <a href="{{ actionUrl('smart-links/redirect/go', {slug: smartLink.slug, platform: 'android', site: smartLink.site.handle}) }}">
                    Get it on Google Play
                </a>
            {% endif %}
        </div>
    </div>
{% endblock %}
```

### Custom SEO per Smart Link

You can also manually set SEO values:

```twig
{% do seomatic.meta.seoTitle(smartLink.title) %}
{% do seomatic.meta.seoDescription(smartLink.description) %}
{% do seomatic.meta.seoImage(smartLink.getImage()) %}

{# Open Graph tags for social sharing #}
{% do seomatic.meta.ogTitle(smartLink.title) %}
{% do seomatic.meta.ogDescription(smartLink.description) %}
{% do seomatic.meta.ogImage(smartLink.getImage()) %}

{# Twitter Card tags #}
{% do seomatic.meta.twitterTitle(smartLink.title) %}
{% do seomatic.meta.twitterDescription(smartLink.description) %}
{% do seomatic.meta.twitterImage(smartLink.getImage()) %}
```

### Accessing Custom Fields

Any custom fields added to the Smart Links field layout are accessible:

```twig
{# Access SEOmatic field (if named 'seo') #}
{% if smartLink.seo is defined %}
    {{ smartLink.seo.metaTitle }}
    {{ smartLink.seo.metaDescription }}
{% endif %}

{# Access other custom fields #}
{{ smartLink.customTextField }}
{{ smartLink.customRichTextField|raw }}

{# Matrix fields #}
{% for block in smartLink.customMatrixField.all() %}
    {# ... #}
{% endfor %}
```

## Template Hooks

The plugin provides template hooks for extending functionality:

- `cp.smartLinks.edit.content` - Add content to smart link edit page
- `cp.smartLinks.edit.settings` - Add settings to smart link edit page
- `cp.smartLinks.edit.meta` - Add meta information to smart link edit page