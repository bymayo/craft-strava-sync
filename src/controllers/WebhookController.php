<?php

namespace bymayo\stravasync\controllers;

use bymayo\stravasync\StravaSync;
use bymayo\stravasync\services\OauthService;
use modules\eventsmodule\EventsModule as EventsModule;

use Craft;
use craft\web\Controller;
use craft\web\twig\variables as Variables;

use yii\helpers\Json;
use yii\web\Response;
use yii;

class WebhookController extends Controller
{

    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = ['sync'];

    // Public Methods
    // =========================================================================

     public function actionSync()
     {

        $webhookService = StravaSync::getInstance()->webhookService;

        $request = Craft::$app->getRequest();

        if ($request->isGet)
        {

           $queryString = $request->getQueryStringWithoutPath();
           $queryStringParts = $queryString ? $webhookService->splitString($queryString) : null;

            return $this->asJson(
               $webhookService->subscriptionValidate(
                  $queryStringParts
               )
            );

        }
        else {

             $requestBody = Json::decode($request->getRawBody());

             $user = StravaSync::getInstance()->userService->getUserFromAthleteId($requestBody['owner_id']);

             if ($user){

                if ($requestBody['aspect_type'] == 'create' && $requestBody['object_type'] == 'activity')
                {
                   // Sync to Events Module
                   EventsModule::getInstance()->events->sync($requestBody, $user->userId);
                   return true;

                }

             }

             return false;

        }

     }

}
