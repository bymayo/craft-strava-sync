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

               $this->clearAccessTokenSession();

               $token = $oauth->getAccessToken(
                  'authorization_code',
                  [
                     'code' => $_GET['code']
                  ]
               );

               // $tokens = array(
               //    'accessToken' => $token->getToken(),
               //    'refreshToken' => $token->getRefreshToken()
               // );

               return $token->getToken();

            }
        } catch (Exception $e) {
            print $e->getMessage();
        }
    }

    public function clearAccessTokenSession()
    {
      Craft::$app->getSession()->remove('accessToken');
      return true;
   }

    public function getAccessTokenSession()
    {
      $accessToken = Craft::$app->getSession()->get('accessToken');
      $this->clearAccessTokenSession();
        return $accessToken;
    }

    public function request($accesToken)
    {
        $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
        $service = new REST($accesToken, $adapter);
        return $client = new Client($service);
    }

    public function connect()
    {

      $tokens = $this->authenticate();

      if ($this->authenticate())
      {
         return StravaSync::getInstance()->userService->postAuthenticateRedirect($tokens);
      }

      exit;
   }

}
