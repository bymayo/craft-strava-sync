<?php

namespace bymayo\stravasync\controllers;

use bymayo\stravasync\StravaSync;
use bymayo\stravasync\services\OauthService;

use Craft;
use craft\web\Controller;
use craft\web\twig\variables as Variables;

class OauthController extends Controller
{
    
    // Public Properties
    // =========================================================================

    public $enableCsrfValidation = false;

    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = ['connect'];

    // Public Methods
    // =========================================================================
    
     public function actionConnect()
     {

         $connect = StravaSync::getInstance()->oauthService->connect();

         if (!$connect) {
             StravaSync::getInstance()->oauthService->_authenticateFailure();
             return false;
         }

         return $this->redirect($connect);

     }

}
