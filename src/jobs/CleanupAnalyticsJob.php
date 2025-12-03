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
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinks\SmartLinks;

/**
 * Cleanup old analytics data based on retention settings
 *
 * @since 1.0.0
 */
class CleanupAnalyticsJob extends BaseJob
{
    use LoggingTrait;

    /**
     * @var bool Whether to reschedule after completion
     */
    public bool $reschedule = false;

    /**
     * @var string|null Next run time display string
     */
    public ?string $nextRunTime = null;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle('smart-links');

        // Calculate and set next run time if not already set
        if ($this->reschedule && !$this->nextRunTime) {
            $delay = $this->calculateNextRunDelay();
            if ($delay > 0) {
                // Short format: "Nov 8, 12:00am"
                $this->nextRunTime = date('M j, g:ia', time() + $delay);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        $pluginName = SmartLinks::$plugin->getSettings()->getDisplayName();
        $description = Craft::t('smart-links', '{pluginName}: Cleaning up old analytics', ['pluginName' => $pluginName]);

        if ($this->nextRunTime) {
            $description .= " ({$this->nextRunTime})";
        }

        return $description;
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
            // Don't reschedule if retention is disabled
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
            // No records to delete, but still reschedule for next run
            if ($this->reschedule) {
                $this->scheduleNextCleanup();
            }
            return;
        }

        $this->setProgress($queue, 0, Craft::t('smart-links', 'Deleting {count} old analytics records', [
            'count' => $totalRecords,
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
                'total' => $totalRecords,
            ]));
        }

        $this->logInfo('Cleaned up analytics records', ['deleted' => $deleted, 'retentionDays' => $retentionDays]);

        // Reschedule if needed
        if ($this->reschedule) {
            $this->scheduleNextCleanup();
        }
    }

    /**
     * Schedule the next cleanup (runs every 24 hours)
     */
    private function scheduleNextCleanup(): void
    {
        $settings = SmartLinks::$plugin->getSettings();

        // Only reschedule if analytics is enabled and retention is set
        if (!$settings->enableAnalytics || $settings->analyticsRetention <= 0) {
            return;
        }

        $delay = $this->calculateNextRunDelay();

        if ($delay > 0) {
            // Calculate next run time for display
            $nextRunTime = date('M j, g:ia', time() + $delay);

            $job = new self([
                'reschedule' => true,
                'nextRunTime' => $nextRunTime,
            ]);

            Craft::$app->getQueue()->delay($delay)->push($job);
        }
    }

    /**
     * Calculate the delay in seconds for the next cleanup (24 hours)
     */
    private function calculateNextRunDelay(): int
    {
        return 86400; // 24 hours
    }
}
