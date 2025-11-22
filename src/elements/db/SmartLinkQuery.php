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
    // Store slug in custom property to prevent Craft from adding elements_sites.slug filter
    /**
     * @var mixed|null Smart link slug filter stored separately from the base query
     */
    private mixed $_smartLinkSlug = null;

    /**
     * @var mixed|null Whether to filter by analytics tracking state
     */
    public mixed $trackAnalytics = null;

    /**
     * @var mixed|null Whether to filter by QR code enabled state
     */
    public mixed $qrCodeEnabled = null;


    /**
     * Narrows the query results based on the smart links' slugs.
     *
     * NOTE: We store slug in a private property to prevent Craft from
     * adding elements_sites.slug to the query. SmartLinks store slug in
     * the smartlinks table, not elements_sites.
     */
    public function slug($value): static
    {
        $this->_smartLinkSlug = $value;
        // DON'T set $this->slug or call parent - prevents elements_sites.slug filter
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

        // For custom statuses (expired, pending), don't pass to parent yet
        // We'll handle the enabled filtering in beforePrepare()
        if ($value === SmartLink::STATUS_EXPIRED || $value === SmartLink::STATUS_PENDING) {
            // Set to null so parent doesn't filter by status
            // We'll add enabled check manually in beforePrepare
            return parent::status(null);
        }

        return parent::status($value);
    }

    /**
     * @var mixed|null Requested status used for custom status handling
     */
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

        if ($this->_smartLinkSlug !== null) {
            $this->subQuery->andWhere(Db::parseParam('smartlinks.slug', $this->_smartLinkSlug));
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
            // Show only expired items (must have dateExpired in the past)
            $this->subQuery->andWhere(['<', 'smartlinks.dateExpired', new Expression('NOW()')]);
            // Also must be enabled in elements_sites
            $this->subQuery->andWhere(['elements_sites.enabled' => true]);
        } elseif ($this->_requestedStatus === SmartLink::STATUS_PENDING) {
            // Show only pending items (postDate in the future)
            $this->subQuery->andWhere(['>', 'smartlinks.postDate', new Expression('NOW()')]);
            // Also must be enabled in elements_sites
            $this->subQuery->andWhere(['elements_sites.enabled' => true]);
        } elseif ($this->_requestedStatus === SmartLink::STATUS_ENABLED) {
            // For enabled status, exclude expired and pending
            // Parent already filtered by elements_sites.enabled = true
            // Add date filters to the MAIN query, not subQuery
            $this->query->andWhere([
                'or',
                ['smartlinks.dateExpired' => null],
                ['>=', 'smartlinks.dateExpired', new Expression('NOW()')]
            ]);
            $this->query->andWhere([
                'or',
                ['smartlinks.postDate' => null],
                ['<=', 'smartlinks.postDate', new Expression('NOW()')]
            ]);
        }

        return true;
    }
}
