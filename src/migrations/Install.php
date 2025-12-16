<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\migrations;

use craft\db\Migration;
use craft\helpers\Db;
use craft\helpers\StringHelper;

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
                'qrCodeSize' => $this->integer()->defaultValue(200),
                'qrCodeColor' => $this->string(7)->null(),
                'qrCodeBgColor' => $this->string(7)->null(),
                'qrCodeEyeColor' => $this->string(7)->null(),
                'qrCodeFormat' => $this->string(10)->null(),
                'qrLogoId' => $this->integer()->null(),
                'hideTitle' => $this->boolean()->defaultValue(false)->notNull(),
                'languageDetection' => $this->boolean()->defaultValue(false),
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
            $this->createIndex(null, '{{%smartlinks}}', ['dateCreated']);
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
                'imageId' => $this->integer()->null(),
                'imageSize' => $this->string(2)->defaultValue('xl')->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            // Create indexes
            $this->createIndex(null, '{{%smartlinks_content}}', ['smartLinkId', 'siteId'], true);
            $this->createIndex(null, '{{%smartlinks_content}}', ['siteId']);
            $this->createIndex(null, '{{%smartlinks_content}}', ['imageId']);

            // Add foreign keys
            $this->addForeignKey(null, '{{%smartlinks_content}}', ['smartLinkId'], '{{%smartlinks}}', ['id'], 'CASCADE');
            $this->addForeignKey(null, '{{%smartlinks_content}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE');
            $this->addForeignKey(null, '{{%smartlinks_content}}', ['imageId'], '{{%assets}}', ['id'], 'SET NULL');
        }

        // Create the smartlinks_analytics table
        if (!$this->db->tableExists('{{%smartlinks_analytics}}')) {
            $this->createTable('{{%smartlinks_analytics}}', [
                'id' => $this->primaryKey(),
                'linkId' => $this->integer()->notNull(),
                'siteId' => $this->integer()->null(),
                'deviceType' => $this->string(50)->null(),
                'deviceBrand' => $this->string(50)->null(),
                'deviceModel' => $this->string(100)->null(),
                'osName' => $this->string(50)->null(),
                'osVersion' => $this->string(50)->null(),
                'browser' => $this->string(100)->null(),
                'browserVersion' => $this->string(20)->null(),
                'browserEngine' => $this->string(50)->null(),
                'clientType' => $this->string(50)->null(),
                'isRobot' => $this->boolean()->defaultValue(false),
                'isMobileApp' => $this->boolean()->defaultValue(false),
                'botName' => $this->string(100)->null(),
                'country' => $this->string(2)->null(),
                'city' => $this->string(100)->null(),
                'region' => $this->string(100)->null(),
                'timezone' => $this->string(50)->null(),
                'latitude' => $this->decimal(10, 8)->null(),
                'longitude' => $this->decimal(11, 8)->null(),
                'language' => $this->string(10)->null(),
                'referrer' => $this->string()->null(),
                'ip' => $this->string(64)->null(),
                'userAgent' => $this->text()->null(),
                'metadata' => $this->text()->null(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            // Create indexes for performance
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['linkId']);
            $this->createIndex(null, '{{%smartlinks_analytics}}', ['siteId']);
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
        }

        // Create the smartlinks_settings table
        if (!$this->db->tableExists('{{%smartlinks_settings}}')) {
            $this->createTable('{{%smartlinks_settings}}', [
                'id' => $this->primaryKey(),
                // Plugin settings
                'pluginName' => $this->string(255)->notNull()->defaultValue('Smart Links'),
                // Site settings
                'enabledSites' => $this->text()->null()->comment('JSON array of enabled site IDs'),
                // Asset/Volume settings
                'imageVolumeUid' => $this->string()->null(),
                // URL settings
                'slugPrefix' => $this->string(50)->notNull()->defaultValue('go'),
                'qrPrefix' => $this->string(50)->notNull()->defaultValue('go/qr'),
                // QR Code settings
                'defaultQrSize' => $this->integer()->notNull()->defaultValue(256),
                'defaultQrColor' => $this->string(7)->notNull()->defaultValue('#000000'),
                'defaultQrBgColor' => $this->string(7)->notNull()->defaultValue('#FFFFFF'),
                'defaultQrFormat' => $this->string(3)->notNull()->defaultValue('png'),
                'defaultQrErrorCorrection' => $this->string(1)->notNull()->defaultValue('M'),
                'defaultQrMargin' => $this->integer()->notNull()->defaultValue(4),
                'qrModuleStyle' => $this->string(10)->notNull()->defaultValue('square'),
                'qrEyeStyle' => $this->string(10)->notNull()->defaultValue('square'),
                'qrEyeColor' => $this->string(7)->null(),
                'enableQrLogo' => $this->boolean()->notNull()->defaultValue(false),
                'qrLogoVolumeUid' => $this->string()->null(),
                'defaultQrLogoId' => $this->integer()->null(),
                'qrLogoSize' => $this->integer()->notNull()->defaultValue(20),
                'enableQrCodeCache' => $this->boolean()->notNull()->defaultValue(true),
                'qrCodeCacheDuration' => $this->integer()->notNull()->defaultValue(86400),
                'cacheStorageMethod' => $this->string(10)->notNull()->defaultValue('file')->comment('Cache storage method: file or redis'),
                'enableQrDownload' => $this->boolean()->notNull()->defaultValue(true),
                'qrDownloadFilename' => $this->string()->notNull()->defaultValue('{slug}-qr-{size}'),
                // Analytics settings
                'enableAnalytics' => $this->boolean()->notNull()->defaultValue(true),
                'analyticsRetention' => $this->integer()->notNull()->defaultValue(90),
                'anonymizeIpAddress' => $this->boolean()->notNull()->defaultValue(false),
                // Template settings
                'redirectTemplate' => $this->string()->null(),
                'qrTemplate' => $this->string()->null(),
                // Device & Geo Detection
                'enableGeoDetection' => $this->boolean()->notNull()->defaultValue(false),
                'cacheDeviceDetection' => $this->boolean()->notNull()->defaultValue(true),
                'deviceDetectionCacheDuration' => $this->integer()->notNull()->defaultValue(3600),
                'languageDetectionMethod' => $this->string(10)->notNull()->defaultValue('browser'),
                // Interface settings
                'itemsPerPage' => $this->integer()->notNull()->defaultValue(100),
                'notFoundRedirectUrl' => $this->string()->notNull()->defaultValue('/'),
                // Export settings
                'includeDisabledInExport' => $this->boolean()->defaultValue(false),
                'includeExpiredInExport' => $this->boolean()->notNull()->defaultValue(false),
                // Integration settings
                'enabledIntegrations' => $this->text()->null()->comment('JSON array of enabled integration handles'),
                'seomaticTrackingEvents' => $this->text()->null()->comment('JSON array of event types to track in SEOmatic'),
                'seomaticEventPrefix' => $this->string(50)->defaultValue('smart_links')->comment('Event prefix for GTM/GA events'),
                'redirectManagerEvents' => $this->text()->null()->comment('JSON array of redirect manager event types'),
                // Logging
                'logLevel' => $this->string(20)->notNull()->defaultValue('error'),
                // Timestamps
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            // Create indexes
            $this->createIndex(null, '{{%smartlinks_settings}}', ['enableAnalytics']);
            $this->createIndex(null, '{{%smartlinks_settings}}', ['enableGeoDetection']);
            $this->createIndex(null, '{{%smartlinks_settings}}', ['cacheDeviceDetection']);

            // Add foreign key for logo
            $this->addForeignKey(null, '{{%smartlinks_settings}}', ['defaultQrLogoId'], '{{%assets}}', ['id'], 'SET NULL');

            // Insert default settings row
            $this->insert('{{%smartlinks_settings}}', [
                'dateCreated' => Db::prepareDateForDb(new \DateTime()),
                'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
                'uid' => StringHelper::UUID(),
            ]);
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
