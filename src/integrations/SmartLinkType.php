<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\integrations;

use Craft;
use craft\base\ElementInterface;
use craft\fields\Link;
use craft\fields\linktypes\BaseElementLinkType;
use craft\helpers\Cp;
use craft\helpers\Html;
use lindemannrock\smartlinks\elements\SmartLink;
use lindemannrock\smartlinks\SmartLinks;

/**
 * Smart Link Type for Link Field
 */
class SmartLinkType extends BaseElementLinkType
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return SmartLinks::$plugin->getSettings()->getDisplayName();
    }

    /**
     * @inheritdoc
     */
    public static function elementType(): string
    {
        return SmartLink::class;
    }

    /**
     * @inheritdoc
     */
    public function inputHtml(Link $field, ?string $value, string $containerId): string
    {
        $id = sprintf('smartlink%s', mt_rand());
        
        // Get the site ID based on the current context
        $siteId = null;
        
        // Try to get site from POST data (when saving)
        if (Craft::$app->getRequest()->getIsPost()) {
            $siteId = Craft::$app->getRequest()->getBodyParam('siteId');
        }
        
        // Try to get site from query param (when editing)
        if (!$siteId) {
            $siteHandle = Craft::$app->getRequest()->getQueryParam('site');
            if ($siteHandle) {
                $site = Craft::$app->sites->getSiteByHandle($siteHandle);
                if ($site) {
                    $siteId = $site->id;
                }
            }
        }
        
        // Fall back to current site
        if (!$siteId) {
            $siteId = Craft::$app->sites->currentSite->id;
        }
        
        // Parse the value to get the element
        $smartLink = null;
        if ($value) {
            $matches = [];
            if (preg_match('/^{smartLink:(\d+)(@(\d+))?:url}$/', $value, $matches)) {
                $elementId = $matches[1];
                $parsedSiteId = $matches[3] ?? null;
                $smartLink = SmartLink::find()
                    ->id($elementId)
                    ->siteId($parsedSiteId ?? $siteId)
                    ->status(null)
                    ->one();
            }
        }

        // Get site for the field
        $currentSite = Craft::$app->sites->getSiteById($siteId);
        
        return Cp::elementSelectFieldHtml([
            'id' => $id,
            'name' => 'value',
            'elements' => $smartLink ? [$smartLink] : [],
            'elementType' => SmartLink::class,
            'sources' => $this->sources,
            'criteria' => [
                'status' => 'enabled',
                'siteId' => $currentSite->id,
            ],
            'single' => true,
            'showSiteMenu' => false,
            'modalSettings' => [
                'defaultSiteId' => $currentSite->id,
                'criteria' => [
                    'siteId' => $currentSite->id,
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function renderValue(string $value): string
    {
        $element = $this->element($value);
        return $element instanceof SmartLink ? $element->getRedirectUrl() : '';
    }

    /**
     * @inheritdoc
     */
    public function linkLabel(string $value): string
    {
        $element = $this->element($value);
        return $element instanceof SmartLink ? $element->title : '';
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue(ElementInterface|string|int $value): string
    {
        if ($value instanceof SmartLink) {
            return sprintf('{smartLink:%s@%s:url}', $value->id, $value->siteId);
        }
        
        if (is_numeric($value)) {
            // If we get a numeric ID, we need to determine the correct site
            $siteId = $this->detectCurrentSiteId();
            return sprintf('{smartLink:%s@%s:url}', $value, $siteId);
        }
        
        return parent::normalizeValue($value);
    }
    
    /**
     * @inheritdoc
     */
    public function value(mixed $element): ?string
    {
        if ($element instanceof SmartLink) {
            return sprintf('{smartLink:%s@%s:url}', $element->id, $element->siteId);
        }
        return parent::value($element);
    }
    
    /**
     * Detect the current site ID from the request context
     */
    private function detectCurrentSiteId(): int
    {
        // Try POST data first
        if (Craft::$app->getRequest()->getIsPost()) {
            $siteId = Craft::$app->getRequest()->getBodyParam('siteId');
            if ($siteId) {
                return (int)$siteId;
            }
        }
        
        // Try query param
        $siteHandle = Craft::$app->getRequest()->getQueryParam('site');
        if ($siteHandle && $site = Craft::$app->sites->getSiteByHandle($siteHandle)) {
            return $site->id;
        }
        
        // Default to current site
        return Craft::$app->sites->currentSite->id;
    }
    
    /**
     * @inheritdoc
     */
    public function validateValue(string $value, ?string &$error = null): bool
    {
        // Parse the value to get the element ID
        $matches = [];
        if (!preg_match('/^{smartLink:(\d+)(@(\d+))?:url}$/', $value, $matches)) {
            $error = Craft::t('smart-links', 'Invalid {pluginName} format.', [
                'pluginName' => SmartLinks::$plugin->getSettings()->getLowerDisplayName()
            ]);
            return false;
        }

        $elementId = $matches[1];
        $siteId = $matches[3] ?? null;

        $smartLink = SmartLink::find()
            ->id($elementId)
            ->siteId($siteId)
            ->status(null)
            ->one();

        if (!$smartLink) {
            $error = Craft::t('smart-links', '{pluginName} not found.', [
                'pluginName' => SmartLinks::$plugin->getSettings()->getDisplayName()
            ]);
            return false;
        }

        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function isValueEmpty(string $value): bool
    {
        return !$this->element($value);
    }

    /**
     * @inheritdoc
     */
    public function element(?string $value): ?ElementInterface
    {
        if (!$value) {
            return null;
        }

        $matches = [];
        if (!preg_match('/^{smartLink:(\d+)(@(\d+))?:url}$/', $value, $matches)) {
            return null;
        }

        $elementId = $matches[1];
        $siteId = $matches[3] ?? null;

        return SmartLink::find()
            ->id($elementId)
            ->siteId($siteId)
            ->status(null)
            ->one();
    }

}