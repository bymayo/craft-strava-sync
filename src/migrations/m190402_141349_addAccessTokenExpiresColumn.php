<?php

namespace bymayo\stravasync\migrations;

use Craft;
use craft\db\Migration;

/**
 * m190402_141349_addAccessTokenExpiresColumns migration.
 */
class m190402_141349_addAccessTokenExpiresColumn extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

      if (!$this->db->columnExists('{{%stravasync_users}}', 'expires')) {
            $this->addColumn('{{%stravasync_users}}', 'expires', $this->integer()->after('refreshToken')->notNull());
      }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190402_141349_addAccessTokenExpiresColumn cannot be reverted.\n";
        return false;
    }
}
