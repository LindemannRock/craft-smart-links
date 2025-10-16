<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\jobs;

use Craft;
use craft\queue\BaseJob;
use lindemannrock\smartlinks\SmartLinks;

/**
 * Track Analytics Job
 */
class TrackAnalyticsJob extends BaseJob
{
    /**
     * @var int Smart link ID
     */
    public int $linkId;

    /**
     * @var array Device info
     */
    public array $deviceInfo = [];

    /**
     * @var array Additional metadata
     */
    public array $metadata = [];

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        // Save analytics (IP is already in metadata from the service)
        $result = SmartLinks::$plugin->analytics->saveAnalytics(
            $this->linkId,
            $this->deviceInfo,
            $this->metadata
        );
        
        if (!$result) {
            Craft::error('Failed to save analytics for link', __METHOD__, ['linkId' => $this->linkId]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        return Craft::t('smart-links', 'Tracking analytics for smart link {id}', [
            'id' => $this->linkId,
        ]);
    }
}