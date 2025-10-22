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
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Client\Browser;
use DeviceDetector\Parser\OperatingSystem;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use lindemannrock\smartlinks\elements\SmartLink;
use lindemannrock\smartlinks\models\DeviceInfo;
use lindemannrock\smartlinks\SmartLinks;

/**
 * Device Detection Service
 */
class DeviceDetectionService extends Component
{
    /**
     * @var DeviceDetector|null
     */
    private ?DeviceDetector $_detector = null;

    /**
     * Detect device information from user agent
     *
     * @param string|null $userAgent
     * @return DeviceInfo
     */
    public function detectDevice(?string $userAgent = null): DeviceInfo
    {
        $settings = SmartLinks::$plugin->getSettings();
        
        // Try to get from cache if enabled
        if ($settings->cacheDeviceDetection && $userAgent) {
            $cached = $this->_getCachedDeviceInfo($userAgent);

            if ($cached !== null) {
                $deviceInfo = new DeviceInfo();
                $deviceInfo->setAttributes($cached, false);
                return $deviceInfo;
            }
        }
        
        $detector = $this->_getDetector();
        
        if ($userAgent) {
            $detector->setUserAgent($userAgent);
        } else {
            $userAgent = Craft::$app->getRequest()->getUserAgent() ?? '';
            $detector->setUserAgent($userAgent);
        }
        
        $detector->parse();
        
        $deviceInfo = new DeviceInfo();
        $deviceInfo->userAgent = $userAgent;
        
        // Check if it's a bot
        if ($detector->isBot()) {
            $deviceInfo->isBot = true;
            $botInfo = $detector->getBot();
            $deviceInfo->botName = $botInfo['name'] ?? null;
            $deviceInfo->platform = 'other';
            return $deviceInfo;
        }
        
        // Get device type
        $deviceType = $detector->getDeviceName();
        $deviceInfo->deviceType = strtolower($deviceType ?: 'desktop');
        
        // Set device booleans based on Matomo detection
        $deviceInfo->isMobile = $detector->isMobile();
        $deviceInfo->isTablet = $detector->isTablet();
        $deviceInfo->isDesktop = $detector->isDesktop();
        
        // Additional device type checks
        $deviceInfo->isMobileApp = $detector->isMobileApp();
        
        // Get brand and model
        $deviceInfo->brand = $detector->getBrandName() ?: null;
        $deviceInfo->model = $detector->getModel() ?: null;
        
        // Get OS information
        $osInfo = $detector->getOs();
        if ($osInfo) {
            $deviceInfo->osName = $osInfo['name'] ?? null;
            $deviceInfo->osVersion = $osInfo['version'] ?? null;
            
            // Determine platform based on OS
            $osName = strtolower($osInfo['name'] ?? '');
            if (str_contains($osName, 'ios') || str_contains($osName, 'iphone') || str_contains($osName, 'ipad')) {
                $deviceInfo->platform = 'ios';
                $deviceInfo->vendor = 'Apple';
            } elseif (str_contains($osName, 'android')) {
                // Check for Huawei/HarmonyOS
                $ua = strtolower($userAgent);
                if (str_contains($ua, 'harmonyos') || str_contains($ua, 'huawei') || str_contains($ua, 'honor')) {
                    $deviceInfo->platform = 'huawei';
                    $deviceInfo->vendor = 'Huawei';
                } else {
                    $deviceInfo->platform = 'android';
                }
            } elseif (str_contains($osName, 'windows phone')) {
                $deviceInfo->platform = 'windows';
                $deviceInfo->vendor = 'Microsoft';
            } elseif (str_contains($osName, 'windows')) {
                $deviceInfo->platform = 'windows';
            } elseif (str_contains($osName, 'mac') || str_contains($osName, 'os x')) {
                $deviceInfo->platform = 'macos';
                $deviceInfo->vendor = 'Apple';
            } elseif (str_contains($osName, 'linux') || str_contains($osName, 'ubuntu')) {
                $deviceInfo->platform = 'linux';
            } else {
                $deviceInfo->platform = 'other';
            }
        }
        
        // Get client/browser information
        $clientInfo = $detector->getClient();
        if ($clientInfo) {
            $deviceInfo->clientType = $clientInfo['type'] ?? null;
            $deviceInfo->browser = $clientInfo['name'] ?? null;
            $deviceInfo->browserVersion = $clientInfo['version'] ?? null;
            $deviceInfo->browserEngine = $clientInfo['engine'] ?? null;
        }
        
        // Set vendor if not already set
        if (!$deviceInfo->vendor && $deviceInfo->brand) {
            $deviceInfo->vendor = $deviceInfo->brand;
        }
        
        // Get language from Accept-Language header
        $deviceInfo->language = $this->detectLanguage();
        
        // Cache the result if enabled
        if ($settings->cacheDeviceDetection && $userAgent) {
            $this->_cacheDeviceInfo($userAgent, $deviceInfo->toArray(), $settings->deviceDetectionCacheDuration);
        }
        
        return $deviceInfo;
    }

    /**
     * Get redirect URL based on device and smart link configuration
     *
     * @param SmartLink $smartLink
     * @param DeviceInfo $deviceInfo
     * @param string|null $language
     * @return string
     */
    public function getRedirectUrl(SmartLink $smartLink, DeviceInfo $deviceInfo, ?string $language = null): string
    {
        // Check for localized URLs first
        if ($language && $smartLink->localizedUrls) {
            $localizedUrls = $smartLink->localizedUrls[$language] ?? null;
            if ($localizedUrls) {
                return $this->_getUrlForPlatform($deviceInfo->platform, $localizedUrls, $smartLink);
            }
        }
        
        // Use default URLs
        return $this->_getUrlForPlatform($deviceInfo->platform, [
            'iosUrl' => $smartLink->iosUrl,
            'androidUrl' => $smartLink->androidUrl,
            'huaweiUrl' => $smartLink->huaweiUrl,
            'amazonUrl' => $smartLink->amazonUrl,
            'windowsUrl' => $smartLink->windowsUrl,
            'macUrl' => $smartLink->macUrl,
            'fallbackUrl' => $smartLink->fallbackUrl,
        ], $smartLink);
    }

    /**
     * Detect language from request
     *
     * @return string
     */
    public function detectLanguage(): string
    {
        $settings = SmartLinks::$plugin->getSettings();
        $request = Craft::$app->getRequest();
        $detectedLang = null;
        
        // Always check URL parameter first (highest priority)
        $langParam = $request->getQueryParam('lang') ?? $request->getQueryParam('locale');
        if ($langParam) {
            $detectedLang = substr($langParam, 0, 2);
        }
        
        // Apply detection method from settings
        if (!$detectedLang) {
            switch ($settings->languageDetectionMethod) {
                case 'browser':
                    $detectedLang = $this->_detectFromBrowser();
                    break;
                    
                case 'ip':
                    if ($settings->enableGeoDetection) {
                        $detectedLang = $this->_detectFromIp();
                    }
                    break;
                    
                case 'both':
                    // Try browser first, then IP
                    $detectedLang = $this->_detectFromBrowser();
                    if (!$detectedLang && $settings->enableGeoDetection) {
                        $detectedLang = $this->_detectFromIp();
                    }
                    break;
            }
        }
        
        // Default to site language if nothing detected
        if (!$detectedLang) {
            $detectedLang = substr(Craft::$app->language, 0, 2);
        }
        
        // Validate against site languages
        $supportedLanguages = [];
        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $supportedLanguages[] = substr($site->language, 0, 2);
        }
        $supportedLanguages = array_unique($supportedLanguages);
        
        if (!in_array($detectedLang, $supportedLanguages)) {
            // Default to primary site language
            $detectedLang = substr(Craft::$app->getSites()->getPrimarySite()->language, 0, 2);
        }
        
        return $detectedLang;
    }
    
    /**
     * Detect language from browser headers
     */
    private function _detectFromBrowser(): ?string
    {
        $acceptLanguage = Craft::$app->getRequest()->getHeaders()->get('Accept-Language');
        if ($acceptLanguage) {
            // Parse Accept-Language header
            $languages = [];
            $parts = explode(',', $acceptLanguage);
            foreach ($parts as $part) {
                $lang = explode(';', $part);
                $code = substr(trim($lang[0]), 0, 2);
                $quality = isset($lang[1]) ? (float) str_replace('q=', '', $lang[1]) : 1.0;
                $languages[$code] = $quality;
            }
            
            // Sort by quality
            arsort($languages);
            return array_key_first($languages);
        }
        
        return null;
    }
    
    /**
     * Detect language from IP geolocation
     */
    private function _detectFromIp(): ?string
    {
        // Get IP address
        $ip = Craft::$app->getRequest()->getUserIP();
        if (!$ip) {
            return null;
        }
        
        // Get location from analytics service
        $location = SmartLinks::$plugin->analytics->getLocationFromIp($ip);
        if ($location && isset($location['countryCode'])) {
            // Map common country codes to languages
            $countryToLang = [
                'SA' => 'ar', // Saudi Arabia
                'AE' => 'ar', // UAE
                'KW' => 'ar', // Kuwait
                'QA' => 'ar', // Qatar
                'BH' => 'ar', // Bahrain
                'OM' => 'ar', // Oman
                'EG' => 'ar', // Egypt
                'JO' => 'ar', // Jordan
                'LB' => 'ar', // Lebanon
                'IQ' => 'ar', // Iraq
                'SY' => 'ar', // Syria
                'YE' => 'ar', // Yemen
                'LY' => 'ar', // Libya
                'TN' => 'ar', // Tunisia
                'DZ' => 'ar', // Algeria
                'MA' => 'ar', // Morocco
                'US' => 'en', // United States
                'GB' => 'en', // United Kingdom
                'CA' => 'en', // Canada
                'AU' => 'en', // Australia
                'NZ' => 'en', // New Zealand
                'IE' => 'en', // Ireland
                // Add more mappings as needed
            ];
            
            return $countryToLang[$location['countryCode']] ?? null;
        }
        
        return null;
    }

    /**
     * Check if device is mobile
     *
     * @param DeviceInfo $deviceInfo
     * @return bool
     */
    public function isMobileDevice(DeviceInfo $deviceInfo): bool
    {
        return $deviceInfo->isMobile || $deviceInfo->isTablet;
    }

    /**
     * Get app store name for platform
     *
     * @param string $platform
     * @return string
     */
    public function getAppStoreName(string $platform): string
    {
        return match ($platform) {
            'ios' => 'App Store',
            'android' => 'Google Play',
            'huawei' => 'AppGallery',
            'amazon' => 'Amazon Appstore',
            'windows' => 'Microsoft Store',
            default => 'App Store',
        };
    }

    /**
     * Get DeviceDetector instance
     *
     * @return DeviceDetector
     */
    private function _getDetector(): DeviceDetector
    {
        if ($this->_detector === null) {
            $this->_detector = new DeviceDetector();
        }
        
        return $this->_detector;
    }

    /**
     * Get URL for specific platform
     *
     * @param string $platform
     * @param array $urls
     * @param SmartLink $smartLink
     * @return string
     */
    private function _getUrlForPlatform(string $platform, array $urls, SmartLink $smartLink): string
    {
        switch ($platform) {
            case 'ios':
                return $urls['iosUrl'] ?? '';

            case 'huawei':
                // Try Huawei first, then fallback to Android URL
                return $urls['huaweiUrl'] ?? $urls['androidUrl'] ?? '';

            case 'android':
                // Check if it's Amazon device
                $ua = strtolower(Craft::$app->getRequest()->getUserAgent() ?? '');
                if (strpos($ua, 'kindle') !== false || strpos($ua, 'silk') !== false) {
                    return $urls['amazonUrl'] ?? $urls['androidUrl'] ?? '';
                }
                return $urls['androidUrl'] ?? '';

            case 'windows':
                return $urls['windowsUrl'] ?? '';

            case 'macos':
                return $urls['macUrl'] ?? '';

            default:
                // Unknown platform - return empty, show landing page
                return '';
        }
    }

    /**
     * Get cached device info from custom file storage
     *
     * @param string $userAgent
     * @return array|null
     */
    private function _getCachedDeviceInfo(string $userAgent): ?array
    {
        $cachePath = Craft::$app->path->getRuntimePath() . '/smart-links/cache/device/';
        $cacheFile = $cachePath . md5($userAgent) . '.cache';

        if (!file_exists($cacheFile)) {
            return null;
        }

        // Check if cache is expired
        $mtime = filemtime($cacheFile);
        $settings = SmartLinks::$plugin->getSettings();
        if (time() - $mtime > $settings->deviceDetectionCacheDuration) {
            @unlink($cacheFile);
            return null;
        }

        $data = file_get_contents($cacheFile);
        return unserialize($data);
    }

    /**
     * Cache device info to custom file storage
     *
     * @param string $userAgent
     * @param array $data
     * @param int $duration
     * @return void
     */
    private function _cacheDeviceInfo(string $userAgent, array $data, int $duration): void
    {
        $cachePath = Craft::$app->path->getRuntimePath() . '/smart-links/cache/device/';

        // Create directory if it doesn't exist
        if (!is_dir($cachePath)) {
            \craft\helpers\FileHelper::createDirectory($cachePath);
        }

        $cacheFile = $cachePath . md5($userAgent) . '.cache';
        file_put_contents($cacheFile, serialize($data));
    }
}