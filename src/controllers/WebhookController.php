<?php

namespace bymayo\stravasync\controllers;

use bymayo\stravasync\StravaSync;
use bymayo\stravasync\services\OauthService;

use Craft;
use craft\web\Controller;
use craft\web\twig\variables as Variables;

use yii\helpers\Json;
use yii\web\Response;
use yii;

class WebhookController extends Controller
{

    // Public Properties
    // =========================================================================

    public $enableCsrfValidation = false;

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

            StravaSync::getInstance()->webhookService->sync($request);

        }

     }

}
