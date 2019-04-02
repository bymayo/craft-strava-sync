<?php

namespace bymayo\stravasync\records;

use bymayo\stravasync\StravaSync;

use Craft;
use craft\db\ActiveRecord;

class UsersRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    public static function tableName()
    {
        return '{{%stravasync_users}}';
    }
}
