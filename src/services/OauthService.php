<?php

namespace bymayo\stravasync\services;

use Strava\API\OAuth;
use Strava\API\Client;
use Strava\API\Exception;
use Strava\API\Service\REST;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

use Iamstuartwilson\StravaApi;

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
                     'read,read_all,profile:read_all,activity:read,activity:read_all'
                        // 'read',
                        // 'read_all',
                        // 'profile:read_all',
                        // 'profile:write',
                        // 'activity:read',
                        // 'activity:read_all',
                        // 'activity:write',
                        // 'view_private'
                     ]
                  ]
               );

               $_SESSION['oauth2state'] = $oauth->getState();

               header('Location: ' . $authUrl);

               exit;

         } elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

            if (isset($_SESSION['oauth2state'])) {
               unset($_SESSION['oauth2state']);
            }
            
            exit('Invalid state');

         } else {

            try {

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
            
            } catch (IdentityProviderException $e) {

               exit($e->getMessage());
      
            }
            

         }

    }

    public function refreshTokens($userId = null)
    {

      $options = [
          'clientId'     => StravaSync::$plugin->getSettings()->clientId,
          'clientSecret' => StravaSync::$plugin->getSettings()->clientSecret,
          'urlAccessToken' => 'https://www.strava.com/oauth/token',
      ];

      $oauth = new Oauth($options);

      $tokens = $this->getTokens($userId);

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

         $this->updateTokens($tokens, $userId);

      }

      return $tokens['accessToken'];

   }

   public function getTokens($userId = null)
   {

      if (!$userId)
      {
         $user = Craft::$app->getUser()->getIdentity();
         $userId = $user->id;
      }

      $userRecord = UsersRecord::findOne(['userId' => $userId]);

      if ($userRecord) {
         return array(
            'accessToken' => $userRecord->accessToken,
            'refreshToken' => $userRecord->refreshToken,
            'expires' => $userRecord->expires
         );
      }

   }

   public function updateTokens($tokens, $userId = null)
   {

      if (!$userId)
      {
         $user = Craft::$app->getUser()->getIdentity();
         $userId = $user->id;
      }

      $userRecord = UsersRecord::findOne(['userId' => $userId]);

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

    public function requestClient($accessToken = null, $userId = null)
    {

      if ($userId) {

         $accessToken = $this->refreshTokens($userId);

      }
      elseif (!$accessToken)
      {

         $accessToken = $this->refreshTokens();

      }

      $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
      $service = new REST($accessToken, $adapter);
      return $client = new Client($service);

    }

    public function request($method, $params = null, $userId = null)
    {

      $client = $this->requestClient(null, $userId);
      return $client->$method($params);

   }

    public function connect()
    {

      $tokens = $this->authenticate();

      if ($tokens)
      {
         return StravaSync::getInstance()->userService->postAuthenticateRedirect($tokens);
      }

      exit;

   }

}
