<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\elements;

use Craft;
use craft\base\Element;
use craft\elements\actions\Delete;
use craft\elements\actions\Duplicate;
use craft\elements\actions\Restore;
use craft\elements\actions\SetStatus;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\validators\UniqueValidator;
use lindemannrock\smartlinks\elements\db\SmartLinkQuery;
use lindemannrock\smartlinks\records\SmartLinkRecord;
use lindemannrock\smartlinks\records\SmartLinkContentRecord;
use lindemannrock\smartlinks\SmartLinks;
use yii\validators\RequiredValidator;
use yii\validators\UrlValidator;

/**
 * SmartLink element
 *
 * @property-read string $redirectUrl
 * @property-read string $qrCodeUrl
 * @property-read array $analyticsData
 */
class SmartLink extends Element
{
    // Properties
    // =========================================================================


    /**
     * @var string|null Slug
     */
    public ?string $slug = null;

    /**
     * @var string|null Description (translatable)
     */
    public ?string $description = null;

    /**
     * @var string|null iOS URL
     */
    public ?string $iosUrl = null;

    /**
     * @var string|null Android URL
     */
    public ?string $androidUrl = null;

    /**
     * @var string|null Huawei URL
     */
    public ?string $huaweiUrl = null;

    /**
     * @var string|null Amazon URL
     */
    public ?string $amazonUrl = null;

    /**
     * @var string|null Windows URL
     */
    public ?string $windowsUrl = null;

    /**
     * @var string|null Mac URL
     */
    public ?string $macUrl = null;

    /**
     * @var string|null Fallback URL
     */
    public ?string $fallbackUrl = null;

    /**
     * @var string|null Icon
     */
    public ?string $icon = null;

    /**
     * @var bool Track analytics
     */
    public bool $trackAnalytics = true;

    /**
     * @var bool Active
     */
    public bool $active = true;

    /**
     * @var bool QR code enabled
     */
    public bool $qrCodeEnabled = true;

    /**
     * @var int QR code size
     */
    public int $qrCodeSize = 256;

    /**
     * @var string QR code color
     */
    public string $qrCodeColor = '#000000';

    /**
     * @var string QR code background color
     */
    public string $qrCodeBgColor = '#FFFFFF';

    /**
     * @var bool Language detection enabled
     */
    public bool $languageDetection = false;

    /**
     * @var array|null Localized URLs
     */
    public ?array $localizedUrls = null;

    /**
     * @var array|null Metadata
     */
    public ?array $metadata = null;

    /**
     * @var int|null Total clicks (cached value)
     */
    private ?int $_clicks = null;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('smart-links', 'Smart Link');
    }

    /**
     * @inheritdoc
     */
    public static function lowerDisplayName(): string
    {
        return Craft::t('smart-links', 'smart link');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('smart-links', 'Smart Links');
    }

    /**
     * @inheritdoc
     */
    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('smart-links', 'smart links');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle(): ?string
    {
        return 'smartLink';
    }

    /**
     * @inheritdoc
     */
    public static function trackChanges(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }
    
    /**
     * @inheritdoc
     */
    protected static function defineSupportedSites(): array
    {
        // Smart links are available in all sites
        return Craft::$app->getSites()->getAllSiteIds();
    }

    /**
     * @inheritdoc
     */
    public static function hasUris(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }



    /**
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ENABLED => Craft::t('app', 'Enabled'),
            self::STATUS_DISABLED => Craft::t('app', 'Disabled'),
        ];
    }

    /**
     * @inheritdoc
     * @return SmartLinkQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new SmartLinkQuery(static::class);
    }


    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('smart-links', 'All Smart Links'),
                'criteria' => [],
            ],
        ];

        $sources[] = ['heading' => Craft::t('smart-links', 'Status')];

        $sources[] = [
            'key' => 'active',
            'label' => Craft::t('smart-links', 'Active'),
            'criteria' => [
                'active' => true,
            ],
            'status' => 'green',
        ];

        $sources[] = [
            'key' => 'inactive',
            'label' => Craft::t('smart-links', 'Inactive'),
            'criteria' => [
                'active' => false,
            ],
            'status' => 'red',
        ];

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source): array
    {
        $actions = [];

        // Set Status
        $actions[] = SetStatus::class;

        // Delete
        $actions[] = Craft::$app->elements->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('smart-links', 'Are you sure you want to delete the selected smart links?'),
            'successMessage' => Craft::t('smart-links', 'Smart links deleted.'),
        ]);

        // Duplicate
        $actions[] = Duplicate::class;

        // Restore
        $actions[] = Craft::$app->elements->createAction([
            'type' => Restore::class,
            'successMessage' => Craft::t('smart-links', 'Smart links restored.'),
            'partialSuccessMessage' => Craft::t('smart-links', 'Some smart links restored.'),
            'failMessage' => Craft::t('smart-links', 'Smart links not restored.'),
        ]);

        return $actions;
    }

    /**
     * @inheritdoc
     */
    protected static function includeSetStatusAction(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('smart-links', 'Title'),
            'slug' => Craft::t('smart-links', 'Slug'),
            [
                'label' => Craft::t('app', 'Date Created'),
                'orderBy' => 'elements.dateCreated',
                'attribute' => 'dateCreated',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('app', 'Date Updated'),
                'orderBy' => 'elements.dateUpdated',
                'attribute' => 'dateUpdated',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('app', 'ID'),
                'orderBy' => 'elements.id',
                'attribute' => 'id',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('smart-links', 'Title')],
            'slug' => ['label' => Craft::t('smart-links', 'Slug')],
            'active' => ['label' => Craft::t('smart-links', 'Active')],
            'analytics' => ['label' => Craft::t('smart-links', 'Clicks')],
            'qrCode' => ['label' => Craft::t('smart-links', 'QR Code')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('app', 'Date Updated')],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'title',
            'slug',
            'active',
            'analytics',
            'dateCreated',
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['title', 'slug', 'description'];
    }

    // Public Methods
    // =========================================================================

    /**
     * Get total clicks count
     *
     * @return int
     */
    public function getClicks(): int
    {
        if ($this->_clicks === null) {
            $this->_clicks = (int) (new \craft\db\Query())
                ->from('{{%smartlinks_analytics}}')
                ->where(['linkId' => $this->id])
                ->count();
        }
        
        return $this->_clicks;
    }
    
    /**
     * Set clicks value (for caching purposes)
     *
     * @param int $clicks
     */
    public function setClicks(int $clicks): void
    {
        $this->_clicks = $clicks;
    }

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        
        // If we have an ID but no content loaded yet, load it now
        if ($this->id && $this->siteId && $this->fallbackUrl === null) {
            $this->loadContent();
        }
    }
    
    /**
     * Load content for the current site
     */
    private function loadContent(): void
    {
        if (!$this->id || !$this->siteId) {
            return;
        }
        
        // Skip loading from content table if this is a revision
        if ($this->getIsRevision()) {
            return;
        }
        
        $contentRecord = SmartLinkContentRecord::findOne([
            'smartLinkId' => $this->id,
            'siteId' => $this->siteId,
        ]);

        if ($contentRecord) {
            // Override with site-specific content
            $this->title = $contentRecord->title;
            $this->description = $contentRecord->description;
            $this->iosUrl = $contentRecord->iosUrl;
            $this->androidUrl = $contentRecord->androidUrl;
            $this->huaweiUrl = $contentRecord->huaweiUrl;
            $this->amazonUrl = $contentRecord->amazonUrl;
            $this->windowsUrl = $contentRecord->windowsUrl;
            $this->macUrl = $contentRecord->macUrl;
            $this->fallbackUrl = $contentRecord->fallbackUrl;
            
        }
    }

    /**
     * @inheritdoc
     */
    public function afterPopulate(): void
    {
        parent::afterPopulate();

        // Load content data for current site
        $this->loadContent();
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout(): ?FieldLayout
    {
        return Craft::$app->fields->getLayoutByType(SmartLink::class);
    }

    /**
     * @inheritdoc
     */
    public function canView(User $user): bool
    {
        return $user->can('smartLinks:viewLinks');
    }

    /**
     * @inheritdoc
     */
    public function canSave(User $user): bool
    {
        if ($this->getIsNew()) {
            return $user->can('smartLinks:createLinks');
        }

        return $user->can('smartLinks:editLinks');
    }

    /**
     * @inheritdoc
     */
    public function canDelete(User $user): bool
    {
        return $user->can('smartLinks:deleteLinks');
    }

    /**
     * @inheritdoc
     */
    public function canDuplicate(User $user): bool
    {
        return $user->can('smartLinks:createLinks');
    }

    /**
     * @inheritdoc
     */
    public function hasRevisions(): bool
    {
        return false;
    }

    /**
     * Get the redirect URL for this smart link
     */
    public function getRedirectUrl(): string
    {
        // Generate URL for the element's site (respects CP site switcher)
        return UrlHelper::siteUrl("go/{$this->slug}", null, null, $this->siteId);
    }
    
    /**
     * @inheritdoc
     */
    public function getUrl(): ?string
    {
        if (!$this->active) {
            return null;
        }
        
        return $this->getRedirectUrl();
    }

    /**
     * Get the QR code URL for this smart link
     */
    public function getQrCodeUrl(array $options = []): string
    {
        $params = array_merge([
            'size' => $this->qrCodeSize,
            'color' => str_replace('#', '', $this->qrCodeColor),
            'bg' => str_replace('#', '', $this->qrCodeBgColor),
        ], $options);

        return UrlHelper::siteUrl("qr/{$this->slug}", $params);
    }

    /**
     * Get analytics data for this smart link
     */
    public function getAnalyticsData(array $criteria = []): array
    {
        return SmartLinks::$plugin->analytics->getAnalytics($this, $criteria);
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): ?string
    {
        return $this->active ? self::STATUS_ENABLED : self::STATUS_DISABLED;
    }

    /**
     * @inheritdoc
     */
    protected function cpEditUrl(): ?string
    {
        return sprintf('smart-links/%s', $this->getCanonicalId());
    }
    
    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['title', 'slug', 'fallbackUrl'], RequiredValidator::class];
        $rules[] = [['slug'], 'match', 'pattern' => '/^[a-zA-Z0-9_\-]+$/', 'message' => Craft::t('smart-links', '{attribute} should only contain letters, numbers, underscores, and hyphens.')];
        $rules[] = [
            ['slug'],
            UniqueValidator::class,
            'targetClass' => SmartLinkRecord::class,
            'targetAttribute' => 'slug',
            'comboNotUnique' => Craft::t('yii', '{attribute} "{value}" has already been taken.'),
            'filter' => $this->id ? ['not', ['id' => $this->id]] : null
        ];
        $rules[] = [
            ['iosUrl', 'androidUrl', 'huaweiUrl', 'amazonUrl', 'windowsUrl', 'macUrl', 'fallbackUrl'],
            UrlValidator::class,
            'defaultScheme' => 'https',
        ];
        $rules[] = [['trackAnalytics', 'active', 'qrCodeEnabled'], 'boolean'];
        $rules[] = [['qrCodeSize'], 'integer', 'min' => 100, 'max' => 1000];
        $rules[] = [['qrCodeColor', 'qrCodeBgColor'], 'match', 'pattern' => '/^#[0-9A-F]{6}$/i'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'active':
                return Html::tag('span', '', [
                    'class' => 'status ' . ($this->active ? 'enabled' : 'disabled'),
                    'title' => $this->active ? Craft::t('smart-links', 'Active') : Craft::t('smart-links', 'Inactive'),
                ]);

            case 'analytics':
                $count = $this->metadata['clicks'] ?? 0;
                return $count > 0 ? number_format($count) : '—';

            case 'qrCode':
                if ($this->qrCodeEnabled) {
                    return Html::img($this->getQrCodeUrl(['size' => 30]), [
                        'alt' => 'QR Code',
                        'style' => 'width: 30px; height: 30px;',
                    ]);
                }
                return '—';
        }

        return parent::getTableAttributeHtml($attribute);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        return parent::beforeSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew): void
    {
        // Skip saving to custom tables if this is a revision
        if (!$this->getIsRevision()) {
            if (!$isNew) {
                $record = SmartLinkRecord::findOne($this->id);

                if (!$record) {
                    throw new \Exception('Invalid smart link ID: ' . $this->id);
                }
            } else {
                $record = new SmartLinkRecord();
                $record->id = $this->id;
            }

            // Save non-translatable fields to main table
            $record->title = $this->title;
            $record->slug = $this->slug;
            $record->icon = $this->icon;
            $record->trackAnalytics = $this->trackAnalytics;
            $record->active = $this->active;
            $record->qrCodeEnabled = $this->qrCodeEnabled;
            $record->qrCodeSize = $this->qrCodeSize;
            $record->qrCodeColor = $this->qrCodeColor;
            $record->qrCodeBgColor = $this->qrCodeBgColor;
            $record->languageDetection = $this->languageDetection;
            $record->metadata = $this->metadata;

            $record->save(false);

            // Save translatable fields to content table
            $contentRecord = SmartLinkContentRecord::findOne([
                'smartLinkId' => $this->id,
                'siteId' => $this->siteId,
            ]);

            if (!$contentRecord) {
                $contentRecord = new SmartLinkContentRecord();
                $contentRecord->smartLinkId = $this->id;
                $contentRecord->siteId = $this->siteId;
            }

            $contentRecord->title = $this->title;
            $contentRecord->description = $this->description;
            $contentRecord->iosUrl = $this->iosUrl;
            $contentRecord->androidUrl = $this->androidUrl;
            $contentRecord->huaweiUrl = $this->huaweiUrl;
            $contentRecord->amazonUrl = $this->amazonUrl;
            $contentRecord->windowsUrl = $this->windowsUrl;
            $contentRecord->macUrl = $this->macUrl;
            $contentRecord->fallbackUrl = $this->fallbackUrl;

            if (!$contentRecord->save(false)) {
                Craft::error('Failed to save content record: ' . json_encode($contentRecord->getErrors()), __METHOD__);
            }
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // Delete analytics data
        SmartLinks::$plugin->analytics->deleteAnalyticsForLink($this);

        return true;
    }

}