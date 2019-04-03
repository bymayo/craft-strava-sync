<?php

namespace bymayo\stravasync\services;

use Strava\API\OAuth;
use Strava\API\Client;
use Strava\API\Exception;
use Strava\API\Service\REST;

use League\OAuth2\Client\Token\AccessToken;

use bymayo\stravasync\StravaSync;
use bymayo\stravasync\records\UsersRecord as UsersRecord;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;

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
                        'read,activity:read,read_all,activity:read_all,profile:read_all'
                           // 'read',
                           // 'activity:read',
                           // 'activity:write',
                           // 'profile:write',
                           // 'read_all',
                           // 'activity:read_all',
                           // 'profile:read_all',
                           // 'view_private'
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

      $tokens = $this->getTokens();

      $accessToken = new AccessToken(
         array(
            'access_token' => $tokens['accessToken'],
            'refresh_token' => $tokens['refreshToken'],
            'expires' => $tokens['expires']
         )
      );

      if ($accessToken->hasExpired()) {

         $token = $oauth->getAccessToken(
            'refresh_token',
            [
               'refresh_token' => $accessToken->getRefreshToken()
            ]
         );

         $tokens = array(
            'accessToken' => $token->getToken(),
            'refreshToken' => $token->getRefreshToken(),
            'expires' => $token->getExpires()
         );

         $this->updateTokens($tokens);

      }

      return true;

   }

   public function getTokens($user = null)
   {
      if (!$user)
      {
         $user = Craft::$app->getUser()->getIdentity();
      }

      $userRecord = UsersRecord::findOne(['userId' => $user->id]);

      if ($userRecord) {
         return array(
            'accessToken' => $userRecord->accessToken,
            'refreshToken' => $userRecord->refreshToken,
            'expires' => $userRecord->expires
         );
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

         $userRecord->accessToken = $tokens['accessToken'];
         $userRecord->refreshToken = $tokens['refreshToken'];
         $userRecord->expires = $tokens['expires'];
         $userRecord->save(true);

         return true;
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

    public function requestClient($accessToken = null)
    {

      if (!$accessToken && $this->refreshTokens())
      {
         $tokens = $this->getTokens();
         $accessToken = $tokens['accessToken'];
      }

      $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
      $service = new REST($accessToken, $adapter);
      return $client = new Client($service);

    }

    public function request($method, $params, $userId)
    {

      $client = $this->requestClient();
      return $client->$method($params);

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
