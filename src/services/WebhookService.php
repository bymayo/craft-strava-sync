<?php

namespace bymayo\stravasync\services;

use bymayo\stravasync\StravaSync;
use bymayo\stravasync\records\UsersRecord as UsersRecord;

use Craft;
use craft\base\Component;

class WebhookService extends Component
{

    // Public Methods
    // =========================================================================

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
