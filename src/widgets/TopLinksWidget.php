<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\widgets;

use Craft;
use craft\base\Widget;
use lindemannrock\smartlinks\SmartLinks;

/**
 * Smart Links Top Performing Links Widget
 *
 * @since 1.0.0
 */
class TopLinksWidget extends Widget
{
    /**
     * @var string Date range for analytics
     */
    public string $dateRange = 'last7days';

    /**
     * @var int Number of links to show
     */
    public int $limit = 5;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['dateRange'], 'string'];
        $rules[] = [['dateRange'], 'default', 'value' => 'last7days'];
        $rules[] = [['limit'], 'integer', 'min' => 1, 'max' => 20];
        $rules[] = [['limit'], 'default', 'value' => 5];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        $pluginName = SmartLinks::$plugin->getSettings()->getFullName();
        return Craft::t('smart-links', '{pluginName} - Top Links', ['pluginName' => $pluginName]);
    }

    /**
     * @inheritdoc
     */
    public static function icon(): ?string
    {
        return '@app/icons/trophy.svg';
    }

    /**
     * @inheritdoc
     */
    public static function maxColspan(): ?int
    {
        return 2;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): ?string
    {
        $pluginName = SmartLinks::$plugin->getSettings()->getFullName();
        return Craft::t('smart-links', '{pluginName} - Top Links', ['pluginName' => $pluginName]);
    }

    /**
     * @inheritdoc
     */
    public function getSubtitle(): ?string
    {
        $labels = [
            'today' => Craft::t('smart-links', 'Today'),
            'yesterday' => Craft::t('smart-links', 'Yesterday'),
            'last7days' => Craft::t('smart-links', 'Last 7 days'),
            'last30days' => Craft::t('smart-links', 'Last 30 days'),
            'last90days' => Craft::t('smart-links', 'Last 90 days'),
            'all' => Craft::t('smart-links', 'All time'),
        ];

        return $labels[$this->dateRange] ?? Craft::t('smart-links', 'Last 7 days');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('smart-links/widgets/top-links/settings', [
            'widget' => $this,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml(): ?string
    {
        // Check if analytics are enabled
        if (!SmartLinks::$plugin->getSettings()->enableAnalytics) {
            return '<p class="light">' . Craft::t('smart-links', 'Analytics are disabled in plugin settings.') . '</p>';
        }

        // Get analytics data
        $analyticsData = SmartLinks::$plugin->analytics->getAnalyticsSummary($this->dateRange);
        $topLinks = array_slice($analyticsData['topLinks'] ?? [], 0, $this->limit);

        return Craft::$app->getView()->renderTemplate('smart-links/widgets/top-links/body', [
            'widget' => $this,
            'links' => $topLinks,
        ]);
    }
}
