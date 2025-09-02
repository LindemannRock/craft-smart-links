<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use yii\db\ActiveQueryInterface;

/**
 * Smart Link Record
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $iosUrl
 * @property string|null $androidUrl
 * @property string|null $huaweiUrl
 * @property string|null $amazonUrl
 * @property string|null $windowsUrl
 * @property string|null $macUrl
 * @property string $fallbackUrl
 * @property string|null $icon
 * @property bool $trackAnalytics
 * @property bool $qrCodeEnabled
 * @property int $qrCodeSize
 * @property string $qrCodeColor
 * @property string $qrCodeBgColor
 * @property array|null $languageDetection
 * @property array|null $localizedUrls
 * @property array|null $metadata
 * @property Element $element
 */
class SmartLinkRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%smartlinks}}';
    }

    /**
     * Returns the smart link's element.
     *
     * @return ActiveQueryInterface
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
}