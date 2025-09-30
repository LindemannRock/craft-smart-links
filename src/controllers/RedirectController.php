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
use yii\web\NotFoundHttpException;
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
     * Handle smart link redirect
     *
     * @param string $slug
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionIndex(string $slug): Response
    {
        // Detect device first to set appropriate cache headers
        $deviceInfo = SmartLinks::$plugin->deviceDetection->detectDevice();
        $isMobile = $deviceInfo->isMobile ?? false;

        // Allow caching but vary by device type for Servd static cache
        $response = Craft::$app->getResponse();
        $response->headers->set('Vary', 'User-Agent');
        $response->headers->set('Cache-Control', 'public, max-age=300'); // Cache for 5 minutes

        // Set custom header for device type that Servd can use for cache variation
        $response->headers->set('X-Device-Type', $isMobile ? 'mobile' : 'desktop');
        
        // Get the smart link
        $smartLink = SmartLink::find()
            ->slug($slug)
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
            
            // Redirect to configured URL instead of throwing 404
            return $this->redirect($redirectUrl);
        }

        // Get device info
        $deviceInfo = SmartLinks::$plugin->deviceDetection->detectDevice();
        
        // Get language
        $language = SmartLinks::$plugin->deviceDetection->detectLanguage();
        
        // Get redirect URL
        $redirectUrl = SmartLinks::$plugin->deviceDetection->getRedirectUrl(
            $smartLink,
            $deviceInfo,
            $language
        );
        
        // Check if this came from QR code
        $isQrCode = Craft::$app->request->getQueryParam('src') === 'qr';

        // Track analytics for mobile auto-redirects OR QR code scans (QR scans are always tracked regardless of device)
        $shouldTrack = SmartLinks::$plugin->deviceDetection->isMobileDevice($deviceInfo) || $isQrCode;

        if ($shouldTrack && $smartLink->trackAnalytics && SmartLinks::$plugin->getSettings()->enableAnalytics) {
            SmartLinks::$plugin->analytics->trackClick(
                $smartLink,
                $deviceInfo,
                [
                    'clickType' => 'redirect',
                    'redirectUrl' => $redirectUrl,
                    'language' => $language,
                    'referrer' => Craft::$app->request->getReferrer(),
                    'source' => $isQrCode ? 'qr' : 'direct',
                ]
            );
        }

        // Mobile devices - redirect immediately
        if (SmartLinks::$plugin->deviceDetection->isMobileDevice($deviceInfo)) {
            $response = $this->redirect($redirectUrl);

            // Prevent caching of QR redirects to ensure tracking works every time
            if ($isQrCode) {
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }

            return $response;
        }

        // Desktop - show redirect page
        $settings = SmartLinks::$plugin->getSettings();
        $template = $settings->redirectTemplate ?: 'smart-links/redirect';
        
        // Render the template (will use site templates by default)
        return $this->renderTemplate($template, [
            'smartLink' => $smartLink,
            'device' => $deviceInfo,
            'redirectUrl' => $redirectUrl,
            'language' => $language,
        ]);
    }
    
    /**
     * Refresh CSRF token for cached pages
     *
     * @return Response
     */
    public function actionRefreshCsrf(): Response
    {
        // Ensure session is started
        Craft::$app->getSession()->open();

        // Prevent caching of this response
        $this->response->setNoCacheHeaders();

        // Detect device using the same library as redirect action
        $deviceInfo = SmartLinks::$plugin->deviceDetection->detectDevice();

        return $this->asJson([
            'csrfToken' => Craft::$app->request->getCsrfToken(),
            'isMobile' => $deviceInfo->isMobile ?? false,
        ]);
    }

    /**
     * Track button click via AJAX
     *
     * @return Response
     */
    public function actionTrackButtonClick(): Response
    {
        $this->requirePostRequest();
        // Don't require JSON - sendBeacon sends FormData without Accept: application/json header

        $request = Craft::$app->getRequest();
        $smartLinkId = $request->getRequiredBodyParam('smartLinkId');
        $platform = $request->getRequiredBodyParam('platform');
        $url = $request->getBodyParam('url');
        $source = $request->getBodyParam('source', 'direct');
        
        // Get the smart link
        $smartLink = SmartLink::find()
            ->id($smartLinkId)
            ->status(SmartLink::STATUS_ENABLED)
            ->one();
            
        if (!$smartLink || !$smartLink->enabled || !$smartLink->trackAnalytics || !SmartLinks::$plugin->getSettings()->enableAnalytics) {
            return $this->asJson(['success' => false]);
        }
        
        // Get device info
        $deviceInfo = SmartLinks::$plugin->deviceDetection->detectDevice();
        
        // Track the button click with platform info
        SmartLinks::$plugin->analytics->trackClick(
            $smartLink,
            $deviceInfo,
            [
                'clickType' => 'button',
                'platform' => $platform,
                'buttonUrl' => $url,
                'referrer' => Craft::$app->request->getReferrer(),
                'source' => $source,
            ]
        );
        
        return $this->asJson(['success' => true]);
    }
}