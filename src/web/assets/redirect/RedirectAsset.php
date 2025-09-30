<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\web\assets\redirect;

use Craft;
use craft\web\AssetBundle;

/**
 * Redirect Tracking Asset Bundle
 *
 * This asset bundle provides the JavaScript needed for tracking redirects
 * and QR code scans on the front-end.
 *
 * @author LindemannRock
 * @since 1.0.0
 */
class RedirectAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        // Define the path to the assets folder
        $this->sourcePath = __DIR__;

        // Use minified JS in production
        $this->js = [
            Craft::$app->getConfig()->getGeneral()->devMode ? 'redirect-tracking.js' : 'redirect-tracking.min.js',
        ];

        parent::init();
    }
}