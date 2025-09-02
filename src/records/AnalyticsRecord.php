<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * Analytics Record
 *
 * @property int $id
 * @property int $linkId
 * @property string|null $devicePlatform
 * @property array|null $deviceInfo
 * @property string|null $redirectUrl
 * @property string|null $language
 * @property string|null $referrer
 * @property string|null $ipHash
 * @property string|null $country
 * @property string $timestamp
 * @property SmartLinkRecord $smartLink
 */
class AnalyticsRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%smartlinks_analytics}}';
    }

    /**
     * Returns the analytics record's smart link.
     *
     * @return ActiveQueryInterface
     */
    public function getSmartLink(): ActiveQueryInterface
    {
        return $this->hasOne(SmartLinkRecord::class, ['id' => 'linkId']);
    }
}