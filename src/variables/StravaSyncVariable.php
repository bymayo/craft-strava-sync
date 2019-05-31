<?php

namespace bymayo\stravasync\variables;

use bymayo\stravasync\StravaSync;
use craft\helpers\UrlHelper;

use Craft;

class StravaSyncVariable
{
    // Public Methods
    // =========================================================================


    public function request($method, $params = null, $userId = null)
    {
      return $connect = StravaSync::getInstance()->oauthService->request($method, $params, $userId);
   }

    public function connected($userId)
    {
        return StravaSync::getInstance()->userService->checkUserLinkExists($userId);
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
