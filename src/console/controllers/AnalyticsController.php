<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\console\controllers;

use Craft;
use craft\console\Controller;
use craft\db\Query;
use craft\helpers\Console;
use lindemannrock\smartlinks\SmartLinks;
use yii\console\ExitCode;

/**
 * Analytics Controller
 */
class AnalyticsController extends Controller
{
    /**
     * Update country data for existing analytics records
     */
    public function actionUpdateCountries(): int
    {
        $this->stdout("Updating country data for analytics records...\n", Console::FG_YELLOW);
        
        // Get all records with NULL country
        $records = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->where(['country' => null])
            ->andWhere(['not', ['ip' => null]])
            ->all();
        
        $total = count($records);
        $updated = 0;
        $failed = 0;
        
        if ($total === 0) {
            $this->stdout("No records found with missing country data.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }
        
        $this->stdout("Found {$total} records to update.\n\n", Console::FG_CYAN);
        
        foreach ($records as $i => $record) {
            $progress = $i + 1;
            $this->stdout("[{$progress}/{$total}] Processing record ID: {$record['id']}... ");
            
            // Since IPs are hashed, we can't look them up anymore
            // We'll need to use the original IP from metadata if available
            $metadata = json_decode($record['metadata'], true);
            
            if (isset($metadata['ip'])) {
                $country = SmartLinks::$plugin->analytics->getCountryFromIp($metadata['ip']);
                
                if ($country) {
                    // Update the record
                    Craft::$app->db->createCommand()
                        ->update('{{%smartlinks_analytics}}', 
                            ['country' => $country], 
                            ['id' => $record['id']]
                        )
                        ->execute();
                    
                    $this->stdout("Updated to {$country}\n", Console::FG_GREEN);
                    $updated++;
                } else {
                    $this->stdout("Failed to detect country\n", Console::FG_RED);
                    $failed++;
                }
            } else {
                // For local IPs or when original IP not in metadata, default to SA
                Craft::$app->db->createCommand()
                    ->update('{{%smartlinks_analytics}}', 
                        ['country' => 'SA'], 
                        ['id' => $record['id']]
                    )
                    ->execute();
                
                $this->stdout("Defaulted to SA (local IP)\n", Console::FG_YELLOW);
                $updated++;
            }
            
            // Add a small delay to avoid hitting API rate limits
            if ($i % 40 === 0 && $i > 0) {
                $this->stdout("\nWaiting 60 seconds to avoid API rate limits...\n", Console::FG_YELLOW);
                sleep(60);
            } else {
                usleep(200000); // 0.2 second delay between requests
            }
        }
        
        $this->stdout("\n" . str_repeat('=', 60) . "\n", Console::FG_CYAN);
        $this->stdout("Update completed!\n", Console::FG_GREEN);
        $this->stdout("✓ Updated: {$updated} records\n", Console::FG_GREEN);
        
        if ($failed > 0) {
            $this->stdout("✗ Failed: {$failed} records\n", Console::FG_RED);
        }
        
        return ExitCode::OK;
    }
    
    /**
     * Update city data for existing analytics records
     */
    public function actionUpdateCities(): int
    {
        $this->stdout("Updating city data for analytics records...\n", Console::FG_YELLOW);
        
        // Get all records with NULL city but have country
        $records = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->where(['city' => null])
            ->andWhere(['not', ['country' => null]])
            ->andWhere(['not', ['ip' => null]])
            ->all();
        
        $total = count($records);
        $updated = 0;
        $failed = 0;
        
        if ($total === 0) {
            $this->stdout("No records found with missing city data.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }
        
        $this->stdout("Found {$total} records to update.\n\n", Console::FG_CYAN);
        
        foreach ($records as $i => $record) {
            $progress = $i + 1;
            $this->stdout("[{$progress}/{$total}] Processing record ID: {$record['id']}... ");
            
            $metadata = json_decode($record['metadata'], true);
            
            if (isset($metadata['ip'])) {
                $location = SmartLinks::$plugin->analytics->getLocationFromIp($metadata['ip']);
                
                if ($location) {
                    // Update the record with location data
                    Craft::$app->db->createCommand()
                        ->update('{{%smartlinks_analytics}}', 
                            [
                                'city' => $location['city'],
                                'region' => $location['region'],
                                'timezone' => $location['timezone'],
                                'latitude' => $location['lat'],
                                'longitude' => $location['lon'],
                                'isp' => $location['isp'],
                            ], 
                            ['id' => $record['id']]
                        )
                        ->execute();
                    
                    $this->stdout("Updated: {$location['city']}, {$location['region']}\n", Console::FG_GREEN);
                    $updated++;
                } else {
                    $this->stdout("Failed to detect location\n", Console::FG_RED);
                    $failed++;
                }
            } else {
                // For local IPs, use default Riyadh data
                Craft::$app->db->createCommand()
                    ->update('{{%smartlinks_analytics}}', 
                        [
                            'city' => 'Riyadh',
                            'region' => 'Riyadh Province',
                            'timezone' => 'Asia/Riyadh',
                            'latitude' => 24.7136,
                            'longitude' => 46.6753,
                            'isp' => 'Local Network',
                        ], 
                        ['id' => $record['id']]
                    )
                    ->execute();
                
                $this->stdout("Defaulted to Riyadh (local IP)\n", Console::FG_YELLOW);
                $updated++;
            }
            
            // Add a small delay to avoid hitting API rate limits
            if ($i % 40 === 0 && $i > 0) {
                $this->stdout("\nWaiting 60 seconds to avoid API rate limits...\n", Console::FG_YELLOW);
                sleep(60);
            } else {
                usleep(200000); // 0.2 second delay between requests
            }
        }
        
        $this->stdout("\n" . str_repeat('=', 60) . "\n", Console::FG_CYAN);
        $this->stdout("Update completed!\n", Console::FG_GREEN);
        $this->stdout("✓ Updated: {$updated} records\n", Console::FG_GREEN);
        
        if ($failed > 0) {
            $this->stdout("✗ Failed: {$failed} records\n", Console::FG_RED);
        }
        
        return ExitCode::OK;
    }
}