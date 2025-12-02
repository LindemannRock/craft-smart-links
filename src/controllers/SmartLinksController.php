<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\controllers;

use Craft;
use craft\base\Element;
use craft\helpers\DateTimeHelper;
use craft\web\Controller;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinks\elements\SmartLink;
use lindemannrock\smartlinks\SmartLinks;
use yii\web\Response;

/**
 * Smart Links Controller
 */
class SmartLinksController extends Controller
{
    use LoggingTrait;
    /**
     * @var array<int|string>|bool|int
     */
    protected array|bool|int $allowAnonymous = false;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle('smart-links');
    }

    /**
     * Edit a smart link
     *
     * @param int|null $smartLinkId
     * @param SmartLink|null $smartLink
     * @return Response
     */
    public function actionEdit(?int $smartLinkId = null, ?SmartLink $smartLink = null): Response
    {
        $this->requirePermission('smartLinks:viewLinks');

        $variables = [
            'smartLinkId' => $smartLinkId,
            'smartLink' => $smartLink,
        ];

        // Get the site
        $site = Craft::$app->getRequest()->getQueryParam('site');
        if ($site) {
            $site = is_numeric($site) ? Craft::$app->getSites()->getSiteById($site) : Craft::$app->getSites()->getSiteByHandle($site);
            if (!$site) {
                throw new \yii\web\BadRequestHttpException('Invalid site handle: ' . $site);
            }
        } else {
            $site = Craft::$app->getSites()->getCurrentSite();
        }

        // Check if Smart Links is enabled for this site
        $settings = SmartLinks::getInstance()->getSettings();
        if (!$settings->isSiteEnabled($site->id)) {
            throw new \yii\web\ForbiddenHttpException('Smart Links is not enabled for this site.');
        }

        // Get the smart link
        if ($smartLinkId !== null) {
            if ($smartLink === null) {
                // Try to find the element
                $smartLink = SmartLink::find()
                    ->id($smartLinkId)
                    ->siteId($site->id)
                    ->status(null)
                    ->trashed(null)
                    ->one();

                if (!$smartLink) {
                    throw new \yii\web\NotFoundHttpException('Smart link not found');
                }
                
                // Don't allow editing trashed elements
                if ($smartLink->trashed) {
                    Craft::$app->getSession()->setError(Craft::t('smart-links', 'Cannot edit trashed smart links.'));
                    return $this->redirect('smart-links');
                }
            }

            $this->requirePermission('smartLinks:editLinks');
            
            // Set the title
            $variables['title'] = $smartLink->title;
        } else {
            $this->requirePermission('smartLinks:createLinks');

            if ($smartLink === null) {
                $smartLink = new SmartLink();
                $smartLink->siteId = $site->id;
                
                // Set default QR code values from settings
                $settings = SmartLinks::$plugin->getSettings();
                $smartLink->qrCodeSize = $settings->defaultQrSize;
                $smartLink->qrCodeColor = $settings->defaultQrColor;
                $smartLink->qrCodeBgColor = $settings->defaultQrBgColor;
            }

            $variables['title'] = Craft::t('smart-links', 'Create a new smart link');
        }

        $variables['smartLink'] = $smartLink;
        $variables['fullPageForm'] = true;
        $variables['saveShortcutRedirect'] = 'smart-links/smartlinks/{id}';
        $variables['continueEditingUrl'] = 'smart-links/smartlinks/{id}';

        // Breadcrumbs
        $variables['crumbs'] = [
            [
                'label' => SmartLinks::$plugin->getSettings()->pluginName,
                'url' => 'smart-links',
            ],
        ];

        // Set the base CP edit URL
        $variables['baseCpEditUrl'] = 'smart-links/smartlinks/{id}';
        
        // Pass analytics service to the template
        // Always pass analytics service if the smart link exists and analytics is enabled
        $plugin = SmartLinks::getInstance();
        if ($smartLink->id && $plugin && $plugin->getSettings()->enableAnalytics) {
            $variables['analyticsService'] = $plugin->analytics;
        }

        // Pass enabled sites for site switcher
        $variables['enabledSites'] = $plugin->getEnabledSites();

        return $this->renderTemplate('smart-links/smartlinks/edit', $variables);
    }

    /**
     * Save a smart link
     *
     * @return Response|null
     */
    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        try {
            $request = Craft::$app->getRequest();
            

            $smartLinkId = $request->getBodyParam('smartLinkId');
            $siteId = $request->getBodyParam('siteId');

            // Get the smart link
            if ($smartLinkId) {
                $smartLink = SmartLinks::$plugin->smartLinks->getSmartLinkById($smartLinkId, $siteId);

                if (!$smartLink) {
                    throw new \yii\web\NotFoundHttpException('Smart link not found');
                }

                $this->requirePermission('smartLinks:editLinks');
            } else {
                $this->requirePermission('smartLinks:createLinks');
                $smartLink = new SmartLink();
                $smartLink->siteId = $siteId ?? Craft::$app->getSites()->getPrimarySite()->id;
            }

            // Set non-translatable attributes (main table)
            $smartLink->title = $request->getBodyParam('title');
            $smartLink->slug = $request->getBodyParam('slug');
            $smartLink->description = $request->getBodyParam('description');
            $smartLink->icon = $request->getBodyParam('icon');
        
            // Handle authorId - elementSelectField returns an array
            $authorIds = $request->getBodyParam('authorId');
            $smartLink->authorId = is_array($authorIds) ? ($authorIds[0] ?? null) : $authorIds;
            $smartLink->trackAnalytics = (bool)$request->getBodyParam('trackAnalytics');
            $smartLink->hideTitle = (bool)$request->getBodyParam('hideTitle');

            $smartLink->qrCodeEnabled = (bool)$request->getBodyParam('qrCodeEnabled');
            $smartLink->qrCodeSize = $request->getBodyParam('qrCodeSize') ?: 200;
        
            // Fix color values - ensure they have # prefix, or set to null if empty
            $qrCodeColor = $request->getBodyParam('qrCodeColor');
            $smartLink->qrCodeColor = $qrCodeColor ? (strpos($qrCodeColor, '#') === 0 ? $qrCodeColor : '#' . $qrCodeColor) : null;

            $qrCodeBgColor = $request->getBodyParam('qrCodeBgColor');
            $smartLink->qrCodeBgColor = $qrCodeBgColor ? (strpos($qrCodeBgColor, '#') === 0 ? $qrCodeBgColor : '#' . $qrCodeBgColor) : null;
        
            // QR code eye color (can be empty)
            $qrCodeEyeColor = $request->getBodyParam('qrCodeEyeColor');
            $smartLink->qrCodeEyeColor = $qrCodeEyeColor ? (strpos($qrCodeEyeColor, '#') === 0 ? $qrCodeEyeColor : '#' . $qrCodeEyeColor) : null;
        
            // QR code format (empty string means use default, store as null)
            $qrCodeFormat = $request->getBodyParam('qrCodeFormat');
            $smartLink->qrCodeFormat = $qrCodeFormat ? $qrCodeFormat : null;
        
            // QR logo (elementSelectField returns an array)
            $qrLogoIds = $request->getBodyParam('qrLogoId');
            $smartLink->qrLogoId = is_array($qrLogoIds) ? ($qrLogoIds[0] ?? null) : (empty($qrLogoIds) ? null : (int)$qrLogoIds);

            // Smart Link image (elementSelectField returns an array)
            $imageIds = $request->getBodyParam('imageId');
            $smartLink->imageId = is_array($imageIds) ? ($imageIds[0] ?? null) : (empty($imageIds) ? null : (int)$imageIds);
        
            // Smart Link image size
            $smartLink->imageSize = $request->getBodyParam('imageSize', 'xl');

            $smartLink->languageDetection = (bool)$request->getBodyParam('languageDetection');

            // Handle enabled status - set BEFORE setFieldValuesFromRequest
            // This is per-site and managed by Craft's element system
            $enabledParam = $request->getBodyParam('enabled');
            $enabled = $enabledParam === '1' || $enabledParam === 1 || $enabledParam === true;

            // Set enabled ONLY for the current site being edited
            $smartLink->setEnabledForSite($enabled);

            // Set translatable attributes (content table) - these need to be set on the element
            $smartLink->iosUrl = $request->getBodyParam('iosUrl');
            $smartLink->androidUrl = $request->getBodyParam('androidUrl');
            $smartLink->huaweiUrl = $request->getBodyParam('huaweiUrl');
            $smartLink->amazonUrl = $request->getBodyParam('amazonUrl');
            $smartLink->windowsUrl = $request->getBodyParam('windowsUrl');
            $smartLink->macUrl = $request->getBodyParam('macUrl');
            $smartLink->fallbackUrl = $request->getBodyParam('fallbackUrl');

            // Set field values
            $smartLink->setFieldValuesFromRequest('fields');

            // Handle dates
            $postDate = $request->getBodyParam('postDate');
            if ($postDate) {
                $dateTime = DateTimeHelper::toDateTime($postDate, true);
                $smartLink->postDate = $dateTime !== false ? $dateTime : null;
            }

            $expiryDate = $request->getBodyParam('expiryDate');
            if ($expiryDate) {
                $dateTime = DateTimeHelper::toDateTime($expiryDate, true);
                $smartLink->dateExpired = $dateTime !== false ? $dateTime : null;
            }

            // Save it
            if (!SmartLinks::$plugin->smartLinks->saveSmartLink($smartLink)) {
                $this->logError('Smart link save failed', ['errors' => $smartLink->getErrors()]);
                // If it's an AJAX request, return JSON response
                if ($this->request->getAcceptsJson()) {
                    return $this->asModelFailure(
                    $smartLink,
                    Craft::t('smart-links', 'Couldn\'t save smart link.'),
                    'smartLink'
                );
                }

                // Otherwise, set error flash and re-render the template
                Craft::$app->getSession()->setError(Craft::t('smart-links', 'Couldn\'t save smart link.'));

                // Set route params so Craft can re-render the template with errors
                Craft::$app->getUrlManager()->setRouteParams([
                'smartLink' => $smartLink,
                'title' => $smartLink->id ? $smartLink->title : Craft::t('smart-links', 'New smart link'),
            ]);

                return null;
            }

            // Clear ALL caches for this element across all sites
            Craft::$app->getElements()->invalidateCachesForElement($smartLink);

            // Reload the element in the correct site context for the response
            // This ensures the notification chip shows the correct enabled status
            $smartLink = SmartLink::find()
            ->id($smartLink->id)
            ->siteId($smartLink->siteId)
            ->status(null)
            ->one();

            return $this->asModelSuccess(
            $smartLink,
            Craft::t('smart-links', 'Smart link saved.'),
            'smartLink'
        );
        } catch (\Exception $e) {
            $this->logError('Smart link save error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            // Return error response
            Craft::$app->getSession()->setError('Error saving smart link: ' . $e->getMessage());

            $plugin = SmartLinks::getInstance();

            return $this->renderTemplate('smart-links/smartlinks/edit', [
                'smartLink' => $smartLink ?? new SmartLink(),
                'title' => Craft::t('smart-links', 'New smart link'),
                'enabledSites' => $plugin->getEnabledSites(),
                'analyticsService' => $plugin->analytics,
            ]);
        }
    }

    /**
     * Delete a smart link
     *
     * @return Response
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('smartLinks:deleteLinks');

        $smartLinkId = Craft::$app->getRequest()->getRequiredBodyParam('id');
        $smartLink = SmartLinks::$plugin->smartLinks->getSmartLinkById($smartLinkId);

        if (!$smartLink) {
            throw new \yii\web\NotFoundHttpException('Smart link not found');
        }

        if (!SmartLinks::$plugin->smartLinks->deleteSmartLink($smartLink)) {
            return $this->asFailure(Craft::t('smart-links', 'Couldn\'t delete smart link.'));
        }

        return $this->asSuccess(Craft::t('smart-links', 'Smart link deleted.'));
    }


    /**
     * Restore a soft-deleted smart link
     *
     * @return Response
     */
    public function actionRestore(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requirePermission('smartLinks:editLinks');

        $smartLinkId = Craft::$app->getRequest()->getRequiredBodyParam('id');
        
        // Find the trashed smart link
        $smartLink = SmartLink::find()
            ->id($smartLinkId)
            ->trashed(true)
            ->status(null)
            ->one();

        if (!$smartLink) {
            throw new \yii\web\NotFoundHttpException('Smart link not found');
        }

        // Restore the element
        if (!Craft::$app->elements->restoreElement($smartLink)) {
            return $this->asFailure(Craft::t('smart-links', 'Couldn\'t restore smart link.'));
        }

        return $this->asSuccess(Craft::t('smart-links', 'Smart link restored.'));
    }

    /**
     * Permanently delete a smart link
     *
     * @return Response
     */
    public function actionHardDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requirePermission('smartLinks:deleteLinks');

        $smartLinkId = Craft::$app->getRequest()->getRequiredBodyParam('id');
        
        // Find the smart link (including trashed)
        $smartLink = SmartLink::find()
            ->id($smartLinkId)
            ->trashed(null)
            ->status(null)
            ->one();

        if (!$smartLink) {
            throw new \yii\web\NotFoundHttpException('Smart link not found');
        }

        // Permanently delete the element
        if (!Craft::$app->elements->deleteElement($smartLink, true)) {
            return $this->asFailure(Craft::t('smart-links', 'Couldn\'t delete smart link permanently.'));
        }

        return $this->asSuccess(Craft::t('smart-links', 'Smart link permanently deleted.'));
    }

    /**
     * Get smart link details
     *
     * @return Response
     */
    public function actionGetDetails(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePermission('accessPlugin-smart-links');

        $smartLinkId = Craft::$app->getRequest()->getRequiredParam('id');
        $smartLink = SmartLinks::$plugin->smartLinks->getSmartLinkById($smartLinkId);

        if (!$smartLink) {
            return $this->asFailure(Craft::t('smart-links', 'Smart link not found'));
        }

        return $this->asJson([
            'success' => true,
            'smartLink' => [
                'id' => $smartLink->id,
                'name' => $smartLink->title,
                'slug' => $smartLink->slug,
                'description' => $smartLink->description,
                'redirectUrl' => $smartLink->getRedirectUrl(),
                'qrCodeUrl' => $smartLink->getQrCodeUrl(),
                'clicks' => $smartLink->clicks,
                'dateCreated' => DateTimeHelper::toIso8601($smartLink->dateCreated),
                'dateUpdated' => DateTimeHelper::toIso8601($smartLink->dateUpdated),
            ],
        ]);
    }

    /**
     * Generate QR code
     *
     * @return Response
     */
    public function actionGenerateQrCode(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePermission('accessPlugin-smart-links');

        $smartLinkId = Craft::$app->getRequest()->getRequiredParam('id');
        $smartLink = SmartLinks::$plugin->smartLinks->getSmartLinkById($smartLinkId);

        if (!$smartLink) {
            return $this->asFailure(Craft::t('smart-links', 'Smart link not found'));
        }

        $options = [
            'size' => Craft::$app->getRequest()->getParam('size', 200),
            'format' => Craft::$app->getRequest()->getParam('format', 'png'),
        ];

        try {
            $qrCodeDataUrl = SmartLinks::$plugin->smartLinks->generateQrCodeDataUrl($smartLink, $options);
            
            return $this->asJson([
                'success' => true,
                'qrCode' => $qrCodeDataUrl,
            ]);
        } catch (\Exception $e) {
            return $this->asFailure($e->getMessage());
        }
    }

    /**
     * View revisions for a smart link
     *
     * @param int $smartLinkId
     * @return Response
     */
    public function actionRevisions(int $smartLinkId): Response
    {
        $this->requirePermission('smartLinks:viewLinks');

        // Get the site
        $site = Craft::$app->getRequest()->getQueryParam('site');
        if ($site) {
            $site = is_numeric($site) ? Craft::$app->getSites()->getSiteById($site) : Craft::$app->getSites()->getSiteByHandle($site);
            if (!$site) {
                throw new \yii\web\BadRequestHttpException('Invalid site handle: ' . $site);
            }
        } else {
            $site = Craft::$app->getSites()->getCurrentSite();
        }

        // Get the smart link
        $smartLink = SmartLink::find()
            ->id($smartLinkId)
            ->siteId($site->id)
            ->status(null)
            ->one();

        if (!$smartLink) {
            throw new \yii\web\NotFoundHttpException('Smart link not found');
        }

        return $this->renderTemplate('smart-links/smartlinks/revisions', [
            'smartLink' => $smartLink,
        ]);
    }
}
