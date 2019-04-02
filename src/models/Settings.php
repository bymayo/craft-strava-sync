<?php
/**
 * Strava Sync plugin for Craft CMS 3.x
 *
 * Connect to Strava with oAuth and sync activities etc to Craft CMS
 *
 * @link      http://bymayo.co.uk
 * @copyright Copyright (c) 2019 bymayo
 */

namespace bymayo\stravasync\models;

use bymayo\stravasync\StravaSync;

use Craft;
use craft\base\Model;

/**
 * @author    bymayo
 * @package   StravaSync
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $clientId;
    public $clientSecret;
    public $loginRedirect;
    public $onboardRedirect;
    public $defaultUserGroup;
    public $fieldMapping = null;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clientId', 'clientSecret', 'loginRedirect', 'onboardRedirect'], 'string'],
            [['defaultUserGroup'], 'integer'],
            [['fieldMapping'], 'array']
        ];
    }
}
