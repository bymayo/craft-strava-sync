<?php

namespace bymayo\stravasync\services;

use bymayo\stravasync\StravaSync;
use bymayo\stravasync\records\UsersRecord as UsersRecord;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use craft\elements\User;
use craft\services\Users;
use craft\helpers\FileHelper;
use craft\records\Session;

class UserService extends Component
{

   private $_requestClient;
   private $_athlete;
   private $_tokens;

    // Public Methods
    // =========================================================================

    public function checkUserLinkExists()
    {

      $user = Craft::$app->getUser()->getIdentity();
      $userRecord = UsersRecord::findOne(['userId' => $user->id]);

      if ($userRecord) {
         return $userRecord;
      }

   }

   public function checkAthleteLinkExists()
   {

      $userRecord = UsersRecord::findOne(['athleteId' => $this->_athlete['id']]);

      if ($userRecord) {
         return $userRecord;
      }

   }

   public function getUserFromAthleteId($athleteId)
   {
      $userRecord = UsersRecord::findOne(['athleteId' => $athleteId]);

      if ($userRecord) {
         return $userRecord;
      }
   }

   public function postAuthenticateRedirect($tokens)
   {

      $this->_tokens = $tokens;
      $this->_requestClient = StravaSync::getInstance()->oauthService->requestClient($tokens['accessToken']);
      $this->_athlete = $this->_requestClient->getAthlete();

      $user = Craft::$app->getUser()->getIdentity();
      $check = $this->checkAthleteLinkExists();

      if ($user && !$check) {
         // Link current user to Strava
         $this->linkUserToStrava($user->id);
         return '/settings/accounts/';
      }
      elseif(!$user && $check)
      {
         // If user already linked, log them in.
         if($this->loginUser($check->userId)) {
            return StravaSync::$plugin->getSettings()->loginRedirect;
          }

          $this->_loginFailure();
          return false;

      }
      elseif(!$user && !$check) {
         // If user is not registered, then chuck them to the onboard form
         Craft::$app->getSession()->set('tokens', $this->_tokens);
         return StravaSync::$plugin->getSettings()->onboardRedirect;
      }

      Craft::$app->getSession()->setError('Your Strava account is linked to another user account.');
      return '/settings/accounts/';

   }

    public function linkUserToStrava($userId)
    {

        $record = new UsersRecord();
        $record->userId = $userId;
        $record->athleteId = $this->_athlete['id'];
        $record->accessToken = $this->_tokens['accessToken'];
        $record->refreshToken = $this->_tokens['refreshToken'];
        $record->expires = $this->_tokens['expires'];
        $record->save(true);

    }

    public function unlinkUserFromStrava($user)
    {

        $athleteRecord = UsersRecord::findOne(['userId' => $user->id]);

        if ($athleteRecord) {
            $athleteRecord->delete();
        }

        return false;

    }

    public function _loginFailure()
    {
        Craft::$app->getSession()->setError('Login failed.');
    }

    public function getFieldMapping()
    {
        $fields = array(
         'photo' => 'profile',
         'username' => 'id',
         'firstName' => 'firstname',
         'lastName' => 'lastname'
      );

        if (isset(StravaSync::$plugin->getSettings()->fieldMapping)) {
            $fields = StravaSync::$plugin->getSettings()->fieldMapping;
        }

        return $fields;

    }

      public function loginUser($userId)
      {

         $user = Craft::$app->users->getUserById($userId);

         if (Craft::$app->getUser()->login($user))
         {
            StravaSync::getInstance()->oauthService->refreshTokens();
            return Craft::$app->getUser()->login($user);
         }

      }

    public function registerUser($emailAddress)
    {

      $this->_tokens = StravaSync::getInstance()->oauthService->getTokensSession();
      $this->_requestClient = StravaSync::getInstance()->oauthService->requestClient($this->_tokens['accessToken']);
      $this->_athlete = $this->_requestClient->getAthlete();

        $user = new User();

        $user->username = $this->_athlete['username'] . $this->_athlete['id']; // Placed before so it can be overwritten by mapped fields

        foreach ($this->getFieldMapping() as $key => $value) {
            $user->{$key} = preg_replace("/[^A-Za-z0-9 ]/", '', $this->_athlete[$value]);
        }

        $user->email = $emailAddress;
        $user->pending = false;
        $user->validate(null, false);

        // Save User
        Craft::$app->getElements()->saveElement($user, false);

        // Assign to User Group and Activate
        Craft::$app->users->assignUserToGroups(
         $user->id,
         [StravaSync::$plugin->getSettings()->defaultUserGroup]
      );

        // Link with Strava
        $this->linkUserToStrava($user->id);

        // Set User Photo
        $this->saveProfilePhoto($user);

        // Activate User
        Craft::$app->users->activateUser($user);

        // Login User
        return $this->loginUser($user->id);

    }

    public function saveProfilePhoto($user)
    {

        $filename = $this->_athlete['id'];
        $extension = '.jpg';
        $photoUrl = $this->_athlete['profile'];

        $tempPath = Craft::$app->path->getTempPath() . '/strava-sync/userphotos/' . $this->_athlete['id'] . '/';

        FileHelper::createDirectory($tempPath);

        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', $photoUrl, [
         'save_to' => $tempPath . $filename
      ]);

        if ($response->getStatusCode() !== 200) {
            return true;
        }

        rename($tempPath . $filename, $tempPath . $filename . $extension);

        $image = Craft::$app->images->loadImage($tempPath . $filename . $extension);
        Craft::$app->users->saveUserPhoto($tempPath . $filename . $extension, $user, $filename . $extension);

    }

    public function disconnect()
    {

      $user = Craft::$app->getUser()->getIdentity();

      if ($user) {
         $this->unlinkUserFromStrava($user);
         // return true;
      }

    }

}
