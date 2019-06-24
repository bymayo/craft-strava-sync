<?php

namespace bymayo\stravasync\migrations;

use bymayo\stravasync\StravaSync;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    bymayo
 * @package   StravaSync
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    public $driver;

    // Public Methods
    // =========================================================================

    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%stravasync_stravasyncrecord}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%stravasync_users}}',
                [
                    'id' => $this->primaryKey(),
                    'userId' => $this->integer()->notNull(),
                    'athleteId' => $this->string(255)->notNull(),
                    'accessToken' => $this->string(255)->notNull(),
                    'refreshToken' => $this->string(255)->notNull(),
                    'expires' => $this->integer()->notNull(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid()
                ]
            );
        }

        return $tablesCreated;
    }

    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName(
                '{{%stravasync_users}}',
                'athleteId',
                true
            ),
            '{{%stravasync_users}}',
            'athleteId',
            true
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName(
               '{{%stravasync_users}}',
                'userId'
            ),
            '{{%stravasync_users}}',
            'userId',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    protected function insertDefaultData()
    {
    }

    protected function removeTables()
    {
        $this->dropTableIfExists('{{%stravasync_users}}');
    }
}
