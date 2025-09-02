<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\migrations;

use craft\db\Migration;

/**
 * Smart Links Install Migration
 *
 * @author    LindemannRock
 * @package   SmartLinks
 * @since     1.0.0
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Create the smartlinks table
        if (!$this->db->tableExists('{{%smartlinks}}')) {
            $this->createTable('{{%smartlinks}}', [
                'id' => $this->integer()->notNull(),
                'title' => $this->string()->notNull(),
                'slug' => $this->string()->notNull(),
                'icon' => $this->string()->null(),
                'trackAnalytics' => $this->boolean()->defaultValue(true),
                'qrCodeEnabled' => $this->boolean()->defaultValue(true),
                'qrCodeSize' => $this->integer()->defaultValue(256),
                'qrCodeColor' => $this->string(7)->defaultValue('#000000'),
                'qrCodeBgColor' => $this->string(7)->defaultValue('#FFFFFF'),
                'qrCodeEyeColor' => $this->string(7)->null(),
                'qrCodeFormat' => $this->string(10)->null(),
                'qrLogoId' => $this->integer()->null(),
                'languageDetection' => $this->json()->null(),
                'metadata' => $this->json()->null(),
                'authorId' => $this->integer()->null(),
                'postDate' => $this->dateTime()->null(),
                'dateExpired' => $this->dateTime()->null(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                'PRIMARY KEY(id)',
            ]);

            // Create indexes
            $this->createIndex(null, '{{%smartlinks}}', ['slug'], true);
            $this->createIndex(null, '{{%smartlinks}}', ['authorId']);
            $this->createIndex(null, '{{%smartlinks}}', ['postDate']);
            $this->createIndex(null, '{{%smartlinks}}', ['dateExpired']);
            $this->createIndex(null, '{{%smartlinks}}', ['qrLogoId']);

            // Add foreign keys
            $this->addForeignKey(null, '{{%smartlinks}}', ['id'], '{{%elements}}', ['id'], 'CASCADE');
            $this->addForeignKey(null, '{{%smartlinks}}', ['authorId'], '{{%users}}', ['id'], 'SET NULL');
            $this->addForeignKey(null, '{{%smartlinks}}', ['qrLogoId'], '{{%assets}}', ['id'], 'SET NULL');
        }

        // Create the smartlinks_content table for multi-site support
        if (!$this->db->tableExists('{{%smartlinks_content}}')) {
            $this->createTable('{{%smartlinks_content}}', [
                'id' => $this->primaryKey(),
                'smartLinkId' => $this->integer()->notNull(),
                'siteId' => $this->integer()->notNull(),
                'title' => $this->string()->notNull(),
                'description' => $this->text()->null(),
                'iosUrl' => $this->string()->null(),
                'androidUrl' => $this->string()->null(),
                'huaweiUrl' => $this->string()->null(),
                'amazonUrl' => $this->string()->null(),
                'windowsUrl' => $this->string()->null(),
                'macUrl' => $this->string()->null(),
                'fallbackUrl' => $this->string()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            // Create indexes
            $this->createIndex(null, '{{%smartlinks_content}}', ['smartLinkId', 'siteId'], true);
            $this->createIndex(null, '{{%smartlinks_content}}', ['siteId']);

            // Add foreign keys
            $this->addForeignKey(null, '{{%smartlinks_content}}', ['smartLinkId'], '{{%smartlinks}}', ['id'], 'CASCADE');
            $this->addForeignKey(null, '{{%smartlinks_content}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE');
        }

        // Create the smartlinks_analytics table
        if (!$this->db->tableExists('{{%smartlinks_analytics}}')) {
            $this->createTable('{{%smartlinks_analytics}}', [
                'id' => $this->primaryKey(),
                'linkId' => $this->integer()->notNull(),
                'siteId' => $this->integer()->null(),
                'platform' => $this->string(50)->null(),
                'deviceType' => $this->string(50)->null(),
                'deviceName' => $this->string(100)->null(),
                'deviceBrand' => $this->string(50)->null(),
                'deviceModel' => $this->string(100)->null(),
                'osName' => $this->string(50)->null(),
                'osVersion' => $this->string(50)->null(),
                'browser' => $this->string(100)->null(),
                'browserVersion' => $this->string(50)->null(),
                'browserEngine' => $this->string(50)->null(),
                'clientType' => $this->string(50)->null(),
                'isRobot' => $this->boolean()->defaultValue(false),
                'botName' => $this->string(100)->null(),
                'isMobileApp' => $this->boolean()->defaultValue(false),
                'redirectUrl' => $this->string()->null(),
                'language' => $this->string(10)->null(),
                'referrer' => $this->string()->null(),
                'userAgent' => $this->text()->null(),
                'ip' => $this->string(64)->null(),
                'country' => $this->string(2)->null(),
                'city' => $this->string(100)->null(),
                'region' => $this->string(100)->null(),
                'timezone' => $this->string(50)->null(),
                'latitude' => $this->decimal(10, 8)->null(),
                'longitude' => $this->decimal(11, 8)->null(),
                'isp' => $this->string(100)->null(),
                'metadata' => $this->json()->null(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            // Create indexes for performance
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['linkId']);
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['siteId']);
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['platform']);
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['deviceType']);
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['country']);
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['dateCreated']);
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['city']);
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['region']);
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['deviceBrand']);
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['osName']);
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['clientType']);

            // Add foreign keys
            $this->addForeignKey(null, '{{%smartlinks_analytics}}', ['linkId'], '{{%smartlinks}}', ['id'], 'CASCADE');
            $this->addForeignKey(null, '{{%smartlinks_analytics}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE');
        }

        // Create the smartlinks_settings table
        if (!$this->db->tableExists('{{%smartlinks_settings}}')) {
            $this->createTable('{{%smartlinks_settings}}', [
                'id' => $this->primaryKey(),
                // Plugin settings
                'pluginName' => $this->string(255)->notNull()->defaultValue('Smart Links'),
                // Analytics settings
                'enableAnalytics' => $this->boolean()->defaultValue(true),
                'analyticsRetention' => $this->integer()->defaultValue(90),
                'enableGeoDetection' => $this->boolean()->defaultValue(false),
                // QR Code settings
                'defaultQrSize' => $this->integer()->defaultValue(256),
                'defaultQrColor' => $this->string(7)->defaultValue('#000000'),
                'defaultQrBgColor' => $this->string(7)->defaultValue('#FFFFFF'),
                'defaultQrFormat' => $this->string(3)->defaultValue('png'),
                'defaultQrErrorCorrection' => $this->string(1)->defaultValue('M'),
                'defaultQrMargin' => $this->integer()->defaultValue(4),
                'qrModuleStyle' => $this->string(10)->defaultValue('square'),
                'qrEyeStyle' => $this->string(10)->defaultValue('square'),
                'qrEyeColor' => $this->string(7)->null(),
                'qrCodeCacheDuration' => $this->integer()->defaultValue(86400),
                // QR Logo settings
                'enableQrLogo' => $this->boolean()->defaultValue(false),
                'qrLogoVolumeUid' => $this->string()->null(),
                'imageVolumeUid' => $this->string()->null(),
                'defaultQrLogoId' => $this->integer()->null(),
                'qrLogoSize' => $this->integer()->defaultValue(20),
                // QR Download settings
                'enableQrDownload' => $this->boolean()->defaultValue(true),
                'qrDownloadFilename' => $this->string()->defaultValue('{slug}-qr-{size}'),
                // Redirect settings
                'redirectTemplate' => $this->string(500)->null(),
                'cacheDeviceDetection' => $this->boolean()->defaultValue(true),
                'deviceDetectionCacheDuration' => $this->integer()->defaultValue(3600),
                'languageDetectionMethod' => $this->string(10)->defaultValue('browser'),
                'supportedLanguages' => $this->json()->null(),
                'notFoundRedirectUrl' => $this->string()->defaultValue('/'),
                // Interface settings
                'itemsPerPage' => $this->integer()->defaultValue(100),
                // Export settings
                'includeDisabledInExport' => $this->boolean()->defaultValue(false),
                'includeExpiredInExport' => $this->boolean()->defaultValue(false),
                // Timestamps
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            // Create indexes
            $this->createIndex(null, '{{%smartlinks_settings}}', ['enableAnalytics']);
            $this->createIndex(null, '{{%smartlinks_settings}}', ['enableGeoDetection']);

            // Add foreign key for logo
            $this->addForeignKey(null, '{{%smartlinks_settings}}', ['defaultQrLogoId'], '{{%assets}}', ['id'], 'SET NULL');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        // Drop tables in reverse order due to foreign key constraints
        $this->dropTableIfExists('{{%smartlinks_analytics}}');
        $this->dropTableIfExists('{{%smartlinks_content}}');
        $this->dropTableIfExists('{{%smartlinks_settings}}');
        $this->dropTableIfExists('{{%smartlinks}}');

        return true;
    }
}