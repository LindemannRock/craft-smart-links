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
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinks\SmartLinks;
use yii\web\Response;

/**
 * Analytics Controller
 */
class AnalyticsController extends Controller
{
    use LoggingTrait;
    /**
     * @var array<int|string>|bool|int
     */
    protected array|bool|int $allowAnonymous = false;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle('smart-links');
    }

    /**
     * Analytics dashboard
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $this->requirePermission('smartLinks:viewAnalytics');

        // Check if analytics are globally enabled
        if (!SmartLinks::$plugin->getSettings()->enableAnalytics) {
            throw new \yii\web\ForbiddenHttpException('Analytics are disabled in plugin settings.');
        }

        $variables = [
            'title' => Craft::t('smart-links', 'Analytics'),
        ];

        // Get date range
        $dateRange = Craft::$app->getRequest()->getParam('dateRange', 'last7days');
        $variables['dateRange'] = $dateRange;

        // Get analytics data
        $variables['analyticsData'] = SmartLinks::$plugin->analytics->getAnalyticsSummary($dateRange);

        // Pass settings to template
        $variables['settings'] = SmartLinks::$plugin->getSettings();

        return $this->renderTemplate('smart-links/analytics/index', $variables);
    }

    /**
     * Get analytics data via AJAX
     *
     * @return Response
     */
    public function actionGetData(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requireLogin();

        // Check if analytics are globally enabled
        if (!SmartLinks::$plugin->getSettings()->enableAnalytics) {
            return $this->asJson([
                'success' => false,
                'error' => 'Analytics are disabled in plugin settings.',
            ]);
        }

        $smartLinkId = Craft::$app->getRequest()->getParam('smartLinkId');
        $dateRange = Craft::$app->getRequest()->getParam('dateRange', 'last7days');
        $type = Craft::$app->getRequest()->getParam('type', 'summary');

        // Log the request for debugging
        $this->logInfo('Analytics getData called', ['type' => $type, 'dateRange' => $dateRange, 'smartLinkId' => $smartLinkId ?? null]);

        // If requesting data for a specific SmartLink, check if it has analytics enabled
        if ($smartLinkId) {
            $smartLink = \lindemannrock\smartlinks\elements\SmartLink::find()
                ->id($smartLinkId)
                ->status(null)
                ->one();
            
            if (!$smartLink) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Smart link not found',
                ]);
            }
            
            if (!($smartLink->trackAnalytics ?? true)) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Analytics tracking is disabled for this smart link',
                ]);
            }
        }

        try {
            $data = match ($type) {
                'clicks' => SmartLinks::$plugin->analytics->getClicksData($smartLinkId, $dateRange),
                'devices' => SmartLinks::$plugin->analytics->getDeviceBreakdown($smartLinkId, $dateRange),
                'device-types' => SmartLinks::$plugin->analytics->getDeviceTypeBreakdown($smartLinkId, $dateRange),
                'device-brands' => SmartLinks::$plugin->analytics->getDeviceBrandBreakdown($smartLinkId, $dateRange),
                'platforms' => SmartLinks::$plugin->analytics->getPlatformBreakdown($smartLinkId, $dateRange),
                'os-breakdown' => SmartLinks::$plugin->analytics->getOsBreakdown($smartLinkId, $dateRange),
                'browsers' => SmartLinks::$plugin->analytics->getBrowserBreakdown($smartLinkId, $dateRange),
                'countries' => SmartLinks::$plugin->analytics->getTopCountries($smartLinkId, $dateRange),
                'all-countries' => SmartLinks::$plugin->analytics->getAllCountries($smartLinkId, $dateRange),
                'all-cities' => SmartLinks::$plugin->analytics->getTopCities($smartLinkId, $dateRange, 50),
                'languages' => SmartLinks::$plugin->analytics->getLanguageBreakdown($smartLinkId, $dateRange),
                'hourly' => SmartLinks::$plugin->analytics->getHourlyAnalytics($smartLinkId, $dateRange),
                'insights' => SmartLinks::$plugin->analytics->getInsights($dateRange),
                default => SmartLinks::$plugin->analytics->getAnalyticsSummary($dateRange, $smartLinkId),
            };

            return $this->asJson([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            $this->logError('Analytics getData error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get analytics data for AJAX requests
     *
     * @return Response
     */
    public function actionGetAnalyticsData(): Response
    {
        $this->requireLogin();
        $this->requireAcceptsJson();

        // Check if analytics are globally enabled
        if (!SmartLinks::$plugin->getSettings()->enableAnalytics) {
            return $this->asJson([
                'success' => false,
                'error' => 'Analytics are disabled in plugin settings.',
            ]);
        }

        $smartLinkId = Craft::$app->getRequest()->getParam('smartLinkId');
        $range = Craft::$app->getRequest()->getParam('range', 'last7days');

        if (!$smartLinkId) {
            return $this->asJson([
                'success' => false,
                'error' => 'Smart link ID is required',
            ]);
        }

        try {
            // Get the smart link (including disabled ones)
            $smartLink = \lindemannrock\smartlinks\elements\SmartLink::find()
                ->id($smartLinkId)
                ->status(null)
                ->one();

            if (!$smartLink) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Smart link not found',
                ]);
            }

            // Check if analytics tracking is enabled for this smart link
            if (!($smartLink->trackAnalytics ?? true)) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Analytics tracking is disabled for this smart link',
                ]);
            }

            // Get analytics service
            $analyticsService = SmartLinks::$plugin->analytics;

            // Set the range parameter in the request so the template can access it
            $_GET['range'] = $range;
            Craft::$app->getRequest()->setQueryParams(array_merge(Craft::$app->getRequest()->getQueryParams(), ['range' => $range]));
            
            // Render only the content part for AJAX
            $html = Craft::$app->getView()->renderTemplate('smart-links/smartlinks/_partials/analytics-content', [
                'smartLink' => $smartLink,
                'analyticsService' => $analyticsService,
                'dateRange' => $range,  // Pass the range directly
                'settings' => SmartLinks::$plugin->getSettings(),
            ]);

            return $this->asJson([
                'success' => true,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            $this->logError('Failed to get analytics data', ['error' => $e->getMessage()]);
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Export analytics data
     *
     * @return Response
     */
    public function actionExport(): Response
    {
        $this->requirePermission('smartLinks:viewAnalytics');

        // Check if analytics are globally enabled
        if (!SmartLinks::$plugin->getSettings()->enableAnalytics) {
            Craft::$app->getSession()->setError('Analytics are disabled in plugin settings.');
            return $this->redirect('smart-links');
        }

        $smartLinkId = Craft::$app->getRequest()->getParam('smartLinkId');
        $dateRange = Craft::$app->getRequest()->getParam('dateRange', 'last7days');
        $format = Craft::$app->getRequest()->getParam('format', 'csv');

        // If exporting for a specific SmartLink, check if it has analytics enabled
        if ($smartLinkId) {
            $smartLink = \lindemannrock\smartlinks\elements\SmartLink::find()
                ->id($smartLinkId)
                ->status(null)
                ->one();
            
            if (!$smartLink) {
                Craft::$app->getSession()->setError('Smart link not found.');
                return $this->redirect('smart-links');
            }
            
            if (!($smartLink->trackAnalytics ?? true)) {
                Craft::$app->getSession()->setError('Analytics tracking is disabled for this smart link.');
                return $this->redirect('smart-links/smartlinks/' . $smartLinkId);
            }
        }

        try {
            $data = SmartLinks::$plugin->analytics->exportAnalytics($smartLinkId, $dateRange, $format);
            
            // Generate filename
            $settings = SmartLinks::$plugin->getSettings();
            $filenamePart = strtolower(str_replace(' ', '-', $settings->getPluralLowerDisplayName()));
            $baseFilename = $filenamePart . '-analytics';
            if ($smartLinkId) {
                $smartLink = \lindemannrock\smartlinks\elements\SmartLink::find()
                    ->id($smartLinkId)
                    ->one();
                if ($smartLink) {
                    // Clean the slug for filename
                    $cleanSlug = preg_replace('/[^a-zA-Z0-9-_]/', '', $smartLink->slug);
                    $singularPart = strtolower(str_replace(' ', '-', $settings->getLowerDisplayName()));
                    $baseFilename = $singularPart . '-' . $cleanSlug . '-analytics';
                }
            }
            
            $filename = $baseFilename . '-' . $dateRange . '-' . date('Y-m-d') . '.' . $format;
            
            return Craft::$app->getResponse()->sendContentAsFile(
                $data,
                $filename,
                [
                    'mimeType' => $format === 'csv' ? 'text/csv' : 'application/json',
                ]
            );
        } catch (\Exception $e) {
            Craft::$app->getSession()->setError($e->getMessage());
            return $this->redirect('smart-links/analytics');
        }
    }
}
