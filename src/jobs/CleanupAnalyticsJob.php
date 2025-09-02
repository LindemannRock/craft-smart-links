<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\jobs;

use Craft;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\queue\BaseJob;
use lindemannrock\smartlinks\SmartLinks;

/**
 * Cleanup old analytics data based on retention settings
 */
class CleanupAnalyticsJob extends BaseJob
{
    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        return Craft::t('smart-links', 'Cleaning up old analytics data');
    }

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $settings = SmartLinks::$plugin->getSettings();
        $retentionDays = $settings->analyticsRetention;
        
        // If retention is 0, keep forever
        if ($retentionDays === 0) {
            return;
        }
        
        // Calculate cutoff date
        $cutoffDate = DateTimeHelper::toDateTime("now -$retentionDays days");
        $cutoffDateString = Db::prepareDateForDb($cutoffDate);
        
        // Get count of records to delete for progress tracking
        $totalRecords = (new Query())
            ->from('{{%smartlinks_analytics}}')
            ->where(['<', 'dateCreated', $cutoffDateString])
            ->count();
        
        if ($totalRecords === 0) {
            return;
        }
        
        $this->setProgress($queue, 0, Craft::t('smart-links', 'Deleting {count} old analytics records', [
            'count' => $totalRecords
        ]));
        
        // Delete in batches to avoid memory issues
        $batchSize = 1000;
        $deleted = 0;
        
        while (true) {
            // Get batch of old record IDs
            $oldRecordIds = (new Query())
                ->select(['id'])
                ->from('{{%smartlinks_analytics}}')
                ->where(['<', 'dateCreated', $cutoffDateString])
                ->limit($batchSize)
                ->column();
            
            if (empty($oldRecordIds)) {
                break;
            }
            
            // Delete batch
            Craft::$app->getDb()->createCommand()
                ->delete('{{%smartlinks_analytics}}', ['id' => $oldRecordIds])
                ->execute();
            
            $deleted += count($oldRecordIds);
            
            $this->setProgress($queue, $deleted / $totalRecords, Craft::t('smart-links', 'Deleted {deleted} of {total} records', [
                'deleted' => $deleted,
                'total' => $totalRecords
            ]));
        }
        
        Craft::info("Cleaned up $deleted analytics records older than $retentionDays days", 'smart-links');
        
        // Re-queue itself to run again in 24 hours if retention is still enabled
        if ($settings->analyticsRetention > 0) {
            // Check if another cleanup job is already scheduled
            $existingJob = (new Query())
                ->from('{{%queue}}')
                ->where(['like', 'job', 'CleanupAnalyticsJob'])
                ->andWhere(['>', 'timePushed', time()]) // Scheduled for future
                ->exists();
            
            if (!$existingJob) {
                $newJob = new self();
                Craft::$app->queue->delay(24 * 60 * 60)->push($newJob);
                
                Craft::info(
                    Craft::t('smart-links', 'Re-queued analytics cleanup to run in 24 hours'),
                    'smart-links'
                );
            } else {
                Craft::info(
                    Craft::t('smart-links', 'Analytics cleanup job already scheduled for future, skipping re-queue'),
                    'smart-links'
                );
            }
        }
    }
}