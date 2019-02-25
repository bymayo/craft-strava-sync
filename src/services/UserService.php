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

use bymayo\stravasync\StravaSync;
use bymayo\stravasync\records\UsersRecord as UsersRecord;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use craft\elements\User;
use craft\services\Users;
use craft\helpers\FileHelper;
use craft\records\Session;

/**
 * @author    bymayo
 * @package   StravaSync
 * @since     1.0.0
 */
class UserService extends Component
{
    // Public Methods
    // =========================================================================

    public function checkUserLinkExists()
    {

      $user = Craft::$app->getUser()->getIdentity();
      $athleteRecord = UsersRecord::findOne(['userId' => $user->id]);

      if ($athleteRecord) {
         return $athleteRecord;
      }

   }

   public function checkAthleteLinkExists()
   {
      $athlete = StravaSync::getInstance()->oauthService->request()->getAthlete();
      $athleteRecord = UsersRecord::findOne(['athleteId' => $athlete['id']]);

      if ($athleteRecord) {
         return $athleteRecord;
      }
   }

   public function postAuthenticateRedirect()
   {

      $check = $this->checkAthleteLinkExists();
      $user = Craft::$app->getUser()->getIdentity();
      $athlete = StravaSync::getInstance()->oauthService->request()->getAthlete();

      if ($user && !$check) {
         // Link current user to Strava
         $this->linkUserToStrava($user->id, $athlete['id']);
         return '/settings/accounts/';
      }
      elseif(!$user && $check)
      {
         // If user already linked, log them in.
         if(!$this->loginUser($check->userId)) {
              $this->_loginFailure();
              return false;
          }
          return StravaSync::$plugin->getSettings()->loginRedirect;
      }
      elseif(!$user && !$check) {
         // If user is not registered, then chuck them to the onboard form
         return StravaSync::$plugin->getSettings()->onboardRedirect;
      }

      Craft::$app->getSession()->setError('stravasync', 'Sorry, but your Strava account is currently linked to another account.');
      return '/settings/accounts/';

   }

    public function linkUserToStrava($userId, $athleteId)
    {
        $record = new UsersRecord();
        $record->userId = $userId;
        $record->athleteId = $athleteId;
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
        Craft::$app->getSession()->setError('stravasync', 'Sorry, login failed.');
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
        return Craft::$app->getUser()->login($user);
    }

    public function registerUser($emailAddress)
    {

        $athlete = StravaSync::getInstance()->oauthService->request()->getAthlete();

        $user = Craft::$app->users->getUserByUsernameOrEmail($emailAddress);

        if ($user) {
            Craft::$app->getSession()->setError('stravasync', 'A user already exists with that email address.');
        }

        $user = new User();

        $user->username = $athlete['username'] . $athlete['id']; // Placed before so it can be overwritten by mapped fields
        foreach ($this->getFieldMapping() as $key => $value) {
            $user->{$key} = preg_replace("/[^A-Za-z0-9 ]/", '', $athlete[$value]);
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
        $this->linkUserToStrava($user->id, $athlete['id']);

        // Set User Photo
        $this->saveProfilePhoto($user, $athlete);

        // Activate User
        Craft::$app->users->activateUser($user);

        // Login User
        return $this->loginUser($user->id);
    }

    public function saveProfilePhoto($user, $athlete)
    {
        $filename = $athlete['id'];
        $extension = '.jpg';
        $photoUrl = $athlete['profile'];

        $tempPath = Craft::$app->path->getTempPath() . '/strava-sync/userphotos/' . $athlete['id'] . '/';

        FileHelper::createDirectory($tempPath);

        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', $photoUrl, [
         'save_to' => $tempPath . $filename
      ]);

        if ($response->getStatusCode() !== 200) {
            return;
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
