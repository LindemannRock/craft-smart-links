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
        return Craft::t('smart-links', 'Smart Links');
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
    public static function iconPath(): ?string
    {
        return Craft::getAlias('@lindemannrock/smartlinks/icon.svg');
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        $smartLinks = SmartLinks::$plugin;
        
        // Get stats
        $totalLinks = \lindemannrock\smartlinks\elements\SmartLink::find()->count();
        $activeLinks = \lindemannrock\smartlinks\elements\SmartLink::find()->status('enabled')->count();
        $totalClicks = (int) (new Query())
            ->from('{{%smartlinks}}')
            ->sum('clicks') ?? 0;
        
        // Get recent analytics
        $recentAnalytics = $smartLinks->analytics->getAnalyticsSummary('last7days');
        
        return Craft::$app->getView()->renderTemplate('smart-links/_components/utilities/SmartLinksUtility', [
            'totalLinks' => $totalLinks,
            'activeLinks' => $activeLinks,
            'totalClicks' => $totalClicks,
            'recentAnalytics' => $recentAnalytics,
        ]);
    }
}