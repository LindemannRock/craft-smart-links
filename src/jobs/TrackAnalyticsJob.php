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
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinks\SmartLinks;

/**
 * Track Analytics Job
 */
class TrackAnalyticsJob extends BaseJob
{
    use LoggingTrait;

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
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle('smart-links');
    }

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
            $this->logError('Failed to save analytics for link', ['linkId' => $this->linkId]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        return Craft::t('smart-links', 'Tracking analytics for {pluginName} {id}', [
            'pluginName' => SmartLinks::$plugin->getSettings()->getLowerDisplayName(),
            'id' => $this->linkId,
        ]);
    }
}
