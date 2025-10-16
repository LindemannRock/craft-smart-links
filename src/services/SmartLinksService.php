<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinks\elements\SmartLink;
use lindemannrock\smartlinks\events\SmartLinkEvent;
use lindemannrock\smartlinks\models\DeviceInfo;
use yii\base\Event;
use lindemannrock\smartlinks\SmartLinks;

/**
 * Smart Links Service
 *
 * @property-read SmartLinks $module
 */
class SmartLinksService extends Component
{
    use LoggingTrait;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle('smart-links');
    }

    // Events
    // =========================================================================

    /**
     * @event SmartLinkEvent The event that is triggered before a smart link redirect.
     */
    const EVENT_BEFORE_REDIRECT = 'beforeRedirect';

    /**
     * @event SmartLinkEvent The event that is triggered after analytics are tracked.
     */
    const EVENT_AFTER_TRACK_ANALYTICS = 'afterTrackAnalytics';

    // Public Methods
    // =========================================================================

    /**
     * Create a new smart link
     *
     * @param array $config
     * @return SmartLink
     */
    public function createSmartLink(array $config): SmartLink
    {
        $smartLink = new SmartLink();
        
        // Set attributes
        foreach ($config as $key => $value) {
            if ($smartLink->canSetProperty($key)) {
                $smartLink->$key = $value;
            }
        }
        
        return $smartLink;
    }

    /**
     * Save a smart link
     *
     * @param SmartLink $smartLink
     * @param bool $runValidation
     * @return bool
     */
    public function saveSmartLink(SmartLink $smartLink, bool $runValidation = true): bool
    {
        if ($runValidation && !$smartLink->validate()) {
            $this->logInfo('Smart link not saved due to validation errors.');
            return false;
        }

        // Save the element
        return Craft::$app->elements->saveElement($smartLink, true, true, true);
    }

    /**
     * Update a smart link
     *
     * @param SmartLink $smartLink
     * @param array $config
     * @return bool
     */
    public function updateSmartLink(SmartLink $smartLink, array $config): bool
    {
        // Update attributes
        foreach ($config as $key => $value) {
            if ($smartLink->canSetProperty($key)) {
                $smartLink->$key = $value;
            }
        }
        
        return $this->saveSmartLink($smartLink);
    }

    /**
     * Delete a smart link
     *
     * @param SmartLink $smartLink
     * @return bool
     */
    public function deleteSmartLink(SmartLink $smartLink): bool
    {
        return Craft::$app->elements->deleteElement($smartLink);
    }

    /**
     * Get a smart link by ID
     *
     * @param int $id
     * @param int|null $siteId
     * @return SmartLink|null
     */
    public function getSmartLinkById(int $id, ?int $siteId = null): ?SmartLink
    {
        return SmartLink::find()
            ->id($id)
            ->siteId($siteId)
            ->status(null)
            ->one();
    }

    /**
     * Get a smart link by slug
     *
     * @param string $slug
     * @param int|null $siteId
     * @return SmartLink|null
     */
    public function getSmartLinkBySlug(string $slug, ?int $siteId = null): ?SmartLink
    {
        return SmartLink::find()
            ->slug($slug)
            ->siteId($siteId)
            ->status(null)
            ->one();
    }

    /**
     * Get all active smart links
     *
     * @param int|null $siteId
     * @return SmartLink[]
     */
    public function getActiveSmartLinks(?int $siteId = null): array
    {
        return SmartLink::find()
            ->siteId($siteId)
            ->status(SmartLink::STATUS_ENABLED)
            ->all();
    }

    /**
     * Generate QR code for a smart link
     *
     * @param SmartLink $smartLink
     * @param array $options
     * @return string
     */
    public function generateQrCode(SmartLink $smartLink, array $options = []): string
    {
        $url = $smartLink->getRedirectUrl();
        $fullUrl = UrlHelper::siteUrl($url);
        
        return SmartLinks::$plugin->qrCode->generateQrCode($fullUrl, $options);
    }

    /**
     * Generate QR code data URL for a smart link
     *
     * @param SmartLink $smartLink
     * @param array $options
     * @return string
     */
    public function generateQrCodeDataUrl(SmartLink $smartLink, array $options = []): string
    {
        $url = $smartLink->getRedirectUrl();
        $fullUrl = UrlHelper::siteUrl($url);
        
        return SmartLinks::$plugin->qrCode->generateQrCodeDataUrl($fullUrl, $options);
    }

    /**
     * Import smart links from array
     *
     * @param array $links
     * @return array Results with 'success' and 'errors' keys
     */
    public function importSmartLinks(array $links): array
    {
        $results = [
            'success' => 0,
            'errors' => [],
        ];
        
        foreach ($links as $linkData) {
            try {
                $smartLink = $this->createSmartLink($linkData);
                
                if ($this->saveSmartLink($smartLink)) {
                    $results['success']++;
                } else {
                    $results['errors'][] = [
                        'data' => $linkData,
                        'errors' => $smartLink->getErrors(),
                    ];
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'data' => $linkData,
                    'errors' => ['exception' => $e->getMessage()],
                ];
            }
        }
        
        return $results;
    }

    /**
     * Export smart links
     *
     * @param SmartLink[] $smartLinks
     * @return array
     */
    public function exportSmartLinks(array $smartLinks): array
    {
        $data = [];
        
        foreach ($smartLinks as $smartLink) {
            $data[] = [
                'name' => $smartLink->title,
                'slug' => $smartLink->slug,
                'description' => $smartLink->description,
                'iosUrl' => $smartLink->iosUrl,
                'androidUrl' => $smartLink->androidUrl,
                'huaweiUrl' => $smartLink->huaweiUrl,
                'amazonUrl' => $smartLink->amazonUrl,
                'windowsUrl' => $smartLink->windowsUrl,
                'macUrl' => $smartLink->macUrl,
                'fallbackUrl' => $smartLink->fallbackUrl,
                'icon' => $smartLink->icon,
                'trackAnalytics' => $smartLink->trackAnalytics,
                'enabled' => $smartLink->enabled,
                'qrCodeEnabled' => $smartLink->qrCodeEnabled,
                'qrCodeSize' => $smartLink->qrCodeSize,
                'qrCodeColor' => $smartLink->qrCodeColor,
                'qrCodeBgColor' => $smartLink->qrCodeBgColor,
                'languageDetection' => $smartLink->languageDetection,
                'localizedUrls' => $smartLink->localizedUrls,
            ];
        }
        
        return $data;
    }

    /**
     * Trigger before redirect event
     *
     * @param SmartLink $smartLink
     * @param DeviceInfo $device
     * @param string $redirectUrl
     * @return string The redirect URL (possibly modified by event handlers)
     */
    public function triggerBeforeRedirect(SmartLink $smartLink, DeviceInfo $device, string $redirectUrl): string
    {
        $event = new SmartLinkEvent([
            'smartLink' => $smartLink,
            'device' => $device,
            'redirectUrl' => $redirectUrl,
        ]);
        
        $this->trigger(self::EVENT_BEFORE_REDIRECT, $event);
        
        return $event->redirectUrl;
    }

    /**
     * Trigger after track analytics event
     *
     * @param SmartLink $smartLink
     * @param DeviceInfo $device
     * @param array $metadata
     */
    public function triggerAfterTrackAnalytics(SmartLink $smartLink, DeviceInfo $device, array $metadata): void
    {
        $event = new SmartLinkEvent([
            'smartLink' => $smartLink,
            'device' => $device,
            'metadata' => $metadata,
        ]);
        
        $this->trigger(self::EVENT_AFTER_TRACK_ANALYTICS, $event);
    }
}