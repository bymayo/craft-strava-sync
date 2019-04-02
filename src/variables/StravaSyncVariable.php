<?php
/**
 * Strava Sync plugin for Craft CMS 3.x
 *
 * Connect to Strava with oAuth and sync activities etc to Craft CMS
 *
 * @link      http://bymayo.co.uk
 * @copyright Copyright (c) 2019 bymayo
 */

namespace bymayo\stravasync\variables;

use bymayo\stravasync\StravaSync;
use craft\helpers\UrlHelper;

use Craft;

/**
 * @author    bymayo
 * @package   StravaSync
 * @since     1.0.0
 */
class StravaSyncVariable
{
    // Public Methods
    // =========================================================================


    public function request($method, $params = null, $userId = null)
    {
      return $connect = StravaSync::getInstance()->oauthService->request($method, $params, $userId);
   }

    public function connected()
    {
        return StravaSync::getInstance()->userService->checkUserLinkExists();
    }

    public function connectUrl()
    {
        return UrlHelper::actionUrl('strava-sync/oauth/connect');
    }

    public function disconnectUrl()
    {
        return UrlHelper::actionUrl('strava-sync/user/disconnect');
    }
}
