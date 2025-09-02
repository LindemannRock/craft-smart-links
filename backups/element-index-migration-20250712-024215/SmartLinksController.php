<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\controllers;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\web\Controller;
use lindemannrock\smartlinks\elements\SmartLink;
use lindemannrock\smartlinks\SmartLinks;
use yii\web\Response;

/**
 * Smart Links Controller
 */
class SmartLinksController extends Controller
{
    /**
     * @var array
     */
    protected array|bool|int $allowAnonymous = false;

    /**
     * Index action - list all smart links
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $this->requirePermission('smartLinks:viewLinks');

        return $this->renderTemplate('smart-links/smartlinks/index', [
            'title' => Craft::t('smart-links', 'Smart Links'),
        ]);
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
                'label' => Craft::t('smart-links', 'Smart Links'),
                'url' => 'smart-links',
            ],
        ];

        // Set the base CP edit URL
        $variables['baseCpEditUrl'] = 'smart-links/smartlinks/{id}';
        
        // Pass analytics service to the template
        if ($smartLink->id && $smartLink->trackAnalytics && SmartLinks::$plugin->getSettings()->enableAnalytics) {
            $variables['analyticsService'] = SmartLinks::$plugin->analytics;
        }

        return $this->renderTemplate('smart-links/smartlinks/_edit', $variables);
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
        $smartLink->trackAnalytics = (bool)$request->getBodyParam('trackAnalytics');
        $smartLink->active = (bool)$request->getBodyParam('active');
        $smartLink->qrCodeEnabled = (bool)$request->getBodyParam('qrCodeEnabled');
        $smartLink->qrCodeSize = $request->getBodyParam('qrCodeSize') ?: 200;
        
        // Fix color values - ensure they have # prefix
        $qrCodeColor = $request->getBodyParam('qrCodeColor') ?: '000000';
        $smartLink->qrCodeColor = strpos($qrCodeColor, '#') === 0 ? $qrCodeColor : '#' . $qrCodeColor;
        
        $qrCodeBgColor = $request->getBodyParam('qrCodeBgColor') ?: 'FFFFFF';
        $smartLink->qrCodeBgColor = strpos($qrCodeBgColor, '#') === 0 ? $qrCodeBgColor : '#' . $qrCodeBgColor;
        
        $smartLink->languageDetection = (bool)$request->getBodyParam('languageDetection');
        
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

        // Save it
        if (!SmartLinks::$plugin->smartLinks->saveSmartLink($smartLink)) {
            Craft::info('Smart link save failed. Errors: ' . json_encode($smartLink->getErrors()), __METHOD__);
            
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

        return $this->asModelSuccess(
            $smartLink,
            Craft::t('smart-links', 'Smart link saved.'),
            'smartLink'
        );
        
        } catch (\Exception $e) {
            Craft::error('Smart link save error: ' . $e->getMessage(), __METHOD__);
            Craft::error('Stack trace: ' . $e->getTraceAsString(), __METHOD__);
            
            // Return error response
            Craft::$app->getSession()->setError('Error saving smart link: ' . $e->getMessage());
            
            return $this->renderTemplate('smart-links/smartlinks/_edit', [
                'smartLink' => $smartLink ?? new SmartLink(),
                'title' => Craft::t('smart-links', 'New smart link'),
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
     * Duplicate a smart link
     *
     * @return Response
     */
    public function actionDuplicate(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('smartLinks:createLinks');

        $smartLinkId = Craft::$app->getRequest()->getRequiredBodyParam('id');
        $smartLink = SmartLinks::$plugin->smartLinks->getSmartLinkById($smartLinkId);

        if (!$smartLink) {
            throw new \yii\web\NotFoundHttpException('Smart link not found');
        }

        // Create a new smart link with the same data
        $newSmartLink = new SmartLink();
        $newSmartLink->siteId = $smartLink->siteId;
        $newSmartLink->title = $smartLink->title; // Keep the same title
        $newSmartLink->description = $smartLink->description;
        $newSmartLink->icon = $smartLink->icon;
        $newSmartLink->trackAnalytics = $smartLink->trackAnalytics;
        $newSmartLink->active = false; // Set as inactive by default
        $newSmartLink->qrCodeEnabled = $smartLink->qrCodeEnabled;
        $newSmartLink->qrCodeSize = $smartLink->qrCodeSize;
        $newSmartLink->qrCodeColor = $smartLink->qrCodeColor;
        $newSmartLink->qrCodeBgColor = $smartLink->qrCodeBgColor;
        $newSmartLink->languageDetection = $smartLink->languageDetection;
        
        // Generate a unique slug
        $baseSlug = $smartLink->slug;
        $newSlug = $this->_getUniqueSlug($baseSlug);
        $newSmartLink->slug = $newSlug;
        
        // Copy translatable fields
        $newSmartLink->iosUrl = $smartLink->iosUrl;
        $newSmartLink->androidUrl = $smartLink->androidUrl;
        $newSmartLink->huaweiUrl = $smartLink->huaweiUrl;
        $newSmartLink->amazonUrl = $smartLink->amazonUrl;
        $newSmartLink->windowsUrl = $smartLink->windowsUrl;
        $newSmartLink->macUrl = $smartLink->macUrl;
        $newSmartLink->fallbackUrl = $smartLink->fallbackUrl;

        if (!SmartLinks::$plugin->smartLinks->saveSmartLink($newSmartLink)) {
            return $this->asFailure(Craft::t('smart-links', 'Couldn\'t duplicate smart link.'));
        }

        // Return success with redirect URL
        return $this->asSuccess(
            Craft::t('smart-links', 'Smart link duplicated.'),
            [
                'id' => $newSmartLink->id,
                'redirect' => "smart-links/smartlinks/{$newSmartLink->id}"
            ]
        );
    }

    /**
     * Get a unique slug by appending numbers
     *
     * @param string $baseSlug
     * @return string
     */
    private function _getUniqueSlug(string $baseSlug): string
    {
        // Remove any existing number suffix from the base slug
        $baseSlug = preg_replace('/-\d+$/', '', $baseSlug);
        
        // Check if the base slug is available (excluding soft-deleted entries)
        $existingLink = SmartLink::find()
            ->slug($baseSlug)
            ->status(null)
            ->trashed(null) // Include soft-deleted entries in the check
            ->one();
            
        if (!$existingLink) {
            return $baseSlug;
        }
        
        // Find the next available number
        $counter = 2;
        while (true) {
            $testSlug = $baseSlug . '-' . $counter;
            $existingLink = SmartLink::find()
                ->slug($testSlug)
                ->status(null)
                ->trashed(null) // Include soft-deleted entries in the check
                ->one();
                
            if (!$existingLink) {
                return $testSlug;
            }
            
            $counter++;
        }
    }

    /**
     * Set status for smart links
     *
     * @return Response
     */
    public function actionSetStatus(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requirePermission('smartLinks:editLinks');

        $ids = Craft::$app->getRequest()->getRequiredBodyParam('ids');
        $status = Craft::$app->getRequest()->getRequiredBodyParam('status');
        
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $active = $status === 'active';
        $updatedCount = 0;

        foreach ($ids as $id) {
            $smartLink = SmartLinks::$plugin->smartLinks->getSmartLinkById($id);
            if ($smartLink) {
                $smartLink->active = $active;
                if (SmartLinks::$plugin->smartLinks->saveSmartLink($smartLink)) {
                    $updatedCount++;
                }
            }
        }

        if ($updatedCount === 0) {
            return $this->asFailure(Craft::t('smart-links', 'Couldn\'t update status.'));
        }

        return $this->asSuccess(
            $updatedCount === 1 
                ? Craft::t('smart-links', 'Status updated.')
                : Craft::t('smart-links', '{count} smart links updated.', ['count' => $updatedCount])
        );
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