<?php
namespace bymayo\stravasync\events;

use yii\base\Event;

class WebhookSyncEvent extends Event
{
    // Properties
    // =========================================================================

    public $athlete;
    public $request;
    
}