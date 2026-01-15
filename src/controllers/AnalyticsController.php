<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\controllers;

use Craft;
use craft\web\Controller;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\web\Response;

/**
 * Analytics Controller
 *
 * @since 1.0.0
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
        $this->setLoggingHandle('smartlink-manager');
    }

    /**
     * Analytics dashboard
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $this->requirePermission('smartLinkManager:viewAnalytics');

        // Check if analytics are globally enabled
        if (!SmartLinkManager::$plugin->getSettings()->enableAnalytics) {
            throw new \yii\web\ForbiddenHttpException('Analytics are disabled in plugin settings.');
        }

        $variables = [
            'title' => Craft::t('smartlink-manager', 'Analytics'),
        ];

        // Get date range and site
        $request = Craft::$app->getRequest();
        $dateRange = $request->getParam('dateRange', 'last7days');
        $siteId = $request->getParam('siteId');
        $siteId = $siteId ? (int)$siteId : null;

        $variables['dateRange'] = $dateRange;
        $variables['siteId'] = $siteId;

        // Get enabled sites for site selector (respects enabledSites setting)
        $settings = SmartLinkManager::$plugin->getSettings();
        $enabledSiteIds = $settings->getEnabledSiteIds();
        $allSites = Craft::$app->getSites()->getAllSites();
        $variables['sites'] = array_filter($allSites, fn($site) => in_array($site->id, $enabledSiteIds));

        // Get analytics data
        $variables['analyticsData'] = SmartLinkManager::$plugin->analytics->getAnalyticsSummary($dateRange, null, $siteId);

        // Pass settings to template
        $variables['settings'] = $settings;

        return $this->renderTemplate('smartlink-manager/analytics/index', $variables);
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
        if (!SmartLinkManager::$plugin->getSettings()->enableAnalytics) {
            return $this->asJson([
                'success' => false,
                'error' => 'Analytics are disabled in plugin settings.',
            ]);
        }

        $request = Craft::$app->getRequest();
        $smartLinkId = $request->getParam('smartLinkId');
        $dateRange = $request->getParam('dateRange', 'last7days');
        $type = $request->getParam('type', 'summary');
        $siteId = $request->getParam('siteId');
        $siteId = $siteId ? (int)$siteId : null;

        // Log the request for debugging
        $this->logInfo('Analytics getData called', ['type' => $type, 'dateRange' => $dateRange, 'smartLinkId' => $smartLinkId ?? null, 'siteId' => $siteId]);

        // If requesting data for a specific SmartLink, check if it has analytics enabled
        if ($smartLinkId) {
            $smartLink = \lindemannrock\smartlinkmanager\elements\SmartLink::find()
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
                'clicks' => SmartLinkManager::$plugin->analytics->getClicksData($smartLinkId, $dateRange, $siteId),
                'devices' => SmartLinkManager::$plugin->analytics->getDeviceBreakdown($smartLinkId, $dateRange, $siteId),
                'device-types' => SmartLinkManager::$plugin->analytics->getDeviceTypeBreakdown($smartLinkId, $dateRange, $siteId),
                'device-brands' => SmartLinkManager::$plugin->analytics->getDeviceBrandBreakdown($smartLinkId, $dateRange, $siteId),
                'platforms' => SmartLinkManager::$plugin->analytics->getPlatformBreakdown($smartLinkId, $dateRange, $siteId),
                'os-breakdown' => SmartLinkManager::$plugin->analytics->getOsBreakdown($smartLinkId, $dateRange, $siteId),
                'browsers' => SmartLinkManager::$plugin->analytics->getBrowserBreakdown($smartLinkId, $dateRange, $siteId),
                'countries' => SmartLinkManager::$plugin->analytics->getTopCountries($smartLinkId, $dateRange, 15, $siteId),
                'all-countries' => SmartLinkManager::$plugin->analytics->getAllCountries($smartLinkId, $dateRange, $siteId),
                'all-cities' => SmartLinkManager::$plugin->analytics->getTopCities($smartLinkId, $dateRange, 50, $siteId),
                'languages' => SmartLinkManager::$plugin->analytics->getLanguageBreakdown($smartLinkId, $dateRange, $siteId),
                'hourly' => SmartLinkManager::$plugin->analytics->getHourlyAnalytics($smartLinkId, $dateRange, $siteId),
                'insights' => SmartLinkManager::$plugin->analytics->getInsights($dateRange, $siteId),
                default => SmartLinkManager::$plugin->analytics->getAnalyticsSummary($dateRange, $smartLinkId, $siteId),
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
        if (!SmartLinkManager::$plugin->getSettings()->enableAnalytics) {
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
            $smartLink = \lindemannrock\smartlinkmanager\elements\SmartLink::find()
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
            $analyticsService = SmartLinkManager::$plugin->analytics;

            // Set the range parameter in the request so the template can access it
            $_GET['range'] = $range;
            Craft::$app->getRequest()->setQueryParams(array_merge(Craft::$app->getRequest()->getQueryParams(), ['range' => $range]));
            
            // Render only the content part for AJAX
            $html = Craft::$app->getView()->renderTemplate('smartlink-manager/smartlinks/_partials/analytics-content', [
                'smartLink' => $smartLink,
                'analyticsService' => $analyticsService,
                'dateRange' => $range,  // Pass the range directly
                'settings' => SmartLinkManager::$plugin->getSettings(),
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
        $this->requirePermission('smartLinkManager:exportAnalytics');

        // Check if analytics are globally enabled
        if (!SmartLinkManager::$plugin->getSettings()->enableAnalytics) {
            Craft::$app->getSession()->setError('Analytics are disabled in plugin settings.');
            return $this->redirect('smartlink-manager');
        }

        $request = Craft::$app->getRequest();
        $smartLinkId = $request->getParam('smartLinkId');
        $dateRange = $request->getParam('dateRange', 'last7days');
        $format = $request->getParam('format', 'csv');
        $siteId = $request->getParam('siteId');
        $siteId = $siteId ? (int)$siteId : null;

        // If exporting for a specific SmartLink, check if it has analytics enabled
        if ($smartLinkId) {
            $smartLink = \lindemannrock\smartlinkmanager\elements\SmartLink::find()
                ->id($smartLinkId)
                ->status(null)
                ->one();

            if (!$smartLink) {
                Craft::$app->getSession()->setError('Smart link not found.');
                return $this->redirect('smartlink-manager');
            }

            if (!($smartLink->trackAnalytics ?? true)) {
                Craft::$app->getSession()->setError('Analytics tracking is disabled for this smart link.');
                return $this->redirect('smartlink-manager/smartlinks/' . $smartLinkId);
            }
        }

        try {
            $data = SmartLinkManager::$plugin->analytics->exportAnalytics($smartLinkId, $dateRange, $format, $siteId);

            // Generate filename
            $settings = SmartLinkManager::$plugin->getSettings();
            $filenamePart = strtolower(str_replace(' ', '-', $settings->getLowerDisplayName()));
            $baseFilename = $filenamePart . '-analytics';
            if ($smartLinkId) {
                $smartLink = \lindemannrock\smartlinkmanager\elements\SmartLink::find()
                    ->id($smartLinkId)
                    ->one();
                if ($smartLink) {
                    // Clean the slug for filename
                    $cleanSlug = preg_replace('/[^a-zA-Z0-9-_]/', '', $smartLink->slug);
                    $singularPart = strtolower(str_replace(' ', '-', $settings->getLowerDisplayName()));
                    $baseFilename = $singularPart . '-' . $cleanSlug . '-analytics';
                }
            }

            // Get site name for filename
            $sitePart = 'all';
            if ($siteId) {
                $site = Craft::$app->getSites()->getSiteById($siteId);
                if ($site) {
                    $sitePart = strtolower(preg_replace('/[^a-zA-Z0-9-_]/', '', str_replace(' ', '-', $site->name)));
                }
            }

            // Use "alltime" instead of "all" for clearer filename
            $dateRangeLabel = $dateRange === 'all' ? 'alltime' : $dateRange;
            $filename = $baseFilename . '-' . $sitePart . '-' . $dateRangeLabel . '-' . date('Y-m-d') . '.' . $format;

            return Craft::$app->getResponse()->sendContentAsFile(
                $data,
                $filename,
                [
                    'mimeType' => $format === 'csv' ? 'text/csv' : 'application/json',
                ]
            );
        } catch (\Exception $e) {
            Craft::$app->getSession()->setError($e->getMessage());

            // Preserve the date range when redirecting back
            if ($smartLinkId) {
                return $this->redirect('smartlink-manager/smartlinks/' . $smartLinkId . '?range=' . $dateRange);
            }
            return $this->redirect('smartlink-manager/analytics?dateRange=' . $dateRange);
        }
    }
}
