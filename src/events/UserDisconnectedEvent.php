<?php
namespace bymayo\stravasync\events;

use yii\base\Event;

class UserDisconnectedEvent extends Event
{
    // Properties
    // =========================================================================

    public $user;
    
}