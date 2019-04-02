<?php

namespace bymayo\stravasync\migrations;

use Craft;
use craft\db\Migration;

class m190402_141349_addAccessTokenExpiresColumn extends Migration
{

    public function safeUp()
    {

      if (!$this->db->columnExists('{{%stravasync_users}}', 'expires')) {
            $this->addColumn('{{%stravasync_users}}', 'expires', $this->integer()->after('refreshToken')->notNull());
      }

    }

    public function safeDown()
    {
        echo "m190402_141349_addAccessTokenExpiresColumn cannot be reverted.\n";
        return false;
    }
}
