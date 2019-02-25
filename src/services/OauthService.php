<?php
/**
 * Strava Sync plugin for Craft CMS 3.x
 *
 * Connect to Strava with oAuth and sync activities etc to Craft CMS
 *
 * @link      http://bymayo.co.uk
 * @copyright Copyright (c) 2019 bymayo
 */

namespace bymayo\stravasync\services;

use Strava\API\OAuth;
use Strava\API\Client;
use Strava\API\Exception;
use Strava\API\Service\REST;

use bymayo\stravasync\StravaSync;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;

/**
 * @author    bymayo
 * @package   StravaSync
 * @since     1.0.0
 */
class OauthService extends Component
{
    // Public Methods
    // =========================================================================

    public function _authenticateFailure()
    {
        Craft::$app->getSession()->setError('stravasync', 'Couldn’t authenticate Strava.');
    }

    public function authenticate()
    {
        try {
            $options = [
               'clientId'     => StravaSync::$plugin->getSettings()->clientId,
               'clientSecret' => StravaSync::$plugin->getSettings()->clientSecret,
               'redirectUri'  => UrlHelper::actionUrl('strava-sync/oauth/connect')
           ];

            $oauth = new Oauth($options);

            if (!isset($_GET['code'])) {

                $authUrl = $oauth->getAuthorizationUrl();
                $_SESSION['oauth2state'] = $oauth->getState();
                header('Location: ' . $authUrl);
                exit;

            } else {
               $this->clearToken();
                $token = $oauth->getAccessToken('authorization_code', [
                   'code' => $_GET['code']
               ]);
                Craft::$app->getSession()->set('token', $token->getToken());
                return true;

            }
        } catch (Exception $e) {
            print $e->getMessage();
        }
    }

    public function clearToken()
    {
      return Craft::$app->getSession()->remove('token');
   }

    public function getToken()
    {
        return Craft::$app->getSession()->get('token');
    }

    public function request()
    {
        $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
        $service = new REST($this->getToken(), $adapter);
        return $client = new Client($service);
    }

    public function connect()
    {
      if ($this->authenticate()) {
         return StravaSync::getInstance()->userService->postAuthenticateRedirect();
      }
      exit;
   }

}
