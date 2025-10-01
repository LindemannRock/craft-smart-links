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
     * @var bool
     */
    private bool $readOnly;

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        // View actions allowed without allowAdminChanges
        $viewActions = ['index', 'general', 'analytics', 'export', 'qr-code', 'redirect', 'interface', 'advanced', 'field-layout', 'debug'];

        if (in_array($action->id, $viewActions)) {
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
        Craft::info('Field Layout ID: ' . ($fieldLayout->id ?? 'null'), 'smart-links');
        Craft::info('Field Layout UID: ' . ($fieldLayout->uid ?? 'null'), 'smart-links');
        Craft::info('Field Layout Type: ' . ($fieldLayout->type ?? 'null'), 'smart-links');
        Craft::info('Field Layout class: ' . get_class($fieldLayout), 'smart-links');

        $variables = [
            'fieldLayout' => $fieldLayout,
            'readOnly' => $this->readOnly,
        ];

        // Debug logging
        Craft::info('actionFieldLayout called - Variables: ' . json_encode([
            'fieldLayout_exists' => $fieldLayout !== null,
            'fieldLayout_id' => $fieldLayout ? $fieldLayout->id : null,
            'readOnly' => $this->readOnly,
        ]), 'smart-links');

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