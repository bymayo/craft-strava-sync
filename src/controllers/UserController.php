<?php
/**
 * Strava Sync plugin for Craft CMS 3.x
 *
 * Connect to Strava with oAuth and sync activities etc to Craft CMS
 *
 * @link      http://bymayo.co.uk
 * @copyright Copyright (c) 2019 bymayo
 */

namespace bymayo\stravasync\controllers;

use bymayo\stravasync\StravaSync;
use bymayo\stravasync\services\OauthService;

use Craft;
use craft\web\Controller;
use craft\web\twig\variables as Variables;

/**
 * @author    bymayo
 * @package   StravaSync
 * @since     1.0.0
 */
class UserController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
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
