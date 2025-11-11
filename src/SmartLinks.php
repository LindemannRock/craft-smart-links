<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * Intelligent device detection and app store routing
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks;

use Craft;
use craft\base\Plugin;
use craft\base\Model;
use craft\events\RegisterCacheOptionsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\fields\Link as LinkField;
use craft\helpers\FileHelper;
use craft\services\Dashboard;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\services\Utilities;
use craft\utilities\ClearCaches;
use craft\web\UrlManager;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;
use lindemannrock\smartlinks\elements\SmartLink;
use lindemannrock\smartlinks\fields\SmartLinkField;
use lindemannrock\smartlinks\integrations\SmartLinkType;
use lindemannrock\smartlinks\jobs\CleanupAnalyticsJob;
use lindemannrock\smartlinks\models\Settings;
use lindemannrock\smartlinks\utilities\SmartLinksUtility;
use lindemannrock\smartlinks\services\AnalyticsService;
use lindemannrock\smartlinks\services\DeviceDetectionService;
use lindemannrock\smartlinks\services\IntegrationService;
use lindemannrock\smartlinks\services\QrCodeService;
use lindemannrock\smartlinks\services\SmartLinksService;
use lindemannrock\smartlinks\variables\SmartLinksVariable;
use lindemannrock\smartlinks\widgets\AnalyticsSummaryWidget;
use lindemannrock\smartlinks\widgets\TopLinksWidget;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\logginglibrary\LoggingLibrary;
use yii\base\Event;

/**
 * Smart Links Plugin
 *
 * @author    LindemannRock
 * @package   SmartLinks
 * @since     1.0.0
 *
 * @property-read SmartLinksService $smartLinks
 * @property-read DeviceDetectionService $deviceDetection
 * @property-read QrCodeService $qrCode
 * @property-read AnalyticsService $analytics
 * @property-read IntegrationService $integration
 * @property-read Settings $settings
 * @method Settings getSettings()
 */
class SmartLinks extends Plugin
{
    use LoggingTrait;

    /**
     * @var SmartLinks|null
     */
    public static ?SmartLinks $plugin = null;

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * @var bool
     */
    public bool $hasCpSection = true;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        // Configure logging
        $settings = $this->getSettings();
        LoggingLibrary::configure([
            'pluginHandle' => $this->handle,
            'pluginName' => $settings->getDisplayName(),
            'logLevel' => $settings->logLevel ?? 'error',
            'itemsPerPage' => $settings->itemsPerPage ?? 50,
            'permissions' => ['smartLinks:viewLogs'],
        ]);

        // Set plugin name from config if available
        $configPath = \Craft::$app->getPath()->getConfigPath() . '/smart-links.php';
        if (file_exists($configPath)) {
            $rawConfig = require $configPath;
            if (isset($rawConfig['pluginName'])) {
                $this->name = $rawConfig['pluginName'];
            }
        }

        // Register services
        $this->setComponents([
            'smartLinks' => SmartLinksService::class,
            'deviceDetection' => DeviceDetectionService::class,
            'qrCode' => QrCodeService::class,
            'analytics' => AnalyticsService::class,
            'integration' => IntegrationService::class,
        ]);

        // Schedule analytics cleanup if retention is enabled
        $this->scheduleAnalyticsCleanup();

        // Register project config event handlers
        $this->registerProjectConfigEventHandlers();

        // Register translations
        Craft::$app->i18n->translations['smart-links'] = [
            'class' => \craft\i18n\PhpMessageSource::class,
            'sourceLanguage' => 'en',
            'basePath' => __DIR__ . '/translations',
            'forceTranslation' => true,
            'allowOverrides' => true,
        ];

        // Register Twig extension for plugin name helpers
        Craft::$app->view->registerTwigExtension(new \lindemannrock\smartlinks\twigextensions\PluginNameExtension());

        // Register template roots
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $event) {
                $event->roots['smart-links'] = __DIR__ . '/templates';
            }
        );

        // Register element type
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SmartLink::class;
            }
        );

        // Register field type
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SmartLinkField::class;
            }
        );

        // Register Link field integration
        Event::on(
            LinkField::class,
            LinkField::EVENT_REGISTER_LINK_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SmartLinkType::class;
            }
        );

        // Register CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, $this->getCpUrlRules());
            }
        );

        // Register site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, $this->getSiteUrlRules());
            }
        );

        // Register variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('smartLinks', SmartLinksVariable::class);
            }
        );

        // Register permissions
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => Craft::t('smart-links', 'Smart Links'),
                    'permissions' => $this->getPluginPermissions(),
                ];
            }
        );

        // Register utilities
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITIES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SmartLinksUtility::class;
            }
        );

        // Register dashboard widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = AnalyticsSummaryWidget::class;
                $event->types[] = TopLinksWidget::class;
            }
        );

        // Register cache clearing options
        Event::on(
            ClearCaches::class,
            ClearCaches::EVENT_REGISTER_CACHE_OPTIONS,
            function(RegisterCacheOptionsEvent $event) {
                $settings = $this->getSettings();
                $pluginName = $settings->getFullName();

                $event->options[] = [
                    'key' => 'smart-links-cache',
                    'label' => Craft::t('smart-links', '{pluginName} Cache', ['pluginName' => $pluginName]),
                    'action' => function() {
                        $cleared = 0;

                        // Clear QR code caches
                        $qrPath = Craft::$app->path->getRuntimePath() . '/smart-links/cache/qr/';
                        if (is_dir($qrPath)) {
                            $files = glob($qrPath . '*.cache');
                            foreach ($files as $file) {
                                if (@unlink($file)) {
                                    $cleared++;
                                }
                            }
                        }

                        // Clear device detection caches
                        $devicePath = Craft::$app->path->getRuntimePath() . '/smart-links/cache/device/';
                        if (is_dir($devicePath)) {
                            $files = glob($devicePath . '*.cache');
                            foreach ($files as $file) {
                                if (@unlink($file)) {
                                    $cleared++;
                                }
                            }
                        }

                        Craft::info('Cleared ' . $this->getSettings()->getFullName() . ' cache entries', __METHOD__, ['count' => $cleared]);
                    },
                ];
            }
        );

        // Listen for settings changes to reschedule cleanup
        Event::on(
            Settings::class,
            Settings::EVENT_AFTER_SAVE_SETTINGS,
            function(Event $event) {
                /** @var Settings $settings */
                $settings = $event->sender;

                // The cleanup job will check the current settings when it runs
                // No need to clear existing jobs as they will adapt to new settings

                // If retention was just enabled (from 0), schedule a new cleanup
                if ($settings->analyticsRetention > 0) {
                    // Check if we need to schedule a new cleanup
                    // The job will handle re-queuing itself after each run
                    $this->scheduleAnalyticsCleanup();
                }

                Craft::info(
                    Craft::t('smart-links', 'Analytics cleanup settings updated'),
                    __METHOD__
                );
            }
        );

        // DO NOT log in init() - it's called on every request
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        // Check if Smart Links is enabled for the current site
        $currentSite = Craft::$app->getSites()->getCurrentSite();
        $settings = $this->getSettings();

        if (!$settings->isSiteEnabled($currentSite->id)) {
            return null; // Hide navigation item entirely
        }

        $item = parent::getCpNavItem();

        if ($item) {
            $item['label'] = $this->getSettings()->getFullName();

            // Use Craft's built-in link icon
            $item['icon'] = '@appicons/link.svg';

            $item['subnav'] = [
                'links' => [
                    'label' => 'Links',
                    'url' => 'smart-links',
                ],
            ];

            if (Craft::$app->getUser()->checkPermission('smartLinks:viewAnalytics') && $this->getSettings()->enableAnalytics) {
                $item['subnav']['analytics'] = [
                    'label' => Craft::t('smart-links', 'Analytics'),
                    'url' => 'smart-links/analytics',
                ];
            }

            // Add logs section using the logging library
            if (Craft::$app->getPlugins()->isPluginInstalled('logging-library') &&
                Craft::$app->getPlugins()->isPluginEnabled('logging-library')) {
                $item = LoggingLibrary::addLogsNav($item, $this->handle, [
                    'smartLinks:viewLogs'
                ]);
            }

            if (Craft::$app->getUser()->checkPermission('smartLinks:manageSettings')) {
                $item['subnav']['settings'] = [
                    'label' => Craft::t('smart-links', 'Settings'),
                    'url' => 'smart-links/settings',
                ];
            }
        }

        return $item;
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        // Always load fresh settings from database
        $settings = Settings::loadFromDatabase();

        // If no settings found in database, return new instance with defaults
        if (!$settings) {
            $settings = new Settings();
        }

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function getSettings(): ?Model
    {
        $settings = parent::getSettings();

        if ($settings) {
            // Load config file settings and merge with database values
            $configPath = \Craft::$app->getPath()->getConfigPath() . '/smart-links.php';
            if (file_exists($configPath)) {
                $config = require $configPath;

                // Apply environment-specific overrides
                $env = \Craft::$app->getConfig()->env;
                if ($env && isset($config[$env])) {
                    $config = array_merge($config, $config[$env]);
                }

                // Apply wildcard overrides
                if (isset($config['*'])) {
                    $config = array_merge($config, $config['*']);
                }

                // Remove environment-specific keys
                unset($config['*'], $config['dev'], $config['staging'], $config['production']);

                // Set config values (these override database values)
                foreach ($config as $key => $value) {
                    if (property_exists($settings, $key)) {
                        $settings->$key = $value;
                    }
                }
            }
        }

        return $settings;
    }

    /**
     * Get sites where Smart Links is enabled
     *
     * @return array
     */
    public function getEnabledSites(): array
    {
        $settings = $this->getSettings();
        $enabledSiteIds = $settings->getEnabledSiteIds();


        // Return only enabled sites
        return array_filter(Craft::$app->getSites()->getAllSites(), function($site) use ($enabledSiteIds) {
            return in_array($site->id, $enabledSiteIds);
        });
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse(): mixed
    {
        return Craft::$app->controller->redirect('smart-links/settings');
    }

    /**
     * Get CP URL rules
     */
    private function getCpUrlRules(): array
    {
        return [
            // Smart Links routes
            'smart-links' => ['template' => 'smart-links/smartlinks/index'],
            'smart-links/smartlinks' => ['template' => 'smart-links/smartlinks/index'],
            'smart-links/new' => 'smart-links/smart-links/edit',
            'smart-links/smartlinks/new' => 'smart-links/smart-links/edit',
            'smart-links/<smartLinkId:\d+>' => 'smart-links/smart-links/edit',
            'smart-links/smartlinks/<smartLinkId:\d+>' => 'smart-links/smart-links/edit',
            'smart-links/analytics' => 'smart-links/analytics/index',
            'smart-links/analytics/<linkId:\d+>' => 'smart-links/analytics/link',
            'smart-links/settings' => 'smart-links/settings/index',
            'smart-links/settings/general' => 'smart-links/settings/general',
            'smart-links/settings/analytics' => 'smart-links/settings/analytics',
            'smart-links/settings/integrations' => 'smart-links/settings/integrations',
            'smart-links/settings/export' => 'smart-links/settings/export',
            'smart-links/settings/qr-code' => 'smart-links/settings/qr-code',
            'smart-links/settings/behavior' => 'smart-links/settings/behavior',
            'smart-links/settings/interface' => 'smart-links/settings/interface',
            'smart-links/settings/cache' => 'smart-links/settings/cache',
            'smart-links/settings/field-layout' => 'smart-links/settings/field-layout',
            'smart-links/settings/save' => 'smart-links/settings/save',
            'smart-links/settings/save-field-layout' => 'smart-links/settings/save-field-layout',
            'smart-links/settings/cleanup-analytics' => 'smart-links/settings/cleanup-analytics',
            // QR Code generation for preview
            'smart-links/qr-code/generate' => 'smart-links/qr-code/generate',
            // Logging routes
            'smart-links/logs' => 'logging-library/logs/index',
            'smart-links/logs/download' => 'logging-library/logs/download',
        ];
    }

    /**
     * Get site URL rules
     */
    private function getSiteUrlRules(): array
    {
        $settings = $this->getSettings();
        $slugPrefix = $settings->slugPrefix ?? 'go';
        $qrPrefix = $settings->qrPrefix ?? 'qr';

        return [
            $slugPrefix . '/<slug:[a-zA-Z0-9\-\_]+>' => 'smart-links/redirect/index',
            $qrPrefix . '/<slug:[a-zA-Z0-9\-\_]+>' => 'smart-links/qr-code/generate',
            $qrPrefix . '/<slug:[a-zA-Z0-9\-\_]+>/view' => 'smart-links/qr-code/display',
            'smart-links/qr-code/generate' => 'smart-links/qr-code/generate',
        ];
    }

    /**
     * Get plugin permissions
     */
    private function getPluginPermissions(): array
    {
        return [
            'smartLinks:viewLinks' => [
                'label' => Craft::t('smart-links', 'View smart links'),
            ],
            'smartLinks:createLinks' => [
                'label' => Craft::t('smart-links', 'Create smart links'),
            ],
            'smartLinks:editLinks' => [
                'label' => Craft::t('smart-links', 'Edit smart links'),
            ],
            'smartLinks:deleteLinks' => [
                'label' => Craft::t('smart-links', 'Delete smart links'),
            ],
            'smartLinks:viewAnalytics' => [
                'label' => Craft::t('smart-links', 'View analytics'),
            ],
            'smartLinks:viewLogs' => [
                'label' => Craft::t('smart-links', 'View logs'),
            ],
            'smartLinks:manageSettings' => [
                'label' => Craft::t('smart-links', 'Manage settings'),
            ],
        ];
    }

    /**
     * Schedule analytics cleanup job
     *
     * @return void
     */
    private function scheduleAnalyticsCleanup(): void
    {
        $settings = $this->getSettings();

        // Only schedule cleanup if analytics is enabled and retention is set
        if ($settings->enableAnalytics && $settings->analyticsRetention > 0) {
            // Check if a cleanup job is already scheduled (within next 24 hours)
            $existingJob = (new \craft\db\Query())
                ->from('{{%queue}}')
                ->where(['like', 'job', 'smartlinks'])
                ->andWhere(['like', 'job', 'CleanupAnalyticsJob'])
                ->andWhere(['<=', 'timePushed', time() + 86400]) // Within next 24 hours
                ->exists();

            if (!$existingJob) {
                // Create cleanup job
                $job = new CleanupAnalyticsJob([
                    'reschedule' => true,
                ]);

                // Add to queue with a small initial delay
                // The job will re-queue itself to run every 24 hours
                Craft::$app->queue->delay(5 * 60)->push($job);

                $this->logInfo('Scheduled initial analytics cleanup job', ['interval' => '24 hours']);
            }
        }
    }

    /**
     * Register project config event handlers
     *
     * @return void
     */
    private function registerProjectConfigEventHandlers(): void
    {
        // Listen for project config changes to field layouts
        Craft::$app->getProjectConfig()
            ->onAdd('smart-links.fieldLayouts.{uid}', [$this, 'handleChangedFieldLayout'])
            ->onUpdate('smart-links.fieldLayouts.{uid}', [$this, 'handleChangedFieldLayout'])
            ->onRemove('smart-links.fieldLayouts.{uid}', [$this, 'handleDeletedFieldLayout']);
    }

    /**
     * Handle field layout changes from project config
     *
     * @param \craft\events\ConfigEvent $event
     * @return void
     */
    public function handleChangedFieldLayout(\craft\events\ConfigEvent $event): void
    {
        // Rebuild field layout from config
        $uid = $event->tokenMatches[0];
        $data = $event->newValue;

        $fieldLayout = \craft\models\FieldLayout::createFromConfig($data);
        $fieldLayout->uid = $uid;
        $fieldLayout->type = \lindemannrock\smartlinks\elements\SmartLink::class;

        Craft::$app->getFields()->saveLayout($fieldLayout, false);

        $this->logInfo('Applied Smart Links field layout from project config', ['uid' => $uid]);
    }

    /**
     * Handle field layout deletion from project config
     *
     * @param \craft\events\ConfigEvent $event
     * @return void
     */
    public function handleDeletedFieldLayout(\craft\events\ConfigEvent $event): void
    {
        $uid = $event->tokenMatches[0];
        $fieldLayout = Craft::$app->getFields()->getLayoutByUid($uid);

        if ($fieldLayout) {
            Craft::$app->getFields()->deleteLayoutById($fieldLayout->id);
        }
    }
}