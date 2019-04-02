<?php

namespace bymayo\stravasync\models;

use bymayo\stravasync\StravaSync;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $clientId;
    public $clientSecret;
    public $loginRedirect;
    public $onboardRedirect;
    public $defaultUserGroup;
    public $fieldMapping = null;
    public $scope = 'read_all';

    // Public Methods
    // =========================================================================

    public function rules()
    {
        return [
            [['clientId', 'clientSecret', 'loginRedirect', 'onboardRedirect', 'scope'], 'string'],
            [['defaultUserGroup'], 'integer'],
            [['fieldMapping'], 'array']
        ];
    }
}
