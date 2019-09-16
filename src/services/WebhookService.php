<?php

namespace bymayo\stravasync\services;

use bymayo\stravasync\StravaSync;
use bymayo\stravasync\records\UsersRecord as UsersRecord;
use bymayo\stravasync\events\WebhookSyncEvent;

use Craft;
use craft\base\Component;

use yii\helpers\Json;

class WebhookService extends Component
{

    // Constants
    // =========================================================================

   const EVENT_WEBHOOK_SYNC = 'webhookSync';

    // Public Methods
    // =========================================================================

    public function sync($request)
    {

        $requestAsJson = Json::decode($request->getRawBody());

        $athlete = StravaSync::getInstance()->userService->getUserFromAthleteId($requestAsJson['owner_id']);

        if ($athlete){

          if ($this->hasEventHandlers(self::EVENT_WEBHOOK_SYNC)) {

             $this->trigger(
                self::EVENT_WEBHOOK_SYNC, 
                new WebhookSyncEvent(
                   [
                      'athlete' => $athlete,
                      'request' => $requestAsJson
                   ]
                )
             );

          }

          return true;

        }

        return false;

    }

    public function splitString($string)
    {
      $explode = explode("&", $string);

      foreach($explode as $part){
          $tmp = explode("=", $part);
          $out[$tmp[0]] = $tmp[1];
      }

      return $out;
   }

    public function subscriptionValidate($queryString)
    {

      $response = (object)[
          "hub.challenge" => $queryString['hub.challenge']
      ];

      return $response;

   }

}
