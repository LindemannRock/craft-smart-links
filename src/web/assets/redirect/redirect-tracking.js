/**
 * Smart Links - Redirect Tracking
 * Handles client-side tracking for redirects and QR code scans
 * Works with CDN/static page caching by tracking via JavaScript API calls
 */
(function() {
    'use strict';

    // Get tracking data from window object (set by template)
    const config = window.smartLinksTracking || {};
    const debug = config.debug || false;

    const log = debug ? console.log.bind(console, '[Smart Links]') : () => {};
    const warn = debug ? console.warn.bind(console, '[Smart Links]') : () => {};
    const error = console.error.bind(console, '[Smart Links]'); // Always log errors

    if (!config.smartLinkId || !config.trackingEndpoint || !config.csrfEndpoint) {
        warn('Tracking not configured');
        return;
    }

    // Prevent duplicate tracking on the same page load
    if (window._smartLinksTracked) {
        log('Already tracked on this page load');
        return;
    }
    window._smartLinksTracked = true;

    const urls = config.urls || {};
    const smartLinkId = config.smartLinkId;
    const trackAnalytics = config.trackAnalytics;
    const trackingEndpoint = config.trackingEndpoint;
    const csrfEndpoint = config.csrfEndpoint;

    // Get source from URL (qr or direct)
    const urlParams = new URLSearchParams(window.location.search);
    const source = urlParams.get('src') || 'direct';

    log('Starting tracking...', {smartLinkId, source, trackAnalytics, urls});

    // Fetch fresh device detection and CSRF token from uncached endpoint
    fetch(csrfEndpoint, {
        credentials: 'same-origin',
        cache: 'no-store'
    })
    .then(r => {
        log('CSRF response received');
        return r.json();
    })
    .then(data => {
        log('CSRF data:', data);

        // Determine redirect URL based on platform from fresh device detection
        let redirectUrl = '';
        const platform = data.platform || 'unknown';

        if (platform === 'ios') {
            redirectUrl = urls.ios || '';
        } else if (platform === 'android' || platform === 'huawei') {
            // Try platform-specific first, fallback to android
            redirectUrl = urls[platform] || urls.android || '';
        } else if (platform === 'windows') {
            redirectUrl = urls.windows || '';
        } else if (platform === 'macos') {
            redirectUrl = urls.mac || '';
        }

        log('Platform detected:', platform, 'Redirect URL:', redirectUrl);

        // Only track if:
        // 1. Has redirect URL (mobile/desktop with platform URL set) OR
        // 2. QR code scan (has ?src=qr parameter)
        const shouldTrack = (redirectUrl && data.isMobile) || source === 'qr';

        // Handle tracking and redirect
        if (trackAnalytics && shouldTrack) {
            log('Sending tracking beacon...');
            const trackingData = new FormData();
            trackingData.append('smartLinkId', smartLinkId);
            trackingData.append('platform', 'redirect');  // Platform/clickType
            trackingData.append('url', redirectUrl);
            trackingData.append('source', source);  // QR or Direct
            trackingData.append('CRAFT_CSRF_TOKEN', data.csrfToken);

            // Send tracking, THEN redirect after it completes
            fetch(trackingEndpoint, {
                method: 'POST',
                body: trackingData
            }).then(() => {
                log('Tracking sent');
                // Redirect after tracking completes
                if (redirectUrl) {
                    log('Redirecting to:', redirectUrl);
                    window.location.replace(redirectUrl);
                }
            }).catch(err => {
                error('Tracking failed:', err);
                // Redirect anyway even if tracking fails
                if (redirectUrl) {
                    log('Redirecting to:', redirectUrl);
                    window.location.replace(redirectUrl);
                }
            });
        } else {
            log('Not tracking page load (no redirect URL or desktop without QR parameter)');
            // Redirect immediately if no tracking needed
            if (redirectUrl) {
                log('Redirecting to:', redirectUrl);
                window.location.replace(redirectUrl);
            } else {
                log('No redirect URL for platform:', platform, '- showing landing page');
            }
        }
    })
    .catch(err => {
        error('Error during tracking:', err);
        warn('Device detection failed, page will load normally:', err);
    });

    // Also track button clicks on the landing page
    document.addEventListener('DOMContentLoaded', function() {
        const trackableLinks = document.querySelectorAll('.smartlink-btn, .trackable-link');

        trackableLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                // Prevent default navigation
                e.preventDefault();

                // Get the destination URL
                const destinationUrl = this.getAttribute('href');
                const buttonText = this.textContent.trim();
                // Use data-platform if available, otherwise derive from button text
                const platform = this.getAttribute('data-platform') || buttonText.toLowerCase().replace(/\s+/g, '-');

                // Fetch fresh CSRF token and send tracking
                fetch(csrfEndpoint, {
                    credentials: 'same-origin',
                    cache: 'no-store'
                })
                .then(r => r.json())
                .then(data => {
                    const trackingData = new FormData();
                    trackingData.append('smartLinkId', smartLinkId);
                    trackingData.append('platform', platform);
                    trackingData.append('url', destinationUrl);
                    trackingData.append('source', 'landing');
                    trackingData.append('CRAFT_CSRF_TOKEN', data.csrfToken);

                    // Send tracking and navigate
                    fetch(trackingEndpoint, {
                        method: 'POST',
                        body: trackingData
                    }).then(() => {
                        window.location.href = destinationUrl;
                    }).catch(() => {
                        // Navigate anyway if tracking fails
                        window.location.href = destinationUrl;
                    });
                })
                .catch(() => {
                    // Navigate anyway if CSRF fetch fails
                    window.location.href = destinationUrl;
                });
            });
        });
    });
})();