<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\controllers;

use Craft;
use craft\web\Controller;
use lindemannrock\smartlinks\elements\SmartLink;
use lindemannrock\smartlinks\SmartLinks;
use yii\web\Response;

/**
 * Redirect Controller
 * Handles smart link redirects on the frontend
 */
class RedirectController extends Controller
{
    /**
     * @var array Allow anonymous access
     */
    protected array|int|bool $allowAnonymous = true;

    /**
     * Handle smart link landing page display
     *
     * @param string $slug
     * @return Response
     */
    public function actionIndex(string $slug): Response
    {
        // Get current site for multi-site support
        $currentSite = Craft::$app->getSites()->getCurrentSite();

        // Get the smart link for the current site
        $smartLink = SmartLink::find()
            ->slug($slug)
            ->siteId($currentSite->id)
            ->status(SmartLink::STATUS_ENABLED)
            ->one();

        if (!$smartLink) {
            // Get the 404 redirect URL from settings
            $settings = SmartLinks::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';

            // Handle relative URLs
            if (strpos($redirectUrl, '://') === false && strpos($redirectUrl, '/') !== 0) {
                $redirectUrl = '/' . $redirectUrl;
            }

            return $this->redirect($redirectUrl);
        }

        // Get device info and language for template display
        $deviceInfo = SmartLinks::$plugin->deviceDetection->detectDevice();
        $language = SmartLinks::$plugin->deviceDetection->detectLanguage();

        // Set cache headers - this page can be fully cached
        $response = Craft::$app->getResponse();
        $response->headers->set('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour

        // Render the template - all links will point to action URLs for tracking
        $settings = SmartLinks::$plugin->getSettings();
        $template = $settings->redirectTemplate ?: 'smart-links/redirect';

        return $this->renderTemplate($template, [
            'smartLink' => $smartLink,
            'device' => $deviceInfo,
            'language' => $language,
        ]);
    }

    /**
     * Track and redirect to platform URL
     * This endpoint handles all navigation with server-side tracking
     *
     * @param string $slug
     * @param string $platform Platform identifier (ios, android, huawei, amazon, windows, mac, fallback, auto)
     * @return Response
     */
    public function actionGo(string $slug, string $platform = 'auto'): Response
    {
        // Normalize platform parameter to lowercase first
        $platform = strtolower($platform);

        // Validate platform parameter - only allow valid values
        $validPlatforms = ['auto', 'ios', 'android', 'huawei', 'amazon', 'windows', 'mac', 'fallback'];
        if (!in_array($platform, $validPlatforms)) {
            // Invalid platform, default to auto
            $platform = 'auto';
        }

        // Get site from param or fall back to current site
        $siteParam = Craft::$app->getRequest()->getParam('site');
        if ($siteParam) {
            $site = Craft::$app->getSites()->getSiteByHandle($siteParam);
            $siteId = $site ? $site->id : Craft::$app->getSites()->getCurrentSite()->id;
        } else {
            $siteId = Craft::$app->getSites()->getCurrentSite()->id;
        }

        // Get the smart link for the site
        $smartLink = SmartLink::find()
            ->slug($slug)
            ->siteId($siteId)
            ->status(SmartLink::STATUS_ENABLED)
            ->one();

        if (!$smartLink) {
            $settings = SmartLinks::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Get device info for tracking
        $deviceInfo = SmartLinks::$plugin->deviceDetection->detectDevice();
        $language = SmartLinks::$plugin->deviceDetection->detectLanguage();

        // Get source parameter for QR tracking
        $source = Craft::$app->getRequest()->getParam('src', 'direct');

        // Determine destination URL
        $destinationUrl = null;
        $clickType = 'button';

        if ($platform === 'auto') {
            // Auto-detect platform and redirect (for mobile auto-redirect)
            $destinationUrl = SmartLinks::$plugin->deviceDetection->getRedirectUrl(
                $smartLink,
                $deviceInfo,
                $language
            );
            $clickType = 'redirect';
            $platform = $deviceInfo->platform ?? 'unknown';
        } else {
            // Manual platform selection from button click
            $destinationUrl = match($platform) {
                'ios' => $smartLink->iosUrl,
                'android' => $smartLink->androidUrl,
                'huawei' => $smartLink->huaweiUrl,
                'amazon' => $smartLink->amazonUrl,
                'windows' => $smartLink->windowsUrl,
                'mac' => $smartLink->macUrl,
                'fallback' => $smartLink->fallbackUrl,
                default => $smartLink->fallbackUrl,
            };
        }

        // Track the click if analytics are enabled
        if ($smartLink->trackAnalytics && SmartLinks::$plugin->getSettings()->enableAnalytics) {
            // Normalize platform value to match DeviceInfo valid values
            $normalizedPlatform = match($platform) {
                'mac' => 'macos',
                'fallback' => 'other',
                default => $platform
            };

            // If platform is 'auto', use the detected platform from deviceInfo
            if ($normalizedPlatform === 'auto') {
                $normalizedPlatform = $deviceInfo->platform ?? 'other';
            }

            SmartLinks::$plugin->analytics->trackClick(
                $smartLink,
                $deviceInfo,
                [
                    'clickType' => $clickType,
                    'platform' => $normalizedPlatform,
                    'buttonUrl' => $destinationUrl,
                    'referrer' => Craft::$app->request->getReferrer(),
                    'source' => $source,
                    'siteId' => $siteId, // Pass the detected site ID
                ]
            );
        }

        // Redirect to destination
        if ($destinationUrl) {
            return $this->redirect($destinationUrl, 302);
        }

        // No destination URL available, redirect to fallback
        return $this->redirect($smartLink->fallbackUrl, 302);
    }

    /**
     * Get fresh device detection (for cached pages)
     * This endpoint is never cached and provides real-time device info
     *
     * @return Response
     */
    public function actionRefreshCsrf(): Response
    {
        // Prevent caching of this response
        $this->response->setNoCacheHeaders();

        // Detect device using the plugin's device detection service
        $deviceInfo = SmartLinks::$plugin->deviceDetection->detectDevice();

        return $this->asJson([
            'csrfToken' => Craft::$app->request->getCsrfToken(),
            'isMobile' => $deviceInfo->isMobile ?? false,
            'platform' => $deviceInfo->platform ?? 'unknown',
        ]);
    }
}