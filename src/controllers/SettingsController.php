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
use lindemannrock\smartlinks\models\Settings;
use lindemannrock\smartlinks\SmartLinks;
use lindemannrock\smartlinks\jobs\CleanupAnalyticsJob;
use yii\web\Response;
use yii\web\ForbiddenHttpException;

/**
 * Settings Controller
 */
class SettingsController extends Controller
{
    /**
     * @var array
     */
    protected array|bool|int $allowAnonymous = false;

    /**
     * Settings index - redirect to general
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        return $this->redirect('smart-links/settings/general');
    }
    
    /**
     * Debug settings loading
     *
     * @return Response
     */
    public function actionDebug(): Response
    {
        $this->requirePermission('smartLinks:settings');
        
        // Test database query directly
        $row = (new \craft\db\Query())
            ->from('{{%smartlinks_settings}}')
            ->where(['id' => 1])
            ->one();
            
        $settings = Settings::loadFromDatabase();
        
        return $this->asJson([
            'database_row' => $row,
            'loaded_settings' => $settings ? $settings->getAttributes() : null,
            'settings_class' => get_class($settings),
        ]);
    }

    /**
     * General settings
     *
     * @return Response
     */
    public function actionGeneral(): Response
    {
        $this->requirePermission('smartLinks:settings');
        
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();
        
        // Debug: Make absolutely sure we have a settings object
        if (!$settings instanceof Settings) {
            throw new \Exception('Settings is not an instance of Settings class');
        }
        
        // Minimal test
        try {
            return $this->renderTemplate('smart-links/settings/general', [
                'settings' => $settings,
                'plugin' => $plugin,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Template render error: ' . $e->getMessage());
        }
    }

    /**
     * Analytics settings
     *
     * @return Response
     */
    public function actionAnalytics(): Response
    {
        $this->requirePermission('smartLinks:settings');
        
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/analytics', [
            'settings' => $settings,
        ]);
    }

    /**
     * Export settings
     *
     * @return Response
     */
    public function actionExport(): Response
    {
        $this->requirePermission('smartLinks:settings');
        
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/export', [
            'settings' => $settings,
        ]);
    }

    /**
     * QR Code settings
     *
     * @return Response
     */
    public function actionQrCode(): Response
    {
        $this->requirePermission('smartLinks:settings');
        
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/qr-code', [
            'settings' => $settings,
        ]);
    }

    /**
     * Redirect settings
     *
     * @return Response
     */
    public function actionRedirect(): Response
    {
        $this->requirePermission('smartLinks:settings');
        
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/redirect', [
            'settings' => $settings,
        ]);
    }

    /**
     * Interface settings
     *
     * @return Response
     */
    public function actionInterface(): Response
    {
        $this->requirePermission('smartLinks:settings');
        
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/interface', [
            'settings' => $settings,
        ]);
    }

    /**
     * Advanced settings
     *
     * @return Response
     */
    public function actionAdvanced(): Response
    {
        $this->requirePermission('smartLinks:settings');
        
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/advanced', [
            'settings' => $settings,
        ]);
    }

    /**
     * Save settings
     *
     * @return Response
     */
    public function actionSave(): Response
    {
        // Basic debug - write to file
        file_put_contents('/tmp/smart-links-debug.log', date('Y-m-d H:i:s') . " - Save action called\n", FILE_APPEND);

        $this->requirePostRequest();
        
        // Check permission first
        $this->requirePermission('smartLinks:settings');
        
        // No need to check allowAdminChanges since settings are stored in database
        // not in project config

        // Load current settings from database
        $settings = Settings::loadFromDatabase();
        if (!$settings) {
            $settings = new Settings();
        }
        
        $settingsData = Craft::$app->getRequest()->getBodyParam('settings');

        // Debug: Log what we received
        Craft::info('Settings data received: ' . json_encode($settingsData), 'smart-links');

        // Debug: Specifically check imageVolumeUid
        if (isset($settingsData['imageVolumeUid'])) {
            Craft::info('imageVolumeUid type: ' . gettype($settingsData['imageVolumeUid']), 'smart-links');
            Craft::info('imageVolumeUid value: ' . json_encode($settingsData['imageVolumeUid']), 'smart-links');
        }

        // Debug: Log all POST data
        Craft::info('All POST data: ' . json_encode(Craft::$app->getRequest()->getBodyParams()), 'smart-links');

        // Handle pluginName field
        if (isset($settingsData['pluginName'])) {
            $settings->pluginName = $settingsData['pluginName'];
        }
        
        // Handle enabledSites checkbox group
        if (isset($settingsData['enabledSites'])) {
            if (is_array($settingsData['enabledSites'])) {
                // Convert string values to integers
                $settingsData['enabledSites'] = array_map('intval', array_filter($settingsData['enabledSites']));
            } else {
                $settingsData['enabledSites'] = [];
            }
        } else {
            // No sites selected = empty array (which means all sites enabled)
            $settingsData['enabledSites'] = [];
        }

        // Handle asset field (returns array)
        if (isset($settingsData['defaultQrLogoId']) && is_array($settingsData['defaultQrLogoId'])) {
            $settingsData['defaultQrLogoId'] = $settingsData['defaultQrLogoId'][0] ?? null;
        }

        // Auto-set qrLogoVolumeUid to same value as imageVolumeUid
        if (isset($settingsData['imageVolumeUid'])) {
            $settingsData['qrLogoVolumeUid'] = $settingsData['imageVolumeUid'];
            Craft::info('Auto-setting qrLogoVolumeUid to match imageVolumeUid: ' . $settingsData['imageVolumeUid'], 'smart-links');
        }
        
        // Fix color fields - add # if missing
        if (isset($settingsData['defaultQrColor']) && !str_starts_with($settingsData['defaultQrColor'], '#')) {
            $settingsData['defaultQrColor'] = '#' . $settingsData['defaultQrColor'];
        }
        if (isset($settingsData['defaultQrBgColor']) && !str_starts_with($settingsData['defaultQrBgColor'], '#')) {
            $settingsData['defaultQrBgColor'] = '#' . $settingsData['defaultQrBgColor'];
        }
        if (isset($settingsData['qrEyeColor'])) {
            if (empty($settingsData['qrEyeColor'])) {
                // If empty, set to null
                $settingsData['qrEyeColor'] = null;
            } elseif (!str_starts_with($settingsData['qrEyeColor'], '#')) {
                // If not empty and doesn't start with #, add it
                $settingsData['qrEyeColor'] = '#' . $settingsData['qrEyeColor'];
            }
        }
        
        $settings->setAttributes($settingsData, false);

        // Debug: Log what's in settings after setAttributes
        Craft::info('Settings after setAttributes - enabledSites: ' . json_encode($settings->enabledSites), 'smart-links');

        if (!$settings->validate()) {
            // Log validation errors for debugging
            Craft::error('Settings validation failed: ' . json_encode($settings->getErrors()), 'smart-links');
            
            // Standard Craft way: Pass errors back to template
            Craft::$app->getSession()->setError(Craft::t('smart-links', 'Couldn\'t save settings.'));
            
            // Re-render the template with errors
            // Get the section from the request to render the correct template
            $section = Craft::$app->getRequest()->getBodyParam('section', 'general');
            $template = "smart-links/settings/{$section}";
            
            return $this->renderTemplate($template, [
                'settings' => $settings,
            ]);
        }

        // Save settings to database
        if ($settings->saveToDatabase()) {
            // Update the plugin's cached settings if plugin is available
            $plugin = SmartLinks::getInstance();
            if ($plugin) {
                // setSettings expects an array, not an object
                $plugin->setSettings($settings->getAttributes());
            }
            
            Craft::$app->getSession()->setNotice(Craft::t('smart-links', 'Settings saved.'));
        } else {
            Craft::$app->getSession()->setError(Craft::t('smart-links', 'Couldn\'t save settings.'));
        }

        return $this->redirectToPostedUrl();
    }

    /**
     * Cleanup analytics data
     *
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionCleanupAnalytics(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        
        // Check admin permissions
        if (!Craft::$app->getUser()->getIsAdmin()) {
            throw new ForbiddenHttpException('User is not an admin');
        }
        
        try {
            // Queue the cleanup job
            Craft::$app->queue->push(new CleanupAnalyticsJob());
            
            return $this->asJson([
                'success' => true,
                'message' => Craft::t('smart-links', 'Analytics cleanup job has been queued. It will run in the background.')
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}