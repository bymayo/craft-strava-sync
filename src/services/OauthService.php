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

use League\OAuth2\Client\Token\AccessToken;

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
        Craft::$app->getSession()->setError('Couldn’t authenticate Strava.');
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

                $authUrl = $oauth->getAuthorizationUrl(
                   [
                      'scope' => [
                           // 'read',
                           // 'activity:read',
                           // 'activity:write',
                           // 'profile:write',
                           'read_all',
                           // 'activity:read_all',
                           // 'profile:read_all',
                           // 'view_private',
                        ]
                   ]
                );

                $_SESSION['oauth2state'] = $oauth->getState();
                header('Location: ' . $authUrl);
                exit;

            } else {

               $this->clearTokensSession();

               $token = $oauth->getAccessToken(
                  'authorization_code',
                  [
                     'code' => $_GET['code']
                  ]
               );

               $tokens = array(
                  'accessToken' => $token->getToken(),
                  'refreshToken' => $token->getRefreshToken(),
                  'expires' => $token->getExpires()
               );

               return $tokens;

            }
        } catch (Exception $e) {
            print $e->getMessage();
        }
    }

    public function refreshTokens()
    {

      $options = [
          'clientId'     => StravaSync::$plugin->getSettings()->clientId,
          'clientSecret' => StravaSync::$plugin->getSettings()->clientSecret,
          'urlAccessToken' => 'https://www.strava.com/oauth/token',
      ];

      $oauth = new Oauth($options);

      // https://github.com/thephpleague/oauth2-client/issues/594
      $accessToken = new AccessToken(
         [
            'access_token' => '06ba89276f82a585a734c2de6881b717e3c3187c',
            'refresh_token' => '1ebad4774e8af345c593689260a83d9bcc1b7a6b',
            'expires' => '1554224082'
         ]
      );

      if ($accessToken->hasExpired()) {

         $tokens = $oauth->getAccessToken(
            'refresh_token',
            [
               'refresh_token' => $accessToken->getRefreshToken()
            ]
         );

         $this->updateTokens($tokens);

      }

   }

   public function updateTokens($tokens, $user = null)
   {

      if (!$user)
      {
         $user = Craft::$app->getUser()->getIdentity();
      }

      $userRecord = UsersRecord::findOne(['userId' => $user->id]);

      if ($userRecord) {
         $record->accessToken = $tokens['accessToken'];
         $record->refreshToken = $tokens['refreshToken'];
         $record->expires = $tokens['expires'];
         $record->save(true);
      }

   }

    public function clearTokensSession()
    {

      Craft::$app->getSession()->remove('tokens');
      return true;

   }

    public function getTokensSession()
    {

      $accessToken = Craft::$app->getSession()->get('tokens');
      $this->clearTokensSession();
       return $accessToken;

    }

    public function requestClient($accesToken)
    {

        $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
        $service = new REST($accesToken, $adapter);
        return $client = new Client($service);

    }

    public function request($method, $params, $userId = null)
    {

      $client = $this->requestClient();
      $client->$method($params);
      $connect = StravaSync::getInstance()->oauthService->connect();
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
