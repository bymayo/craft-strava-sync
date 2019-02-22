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
class OauthController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['connect', 'register'];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */

    public function actionConnect()
    {
        $connect = StravaSync::getInstance()->oauthSerivce->connect();

        if (!$connect) {
            // Craft::$app->getSession()->setError(\Craft::t('app', 'Couldn’t connect to Strava.'));
            StravaSync::getInstance()->oauthSerivce->_loginFailure();
            return false;
        }

        return $this->redirect($connect);
    }

    public function actionRegister()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $emailAddress = $request->getBodyParam('email');

        if (!StravaSync::getInstance()->oauthSerivce->registerUser($emailAddress)) {
            StravaSync::getInstance()->oauthSerivce->_loginFailure();
            return false;
        }

        return $this->redirect(StravaSync::$plugin->getSettings()->loginRedirect);
    }
}
