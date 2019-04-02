<?php

namespace bymayo\stravasync\migrations;

use Craft;
use craft\db\Migration;
/**
 * m190401_102026_addAccessTokenColumns migration.
 */
class m190401_102026_addAccessTokenColumns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

         if (!$this->db->columnExists('{{%stravasync_users}}', 'accessToken')) {
              $this->addColumn('{{%stravasync_users}}', 'accessToken', $this->string(255)->after('athleteId')->notNull());
         }

         if (!$this->db->columnExists('{{%stravasync_users}}', 'refreshToken')) {
              $this->addColumn('{{%stravasync_users}}', 'refreshToken', $this->string(255)->after('accessToken')->notNull());
         }

         return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190401_102026_addAccessTokenColumns cannot be reverted.\n";
        return false;
    }
}
