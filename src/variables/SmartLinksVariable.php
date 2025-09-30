<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\variables;

use Craft;
use lindemannrock\smartlinks\elements\db\SmartLinkQuery;
use lindemannrock\smartlinks\elements\SmartLink;
use lindemannrock\smartlinks\SmartLinks;

/**
 * Smart Links Variable
 * 
 * @author    Al Hatab Foods
 * @package   SmartLinks
 * @since     1.0.0
 */
class SmartLinksVariable
{
    /**
     * Returns a new SmartLinkQuery instance.
     *
     * @param array $criteria
     * @return SmartLinkQuery
     */
    public function find(array $criteria = []): SmartLinkQuery
    {
        $query = SmartLink::find();
        
        if (!empty($criteria)) {
            Craft::configure($query, $criteria);
        }
        
        return $query;
    }

    /**
     * Returns smart links that match the given criteria.
     *
     * @param array $criteria
     * @return SmartLink[]
     */
    public function all(array $criteria = []): array
    {
        return $this->find($criteria)->all();
    }

    /**
     * Returns one smart link that matches the given criteria.
     *
     * @param array $criteria
     * @return SmartLink|null
     */
    public function one(array $criteria = []): ?SmartLink
    {
        return $this->find($criteria)->one();
    }

    /**
     * Returns a smart link by its ID.
     *
     * @param int $id
     * @return SmartLink|null
     */
    public function getById(int $id): ?SmartLink
    {
        return SmartLink::find()->id($id)->one();
    }

    /**
     * Returns a smart link by its slug.
     *
     * @param string $slug
     * @return SmartLink|null
     */
    public function getBySlug(string $slug): ?SmartLink
    {
        return SmartLink::find()->slug($slug)->one();
    }

    /**
     * Alias for getBySlug()
     *
     * @param string $slug
     * @return SmartLinkQuery
     */
    public function slug(string $slug): SmartLinkQuery
    {
        return SmartLink::find()->slug($slug);
    }

    /**
     * Returns only active smart links.
     *
     * @return SmartLinkQuery
     */
    public function active(): SmartLinkQuery
    {
        return SmartLink::find()->status(SmartLink::STATUS_ENABLED);
    }


    /**
     * Creates a new smart link (for demonstration/documentation)
     * Note: This doesn't save to database, just shows structure
     *
     * @param array $config
     * @return SmartLink
     */
    public function create(array $config = []): SmartLink
    {
        $smartLink = new SmartLink();
        
        if (!empty($config)) {
            Craft::configure($smartLink, $config);
        }
        
        return $smartLink;
    }

    /**
     * Get analytics data for a smart link
     *
     * @param SmartLink $smartLink
     * @param array $criteria
     * @return array
     */
    public function getAnalytics(SmartLink $smartLink, array $criteria = []): array
    {
        return SmartLinks::$plugin->analytics->getAnalytics($smartLink, $criteria);
    }

    /**
     * Get the module instance
     *
     * @return SmartLinks
     */
    public function getModule(): SmartLinks
    {
        return SmartLinks::$plugin;
    }

    /**
     * Get module settings
     *
     * @return \lindemannrock\smartlinks\models\Settings
     */
    public function getSettings(): \lindemannrock\smartlinks\models\Settings
    {
        return SmartLinks::$plugin->getSettings();
    }

    /**
     * Register redirect tracking assets
     *
     * Usage in templates:
     * {% do craft.smartLinks.registerTracking(smartLink, redirectUrl) %}
     * {% do craft.smartLinks.registerTracking(smartLink, redirectUrl, {debug: true}) %}
     *
     * @param SmartLink $smartLink
     * @param string $redirectUrl
     * @param array $options Optional configuration (debug: bool)
     * @return void
     */
    public function registerTracking(SmartLink $smartLink, string $redirectUrl, array $options = []): void
    {
        $view = Craft::$app->getView();

        // Register the asset bundle
        $view->registerAssetBundle(\lindemannrock\smartlinks\web\assets\redirect\RedirectAsset::class);

        // Register the tracking configuration
        // Pass ALL URLs to JavaScript, let it decide based on client-side device detection
        $trackingConfig = [
            'smartLinkId' => $smartLink->id,
            'urls' => [
                'ios' => $smartLink->iosUrl ?: '',
                'android' => $smartLink->androidUrl ?: '',
                'huawei' => $smartLink->huaweiUrl ?: '',
                'amazon' => $smartLink->amazonUrl ?: '',
                'windows' => $smartLink->windowsUrl ?: '',
                'mac' => $smartLink->macUrl ?: '',
            ],
            'trackAnalytics' => $smartLink->trackAnalytics,
            'trackingEndpoint' => \craft\helpers\UrlHelper::actionUrl('smart-links/redirect/track-button-click'),
            'csrfEndpoint' => \craft\helpers\UrlHelper::actionUrl('smart-links/redirect/refresh-csrf'),
            'debug' => $options['debug'] ?? false,
        ];

        $view->registerJs(
            'window.smartLinksTracking = ' . json_encode($trackingConfig) . ';',
            $view::POS_HEAD
        );
    }
}