<?php

namespace bymayo\stravasync\controllers;

use bymayo\stravasync\StravaSync;
use bymayo\stravasync\services\OauthService;

use Craft;
use craft\web\Controller;
use craft\web\twig\variables as Variables;

class UserController extends Controller
{

    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = ['register', 'connect', 'disconnect'];

    // Public Methods
    // =========================================================================

    public function actionRegister()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $emailAddress = $request->getBodyParam('email');

        $user = Craft::$app->users->getUserByUsernameOrEmail($emailAddress);

         if ($user) {
            Craft::$app->getSession()->setError('A user already exists with that email address.');
            return $this->redirect(StravaSync::$plugin->getSettings()->onboardRedirect);
         }

        if (!StravaSync::getInstance()->userService->registerUser($emailAddress)) {
            StravaSync::getInstance()->userService->_loginFailure();
            return false;
        }

        return $this->redirect(StravaSync::$plugin->getSettings()->loginRedirect);
    }

    public function actionDisconnect()
    {
      StravaSync::getInstance()->userService->disconnect();
      return $this->redirect('/settings/accounts');
   }

}
