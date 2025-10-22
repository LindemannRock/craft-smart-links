<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use lindemannrock\smartlinks\elements\SmartLink;
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
     * @var bool
     */
    private bool $readOnly;

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        // View actions allowed without allowAdminChanges
        $viewActions = ['index', 'general', 'analytics', 'integrations', 'export', 'qr-code', 'redirect', 'interface', 'advanced', 'field-layout', 'debug'];

        // Cache clearing actions don't require allowAdminChanges (cache is runtime data, not config)
        $cacheActions = ['clear-qr-cache', 'clear-device-cache', 'clear-all-caches', 'clear-all-analytics', 'cleanup-platform-values'];

        if (in_array($action->id, $viewActions) || in_array($action->id, $cacheActions)) {
            $this->requirePermission('smartLinks:settings');
        } else {
            // Save actions require allowAdminChanges
            $this->requirePermission('smartLinks:settings');
            if (!Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
                throw new ForbiddenHttpException('Administrative changes are disallowed in this environment.');
            }
        }

        $this->readOnly = !Craft::$app->getConfig()->getGeneral()->allowAdminChanges;
        return parent::beforeAction($action);
    }

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
                'readOnly' => $this->readOnly,
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
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/analytics', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Integrations settings
     *
     * @return Response
     */
    public function actionIntegrations(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/integrations', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Export settings
     *
     * @return Response
     */
    public function actionExport(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/export', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * QR Code settings
     *
     * @return Response
     */
    public function actionQrCode(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/qr-code', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Redirect settings
     *
     * @return Response
     */
    public function actionRedirect(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/redirect', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Interface settings
     *
     * @return Response
     */
    public function actionInterface(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/interface', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Advanced settings
     *
     * @return Response
     */
    public function actionAdvanced(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinks::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smart-links/settings/advanced', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Field Layout settings
     *
     * @return Response
     */
    public function actionFieldLayout(): Response
    {
        // Try new format first (smart-links.fieldLayouts)
        $fieldLayouts = Craft::$app->getProjectConfig()->get('smart-links.fieldLayouts') ?? [];

        $fieldLayout = null;

        if (!empty($fieldLayouts)) {
            // Get the first (and only) field layout
            $fieldLayoutUid = array_key_first($fieldLayouts);
            $fieldLayout = Craft::$app->getFields()->getLayoutByUid($fieldLayoutUid);
        }

        // Backwards compatibility: try old format (smart-links.fieldLayout with just UID)
        if (!$fieldLayout) {
            $oldUid = Craft::$app->getProjectConfig()->get('smart-links.fieldLayout');
            if ($oldUid) {
                $fieldLayout = Craft::$app->getFields()->getLayoutByUid($oldUid);
            }
        }

        // Fallback: try to get by type (in case it exists in database)
        if (!$fieldLayout) {
            $fieldLayout = Craft::$app->getFields()->getLayoutByType(\lindemannrock\smartlinks\elements\SmartLink::class);
        }

        if (!$fieldLayout) {
            // Create a new field layout if none exists
            $fieldLayout = new \craft\models\FieldLayout([
                'type' => \lindemannrock\smartlinks\elements\SmartLink::class,
            ]);

            // Save the empty field layout so it has an ID (needed for designer to work)
            Craft::$app->getFields()->saveLayout($fieldLayout);

            // Save to project config only if not in read-only mode
            if (!$this->readOnly) {
                $fieldLayoutConfig = $fieldLayout->getConfig();
                if ($fieldLayoutConfig) {
                    Craft::$app->getProjectConfig()->set(
                        "smart-links.fieldLayouts.{$fieldLayout->uid}",
                        $fieldLayoutConfig,
                        "Create Smart Links field layout"
                    );
                }
            }
        }

        // Debug field layout
        Craft::info('Field Layout debug info', 'smart-links', [
            'id' => $fieldLayout->id ?? 'null',
            'uid' => $fieldLayout->uid ?? 'null',
            'type' => $fieldLayout->type ?? 'null',
            'class' => get_class($fieldLayout)
        ]);

        $variables = [
            'fieldLayout' => $fieldLayout,
            'readOnly' => $this->readOnly,
        ];

        // Debug logging
        Craft::info('actionFieldLayout called', 'smart-links', [
            'fieldLayout_exists' => $fieldLayout !== null,
            'fieldLayout_id' => $fieldLayout ? $fieldLayout->id : null,
            'readOnly' => $this->readOnly,
        ]);

        return $this->renderTemplate('smart-links/settings/field-layout', $variables);
    }

    /**
     * Save field layout
     *
     * @return Response|null
     */
    public function actionSaveFieldLayout(): ?Response
    {
        $this->requirePostRequest();
        $this->requirePermission('smartLinks:settings');

        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = \lindemannrock\smartlinks\elements\SmartLink::class;

        if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
            Craft::$app->getSession()->setError(Craft::t('smart-links', 'Couldn\'t save field layout.'));
            return null;
        }

        // Save field layout config to project config so it syncs across environments
        $fieldLayoutConfig = $fieldLayout->getConfig();
        if ($fieldLayoutConfig) {
            Craft::$app->getProjectConfig()->set(
                "smart-links.fieldLayouts.{$fieldLayout->uid}",
                $fieldLayoutConfig,
                "Save Smart Links field layout"
            );

            // Remove old format if it exists (migration)
            if (Craft::$app->getProjectConfig()->get('smart-links.fieldLayout')) {
                Craft::$app->getProjectConfig()->remove('smart-links.fieldLayout');
            }
        }

        Craft::$app->getSession()->setNotice(Craft::t('smart-links', 'Field layout saved.'));
        return $this->redirectToPostedUrl();
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
        Craft::info('Settings data received', 'smart-links', ['settingsData' => $settingsData]);

        // Debug: Specifically check imageVolumeUid
        if (isset($settingsData['imageVolumeUid'])) {
            Craft::info('imageVolumeUid debug', 'smart-links', [
                'type' => gettype($settingsData['imageVolumeUid']),
                'value' => $settingsData['imageVolumeUid']
            ]);
        }

        // Debug: Log all POST data
        Craft::info('All POST data', 'smart-links', ['bodyParams' => Craft::$app->getRequest()->getBodyParams()]);

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
            Craft::info('Auto-setting qrLogoVolumeUid to match imageVolumeUid', 'smart-links', ['uid' => $settingsData['imageVolumeUid']]);
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
        Craft::info('Settings after setAttributes', 'smart-links', ['enabledSites' => $settings->enabledSites]);

        if (!$settings->validate()) {
            // Log validation errors for debugging
            Craft::error('Settings validation failed', 'smart-links', ['errors' => $settings->getErrors()]);

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

    /**
     * Clear QR code cache
     *
     * @return Response
     */
    public function actionClearQrCache(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        try {
            $cachePath = Craft::$app->path->getRuntimePath() . '/smart-links/cache/qr/';
            $cleared = 0;

            if (is_dir($cachePath)) {
                $files = glob($cachePath . '*.cache');
                foreach ($files as $file) {
                    if (@unlink($file)) {
                        $cleared++;
                    }
                }
            }

            return $this->asJson([
                'success' => true,
                'message' => Craft::t('smart-links', 'Cleared {count} QR code caches.', ['count' => $cleared])
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear device detection cache
     *
     * @return Response
     */
    public function actionClearDeviceCache(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        try {
            $cachePath = Craft::$app->path->getRuntimePath() . '/smart-links/cache/device/';
            $cleared = 0;

            if (is_dir($cachePath)) {
                $files = glob($cachePath . '*.cache');
                foreach ($files as $file) {
                    if (@unlink($file)) {
                        $cleared++;
                    }
                }
            }

            return $this->asJson([
                'success' => true,
                'message' => Craft::t('smart-links', 'Cleared {count} device detection caches.', ['count' => $cleared])
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear all Smart Links caches
     *
     * @return Response
     */
    public function actionClearAllCaches(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        try {
            $totalCleared = 0;

            // Clear QR code caches
            $qrPath = Craft::$app->path->getRuntimePath() . '/smart-links/cache/qr/';
            if (is_dir($qrPath)) {
                $files = glob($qrPath . '*.cache');
                foreach ($files as $file) {
                    if (@unlink($file)) {
                        $totalCleared++;
                    }
                }
            }

            // Clear device detection caches
            $devicePath = Craft::$app->path->getRuntimePath() . '/smart-links/cache/device/';
            if (is_dir($devicePath)) {
                $files = glob($devicePath . '*.cache');
                foreach ($files as $file) {
                    if (@unlink($file)) {
                        $totalCleared++;
                    }
                }
            }

            return $this->asJson([
                'success' => true,
                'message' => Craft::t('smart-links', 'Cleared {count} cache entries.', ['count' => $totalCleared])
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear all analytics data
     *
     * @return Response
     */
    public function actionClearAllAnalytics(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        // Require admin permission for deleting analytics data
        if (!Craft::$app->getUser()->getIsAdmin()) {
            return $this->asJson([
                'success' => false,
                'error' => Craft::t('smart-links', 'Only administrators can clear analytics data.')
            ]);
        }

        try {
            // Get count before deleting
            $count = (new \craft\db\Query())
                ->from('{{%smartlinks_analytics}}')
                ->count();

            // Delete all analytics records
            Craft::$app->db->createCommand()
                ->delete('{{%smartlinks_analytics}}')
                ->execute();

            // Reset click counts in metadata on all smart links
            $smartLinks = SmartLink::find()->all();
            foreach ($smartLinks as $smartLink) {
                $metadata = $smartLink->metadata ?? [];
                $metadata['clicks'] = 0;
                $metadata['lastClick'] = null;
                Craft::$app->db->createCommand()
                    ->update('{{%smartlinks}}', [
                        'metadata' => Json::encode($metadata)
                    ], ['id' => $smartLink->id])
                    ->execute();
            }

            return $this->asJson([
                'success' => true,
                'message' => Craft::t('smart-links', 'Cleared {count} analytics records and reset all click counts.', ['count' => $count])
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clean up invalid platform values in analytics data
     *
     * @return Response
     */
    public function actionCleanupPlatformValues(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        // Require admin permission
        if (!Craft::$app->getUser()->getIsAdmin()) {
            return $this->asJson([
                'success' => false,
                'error' => Craft::t('smart-links', 'Only administrators can clean up analytics data.')
            ]);
        }

        try {
            $updated = SmartLinks::$plugin->analytics->cleanupPlatformValues();

            return $this->asJson([
                'success' => true,
                'message' => Craft::t('smart-links', 'Cleaned up {count} analytics records with invalid platform values.', ['count' => $updated])
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}