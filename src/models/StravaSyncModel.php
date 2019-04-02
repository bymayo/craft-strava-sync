<?php

namespace bymayo\stravasync\models;

use bymayo\stravasync\StravaSync;

use Craft;
use craft\base\Model;

class StravaSyncModel extends Model
{
    // Public Properties
    // =========================================================================

    public $someAttribute = 'Some Default';

    // Public Methods
    // =========================================================================

    public function rules()
    {
        return [
            ['someAttribute', 'string'],
            ['someAttribute', 'default', 'value' => 'Some Default'],
        ];
    }
}
