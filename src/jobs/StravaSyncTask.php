<?php

namespace bymayo\stravasync\jobs;

use bymayo\stravasync\StravaSync;

use Craft;
use craft\queue\BaseJob;

class StravaSyncTask extends BaseJob
{
    // Public Properties
    // =========================================================================

    public $someAttribute = 'Some Default';

    // Public Methods
    // =========================================================================

    public function execute($queue)
    {
        // Do work here
    }

    // Protected Methods
    // =========================================================================

    protected function defaultDescription(): string
    {
        return Craft::t('strava-sync', 'StravaSyncTask');
    }
}
