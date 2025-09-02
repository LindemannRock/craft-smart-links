<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use lindemannrock\smartlinks\elements\SmartLink;
use yii\db\Expression;

/**
 * SmartLinkQuery represents a SELECT SQL statement for smart links.
 *
 * @method SmartLink[]|array all($db = null)
 * @method SmartLink|array|null one($db = null)
 * @method SmartLink|array|null nth(int $n, ?Connection $db = null)
 */
class SmartLinkQuery extends ElementQuery
{
    public mixed $slug = null;
    public mixed $trackAnalytics = null;
    public mixed $qrCodeEnabled = null;


    /**
     * Narrows the query results based on the smart links' slugs.
     */
    public function slug($value): static
    {
        $this->slug = $value;
        return $this;
    }


    /**
     * Narrows the query results based on whether the smart links track analytics.
     */
    public function trackAnalytics($value = true): static
    {
        $this->trackAnalytics = $value;
        return $this;
    }

    /**
     * Narrows the query results based on whether the smart links have QR codes enabled.
     */
    public function qrCodeEnabled($value = true): static
    {
        $this->qrCodeEnabled = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function status($value): static
    {
        // Store the requested status for use in beforePrepare
        $this->_requestedStatus = $value;
        return parent::status($value);
    }
    
    private $_requestedStatus = null;

    protected function beforePrepare(): bool
    {
        // Call parent first to handle revisions/drafts properly
        if (!parent::beforePrepare()) {
            return false;
        }

        // Join in the smartlinks table
        $this->joinElementTable('smartlinks');

        // Select the columns from main table (non-translatable fields)
        $this->query->select([
            'smartlinks.title',
            'smartlinks.slug',
            'smartlinks.icon',
            'smartlinks.authorId',
            'smartlinks.postDate',
            'smartlinks.dateExpired',
            'smartlinks.trackAnalytics',
            'smartlinks.hideTitle',
            'smartlinks.qrCodeEnabled',
            'smartlinks.qrCodeSize',
            'smartlinks.qrCodeColor',
            'smartlinks.qrCodeBgColor',
            'smartlinks.qrCodeFormat',
            'smartlinks.qrCodeEyeColor',
            'smartlinks.qrLogoId',
            'smartlinks.languageDetection',
            'smartlinks.metadata',
            // Ensure we get the enabled status from elements_sites
            'elements_sites.enabled',
        ]);

        if ($this->slug !== null) {
            $this->subQuery->andWhere(Db::parseParam('smartlinks.slug', $this->slug));
        }

        // Note: The enabled/disabled status is handled by Craft's ElementQuery
        // through the elements_sites.enabled column, not smartlinks.active

        if ($this->trackAnalytics !== null) {
            $this->subQuery->andWhere(Db::parseParam('smartlinks.trackAnalytics', $this->trackAnalytics));
        }

        if ($this->qrCodeEnabled !== null) {
            $this->subQuery->andWhere(Db::parseParam('smartlinks.qrCodeEnabled', $this->qrCodeEnabled));
        }
        
        // Handle custom statuses
        if ($this->_requestedStatus === SmartLink::STATUS_EXPIRED) {
            $this->subQuery->andWhere(['<', 'smartlinks.dateExpired', new Expression('NOW()')]);
            // Set status to enabled so parent query doesn't filter by disabled
            $this->status = SmartLink::STATUS_ENABLED;
        } elseif ($this->_requestedStatus === SmartLink::STATUS_PENDING) {
            $this->subQuery->andWhere(['>', 'smartlinks.postDate', new Expression('NOW()')]);
            // Set status to enabled so parent query doesn't filter by disabled
            $this->status = SmartLink::STATUS_ENABLED;
        } elseif ($this->_requestedStatus === SmartLink::STATUS_ENABLED) {
            // For enabled status, exclude expired and pending
            $this->subQuery->andWhere([
                'or',
                ['smartlinks.dateExpired' => null],
                ['>=', 'smartlinks.dateExpired', new Expression('NOW()')]
            ]);
            $this->subQuery->andWhere([
                'or',
                ['smartlinks.postDate' => null],
                ['<=', 'smartlinks.postDate', new Expression('NOW()')]
            ]);
        }

        return true;
    }
}