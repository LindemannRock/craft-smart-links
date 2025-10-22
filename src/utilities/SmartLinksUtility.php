<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\utilities;

use Craft;
use craft\base\Utility;
use craft\db\Query;
use lindemannrock\smartlinks\SmartLinks;

/**
 * Smart Links Utility
 */
class SmartLinksUtility extends Utility
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        $pluginName = SmartLinks::getInstance()->getSettings()->pluginName;
        return $pluginName;
    }

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'smart-links';
    }

    /**
     * @inheritdoc
     */
    public static function icon(): ?string
    {
        return 'link';
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        $smartLinks = SmartLinks::$plugin;

        // Get basic stats
        $totalLinks = \lindemannrock\smartlinks\elements\SmartLink::find()->count();
        $activeLinks = \lindemannrock\smartlinks\elements\SmartLink::find()->status('enabled')->count();
        $disabledLinks = $totalLinks - $activeLinks;

        // Get click stats from analytics table
        $totalClicks = (int) (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->count();

        // Get recent analytics (last 7 days)
        $recentAnalytics = $smartLinks->analytics->getAnalyticsSummary('last7days');

        // Get QR code stats
        $qrScans = (int) (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->where("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.source')) = 'qr'")
            ->count();

        // Get auto redirects (clickType = redirect)
        $autoRedirects = (int) (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->where("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.clickType')) = 'redirect'")
            ->count();

        // Get button clicks (clickType = button)
        $buttonClicks = (int) (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->where("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.clickType')) = 'button'")
            ->count();

        // Get platform breakdown from JSON metadata
        $platformStats = (new Query())
            ->select(["JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.platform')) as platform", 'COUNT(*) as count'])
            ->from('{{%smartlinks_analytics}}')
            ->groupBy('platform')
            ->orderBy(['count' => SORT_DESC])
            ->all();

        // Get click type breakdown from JSON metadata
        $clickTypes = (new Query())
            ->select(["JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.clickType')) as clickType", 'COUNT(*) as count'])
            ->from('{{%smartlinks_analytics}}')
            ->groupBy('clickType')
            ->all();

        // Get daily clicks for last 14 days
        $dailyClicks = (new Query())
            ->select([
                'DATE(dateCreated) as date',
                'COUNT(*) as clicks'
            ])
            ->from('{{%smartlinks_analytics}}')
            ->where(['>=', 'dateCreated', (new \DateTime('-14 days'))->format('Y-m-d H:i:s')])
            ->groupBy('DATE(dateCreated)')
            ->orderBy(['date' => SORT_ASC])
            ->all();

        // Cache stats
        $settings = $smartLinks->getSettings();
        $pluginName = $settings->pluginName ?? 'Smart Links';
        $singularName = preg_replace('/s$/', '', $pluginName) ?: $pluginName;

        $qrCachePath = Craft::$app->path->getRuntimePath() . '/smart-links/cache/qr/';
        $deviceCachePath = Craft::$app->path->getRuntimePath() . '/smart-links/cache/device/';

        $qrCacheFiles = is_dir($qrCachePath) ? count(glob($qrCachePath . '*.cache')) : 0;
        $deviceCacheFiles = is_dir($deviceCachePath) ? count(glob($deviceCachePath . '*.cache')) : 0;

        // Check if there are any invalid platform values that need fixing
        $invalidPlatformValues = [
            'app-store', 'google-play', 'mac-app-store', 'amazon-appstore',
            'redirect', 'auto-redirect', 'button', 'test',
            'iOS', 'Ios', 'IOS', 'Android', 'ANDROID',
            'Windows', 'WINDOWS', 'macOS', 'MacOS', 'Mac', 'MAC',
            'Linux', 'LINUX'
        ];

        // Build the WHERE clause manually since Yii2 doesn't handle JSON functions well in IN clause
        $platformConditions = array_map(function($val) {
            return "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.platform')) = '" . addslashes($val) . "'";
        }, $invalidPlatformValues);

        $whereClause = '(' . implode(' OR ', $platformConditions) . ')';

        $invalidPlatformCount = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->where($whereClause)
            ->count();

        return Craft::$app->getView()->renderTemplate('smart-links/utilities/index', [
            'totalLinks' => $totalLinks,
            'activeLinks' => $activeLinks,
            'disabledLinks' => $disabledLinks,
            'totalClicks' => $totalClicks,
            'qrScans' => $qrScans,
            'autoRedirects' => $autoRedirects,
            'buttonClicks' => $buttonClicks,
            'platformStats' => $platformStats,
            'clickTypes' => $clickTypes,
            'dailyClicks' => $dailyClicks,
            'recentAnalytics' => $recentAnalytics,
            'qrCacheFiles' => $qrCacheFiles,
            'deviceCacheFiles' => $deviceCacheFiles,
            'settings' => $settings,
            'pluginName' => $pluginName,
            'singularName' => $singularName,
            'invalidPlatformCount' => $invalidPlatformCount,
        ]);
    }
}