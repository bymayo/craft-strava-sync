<?php
/**
 * Strava Sync plugin for Craft CMS 3.x
 *
 * Connect to Strava with oAuth and sync activities etc to Craft CMS
 *
 * @link      http://bymayo.co.uk
 * @copyright Copyright (c) 2019 bymayo
 */

namespace bymayo\stravasync\records;

use bymayo\stravasync\StravaSync;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    bymayo
 * @package   StravaSync
 * @since     1.0.0
 */
class UsersRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%stravasync_users}}';
    }
}
