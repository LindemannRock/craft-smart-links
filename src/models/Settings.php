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
use craft\helpers\Db;

/**
 * Smart Links Settings Model
 */
class Settings extends Model
{
    /**
     * @event Event The event that is triggered after settings are saved
     */
    const EVENT_AFTER_SAVE_SETTINGS = 'afterSaveSettings';
    
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
     * @var int QR code cache duration in seconds
     */
    public int $qrCodeCacheDuration = 86400; // 24 hours
    
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
     * @var bool Enable geographic detection
     */
    public bool $enableGeoDetection = false;

    /**
     * @var bool Cache device detection results
     */
    public bool $cacheDeviceDetection = true;

    /**
     * @var int Device detection cache duration in seconds
     */
    public int $deviceDetectionCacheDuration = 3600; // 1 hour

    /**
     * @var string Default language detection method
     */
    public string $languageDetectionMethod = 'browser'; // browser, ip, or both

    /**
     * @var int Items per page in element index
     */
    public int $itemsPerPage = 100;
    
    /**
     * @var string URL to redirect to when smart link is not found (404)
     */
    public string $notFoundRedirectUrl = '/';

    /**
     * @var array Site IDs where Smart Links should be enabled
     */
    public array $enabledSites = [];

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
            [['pluginName'], 'required'],
            [['pluginName'], 'string', 'max' => 255],
            [['enableAnalytics', 'enableGeoDetection', 'cacheDeviceDetection', 'includeDisabledInExport', 'includeExpiredInExport'], 'boolean'],
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
            [['redirectTemplate', 'notFoundRedirectUrl'], 'string'],
            [['languageDetectionMethod'], 'in', 'range' => ['browser', 'ip', 'both']],
            [['enabledSites'], 'each', 'rule' => ['integer']],
        ];
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
            Craft::error('Failed to load settings from database: ' . $e->getMessage(), 'smart-links');
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
                'enableQrDownload'
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
                'itemsPerPage'
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
            
            // Set attributes from database
            $settings->setAttributes($row, false);
        } else {
            Craft::warning('No settings found in database', 'smart-links');
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
            Craft::error('Settings validation failed: ' . json_encode($this->getErrors()), 'smart-links');
            return false;
        }

        $db = Craft::$app->getDb();
        $attributes = $this->getAttributes();

        // Debug: Log what we're trying to save
        Craft::info('Attempting to save settings: ' . json_encode($attributes), 'smart-links');

        // Handle array serialization
        if (isset($attributes['enabledSites'])) {
            $attributes['enabledSites'] = json_encode($attributes['enabledSites']);
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
            Craft::info('Database update result: ' . $result, 'smart-links');

            if ($result !== false) {
                // Trigger event after successful save
                $this->trigger(self::EVENT_AFTER_SAVE_SETTINGS);
                Craft::info('Settings saved successfully to database', 'smart-links');
                return true;
            }

            Craft::error('Database update returned false', 'smart-links');
            return false;
        } catch (\Exception $e) {
            Craft::error('Failed to save Smart Links settings: ' . $e->getMessage(), 'smart-links');
            return false;
        }
    }

    /**
     * Check if a setting is overridden by config file
     *
     * @param string $attribute
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
        
        // Check for the attribute in the config
        if (isset($rawConfig[$attribute])) {
            return true;
        }
        
        // Check environment-specific configs
        $env = \Craft::$app->getConfig()->env;
        if ($env && isset($rawConfig[$env][$attribute])) {
            return true;
        }
        
        // Check wildcard config
        if (isset($rawConfig['*'][$attribute])) {
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
            'enableGeoDetection' => Craft::t('smart-links', 'Enable Geographic Detection'),
            'cacheDeviceDetection' => Craft::t('smart-links', 'Cache Device Detection'),
            'deviceDetectionCacheDuration' => Craft::t('smart-links', 'Device Detection Cache Duration (seconds)'),
            'languageDetectionMethod' => Craft::t('smart-links', 'Language Detection Method'),
            'itemsPerPage' => Craft::t('smart-links', 'Items Per Page'),
            'notFoundRedirectUrl' => Craft::t('smart-links', '404 Redirect URL'),
            'enabledSites' => Craft::t('smart-links', 'Enabled Sites'),
        ];
    }
}