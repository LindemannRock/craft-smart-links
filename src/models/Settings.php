<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\models;

use Craft;
use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\db\Query;
use craft\helpers\App;
use craft\helpers\Db;
use lindemannrock\logginglibrary\traits\LoggingTrait;

/**
 * Smart Links Settings Model
 */
class Settings extends Model
{
    use LoggingTrait;

    /**
     * @event Event The event that is triggered after settings are saved
     */
    public const EVENT_AFTER_SAVE_SETTINGS = 'afterSaveSettings';

    /**
     * @var string Plugin display name
     */
    public string $pluginName = 'Smart Links';
    
    /**
     * @var bool Enable analytics tracking
     */
    public bool $enableAnalytics = true;

    /**
     * @var int Analytics data retention in days
     */
    public int $analyticsRetention = 90;

    /**
     * @var bool Include disabled smart links in analytics exports
     */
    public bool $includeDisabledInExport = false;

    /**
     * @var bool Include expired smart links in analytics exports
     */
    public bool $includeExpiredInExport = false;

    /**
     * @var bool Anonymize IP addresses before storing (masks last octet for IPv4, last 80 bits for IPv6)
     */
    public bool $anonymizeIpAddress = false;

    /**
     * @var int Default QR code size
     */
    public int $defaultQrSize = 256;

    /**
     * @var string Default QR code color
     */
    public string $defaultQrColor = '#000000';

    /**
     * @var string Default QR code background color
     */
    public string $defaultQrBgColor = '#FFFFFF';

    /**
     * @var string Default QR code format (png or svg)
     */
    public string $defaultQrFormat = 'png';

    /**
     * @var bool Enable QR code caching
     */
    public bool $enableQrCodeCache = true;

    /**
     * @var int QR code cache duration in seconds (24 hours)
     */
    public int $qrCodeCacheDuration = 86400;

    /**
     * @var string Default QR code error correction level (L, M, Q, H)
     */
    public string $defaultQrErrorCorrection = 'M';
    
    /**
     * @var int Default QR code margin/quiet zone (0-10)
     */
    public int $defaultQrMargin = 4;
    
    /**
     * @var string QR code module style (square, rounded, dots)
     */
    public string $qrModuleStyle = 'square';
    
    /**
     * @var string QR code eye style (square, rounded, leaf)
     */
    public string $qrEyeStyle = 'square';
    
    /**
     * @var string|null QR code eye color (null = same as module color)
     */
    public ?string $qrEyeColor = null;
    
    /**
     * @var bool Enable QR code logo overlay
     */
    public bool $enableQrLogo = false;
    
    /**
     * @var string|null Asset volume UID for logo selection (null = all volumes)
     */
    public ?string $qrLogoVolumeUid = null;
    
    /**
     * @var string|null Asset volume UID for smart link image selection (null = all volumes)
     */
    public ?string $imageVolumeUid = null;
    
    /**
     * @var int|null Default QR code logo asset ID
     */
    public ?int $defaultQrLogoId = null;
    
    /**
     * @var int QR code logo size as percentage (10-30)
     */
    public int $qrLogoSize = 20;
    
    /**
     * @var bool Enable QR code downloads
     */
    public bool $enableQrDownload = true;
    
    /**
     * @var string QR code download filename pattern
     */
    public string $qrDownloadFilename = '{slug}-qr-{size}';

    /**
     * @var string|null Custom redirect template path
     */
    public ?string $redirectTemplate = null;

    /**
     * @var string|null Custom QR code display template path
     */
    public ?string $qrTemplate = null;

    /**
     * @var bool Enable geographic detection
     */
    public bool $enableGeoDetection = false;

    /**
     * @var string|null Default country for local development (when IP is private)
     */
    public ?string $defaultCountry = null;

    /**
     * @var string|null Default city for local development (when IP is private)
     */
    public ?string $defaultCity = null;

    /**
     * @var bool Cache device detection results
     */
    public bool $cacheDeviceDetection = true;

    /**
     * @var int Device detection cache duration in seconds (1 hour)
     */
    public int $deviceDetectionCacheDuration = 3600;

    /**
     * @var string Default language detection method (browser, ip, or both)
     */
    public string $languageDetectionMethod = 'browser';

    /**
     * @var int Items per page in element index
     */
    public int $itemsPerPage = 100;

    /**
     * @var string URL prefix for smart links (default: 'go')
     */
    public string $slugPrefix = 'go';

    /**
     * @var string URL prefix for QR codes (default: 'qr')
     */
    public string $qrPrefix = 'qr';


    /**
     * @var string URL to redirect to when smart link is not found (404)
     */
    public string $notFoundRedirectUrl = '/';

    /**
     * @var array Site IDs where Smart Links should be enabled
     */
    public array $enabledSites = [];

    /**
     * @var string Log level (error, warning, info, debug)
     */
    public string $logLevel = 'error';

    /**
     * @var array Enabled integration handles (e.g., ['seomatic'])
     */
    public array $enabledIntegrations = [];

    /**
     * @var array Event types to track in integrations
     */
    public array $seomaticTrackingEvents = ['redirect', 'button_click', 'qr_scan'];

    /**
     * @var array Event types that create redirects in Redirect Manager
     */
    public array $redirectManagerEvents = ['slug-change', 'delete'];

    /**
     * @var string Event prefix for GTM/GA events
     */
    public string $seomaticEventPrefix = 'smart_links';

    /**
     * @var string|null IP hash salt for privacy protection
     */
    public ?string $ipHashSalt = null;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle('smart-links');

        // Fallback to .env if ipHashSalt not set by config file
        if ($this->ipHashSalt === null) {
            $this->ipHashSalt = App::env('SMART_LINKS_IP_SALT');
        }

        // Load default location from .env if not set by config file
        if ($this->defaultCountry === null) {
            $this->defaultCountry = App::env('SMART_LINKS_DEFAULT_COUNTRY');
        }
        if ($this->defaultCity === null) {
            $this->defaultCity = App::env('SMART_LINKS_DEFAULT_CITY');
        }
    }

    /**
     * @inheritdoc
     */
    protected function defineBehaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => [
                    'redirectTemplate',
                    'qrTemplate',
                    'imageVolumeUid',
                    'qrLogoVolumeUid',
                    'ipHashSalt',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        return [
            [['pluginName', 'slugPrefix', 'qrPrefix'], 'required'],
            [['pluginName'], 'string', 'max' => 255],
            [['slugPrefix', 'qrPrefix'], 'string', 'max' => 50],
            [['slugPrefix'], 'match', 'pattern' => '/^[a-zA-Z0-9\-\_]+$/', 'message' => Craft::t('smart-links', 'Only letters, numbers, hyphens, and underscores are allowed.')],
            [['qrPrefix'], 'match', 'pattern' => '/^[a-zA-Z0-9\-\_\/]+$/', 'message' => Craft::t('smart-links', 'Only letters, numbers, hyphens, underscores, and slashes are allowed.')],
            [['slugPrefix'], 'validateSlugPrefix'],
            [['qrPrefix'], 'validateQrPrefix'],
            [['enableAnalytics', 'enableGeoDetection', 'cacheDeviceDetection', 'includeDisabledInExport', 'includeExpiredInExport', 'anonymizeIpAddress'], 'boolean'],
            [['analyticsRetention', 'defaultQrSize', 'qrCodeCacheDuration', 'deviceDetectionCacheDuration', 'itemsPerPage'], 'integer'],
            [['analyticsRetention'], 'integer', 'min' => 0, 'max' => 3650], // 0 for unlimited, up to 10 years
            [['defaultQrSize'], 'integer', 'min' => 100, 'max' => 1000],
            [['itemsPerPage'], 'integer', 'min' => 10, 'max' => 500],
            [['defaultQrColor', 'defaultQrBgColor', 'qrEyeColor'], 'string'],
            [['defaultQrColor', 'defaultQrBgColor'], 'match', 'pattern' => '/^#[0-9A-F]{6}$/i'],
            [['qrEyeColor'], 'match', 'pattern' => '/^#[0-9A-F]{6}$/i', 'skipOnEmpty' => true],
            [['defaultQrFormat'], 'in', 'range' => ['png', 'svg']],
            [['defaultQrErrorCorrection'], 'in', 'range' => ['L', 'M', 'Q', 'H']],
            [['qrModuleStyle'], 'in', 'range' => ['square', 'rounded', 'dots']],
            [['qrEyeStyle'], 'in', 'range' => ['square', 'rounded', 'leaf']],
            [['qrLogoSize'], 'integer', 'min' => 10, 'max' => 30],
            [['defaultQrMargin'], 'integer', 'min' => 0, 'max' => 10],
            [['qrDownloadFilename'], 'string'],
            [['enableQrLogo', 'enableQrDownload'], 'boolean'],
            [['qrLogoVolumeUid', 'imageVolumeUid'], 'string'],
            [['defaultQrLogoId'], 'integer'],
            // Require default logo when logo overlay is enabled
            [['defaultQrLogoId'], 'required', 'when' => function($model) {
                return $model->enableQrLogo;
            }, 'message' => Craft::t('smart-links', 'Default logo is required when logo overlay is enabled.')],
            [['redirectTemplate', 'qrTemplate', 'notFoundRedirectUrl'], 'string'],
            [['languageDetectionMethod'], 'in', 'range' => ['browser', 'ip', 'both']],
            [['enabledSites'], 'each', 'rule' => ['integer']],
            [['logLevel'], 'in', 'range' => ['debug', 'info', 'warning', 'error']],
            [['logLevel'], 'validateLogLevel'],
            [['enabledIntegrations', 'seomaticTrackingEvents'], 'each', 'rule' => ['string']],
            [['seomaticEventPrefix'], 'string', 'max' => 50],
            [['seomaticEventPrefix'], 'match', 'pattern' => '/^[a-z0-9\_]+$/', 'message' => Craft::t('smart-links', 'Only lowercase letters, numbers, and underscores are allowed.')],
        ];
    }

    /**
     * Set enabled integrations from string (for form submission)
     *
     * @param string|array $value
     */
    public function setEnabledIntegrations($value): void
    {
        if (is_string($value)) {
            // If empty string, set to empty array
            if (trim($value) === '') {
                $this->enabledIntegrations = [];
            } else {
                // Single integration handle as string, convert to array
                $this->enabledIntegrations = [$value];
            }
        } elseif (is_array($value)) {
            $this->enabledIntegrations = $value;
        } else {
            $this->enabledIntegrations = [];
        }
    }

    /**
     * Validate log level - debug requires devMode
     */
    public function validateLogLevel($attribute, $params, $validator)
    {
        $logLevel = $this->$attribute;

        // Reset session warning when devMode is true - allows warning to show again if devMode changes
        if (Craft::$app->getConfig()->getGeneral()->devMode && !Craft::$app->getRequest()->getIsConsoleRequest()) {
            Craft::$app->getSession()->remove('sl_debug_config_warning');
        }

        // Debug level is only allowed when devMode is enabled
        if ($logLevel === 'debug' && !Craft::$app->getConfig()->getGeneral()->devMode) {
            $this->$attribute = 'info';

            if ($this->isOverriddenByConfig('logLevel')) {
                if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
                    if (Craft::$app->getSession()->get('sl_debug_config_warning') === null) {
                        $this->logWarning('Log level "debug" from config file changed to "info" because devMode is disabled', [
                            'configFile' => 'config/smart-links.php',
                        ]);
                        Craft::$app->getSession()->set('sl_debug_config_warning', true);
                    }
                } else {
                    $this->logWarning('Log level "debug" from config file changed to "info" because devMode is disabled', [
                        'configFile' => 'config/smart-links.php',
                    ]);
                }
            } else {
                $this->logWarning('Log level automatically changed from "debug" to "info" because devMode is disabled');
                $this->saveToDatabase();
            }
        }
    }

    /**
     * Validate slug prefix to prevent conflicts
     */
    public function validateSlugPrefix($attribute, $params, $validator)
    {
        $slugPrefix = $this->$attribute;

        if (empty($slugPrefix)) {
            return;
        }

        $conflicts = [];

        // Check against ShortLink Manager if installed
        if (Craft::$app->plugins->isPluginInstalled('shortlink-manager')) {
            try {
                $shortlinkPlugin = Craft::$app->plugins->getPlugin('shortlink-manager');
                if ($shortlinkPlugin) {
                    $shortlinkSettings = $shortlinkPlugin->getSettings();
                    $shortlinkPluginName = $shortlinkSettings->pluginName ?? 'ShortLink Manager';

                    // Check against ShortLink Manager slugPrefix
                    /** @phpstan-ignore-next-line - Dynamic property access on plugin settings */
                    $shortlinkSlugPrefix = property_exists($shortlinkSettings, 'slugPrefix') ? $shortlinkSettings->slugPrefix : 's';
                    if ($slugPrefix === $shortlinkSlugPrefix) {
                        $conflicts[] = "{$shortlinkPluginName} slug prefix ('{$shortlinkSlugPrefix}')";
                    }

                    // Check against ShortLink Manager qrPrefix
                    /** @phpstan-ignore-next-line - Dynamic property access on plugin settings */
                    $shortlinkQrPrefix = property_exists($shortlinkSettings, 'qrPrefix') ? $shortlinkSettings->qrPrefix : 'qr';
                    if ($slugPrefix === $shortlinkQrPrefix) {
                        $conflicts[] = "{$shortlinkPluginName} QR prefix ('{$shortlinkQrPrefix}')";
                    }
                }
            } catch (\Exception $e) {
                // Silently continue if we can't check shortlink-manager
            }
        }

        if (!empty($conflicts)) {
            $suggestions = ['go', 'link', 'links', 'l'];
            $this->addError($attribute, Craft::t('smart-links', 'Slug prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}', [
                'prefix' => $slugPrefix,
                'conflicts' => implode(', ', $conflicts),
                'suggestions' => implode(', ', $suggestions),
            ]));
        }
    }

    /**
     * Validate QR prefix to prevent conflicts
     */
    public function validateQrPrefix($attribute, $params, $validator)
    {
        $qrPrefix = $this->$attribute;

        if (empty($qrPrefix)) {
            return;
        }

        $conflicts = [];

        // Parse the prefix (supports both "qr" and "go/qr" patterns)
        $segments = explode('/', $qrPrefix);
        $isNested = count($segments) > 1;

        // Check against own slugPrefix
        if (!$isNested && $qrPrefix === $this->slugPrefix) {
            $this->addError($attribute, Craft::t('smart-links', 'QR prefix cannot be the same as your slug prefix. Try: qr, code, qrc, or {slug}/qr', [
                'slug' => $this->slugPrefix,
            ]));
            return;
        }

        // Check if nested pattern conflicts with own slugPrefix
        if ($isNested) {
            $baseSegment = $segments[0];
            if ($baseSegment !== $this->slugPrefix) {
                $this->addError($attribute, Craft::t('smart-links', 'Nested QR prefix must start with your slug prefix "{slug}". Use: {slug}/{qr} or use standalone like "qr"', [
                    'slug' => $this->slugPrefix,
                    'qr' => $segments[1] ?? 'qr',
                ]));
                return;
            }
        }

        // Check against ShortLink Manager if installed
        if (Craft::$app->plugins->isPluginInstalled('shortlink-manager')) {
            try {
                $shortlinkPlugin = Craft::$app->plugins->getPlugin('shortlink-manager');
                if ($shortlinkPlugin) {
                    $shortlinkSettings = $shortlinkPlugin->getSettings();
                    $shortlinkPluginName = $shortlinkSettings->pluginName ?? 'ShortLink Manager';

                    // Only check standalone patterns (nested patterns are already validated above)
                    if (!$isNested) {
                        // Check against ShortLink Manager slugPrefix
                        /** @phpstan-ignore-next-line - Dynamic property access on plugin settings */
                        $shortlinkSlugPrefix = property_exists($shortlinkSettings, 'slugPrefix') ? $shortlinkSettings->slugPrefix : 's';
                        if ($qrPrefix === $shortlinkSlugPrefix) {
                            $conflicts[] = "{$shortlinkPluginName} slug prefix ('{$shortlinkSlugPrefix}')";
                        }

                        // Check against ShortLink Manager qrPrefix
                        /** @phpstan-ignore-next-line - Dynamic property access on plugin settings */
                        $shortlinkQrPrefix = property_exists($shortlinkSettings, 'qrPrefix') ? $shortlinkSettings->qrPrefix : 'qr';
                        if ($qrPrefix === $shortlinkQrPrefix) {
                            $conflicts[] = "{$shortlinkPluginName} QR prefix ('{$shortlinkQrPrefix}')";
                        }
                    }
                }
            } catch (\Exception $e) {
                // Silently continue if we can't check shortlink-manager
            }
        }

        if (!empty($conflicts)) {
            $suggestions = ['qr', 'qrc', 'code', $this->slugPrefix . '/qr'];
            $this->addError($attribute, Craft::t('smart-links', 'QR prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}', [
                'prefix' => $qrPrefix,
                'conflicts' => implode(', ', $conflicts),
                'suggestions' => implode(', ', $suggestions),
            ]));
        }
    }

    /**
     * Load settings from database
     *
     * @param Settings|null $settings Optional existing settings instance
     * @return self
     */
    public static function loadFromDatabase(?Settings $settings = null): self
    {
        if ($settings === null) {
            $settings = new self();
        }
        
        // Load from database
        try {
            $row = (new Query())
                ->from('{{%smartlinks_settings}}')
                ->where(['id' => 1])
                ->one();
        } catch (\Exception $e) {
            $settings->logError('Failed to load settings from database', ['error' => $e->getMessage()]);
            return $settings;
        }
        
        if ($row) {
            // Remove system fields that aren't attributes
            unset($row['id'], $row['dateCreated'], $row['dateUpdated'], $row['uid']);
            
            // Convert numeric boolean values to actual booleans
            $booleanFields = [
                'enableAnalytics',
                'includeDisabledInExport',
                'includeExpiredInExport',
                'enableGeoDetection',
                'cacheDeviceDetection',
                'enableQrLogo',
                'enableQrDownload',
            ];
            
            foreach ($booleanFields as $field) {
                if (isset($row[$field])) {
                    $row[$field] = (bool) $row[$field];
                }
            }
            
            // Convert numeric values to integers
            $integerFields = [
                'analyticsRetention',
                'defaultQrSize',
                'qrCodeCacheDuration',
                'deviceDetectionCacheDuration',
                'itemsPerPage',
                'defaultQrMargin',
                'qrLogoSize',
            ];

            foreach ($integerFields as $field) {
                if (isset($row[$field])) {
                    $row[$field] = (int) $row[$field];
                }
            }

            // Handle array fields (JSON serialization)
            if (isset($row['enabledSites'])) {
                $row['enabledSites'] = !empty($row['enabledSites']) ? json_decode($row['enabledSites'], true) : [];
            }
            if (isset($row['enabledIntegrations'])) {
                $row['enabledIntegrations'] = !empty($row['enabledIntegrations']) ? json_decode($row['enabledIntegrations'], true) : [];
            }
            if (isset($row['seomaticTrackingEvents'])) {
                $row['seomaticTrackingEvents'] = !empty($row['seomaticTrackingEvents']) ? json_decode($row['seomaticTrackingEvents'], true) : [];
            }
            if (isset($row['redirectManagerEvents'])) {
                $row['redirectManagerEvents'] = !empty($row['redirectManagerEvents']) ? json_decode($row['redirectManagerEvents'], true) : [];
            }

            // Set attributes from database
            $settings->setAttributes($row, false);
        } else {
            $settings->logWarning('No settings found in database');
        }
        
        return $settings;
    }

    /**
     * Save settings to database
     *
     * @return bool
     */
    public function saveToDatabase(): bool
    {
        if (!$this->validate()) {
            $this->logError('Settings validation failed', ['errors' => $this->getErrors()]);
            return false;
        }

        $db = Craft::$app->getDb();
        $attributes = $this->getAttributes();

        // Exclude config-only attributes that shouldn't be saved to database
        unset($attributes['ipHashSalt'], $attributes['defaultCountry'], $attributes['defaultCity']); // These come from .env/config, not database

        // Debug: Log what we're trying to save
        $this->logDebug('Attempting to save settings', ['attributes' => $attributes]);

        // Handle array serialization
        if (isset($attributes['enabledSites'])) {
            $attributes['enabledSites'] = json_encode($attributes['enabledSites']);
        }
        if (isset($attributes['enabledIntegrations'])) {
            $attributes['enabledIntegrations'] = json_encode($attributes['enabledIntegrations']);
        }
        if (isset($attributes['seomaticTrackingEvents'])) {
            $attributes['seomaticTrackingEvents'] = json_encode($attributes['seomaticTrackingEvents']);
        }
        if (isset($attributes['redirectManagerEvents'])) {
            $attributes['redirectManagerEvents'] = json_encode($attributes['redirectManagerEvents']);
        }

        // Add/update timestamps
        $now = Db::prepareDateForDb(new \DateTime());
        $attributes['dateUpdated'] = $now;
        
        // Update existing settings (we know there's always one row from migration)
        try {
            $result = $db->createCommand()
                ->update('{{%smartlinks_settings}}', $attributes, ['id' => 1])
                ->execute();

            // Debug: Log the result
            $this->logDebug('Database update result', ['result' => $result]);

            // Trigger event after successful save
            $this->trigger(self::EVENT_AFTER_SAVE_SETTINGS);
            $this->logInfo('Settings saved successfully to database');
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to save ' . $this->getFullName() . ' settings', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if a setting is overridden by config file
     * Supports dot notation for nested settings like: enabledIntegrations.0
     *
     * @param string $attribute The setting attribute name or dot-notation path
     * @return bool
     */
    public function isOverriddenByConfig(string $attribute): bool
    {
        $configPath = \Craft::$app->getPath()->getConfigPath() . '/smart-links.php';

        if (!file_exists($configPath)) {
            return false;
        }

        // Load the raw config file instead of using Craft's config which merges with database
        $rawConfig = require $configPath;

        // Handle dot notation for nested config
        if (str_contains($attribute, '.')) {
            $parts = explode('.', $attribute);
            $current = $rawConfig;

            foreach ($parts as $part) {
                if (!is_array($current) || !array_key_exists($part, $current)) {
                    return false;
                }
                $current = $current[$part];
            }

            return true;
        }

        // Check for the attribute in the config
        // Use array_key_exists instead of isset to detect null values
        if (array_key_exists($attribute, $rawConfig)) {
            return true;
        }

        // Check environment-specific configs
        $env = \Craft::$app->getConfig()->env;
        if ($env && is_array($rawConfig[$env] ?? null) && array_key_exists($attribute, $rawConfig[$env])) {
            return true;
        }

        // Check wildcard config
        if (is_array($rawConfig['*'] ?? null) && array_key_exists($attribute, $rawConfig['*'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if a site is enabled for Smart Links
     *
     * @param int $siteId
     * @return bool
     */
    public function isSiteEnabled(int $siteId): bool
    {
        // If no sites are specifically enabled, assume all sites are enabled (backwards compatibility)
        if (empty($this->enabledSites)) {
            return true;
        }

        return in_array($siteId, $this->enabledSites);
    }

    /**
     * Get enabled site IDs, defaulting to all sites if none specified
     *
     * @return array
     */
    public function getEnabledSiteIds(): array
    {
        if (empty($this->enabledSites)) {
            // Return all site IDs if none specifically enabled
            return array_map(function($site) {
                return $site->id;
            }, Craft::$app->getSites()->getAllSites());
        }

        return $this->enabledSites;
    }

    /**
     * Get attribute labels
     *
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'pluginName' => Craft::t('smart-links', 'Plugin Name'),
            'slugPrefix' => Craft::t('smart-links', 'Smart Link URL Prefix'),
            'qrPrefix' => Craft::t('smart-links', 'QR Code URL Prefix'),
            'enableAnalytics' => Craft::t('smart-links', 'Enable Analytics'),
            'analyticsRetention' => Craft::t('smart-links', 'Analytics Retention (days)'),
            'includeDisabledInExport' => Craft::t('smart-links', 'Include Disabled Links in Export'),
            'includeExpiredInExport' => Craft::t('smart-links', 'Include Expired Links in Export'),
            'defaultQrSize' => Craft::t('smart-links', 'Default QR Code Size'),
            'defaultQrColor' => Craft::t('smart-links', 'Default QR Code Color'),
            'defaultQrBgColor' => Craft::t('smart-links', 'Default QR Background Color'),
            'defaultQrFormat' => Craft::t('smart-links', 'Default QR Code Format'),
            'qrCodeCacheDuration' => Craft::t('smart-links', 'QR Code Cache Duration (seconds)'),
            'defaultQrErrorCorrection' => Craft::t('smart-links', 'Error Correction Level'),
            'defaultQrMargin' => Craft::t('smart-links', 'QR Code Margin'),
            'qrModuleStyle' => Craft::t('smart-links', 'Module Style'),
            'qrEyeStyle' => Craft::t('smart-links', 'Eye Style'),
            'qrEyeColor' => Craft::t('smart-links', 'Eye Color'),
            'enableQrLogo' => Craft::t('smart-links', 'Enable QR Code Logo'),
            'qrLogoVolumeUid' => Craft::t('smart-links', 'Logo Volume'),
            'imageVolumeUid' => Craft::t('smart-links', 'Image Volume'),
            'defaultQrLogoId' => Craft::t('smart-links', 'Default Logo'),
            'qrLogoSize' => Craft::t('smart-links', 'Logo Size (%)'),
            'enableQrDownload' => Craft::t('smart-links', 'Enable QR Code Downloads'),
            'qrDownloadFilename' => Craft::t('smart-links', 'Download Filename Pattern'),
            'redirectTemplate' => Craft::t('smart-links', 'Custom Redirect Template'),
            'qrTemplate' => Craft::t('smart-links', 'Custom QR Code Template'),
            'enableGeoDetection' => Craft::t('smart-links', 'Enable Geographic Detection'),
            'cacheDeviceDetection' => Craft::t('smart-links', 'Cache Device Detection'),
            'deviceDetectionCacheDuration' => Craft::t('smart-links', 'Device Detection Cache Duration (seconds)'),
            'languageDetectionMethod' => Craft::t('smart-links', 'Language Detection Method'),
            'itemsPerPage' => Craft::t('smart-links', 'Items Per Page'),
            'notFoundRedirectUrl' => Craft::t('smart-links', '404 Redirect URL'),
            'enabledSites' => Craft::t('smart-links', 'Enabled Sites'),
            'logLevel' => Craft::t('smart-links', 'Log Level'),
            'enabledIntegrations' => Craft::t('smart-links', 'Enabled Integrations'),
            'seomaticTrackingEvents' => Craft::t('smart-links', 'Tracking Events'),
            'seomaticEventPrefix' => Craft::t('smart-links', 'Event Prefix'),
        ];
    }

    /**
     * Get display name (singular, without "Manager")
     *
     * Strips "Manager" and singularizes the plugin name for use in UI labels.
     * E.g., "Smart Link Manager" → "Smart Link", "Smart Links" → "Smart Link"
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        // Strip "Manager" or "manager" from the name
        $name = str_replace([' Manager', ' manager'], '', $this->pluginName);

        // Singularize by removing trailing 's' if present
        $singular = preg_replace('/s$/', '', $name) ?: $name;

        return $singular;
    }

    /**
     * Get full plugin name (as configured, with "Manager" if present)
     *
     * Returns the plugin name exactly as configured in settings.
     * E.g., "Smart Link Manager", "Smart Links", etc.
     *
     * @return string
     */
    public function getFullName(): string
    {
        return $this->pluginName;
    }

    /**
     * Get plural display name (without "Manager")
     *
     * Strips "Manager" from the plugin name but keeps plural form.
     * E.g., "Smart Link Manager" → "Smart Links", "Smart Links" → "Smart Links"
     *
     * @return string
     */
    public function getPluralDisplayName(): string
    {
        // Strip "Manager" or "manager" from the name
        return str_replace([' Manager', ' manager'], '', $this->pluginName);
    }

    /**
     * Get lowercase display name (singular, without "Manager")
     *
     * Lowercase version of getDisplayName() for use in messages, handles, etc.
     * E.g., "Smart Link Manager" → "smart link", "Smart Links" → "smart link"
     *
     * @return string
     */
    public function getLowerDisplayName(): string
    {
        return strtolower($this->getDisplayName());
    }

    /**
     * Get lowercase plural display name (without "Manager")
     *
     * Lowercase version of getPluralDisplayName() for use in messages, handles, etc.
     * E.g., "Smart Link Manager" → "smart links", "Smart Links" → "smart links"
     *
     * @return string
     */
    public function getPluralLowerDisplayName(): string
    {
        return strtolower($this->getPluralDisplayName());
    }
}
