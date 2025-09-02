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
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use lindemannrock\smartlinks\elements\SmartLink;
use lindemannrock\smartlinks\models\DeviceInfo;
use lindemannrock\smartlinks\records\AnalyticsRecord;
use lindemannrock\smartlinks\SmartLinks;

/**
 * Analytics Service
 */
class AnalyticsService extends Component
{
    /**
     * Get analytics summary
     *
     * @param string $dateRange
     * @param int|null $smartLinkId
     * @return array
     */
    public function getAnalyticsSummary(string $dateRange = 'last7days', ?int $smartLinkId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}');
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $totalClicks = (int) $query->count();
        $uniqueVisitors = (int) $query->select('COUNT(DISTINCT ip)')->scalar();
        
        // Get active links count
        $activeLinks = SmartLink::find()
            ->status(SmartLink::STATUS_ENABLED)
            ->count();
        
        // Get total links
        $totalLinks = SmartLink::find()->count();
        
        // Get count of ACTIVE links that have been clicked in this period
        $linksQuery = (new Query())
            ->from('{{%smartlinks_analytics}} a')
            ->innerJoin('{{%smartlinks}} s', 'a.linkId = s.id')
            ->innerJoin('{{%elements}} e', 's.id = e.id')
            ->select('COUNT(DISTINCT a.linkId)')
            ->where(['e.enabled' => true]);
        
        // Apply date filter to analytics table
        $this->applyDateRangeFilter($linksQuery, $dateRange, 'a.dateCreated');
        
        $linksWithClicks = (int) $linksQuery->scalar();
        
        // Calculate what percentage of active links have been used
        // Cap at 100% to avoid confusion
        $linksUsedPercentage = $activeLinks > 0 ? min(100, round(($linksWithClicks / $activeLinks) * 100, 0)) : 0;
        
        return [
            'totalClicks' => $totalClicks,
            'uniqueVisitors' => $uniqueVisitors,
            'activeLinks' => $activeLinks,
            'totalLinks' => $totalLinks,
            'linksUsed' => $linksWithClicks,
            'linksUsedPercentage' => $linksUsedPercentage,
            'topLinks' => $this->getTopLinks($dateRange),
            'topCountries' => $this->getTopCountries(null, $dateRange),
            'topCities' => $this->getTopCities(null, $dateRange),
            'recentClicks' => $this->getAllRecentClicks($dateRange, 20),
        ];
    }
    
    /**
     * Get analytics for a specific smart link
     *
     * @param int $smartLinkId
     * @param string $dateRange
     * @return array
     */
    public function getSmartLinkAnalytics(int $smartLinkId, string $dateRange = 'last7days'): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->where(['linkId' => $smartLinkId]);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Get total and unique clicks
        $totalClicks = (int) $query->count();
        $uniqueClicks = (int) (clone $query)->select('COUNT(DISTINCT ip)')->scalar();
        
        // Get device breakdown
        $deviceResults = (clone $query)
            ->select(['deviceType', 'COUNT(*) as count'])
            ->groupBy('deviceType')
            ->all();
        
        $deviceBreakdown = [];
        foreach ($deviceResults as $row) {
            if (!empty($row['deviceType'])) {
                $deviceBreakdown[$row['deviceType']] = (int) $row['count'];
            }
        }
        
        // Get OS breakdown (replacing platform)
        $osResults = (clone $query)
            ->select(['osName', 'COUNT(*) as count'])
            ->groupBy('osName')
            ->all();
        
        $platformBreakdown = [];
        foreach ($osResults as $row) {
            if (!empty($row['osName'])) {
                $platformBreakdown[$row['osName']] = (int) $row['count'];
            }
        }
        
        // Get button clicks breakdown
        $buttonClicks = $this->getButtonClicks($smartLinkId, $dateRange);
        
        // Calculate average clicks per day
        $days = 1;
        $startDate = $this->getStartDateForRange($dateRange);
        if ($startDate) {
            $start = new \DateTime($startDate);
            $end = new \DateTime();
            $interval = $start->diff($end);
            $days = max(1, $interval->days + 1);
        }
        $averageClicksPerDay = $totalClicks / $days;
        
        return [
            'totalClicks' => $totalClicks,
            'uniqueClicks' => $uniqueClicks,
            'averageClicksPerDay' => $averageClicksPerDay,
            'deviceBreakdown' => $deviceBreakdown,
            'platformBreakdown' => $platformBreakdown,
            'buttonClicks' => $buttonClicks,
        ];
    }
    
    /**
     * Get recent clicks for a smart link
     *
     * @param int $smartLinkId
     * @param int $limit
     * @return array
     */
    public function getRecentClicks(int $smartLinkId, int $limit = 20): array
    {
        $results = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->where(['linkId' => $smartLinkId])
            ->orderBy('dateCreated DESC')
            ->limit($limit)
            ->all();
        
        // Convert dateCreated to DateTime objects for consistent timezone handling
        foreach ($results as &$result) {
            if (isset($result['dateCreated'])) {
                $result['dateCreated'] = DateTimeHelper::toDateTime($result['dateCreated']);
            }
        }
        
        return $results;
    }
    
    /**
     * Get button click analytics
     *
     * @param int $smartLinkId
     * @param string $dateRange
     * @return array
     */
    public function getButtonClicks(int $smartLinkId, string $dateRange = 'last7days'): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->where(['linkId' => $smartLinkId])
            ->andWhere(['like', 'metadata', '"clickType":"button"']);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Get all button click records
        $records = $query->all();
        
        // Parse platform data from metadata
        $platformCounts = [];
        $totalButtonClicks = 0;
        
        foreach ($records as $record) {
            $metadata = Json::decodeIfJson($record['metadata']);
            if (isset($metadata['platform'])) {
                $platform = $metadata['platform'];
                if (!isset($platformCounts[$platform])) {
                    $platformCounts[$platform] = 0;
                }
                $platformCounts[$platform]++;
                $totalButtonClicks++;
            }
        }
        
        // Sort by count descending
        arsort($platformCounts);
        
        return [
            'total' => $totalButtonClicks,
            'byPlatform' => $platformCounts,
        ];
    }
    
    /**
     * Get start date for date range
     *
     * @param string $range
     * @return string|null
     */
    private function getStartDateForRange(string $range): ?string
    {
        $date = null;
        
        switch ($range) {
            case 'today':
                $date = DateTimeHelper::now()->setTime(0, 0, 0);
                break;
            case 'yesterday':
                $date = DateTimeHelper::now()->modify('-1 day')->setTime(0, 0, 0);
                break;
            case 'last7days':
                $date = DateTimeHelper::now()->modify('-7 days');
                break;
            case 'last30days':
                $date = DateTimeHelper::now()->modify('-30 days');
                break;
            case 'last90days':
                $date = DateTimeHelper::now()->modify('-90 days');
                break;
            case 'all':
            case 'alltime':
            default:
                return null;
        }
        
        return $date ? Db::prepareDateForDb($date) : null;
    }
    
    /**
     * Get end date for date range (for specific day filtering)
     *
     * @param string $range
     * @return string|null
     */
    private function getEndDateForRange(string $range): ?string
    {
        $date = null;
        
        switch ($range) {
            case 'today':
                $date = DateTimeHelper::now()->setTime(23, 59, 59);
                break;
            case 'yesterday':
                $date = DateTimeHelper::now()->modify('-1 day')->setTime(23, 59, 59);
                break;
            default:
                return null;
        }
        
        return $date ? Db::prepareDateForDb($date) : null;
    }
    
    /**
     * Apply date range filter to query
     *
     * @param Query $query
     * @param string $dateRange
     * @param string $dateColumn
     * @return Query
     */
    private function applyDateRangeFilter(Query $query, string $dateRange, string $dateColumn = 'dateCreated'): Query
    {
        $startDate = $this->getStartDateForRange($dateRange);
        $endDate = $this->getEndDateForRange($dateRange);
        
        if ($startDate) {
            $query->andWhere(['>=', $dateColumn, $startDate]);
        }
        
        if ($endDate) {
            $query->andWhere(['<=', $dateColumn, $endDate]);
        }
        
        return $query;
    }
    
    /**
     * Get clicks data for charts
     */
    public function getClicksData(?int $smartLinkId, string $dateRange): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select(['DATE(dateCreated) as date', 'COUNT(*) as count'])
            ->groupBy(['DATE(dateCreated)'])
            ->orderBy(['date' => SORT_ASC]);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $results = $query->all();
        
        $labels = [];
        $values = [];
        
        foreach ($results as $row) {
            $labels[] = date('M j', strtotime($row['date']));
            $values[] = (int)$row['count'];
        }
        
        // Get the actual date range boundaries
        $startDate = $this->getStartDateForRange($dateRange);
        $endDate = $this->getEndDateForRange($dateRange);
        
        // Determine the date range
        if ($startDate) {
            $startTimestamp = strtotime($startDate);
        } else {
            // For ranges like "all", use the first data point or 30 days ago as fallback
            $startTimestamp = !empty($results) ? strtotime($results[0]['date']) : strtotime('-30 days');
        }
        
        if ($endDate) {
            $endTimestamp = strtotime($endDate);
        } else {
            // For open-ended ranges, use today
            $endTimestamp = strtotime('today 23:59:59');
        }
        
        // Create a map of existing data
        $dataMap = [];
        foreach ($results as $row) {
            $dataMap[date('M j', strtotime($row['date']))] = (int)$row['count'];
        }
        
        // Fill in all dates in the range
        $filledLabels = [];
        $filledValues = [];
        
        for ($timestamp = $startTimestamp; $timestamp <= $endTimestamp; $timestamp += 86400) {
            $label = date('M j', $timestamp);
            $filledLabels[] = $label;
            $filledValues[] = $dataMap[$label] ?? 0;
        }
        
        return [
            'labels' => $filledLabels,
            'values' => $filledValues
        ];
    }
    
    /**
     * Get device breakdown (mobile, tablet, desktop)
     */
    public function getDeviceBreakdown(?int $smartLinkId, string $dateRange): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select(['deviceType', 'COUNT(*) as count'])
            ->groupBy(['deviceType']);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $results = $query->all();
        
        $labels = [];
        $values = [];
        
        foreach ($results as $row) {
            $labels[] = ucfirst($row['deviceType']);
            $values[] = (int)$row['count'];
        }
        
        // Return empty data if no analytics exist
        if (empty($labels)) {
            return [
                'labels' => ['No data yet'],
                'values' => [1] // Show empty state
            ];
        }
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
    
    /**
     * Get platform breakdown (iOS, Android, Windows, macOS, Linux)
     */
    public function getPlatformBreakdown(?int $smartLinkId, string $dateRange): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select(['osName', 'COUNT(*) as count'])
            ->groupBy(['osName']);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $results = $query->all();
        
        $labels = [];
        $values = [];
        
        // Map platform names to more user-friendly labels
        $platformLabels = [
            'ios' => 'iOS',
            'android' => 'Android',
            'windows' => 'Windows',
            'macos' => 'macOS',
            'linux' => 'Linux',
            'huawei' => 'HarmonyOS',
            'other' => 'Other'
        ];
        
        foreach ($results as $row) {
            $osName = strtolower($row['osName'] ?? '');
            $labels[] = $platformLabels[$osName] ?? ucfirst($row['osName'] ?? 'Unknown');
            $values[] = (int)$row['count'];
        }
        
        // Return empty data if no analytics exist
        if (empty($labels)) {
            return [
                'labels' => ['No data yet'],
                'values' => [1] // Show empty state
            ];
        }
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
    
    /**
     * Get top countries
     */
    public function getTopCountries(?int $smartLinkId, string $dateRange): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select(['country', 'COUNT(*) as clicks'])
            ->where(['not', ['country' => null]])
            ->groupBy(['country'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit(15);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));
        $countries = [];
        
        foreach ($results as $row) {
            $countries[] = [
                'code' => $row['country'],
                'name' => $this->_getCountryName($row['country']),
                'clicks' => (int)$row['clicks'],
                'percentage' => $totalClicks > 0 ? round(($row['clicks'] / $totalClicks) * 100, 1) : 0,
            ];
        }
        
        return $countries;
    }
    
    /**
     * Get all countries (no limit)
     */
    public function getAllCountries(?int $smartLinkId, string $dateRange): array
    {
        return $this->getTopCountries($smartLinkId, $dateRange, 9999);
    }
    
    /**
     * Get top cities
     */
    public function getTopCities(?int $smartLinkId, string $dateRange, int $limit = 15): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select(['city', 'country', 'COUNT(*) as clicks'])
            ->where(['not', ['city' => null]])
            ->groupBy(['city', 'country'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit($limit);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));
        $cities = [];
        
        foreach ($results as $row) {
            $cities[] = [
                'city' => $row['city'],
                'country' => $row['country'],
                'countryName' => $this->_getCountryName($row['country']),
                'clicks' => (int)$row['clicks'],
                'percentage' => $totalClicks > 0 ? round(($row['clicks'] / $totalClicks) * 100, 1) : 0,
            ];
        }
        
        return $cities;
    }
    
    /**
     * Get hourly analytics for peak usage times
     */
    public function getHourlyAnalytics(?int $smartLinkId, string $dateRange): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select([
                'HOUR(dateCreated) as hour',
                'COUNT(*) as clicks'
            ])
            ->groupBy(['hour'])
            ->orderBy(['hour' => SORT_ASC]);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $results = $query->all();
        
        // Initialize all hours with 0
        $hourlyData = array_fill(0, 24, 0);
        
        foreach ($results as $row) {
            $hourlyData[(int)$row['hour']] = (int)$row['clicks'];
        }
        
        // Find peak hour
        $peakHour = array_search(max($hourlyData), $hourlyData);
        
        return [
            'data' => $hourlyData,
            'peakHour' => $peakHour,
            'peakHourFormatted' => date('g A', strtotime("{$peakHour}:00")),
        ];
    }
    
    /**
     * Get insights (cross-referenced analytics)
     */
    public function getInsights(string $dateRange): array
    {
        $insights = [];
        
        // Mobile usage by top cities
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select([
                'city',
                'SUM(CASE WHEN osName IN ("iOS", "Android") THEN 1 ELSE 0 END) as mobile_clicks',
                'COUNT(*) as total_clicks'
            ])
            ->where(['not', ['city' => null]])
            ->groupBy(['city'])
            ->having(['>', 'total_clicks', 10]) // Only cities with significant traffic
            ->orderBy(['total_clicks' => SORT_DESC])
            ->limit(5);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        $cityMobileUsage = [];
        foreach ($query->all() as $row) {
            $mobilePercentage = round(($row['mobile_clicks'] / $row['total_clicks']) * 100, 1);
            $cityMobileUsage[] = [
                'city' => $row['city'],
                'mobilePercentage' => $mobilePercentage,
                'totalClicks' => (int)$row['total_clicks'],
            ];
        }
        
        // Browser usage by country
        $browserByCountry = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select([
                'country',
                'browser',
                'COUNT(*) as clicks'
            ])
            ->where(['not', ['country' => null]])
            ->andWhere(['not', ['browser' => null]])
            ->groupBy(['country', 'browser'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit(10);
        
        // Apply date range filter
        $this->applyDateRangeFilter($browserByCountry, $dateRange);
        
        $browserData = [];
        foreach ($browserByCountry->all() as $row) {
            $countryName = $this->_getCountryName($row['country']);
            if (!isset($browserData[$countryName])) {
                $browserData[$countryName] = [];
            }
            $browserData[$countryName][] = [
                'browser' => $row['browser'],
                'clicks' => (int)$row['clicks'],
            ];
        }
        
        // Device brands by country
        $brandsByCountry = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select([
                'country',
                'deviceBrand',
                'COUNT(*) as clicks'
            ])
            ->where(['not', ['country' => null]])
            ->andWhere(['not', ['deviceBrand' => null]])
            ->groupBy(['country', 'deviceBrand'])
            ->orderBy(['clicks' => SORT_DESC]);
        
        // Apply date range filter
        $this->applyDateRangeFilter($brandsByCountry, $dateRange);
        
        $brandsData = [];
        foreach ($brandsByCountry->all() as $row) {
            $countryName = $this->_getCountryName($row['country']);
            if (!isset($brandsData[$countryName])) {
                $brandsData[$countryName] = [];
            }
            // Only keep top 3 brands per country
            if (count($brandsData[$countryName]) < 3) {
                $brandsData[$countryName][] = [
                    'brand' => $row['deviceBrand'],
                    'clicks' => (int)$row['clicks'],
                ];
            }
        }
        
        return [
            'cityMobileUsage' => $cityMobileUsage,
            'browserByCountry' => $browserData,
            'brandsByCountry' => $brandsData,
        ];
    }
    
    /**
     * Get language breakdown
     */
    public function getLanguageBreakdown(?int $smartLinkId, string $dateRange): array
    {
        return [
            'labels' => [],
            'values' => []
        ];
    }
    
    /**
     * Get device brand breakdown
     */
    public function getDeviceBrandBreakdown(?int $smartLinkId, string $dateRange): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select([
                'deviceBrand',
                'COUNT(*) as clicks'
            ])
            ->where(['not', ['deviceBrand' => null]])
            ->andWhere(['not', ['deviceBrand' => '']])
            ->groupBy(['deviceBrand'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit(10);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));
        
        $labels = [];
        $values = [];
        $percentages = [];
        
        foreach ($results as $row) {
            $labels[] = $row['deviceBrand'] ?: 'Unknown';
            $values[] = (int)$row['clicks'];
            $percentages[] = $totalClicks > 0 ? round(($row['clicks'] / $totalClicks) * 100, 1) : 0;
        }
        
        return [
            'labels' => $labels,
            'values' => $values,
            'percentages' => $percentages,
            'totalClicks' => $totalClicks,
        ];
    }
    
    /**
     * Get OS breakdown with versions
     */
    public function getOsBreakdown(?int $smartLinkId, string $dateRange): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select([
                'osName',
                'osVersion',
                'COUNT(*) as clicks'
            ])
            ->where(['not', ['osName' => null]])
            ->andWhere(['not', ['osName' => '']])
            ->groupBy(['osName', 'osVersion'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit(15);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));
        
        // Group by OS name first
        $osData = [];
        foreach ($results as $row) {
            $osName = $row['osName'] ?: 'Unknown';
            if (!isset($osData[$osName])) {
                $osData[$osName] = [
                    'name' => $osName,
                    'totalClicks' => 0,
                    'versions' => []
                ];
            }
            
            $osData[$osName]['totalClicks'] += (int)$row['clicks'];
            
            if ($row['osVersion']) {
                $osData[$osName]['versions'][] = [
                    'version' => $row['osVersion'],
                    'clicks' => (int)$row['clicks']
                ];
            }
        }
        
        // Sort by total clicks and prepare final data
        uasort($osData, function($a, $b) {
            return $b['totalClicks'] - $a['totalClicks'];
        });
        
        $labels = [];
        $values = [];
        $details = [];
        
        foreach ($osData as $os) {
            $labels[] = $os['name'];
            $values[] = $os['totalClicks'];
            $details[] = [
                'name' => $os['name'],
                'clicks' => $os['totalClicks'],
                'percentage' => $totalClicks > 0 ? round(($os['totalClicks'] / $totalClicks) * 100, 1) : 0,
                'versions' => array_slice($os['versions'], 0, 5) // Top 5 versions per OS
            ];
        }
        
        return [
            'labels' => $labels,
            'values' => $values,
            'details' => $details,
            'totalClicks' => $totalClicks,
        ];
    }
    
    /**
     * Get browser breakdown with versions
     */
    public function getBrowserBreakdown(?int $smartLinkId, string $dateRange): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select([
                'browser',
                'browserVersion',
                'COUNT(*) as clicks'
            ])
            ->where(['not', ['browser' => null]])
            ->andWhere(['not', ['browser' => '']])
            ->groupBy(['browser', 'browserVersion'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit(20);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));
        
        // Group by browser name first
        $browserData = [];
        foreach ($results as $row) {
            $browserName = $row['browser'] ?: 'Unknown';
            if (!isset($browserData[$browserName])) {
                $browserData[$browserName] = [
                    'name' => $browserName,
                    'totalClicks' => 0,
                    'versions' => []
                ];
            }
            
            $browserData[$browserName]['totalClicks'] += (int)$row['clicks'];
            
            if ($row['browserVersion']) {
                // Simplify version (e.g., "102.0.5005.124" -> "102.0")
                $versionParts = explode('.', $row['browserVersion']);
                $simplifiedVersion = count($versionParts) >= 2 
                    ? $versionParts[0] . '.' . $versionParts[1] 
                    : $row['browserVersion'];
                
                if (!isset($browserData[$browserName]['versions'][$simplifiedVersion])) {
                    $browserData[$browserName]['versions'][$simplifiedVersion] = 0;
                }
                $browserData[$browserName]['versions'][$simplifiedVersion] += (int)$row['clicks'];
            }
        }
        
        // Sort by total clicks and prepare final data
        uasort($browserData, function($a, $b) {
            return $b['totalClicks'] - $a['totalClicks'];
        });
        
        $labels = [];
        $values = [];
        $details = [];
        
        foreach ($browserData as $browser) {
            $labels[] = $browser['name'];
            $values[] = $browser['totalClicks'];
            
            // Sort versions by clicks
            arsort($browser['versions']);
            $versions = [];
            foreach (array_slice($browser['versions'], 0, 5, true) as $version => $clicks) {
                $versions[] = [
                    'version' => $version,
                    'clicks' => $clicks
                ];
            }
            
            $details[] = [
                'name' => $browser['name'],
                'clicks' => $browser['totalClicks'],
                'percentage' => $totalClicks > 0 ? round(($browser['totalClicks'] / $totalClicks) * 100, 1) : 0,
                'versions' => $versions
            ];
        }
        
        return [
            'labels' => $labels,
            'values' => $values,
            'details' => $details,
            'totalClicks' => $totalClicks,
        ];
    }
    
    /**
     * Get detailed device type breakdown
     */
    public function getDeviceTypeBreakdown(?int $smartLinkId, string $dateRange): array
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select([
                'deviceType',
                'COUNT(*) as clicks'
            ])
            ->where(['not', ['deviceType' => null]])
            ->andWhere(['not', ['deviceType' => '']])
            ->groupBy(['deviceType'])
            ->orderBy(['clicks' => SORT_DESC]);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));
        
        // Map device types to friendly names and categories
        $deviceTypeMap = [
            'smartphone' => ['name' => 'Smartphone', 'category' => 'mobile'],
            'tablet' => ['name' => 'Tablet', 'category' => 'mobile'],
            'phablet' => ['name' => 'Phablet', 'category' => 'mobile'],
            'feature phone' => ['name' => 'Feature Phone', 'category' => 'mobile'],
            'console' => ['name' => 'Game Console', 'category' => 'other'],
            'tv' => ['name' => 'Smart TV', 'category' => 'other'],
            'car browser' => ['name' => 'Car Browser', 'category' => 'other'],
            'smart display' => ['name' => 'Smart Display', 'category' => 'other'],
            'camera' => ['name' => 'Camera', 'category' => 'other'],
            'portable media player' => ['name' => 'Media Player', 'category' => 'other'],
            'desktop' => ['name' => 'Desktop', 'category' => 'desktop'],
            'unknown' => ['name' => 'Unknown', 'category' => 'unknown'],
        ];
        
        $labels = [];
        $values = [];
        $categories = [
            'mobile' => 0,
            'desktop' => 0,
            'other' => 0,
            'unknown' => 0
        ];
        
        foreach ($results as $row) {
            $deviceType = strtolower($row['deviceType'] ?: 'unknown');
            $deviceInfo = $deviceTypeMap[$deviceType] ?? ['name' => ucfirst($deviceType), 'category' => 'other'];
            
            $labels[] = $deviceInfo['name'];
            $values[] = (int)$row['clicks'];
            $categories[$deviceInfo['category']] += (int)$row['clicks'];
        }
        
        // Calculate percentages for categories
        $categoryPercentages = [];
        foreach ($categories as $category => $clicks) {
            $categoryPercentages[$category] = $totalClicks > 0 ? round(($clicks / $totalClicks) * 100, 1) : 0;
        }
        
        return [
            'labels' => $labels,
            'values' => $values,
            'categories' => $categories,
            'categoryPercentages' => $categoryPercentages,
            'totalClicks' => $totalClicks,
        ];
    }
    
    /**
     * Export analytics data
     */
    public function exportAnalytics(?int $smartLinkId, string $dateRange, string $format): string
    {
        $query = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->select([
                'dateCreated',
                'linkId',
                'siteId',
                'deviceType',
                'deviceBrand',
                'deviceModel',
                'osName',
                'osVersion',
                'browser',
                'browserVersion',
                'country',
                'city',
                'language',
                'referrer',
                'metadata',
                'ip',
                'userAgent'
            ])
            ->orderBy(['dateCreated' => SORT_DESC]);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);
        
        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }
        
        $results = $query->all();
        
        // CSV format only
            $csv = "Date,Time,Smart Link Title,Smart Link Status,Smart Link URL,Site,Type,Button,Source,Destination URL,Referrer,User Device Type,User Device Brand,User Device Model,User OS,User OS Version,User Browser,User Browser Version,User Country,User City,User Language,User Agent\n";
            
            foreach ($results as $row) {
                // Check settings to determine if we should include disabled/expired links
                $settings = SmartLinks::$plugin->getSettings();
                $includeDisabled = $settings->includeDisabledInExport ?? false;
                $includeExpired = $settings->includeExpiredInExport ?? false;
                
                // Always get the link with all statuses to check if it exists and its status
                $smartLink = SmartLink::find()->id($row['linkId'])->status(null)->one();
                
                if (!$smartLink) {
                    continue;
                }
                
                // Get the actual status
                $status = $smartLink->getStatus();
                
                // Skip based on settings
                if (!$includeDisabled && $status === SmartLink::STATUS_DISABLED) {
                    continue;
                }
                
                if (!$includeExpired && $status === SmartLink::STATUS_EXPIRED) {
                    continue;
                }
                
                $linkName = $smartLink->title;
                $linkStatus = match($status) {
                    SmartLink::STATUS_ENABLED => 'Active',
                    SmartLink::STATUS_DISABLED => 'Disabled',
                    SmartLink::STATUS_PENDING => 'Pending',
                    SmartLink::STATUS_EXPIRED => 'Expired',
                    default => 'Unknown'
                };
                $linkUrl = '';
                
                // Get site handle and build the smart link URL
                $siteHandle = '';
                if (!empty($row['siteId'])) {
                    $site = Craft::$app->getSites()->getSiteById($row['siteId']);
                    $siteHandle = $site ? $site->handle : '';
                    if ($smartLink) {
                        // Generate the URL for the specific site
                        $linkUrl = UrlHelper::siteUrl("go/{$smartLink->slug}", null, null, $row['siteId']);
                    }
                }
                
                $date = DateTimeHelper::toDateTime($row['dateCreated']);
                $dateStr = $date ? $date->format('Y-m-d') : '';
                $timeStr = $date ? $date->format('H:i:s') : '';
                
                // Parse metadata
                $metadata = $row['metadata'] ? Json::decode($row['metadata']) : [];
                $source = $metadata['source'] ?? 'direct';
                $clickType = $metadata['clickType'] ?? 'redirect';
                $buttonPlatform = '';
                $targetUrl = '';
                
                // Get the URL that was used
                if ($clickType === 'button') {
                    // For button clicks, show which button URL was clicked
                    $targetUrl = $metadata['buttonUrl'] ?? '';
                    if (isset($metadata['platform'])) {
                        $buttonPlatform = ucfirst($metadata['platform']);
                    }
                } else {
                    // For redirects, show which URL they were sent to
                    $targetUrl = $metadata['redirectUrl'] ?? '';
                }
                
                // Keep the actual referrer URL
                $referrerDisplay = $row['referrer'] ?? '';
                
                // Convert source to display format
                $sourceDisplay = match($source) {
                    'qr' => 'QR',
                    'landing' => 'Landing',
                    default => 'Direct'
                };
                
                $csv .= sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                    $dateStr,
                    $timeStr,
                    $linkName,
                    $linkStatus,
                    $linkUrl,
                    $siteHandle,
                    ucfirst($clickType),
                    $buttonPlatform,
                    $sourceDisplay,
                    $targetUrl,
                    $referrerDisplay,
                    $row['deviceType'] ?? '',
                    $row['deviceBrand'] ?? '',
                    $row['deviceModel'] ?? '',
                    $row['osName'] ?? '',
                    $row['osVersion'] ?? '',
                    $row['browser'] ?? '',
                    $row['browserVersion'] ?? '',
                    $this->_getCountryName($row['country'] ?? ''),
                    $row['city'] ?? '',
                    $row['language'] ?? '',
                    $row['userAgent'] ?? ''
                );
            }
            
            return $csv;
    }
    /**
     * Get top smart links by clicks
     *
     * @param string $dateRange
     * @param int $limit
     * @return array
     */
    public function getTopLinks(string $dateRange = 'last7days', int $limit = 5): array
    {
        $query = (new Query())
            ->from(['a' => '{{%smartlinks_analytics}}'])
            ->select([
                'a.linkId', 
                'COUNT(*) as clicks', 
                'MAX(a.dateCreated) as lastClick',
                'SUM(CASE WHEN JSON_EXTRACT(a.metadata, \'$.source\') = \'qr\' THEN 1 ELSE 0 END) as qrScans',
                'SUM(CASE WHEN JSON_EXTRACT(a.metadata, \'$.source\') != \'qr\' OR JSON_EXTRACT(a.metadata, \'$.source\') IS NULL THEN 1 ELSE 0 END) as directVisits'
            ])
            ->groupBy(['a.linkId'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit($limit);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange, 'a.dateCreated');
        
        $results = $query->all();
        $topLinks = [];
        
        foreach ($results as $row) {
            $smartLink = SmartLink::find()->id($row['linkId'])->one();
            if ($smartLink && $smartLink->enabled) { // Only include active links
                // Get the last interaction details
                $lastInteractionQuery = (new Query())
                    ->from('{{%smartlinks_analytics}}')
                    ->where(['linkId' => $row['linkId']])
                    ->orderBy(['dateCreated' => SORT_DESC]);
                
                // Apply same date range filter
                $this->applyDateRangeFilter($lastInteractionQuery, $dateRange);
                
                $lastInteraction = $lastInteractionQuery->one();
                
                $lastInteractionType = 'Unknown';
                $lastDestinationUrl = '';
                
                if ($lastInteraction && !empty($lastInteraction['metadata'])) {
                    $metadata = Json::decodeIfJson($lastInteraction['metadata']);
                    
                    // Determine interaction type
                    if (isset($metadata['action'])) {
                        $lastInteractionType = $metadata['action'] === 'redirect' ? 'Redirect' : 'Button';
                    } elseif (isset($metadata['clickType'])) {
                        $lastInteractionType = $metadata['clickType'] === 'button' ? 'Button' : 'Redirect';
                    } elseif (isset($metadata['redirectUrl'])) {
                        // If there's a redirectUrl but no clickType, it's an automatic redirect
                        $lastInteractionType = 'Redirect';
                    } elseif (isset($metadata['buttonUrl'])) {
                        // If there's a buttonUrl but no clickType, it's a button click
                        $lastInteractionType = 'Button';
                    }
                    
                    // Get destination URL
                    if (isset($metadata['buttonUrl'])) {
                        $lastDestinationUrl = $metadata['buttonUrl'];
                    } elseif (isset($metadata['redirectUrl'])) {
                        $lastDestinationUrl = $metadata['redirectUrl'];
                    } elseif (isset($metadata['destinationUrl'])) {
                        $lastDestinationUrl = $metadata['destinationUrl'];
                    }
                }
                
                $topLinks[] = [
                    'id' => $smartLink->id,
                    'name' => $smartLink->title,
                    'slug' => $smartLink->slug,
                    'enabled' => $smartLink->enabled,
                    'clicks' => (int)$row['clicks'],
                    'lastClick' => $row['lastClick'] ? DateTimeHelper::toIso8601(DateTimeHelper::toDateTime($row['lastClick'])) : null,
                    'lastInteractionType' => $lastInteractionType,
                    'lastDestinationUrl' => $lastDestinationUrl,
                    'qrScans' => (int)$row['qrScans'],
                    'directVisits' => (int)$row['directVisits'],
                ];
            }
        }
        
        return $topLinks;
    }
    
    /**
     * Get recent clicks across all smart links
     *
     * @param string $dateRange
     * @param int $limit
     * @return array
     */
    public function getAllRecentClicks(string $dateRange = 'last7days', int $limit = 20): array
    {
        $query = (new Query())
            ->from(['a' => '{{%smartlinks_analytics}}'])
            ->innerJoin(['s' => '{{%smartlinks}}'], 'a.linkId = s.id')
            ->innerJoin(['e' => '{{%elements}}'], 's.id = e.id')
            ->select([
                'a.*',
                's.title as smartLinkTitle',
                's.slug as smartLinkSlug'
            ])
            ->where(['e.enabled' => true])
            ->orderBy('a.dateCreated DESC')
            ->limit($limit);
        
        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange, 'a.dateCreated');
        
        $results = $query->all();
        $clicks = [];
        
        foreach ($results as $row) {
            $metadata = $row['metadata'] ? Json::decode($row['metadata']) : [];
            $clickType = $metadata['clickType'] ?? 'redirect';
            $destinationUrl = '';
            
            if ($clickType == 'button') {
                $destinationUrl = $metadata['buttonUrl'] ?? '';
            } else {
                $destinationUrl = $metadata['redirectUrl'] ?? '';
            }
            
            $clicks[] = [
                'id' => $row['id'],
                'linkId' => $row['linkId'],
                'smartLinkTitle' => $row['smartLinkTitle'],
                'smartLinkSlug' => $row['smartLinkSlug'],
                'dateCreated' => DateTimeHelper::toIso8601(DateTimeHelper::toDateTime($row['dateCreated'])),
                'siteId' => $row['siteId'],
                'deviceType' => $row['deviceType'],
                'osName' => $row['osName'],
                'country' => $row['country'],
                'city' => $row['city'],
                'clickType' => $clickType,
                'platform' => $metadata['platform'] ?? null,
                'destinationUrl' => $destinationUrl,
                'source' => $metadata['source'] ?? 'direct',
            ];
        }
        
        return $clicks;
    }
    
    /**
     * Track a click on a smart link
     *
     * @param SmartLink $smartLink
     * @param DeviceInfo $deviceInfo
     * @param array $metadata
     * @return void
     */
    public function trackClick(SmartLink $smartLink, DeviceInfo $deviceInfo, array $metadata = []): void
    {
        // Add IP address to metadata now
        $metadata['ip'] = Craft::$app->request->getUserIP();
        
        // Save analytics directly (like Retour does)
        try {
            $this->saveAnalytics(
                $smartLink->id,
                $deviceInfo->toArray(),
                $metadata
            );
        } catch (\Exception $e) {
            // Log but don't throw - analytics shouldn't break the redirect
            Craft::error('Failed to save analytics: ' . $e->getMessage(), __METHOD__);
        }
        
        // Update click count in metadata
        $this->_incrementClickCount($smartLink);
    }

    /**
     * Save analytics record
     *
     * @param int $linkId
     * @param array $deviceInfo
     * @param array $metadata
     * @return bool
     */
    public function saveAnalytics(int $linkId, array $deviceInfo, array $metadata = []): bool
    {
        Craft::info('saveAnalytics called with linkId: ' . $linkId, __METHOD__);
        
        try {
            $db = Craft::$app->getDb();
            
            // Prepare the data according to actual database columns
            $data = [
                'linkId' => $linkId,
                'siteId' => Craft::$app->getSites()->getCurrentSite()->id,
                'deviceType' => $deviceInfo['deviceType'] ?? $deviceInfo['type'] ?? null,
                // 'deviceName' => REMOVED
                'deviceBrand' => $deviceInfo['brand'] ?? null,
                'deviceModel' => $deviceInfo['model'] ?? null,
                // 'platform' => REMOVED
                'osName' => $deviceInfo['osName'] ?? null,
                'osVersion' => $deviceInfo['osVersion'] ?? null,
                'browser' => $deviceInfo['browser'] ?? null,
                'browserVersion' => $deviceInfo['browserVersion'] ?? null,
                'browserEngine' => $deviceInfo['browserEngine'] ?? null,
                'clientType' => $deviceInfo['clientType'] ?? null,
                'isRobot' => $deviceInfo['isBot'] ?? $deviceInfo['isRobot'] ?? false,
                'isMobileApp' => $deviceInfo['isMobileApp'] ?? false,
                'botName' => $deviceInfo['botName'] ?? null,
                'country' => null,
                'language' => $metadata['language'] ?? null,
                'referrer' => $metadata['referrer'] ?? null,
                'ip' => isset($metadata['ip']) ? hash('sha256', $metadata['ip'] . Craft::$app->security->generateRandomString(16)) : null,
                'userAgent' => $deviceInfo['userAgent'] ?? null,
                'metadata' => Json::encode($metadata),
                'dateCreated' => Db::prepareDateForDb(new \DateTime()),
                'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
                'uid' => StringHelper::UUID(),
            ];
            
            // Get location data from IP if geo detection is enabled
            if (SmartLinks::$plugin->getSettings()->enableGeoDetection && isset($metadata['ip'])) {
                $location = $this->getLocationFromIp($metadata['ip']);
                if ($location) {
                    $data['country'] = $location['countryCode'];
                    $data['city'] = $location['city'];
                    $data['region'] = $location['region'];
                    $data['timezone'] = $location['timezone'];
                    $data['latitude'] = $location['lat'];
                    $data['longitude'] = $location['lon'];
                    // 'isp' => REMOVED
                }
            }
            
            return (bool)$db->createCommand()
                ->insert('{{%smartlinks_analytics}}', $data)
                ->execute();
                
        } catch (\Exception $e) {
            Craft::error('Failed to save analytics: ' . $e->getMessage() . ' | Data: ' . json_encode($data), __METHOD__);
            Craft::error('Stack trace: ' . $e->getTraceAsString(), __METHOD__);
            return false;
        }
    }

    /**
     * Get analytics data for a smart link
     *
     * @param SmartLink $smartLink
     * @param array $criteria
     * @return array
     */
    public function getAnalytics(SmartLink $smartLink, array $criteria = []): array
    {
        $query = (new Query())
            ->from(['{{%smartlinks_analytics}}'])
            ->where(['linkId' => $smartLink->id]);
        
        // Date range filter
        if (isset($criteria['from'])) {
            $query->andWhere(['>=', 'timestamp', Db::prepareDateForDb($criteria['from'])]);
        }
        
        if (isset($criteria['to'])) {
            $query->andWhere(['<=', 'timestamp', Db::prepareDateForDb($criteria['to'])]);
        }
        
        // OS filter (replacing platform)
        if (isset($criteria['os'])) {
            $query->andWhere(['osName' => $criteria['os']]);
        }
        
        // Get total count
        $total = (clone $query)->count();
        
        // Get device breakdown
        $devices = (clone $query)
            ->select(['devicePlatform', 'COUNT(*) as count'])
            ->groupBy(['devicePlatform'])
            ->indexBy('devicePlatform')
            ->column();
        
        // Get daily breakdown for last 30 days
        $daily = [];
        if (!isset($criteria['skipDaily']) || !$criteria['skipDaily']) {
            $dailyQuery = (clone $query)
                ->select(['DATE(timestamp) as date', 'COUNT(*) as count'])
                ->andWhere(['>=', 'timestamp', DateTimeHelper::currentTimeStamp() - (30 * 24 * 60 * 60)])
                ->groupBy(['DATE(timestamp)'])
                ->orderBy(['date' => SORT_ASC]);
            
            foreach ($dailyQuery->all() as $row) {
                $daily[$row['date']] = (int)$row['count'];
            }
        }
        
        // Get language breakdown
        $languages = (clone $query)
            ->select(['language', 'COUNT(*) as count'])
            ->andWhere(['not', ['language' => null]])
            ->groupBy(['language'])
            ->indexBy('language')
            ->column();
        
        // Get country breakdown if enabled
        $countries = [];
        if (SmartLinks::$plugin->getSettings()->enableGeoDetection) {
            $countries = (clone $query)
                ->select(['country', 'COUNT(*) as count'])
                ->andWhere(['not', ['country' => null]])
                ->groupBy(['country'])
                ->orderBy(['count' => SORT_DESC])
                ->limit(10)
                ->indexBy('country')
                ->column();
        }
        
        return [
            'total' => (int)$total,
            'devices' => $devices,
            'daily' => $daily,
            'languages' => $languages,
            'countries' => $countries,
        ];
    }

    /**
     * Get aggregated stats for multiple smart links
     *
     * @param array $linkIds
     * @param string $period
     * @return array
     */
    public function getAggregatedStats(array $linkIds, string $period = '30d'): array
    {
        $query = (new Query())
            ->from(['{{%smartlinks_analytics}}'])
            ->where(['in', 'linkId', $linkIds]);
        
        // Apply period filter
        $seconds = $this->_periodToSeconds($period);
        if ($seconds > 0) {
            $query->andWhere(['>=', 'timestamp', DateTimeHelper::currentTimeStamp() - $seconds]);
        }
        
        // Get stats
        $stats = [];
        foreach ($linkIds as $linkId) {
            $linkQuery = (clone $query)->andWhere(['linkId' => $linkId]);
            $stats[$linkId] = [
                'total' => (int)$linkQuery->count(),
                'devices' => $linkQuery
                    ->select(['devicePlatform', 'COUNT(*) as count'])
                    ->groupBy(['devicePlatform'])
                    ->indexBy('devicePlatform')
                    ->column(),
            ];
        }
        
        return $stats;
    }

    /**
     * Delete analytics data for a smart link
     *
     * @param SmartLink $smartLink
     * @return int Number of records deleted
     */
    public function deleteAnalyticsForLink(SmartLink $smartLink): int
    {
        return Craft::$app->db->createCommand()
            ->delete('{{%smartlinks_analytics}}', ['linkId' => $smartLink->id])
            ->execute();
    }

    /**
     * Clean old analytics data
     *
     * @param int $days
     * @return int Number of records deleted
     */
    public function cleanOldAnalytics(int $days): int
    {
        $cutoffDate = DateTimeHelper::currentTimeStamp() - ($days * 24 * 60 * 60);
        
        return Craft::$app->db->createCommand()
            ->delete('{{%smartlinks_analytics}}', ['<', 'timestamp', $cutoffDate])
            ->execute();
    }

    /**
     * Increment click count in smart link metadata
     *
     * @param SmartLink $smartLink
     * @return void
     */
    private function _incrementClickCount(SmartLink $smartLink): void
    {
        $metadata = $smartLink->metadata ?? [];
        $metadata['clicks'] = ($metadata['clicks'] ?? 0) + 1;
        $metadata['lastClick'] = DateTimeHelper::currentTimeStamp();
        
        $smartLink->metadata = $metadata;
        $smartLink->clicks = $metadata['clicks'];
        
        // Update directly in database to avoid triggering events
        Craft::$app->db->createCommand()
            ->update('{{%smartlinks}}', [
                'metadata' => Json::encode($metadata),
                'clicks' => $metadata['clicks']
            ], ['id' => $smartLink->id])
            ->execute();
    }

    /**
     * Convert period string to seconds
     *
     * @param string $period
     * @return int
     */
    private function _periodToSeconds(string $period): int
    {
        $matches = [];
        if (!preg_match('/^(\d+)([hdwmy])$/', $period, $matches)) {
            return 0;
        }
        
        $value = (int)$matches[1];
        $unit = $matches[2];
        
        return match ($unit) {
            'h' => $value * 3600,
            'd' => $value * 86400,
            'w' => $value * 604800,
            'm' => $value * 2592000,
            'y' => $value * 31536000,
            default => 0,
        };
    }

    /**
     * Get location data from IP address
     *
     * @param string $ip
     * @return array|null
     */
    public function getLocationFromIp(string $ip): ?array
    {
        try {
            // Skip local/private IPs - return default location data
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                // Check for environment variable to override default location
                $defaultCountry = getenv('SMART_LINKS_DEFAULT_COUNTRY') ?: 'SA';
                $defaultCity = getenv('SMART_LINKS_DEFAULT_CITY') ?: 'Riyadh';
                
                // Predefined locations for common defaults
                $locations = [
                    'AE' => [
                        'Dubai' => [
                            'countryCode' => 'AE',
                            'country' => 'United Arab Emirates',
                            'city' => 'Dubai',
                            'region' => 'Dubai',
                            'timezone' => 'Asia/Dubai',
                            'lat' => 25.2048,
                            'lon' => 55.2708,
                            'isp' => 'Local Network',
                        ],
                        'Abu Dhabi' => [
                            'countryCode' => 'AE',
                            'country' => 'United Arab Emirates',
                            'city' => 'Abu Dhabi',
                            'region' => 'Abu Dhabi',
                            'timezone' => 'Asia/Dubai',
                            'lat' => 24.4539,
                            'lon' => 54.3773,
                            'isp' => 'Local Network',
                        ],
                    ],
                    'SA' => [
                        'Riyadh' => [
                            'countryCode' => 'SA',
                            'country' => 'Saudi Arabia',
                            'city' => 'Riyadh',
                            'region' => 'Riyadh Province',
                            'timezone' => 'Asia/Riyadh',
                            'lat' => 24.7136,
                            'lon' => 46.6753,
                            'isp' => 'Local Network',
                        ],
                        'Jeddah' => [
                            'countryCode' => 'SA',
                            'country' => 'Saudi Arabia',
                            'city' => 'Jeddah',
                            'region' => 'Makkah Province',
                            'timezone' => 'Asia/Riyadh',
                            'lat' => 21.5433,
                            'lon' => 39.1728,
                            'isp' => 'Local Network',
                        ],
                    ],
                ];
                
                // Return the configured location or default to Riyadh
                if (isset($locations[$defaultCountry][$defaultCity])) {
                    return $locations[$defaultCountry][$defaultCity];
                }
                
                // Fallback to Riyadh if configuration not found
                return $locations['SA']['Riyadh'];
            }
            
            // Use ip-api.com (free, no API key required, 45 requests per minute)
            // Request all available fields for comprehensive analytics
            $url = "http://ip-api.com/json/{$ip}?fields=status,countryCode,country,city,regionName,region,lat,lon,timezone,isp,org,as,mobile,proxy,hosting";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2); // 2 second timeout
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $data = Json::decode($response);
                if (isset($data['status']) && $data['status'] === 'success') {
                    return [
                        'countryCode' => $data['countryCode'] ?? null,
                        'country' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'lat' => $data['lat'] ?? null,
                        'lon' => $data['lon'] ?? null,
                        'isp' => $data['isp'] ?? null,
                        'mobile' => $data['mobile'] ?? false,
                        'proxy' => $data['proxy'] ?? false,
                        'hosting' => $data['hosting'] ?? false,
                    ];
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Craft::warning('Failed to get location from IP: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }
    
    /**
     * Get country from IP address (backward compatibility)
     *
     * @param string $ip
     * @return string|null
     */
    public function getCountryFromIp(string $ip): ?string
    {
        $location = $this->getLocationFromIp($ip);
        return $location ? $location['countryCode'] : null;
    }
    
    /**
     * Get country name from code
     *
     * @param string $code
     * @return string
     */
    private function _getCountryName(string $code): string
    {
        // Full list of ISO country codes
        $countries = [
            // Europe
            'DE' => 'Germany',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'CH' => 'Switzerland',
            'AT' => 'Austria',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'DK' => 'Denmark',
            'FI' => 'Finland',
            'PL' => 'Poland',
            'CZ' => 'Czech Republic',
            'GR' => 'Greece',
            'PT' => 'Portugal',
            'RO' => 'Romania',
            'HU' => 'Hungary',
            'GB' => 'United Kingdom',
            'IE' => 'Ireland',
            
            // Middle East & Africa
            'SA' => 'Saudi Arabia',
            'AE' => 'United Arab Emirates',
            'KW' => 'Kuwait',
            'QA' => 'Qatar',
            'BH' => 'Bahrain',
            'OM' => 'Oman',
            'JO' => 'Jordan',
            'EG' => 'Egypt',
            'LB' => 'Lebanon',
            'SY' => 'Syria',
            'IQ' => 'Iraq',
            'YE' => 'Yemen',
            'IL' => 'Israel',
            'TR' => 'Turkey',
            'IR' => 'Iran',
            'MA' => 'Morocco',
            'DZ' => 'Algeria',
            'TN' => 'Tunisia',
            'LY' => 'Libya',
            'SD' => 'Sudan',
            'ZA' => 'South Africa',
            'NG' => 'Nigeria',
            'KE' => 'Kenya',
            
            // Americas
            'US' => 'United States',
            'CA' => 'Canada',
            'MX' => 'Mexico',
            'BR' => 'Brazil',
            'AR' => 'Argentina',
            'CL' => 'Chile',
            'CO' => 'Colombia',
            'PE' => 'Peru',
            'VE' => 'Venezuela',
            
            // Asia Pacific
            'CN' => 'China',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'IN' => 'India',
            'PK' => 'Pakistan',
            'BD' => 'Bangladesh',
            'ID' => 'Indonesia',
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
            'TH' => 'Thailand',
            'PH' => 'Philippines',
            'VN' => 'Vietnam',
            'AU' => 'Australia',
            'NZ' => 'New Zealand',
            
            // Russia & CIS
            'RU' => 'Russia',
            'UA' => 'Ukraine',
            'KZ' => 'Kazakhstan',
            'UZ' => 'Uzbekistan',
        ];
        
        return $countries[$code] ?? $code;
    }
}