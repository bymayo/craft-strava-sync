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
use bymayo\stravasync\records\UsersRecord as UsersRecord;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use craft\elements\User;
use craft\services\Users;
use craft\helpers\FileHelper;

/**
 * @author    bymayo
 * @package   StravaSync
 * @since     1.0.0
 */
class OauthService extends Component
{
    // Public Methods
    // =========================================================================

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

    public function linkUserToStrava($userId, $athleteId)
    {
        $record = new UsersRecord();
        $record->userId = $userId;
        $record->athleteId = $athleteId;
        $record->save(true);
    }

    public function removeUserFromStrava($user)
    {
        $athleteRecord = UsersRecord::findOne(['userId' => $user->id]);

        if ($athleteRecord) {
            $athleteRecord->delete();
        }
    }

    public function _loginFailure()
    {
        Craft::$app->getSession()->setError('stravasync', 'Couldn’t authenticate Strava.');
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
        $athlete = $this->request()->getAthlete();

        $user = Craft::$app->users->getUserByUsernameOrEmail($emailAddress);

        if ($user) {
            Craft::$app->getSession()->setError('stravasync', 'A user already exists with that email address.');
            return false;
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

    public function connectToUser()
    {
        $athlete = $this->request()->getAthlete();
        $athleteRecord = UsersRecord::find()->where(['athleteId' => $athlete['id']]);

        if ($athleteRecord->exists()) {
            if (!$this->loginUser($athleteRecord->one()->userId)) {
                $this->_loginFailure();
                return false;
            }
            return StravaSync::$plugin->getSettings()->loginRedirect;
        } else {
            return StravaSync::$plugin->getSettings()->onboardRedirect;
        }
    }

    public function connect()
    {
        if ($this->authenticate()) {
            return $this->connectToUser();
        }
        exit;
    }

    public function disconnect()
    {
    }
}
