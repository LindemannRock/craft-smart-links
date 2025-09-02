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
use lindemannrock\smartlinks\elements\SmartLink;
use lindemannrock\smartlinks\SmartLinks;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * QR Code Controller
 * Handles QR code generation for smart links
 */
class QrCodeController extends Controller
{
    /**
     * @var array Allow anonymous access
     */
    protected array|int|bool $allowAnonymous = true;

    /**
     * Display QR code page for smart link
     *
     * @param string $slug
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDisplay(string $slug): Response
    {
        // Get the smart link
        $smartLink = SmartLink::find()
            ->slug($slug)
            ->status(null) // Allow any status
            ->one();

        if (!$smartLink || !$smartLink->qrCodeEnabled) {
            throw new NotFoundHttpException('QR code not found.');
        }
        
        // Check if link is trashed
        if ($smartLink->trashed) {
            throw new NotFoundHttpException('QR code not found.');
        }

        // Get parameters
        $request = Craft::$app->request;
        $size = $request->getQueryParam('size', SmartLinks::$plugin->getSettings()->defaultQrSize);
        $format = $request->getQueryParam('format', SmartLinks::$plugin->getSettings()->defaultQrFormat);
        
        // Generate QR code data
        $options = [
            'size' => $size,
            'color' => $request->getQueryParam('color', str_replace('#', '', $smartLink->qrCodeColor)),
            'bg' => $request->getQueryParam('bg', str_replace('#', '', $smartLink->qrCodeBgColor)),
            'format' => $format,
            'margin' => $request->getQueryParam('margin'),
            'moduleStyle' => $request->getQueryParam('moduleStyle'),
            'eyeStyle' => $request->getQueryParam('eyeStyle'),
            'eyeColor' => $request->getQueryParam('eyeColor', str_replace('#', '', $smartLink->qrCodeEyeColor)),
            'logo' => $request->getQueryParam('logo', $smartLink->qrLogoId),
        ];

        // Remove null values
        $options = array_filter($options, fn($value) => $value !== null);

        // Generate full URL for the smart link with QR tracking parameter
        $url = $smartLink->getRedirectUrl();
        
        // Debug logging
        Craft::info('SmartLink redirect URL (display): ' . $url, 'smart-links');
        
        // The redirect URL should already be a full URL from UrlHelper::siteUrl()
        // Just add the QR source parameter
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $fullUrl = $url . $separator . 'src=qr';
        
        Craft::info('Full URL for QR: ' . $fullUrl, 'smart-links');

        try {
            $qrCode = SmartLinks::$plugin->qrCode->generateQrCode($fullUrl, $options);
            
            // Prepare template variables
            $templateVars = [
                'smartLink' => $smartLink,
                'size' => $size,
                'format' => $format,
            ];
            
            if ($format === 'svg') {
                $templateVars['qrCodeSvg'] = $qrCode;
            } else {
                $templateVars['qrCodeData'] = base64_encode($qrCode);
            }
            
            return $this->renderTemplate('smart-links/qr', $templateVars);
            
        } catch (\Exception $e) {
            Craft::error('Failed to generate QR code: ' . $e->getMessage(), __METHOD__);
            throw new NotFoundHttpException('Failed to generate QR code.');
        }
    }

    /**
     * Generate QR code for smart link
     *
     * @param string|null $slug
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionGenerate(?string $slug = null): Response
    {
        $request = Craft::$app->request;
        
        // Check if this is a preview request
        $isPreview = $request->getQueryParam('preview');
        $url = $request->getQueryParam('url');
        
        if ($isPreview && $url) {
            // Preview mode - generate QR code for any URL
            $fullUrl = $url;
            $smartLink = null;
        } else {
            // Normal mode - require a smart link
            if (!$slug) {
                throw new NotFoundHttpException('Smart link not specified.');
            }
            
            // Get the smart link - allow all statuses except trashed
            $smartLink = SmartLink::find()
                ->slug($slug)
                ->status(null) // Allow any status
                ->one();

            if (!$smartLink || !$smartLink->qrCodeEnabled) {
                throw new NotFoundHttpException('QR code not found.');
            }
            
            // Check if link is trashed
            if ($smartLink->trashed) {
                throw new NotFoundHttpException('QR code not found.');
            }

            // Generate full URL for the smart link with QR tracking parameter
            $url = $smartLink->getRedirectUrl();
            
            // Debug logging
            Craft::info('SmartLink redirect URL (generate): ' . $url, 'smart-links');
            
            // The redirect URL should already be a full URL from UrlHelper::siteUrl()
            // Just add the QR source parameter
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $fullUrl = $url . $separator . 'src=qr';
            
            Craft::info('Full URL for QR: ' . $fullUrl, 'smart-links');
        }

        // Get parameters
        $options = [
            'size' => $request->getQueryParam('size'),
            'color' => $request->getQueryParam('color'),
            'bg' => $request->getQueryParam('bg'),
            'format' => $request->getQueryParam('format'),
            'margin' => $request->getQueryParam('margin'),
            'moduleStyle' => $request->getQueryParam('moduleStyle'),
            'eyeStyle' => $request->getQueryParam('eyeStyle'),
            'eyeColor' => $request->getQueryParam('eyeColor'),
            'logo' => $request->getQueryParam('logo'),
            'logoSize' => $request->getQueryParam('logoSize'),
            'errorCorrection' => $request->getQueryParam('errorCorrection'),
        ];

        // Remove null values
        $options = array_filter($options, fn($value) => $value !== null);

        // Generate QR code
        try {
            $qrCode = SmartLinks::$plugin->qrCode->generateQrCode($fullUrl, $options);
            
            // Determine content type
            $format = $options['format'] ?? SmartLinks::$plugin->getSettings()->defaultQrFormat;
            $contentType = $format === 'svg' ? 'image/svg+xml' : 'image/png';
            
            // Return response
            $response = Craft::$app->response;
            $response->format = Response::FORMAT_RAW;
            $response->headers->set('Content-Type', $contentType);
            $response->headers->set('Cache-Control', 'public, max-age=86400'); // Cache for 1 day
            
            // Handle download request
            if ($request->getQueryParam('download') && $smartLink) {
                $settings = SmartLinks::$plugin->getSettings();
                $filename = strtr($settings->qrDownloadFilename, [
                    '{slug}' => $smartLink->slug,
                    '{size}' => $options['size'] ?? $settings->defaultQrSize,
                    '{format}' => $format,
                ]);
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.' . $format . '"');
            }
            
            $response->content = $qrCode;
            
            return $response;
        } catch (\Exception $e) {
            Craft::error('Failed to generate QR code: ' . $e->getMessage(), __METHOD__);
            throw new NotFoundHttpException('Failed to generate QR code.');
        }
    }
}