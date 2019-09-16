<img src="https://raw.githubusercontent.com/bymayo/craft-strava-sync/master/resources/icon.png" width="70">

# Strava Sync Plugin for Craft CMS 3.x

Strava Sync is a Craft CMS plugin that lets you connect Strava with Craft CMS. Allowing users to login with Strava oAuth, get data from the Strava API (Athletes, activities, segments, routes etc)

https://plugins.craftcms.com/strava-sync

- [Features](#features)
- [Requirements](#requirements)
- [Install](#install)
- [Configuration](#configuration)
- [Options](#options)
- [Webhooks](#webhooks)
- [Support](#support)

## Features

- Login via oAuth
- Automatically fills profile data (First Name, Last Name, Profile Photo etc)
- Map additional athlete data to user fields (City, Country, Sex etc)
- Get data from the Strava API (Athletes, activities, segments etc)
- Sync data directly in to Craft CMS via Webhooks
- Pane inside a user to show athlete data and if connected/disconnected to Strava
- Table attribute column on 'Users' table, to show if user is connected/disconnected to Strava

## Requirements

- Craft CMS 3.x
- Strava Developer Account (https://developers.strava.com)

## Install

- Install via the Plugin Store in the Craft Admin CP by searching for `Strava Sync`

OR

- Install with Composer via `composer require bymayo/strava-sync` from your project directory
- Install the plugin in the Craft Control Panel under `Settings > Plugins`

## Configuration

1. Go to `Settings -> Strava Sync` in Craft CMS.
2. Create an API Application on Strava - https://www.strava.com/settings/api (Ensuring the `Authorization Callback Domain` is the domain your going to be connecting from)
3. Copy the `Client ID` and `Client Secret` and enter them in to the correct fields in the `Strava Sync` settings.
4. Also, set your `Login Redirect` and `Onboard Redirect` route / paths (See `Onboard` below for an explanation regarding `Onboard Redirect`).
5. Hit save, and follow the templating instructions.

You can also set plugin settings by creating a `strava-sync.php` file in your projects `config` folder. See the `config.php` in this plugin to see all available options.

## Options

### Field Mapping

You can map Strava athlete data (`https://www.strava.com/api/v3/athlete`) to user fields once the user has authorised and registered. This can only be done via the `strava-sync.php` config file add the `fieldMapping` option.

```
'fieldMapping' => [
   'username' => 'id',
   'firstName' => 'firstname',
   'lastName' => 'lastname',
   'userGender' => 'sex',
   'userLocation' => 'country'
]
```

The key (E.g. `username`) is the Craft CMS field your mapping to, and the value (e.g. `id`) is the property from the Strava API Athelete GET (`https://www.strava.com/api/v3/athlete`)

### Scope

When you make an oAuth request you need to tell Strava what you require from that user. You may not require access to all of a users Strava data, e.g. You may only require to get an Athletes Details and not their Activities. To put trust in users authorising your website to connec to their Strava account it's important to choose the correct `scope` value when we make a oAuth call to Strava.

The scope setting is a comma seperated list, by default this is:

`read, activity:read, read_all, activity:read_all, profile:read_all`

You can see a list of available scope options at https://developers.strava.com/docs/oauth-updates/ under `Details about requesting access`.

## Templating

- [Onboard](#onboard-required)
- [Connect URL (Login / Register)](#connect-url-login--register)
- [Disconnected URL](#disconnect-url)
- [Connected](#connected)
- [Request](#request)

### Onboard (Required)
Because the Strava API doesn't give us access to the authorised users email address, we need to collect this to create a Craft CMS user. This is done by placing the following code on to a template and ensuring this template is the one accessible from the `Onboard Redirect` plugin setting:

```
<form method="post" accept-charset="UTF-8">

   {{ getCsrfInput() }}
   {{ actionInput('strava-sync/user/register') }}

   <label for="email">Email Address</label>
   <input type="email" name="email" id="email" required>
   {% if craft.app.session.getFlash('error')|length %}
      {{ craft.app.session.getFlash('error') }}
   {% endif %}

   <button>Continue</button>

</form>
```

### Connect URL (Login / Register)
Use the `connectUrl` method to register and login a user via Strava oAuth.

```
<a href="{{ craft.stravaSync.connectUrl() }}">Login with Strava</a>
```

Optionally pass a `redirect` param to the method to overwrite the `Login Redirect` plugin setting, and redirect them when they have successfully logged in:

```
<a href="{{ craft.stravaSync.connectUrl({ redirect: '/success'  }) }}">Login with Strava</a>
```

### Disconnect URL
Use the `disconnectUrl` method to disconnect the current logged in user from Strava and unlink it from their Craft CMS user account:

```
<a href="{{ craft.stravaSync.disconnectUrl() }}">Disconnect from Strava</a>
```

Optionally pass a `redirect` param to the method redirect the user after they have disconnected:

```
<a href="{{ craft.stravaSync.disconnectUrl({ redirect: '/account'  }) }}">Disconnect from Strava</a>
```

### Connected
Use the `connected` method to check to see if the current logged in user has connected their Strava account:

```
{% if craft.stravaSync.connected %}
   <a href="{{ craft.stravaSync.disconnectUrl() }}">Disconnect from Strava</a>
{% else %}
   <a href="{{ craft.stravaSync.connectUrl() }}">Connect to Strava</a>
{% endif %}
```

### Request
Use the `request` method to get the authorised users data from Strava. Whether this is the users Activities, Clubs, Profile data etc.

NOTE: I would recommend caching this data for X amount of time using Craft `{% cache %}` twig tags. This will reduce the amount of requests to Strava and make templates load better.

```
{% set athleteActivities = craft.stravaSync.request(
   'getAthleteActivities'
) %}

{% for activity in athleteActivities %}
   {{ activity.name }} / {{ activity.distance }}<br>
{% endfor %}
```

Depending on your scope type when you authorised the account, the supported request types are:

- getAthlete
- getAthleteClubs
- getAthleteRoutes
- getAthleteActivities
- getAthleteZones
- getAthleteStarredSegments

- getActivity
- getActivityComments
- getActivityKudos
- getActivityPhotos
- getActivityZones
- getActivityLaps

- getGear
- getClub
- getRoute
- getSegment
- getSegmentLeaderboard

- getStreamsActivity
- getStreamsEffort
- getStreamsSegment
- getStreamsRoute

## Webhooks

If you want to receive data from the Strava Webhook Events API (https://developers.strava.com/docs/webhooks/) when an activity/athlete is created or updated for example, you can use the plugins `webhookSync` event. 

To initally set this up, you need to request Webhook access from Strava (See _Webhooks Overview_ on https://developers.strava.com/docs/webhooks). Strava will then enable your account to access the Webhooks feature.

Next, you need to create a _Webhook Subscription_ by doing a POST request to the Strava Sync webhook controller (`http://website.com/strava-sync/webhook/sync`) with a Bearer Token and `client_id`, `client_secret`, `verify_token`, `callback_url` parameters (The `callback_url` should be the same as the POST request URL)

This will then return a callback validation. If this is successful you will get back an `id` (It's worth making note of this to view/delete the subscription during your project developement)

Once the subscription has been created, you can now use the `webhookSync` event. So whenever an activity/athlete is created, edited or deleted on Strava you can get data back from it for your own plugin/module:

      use bymayo\stravasync\events\WebhookSyncEvent;
      use bymayo\stravasync\services\WebhookService;
      use yii\base\Event;

      Event::on(
         WebhookService::class,
         WebhookService::EVENT_WEBHOOK_SYNC,
         function(WebhookSyncEvent $event) {
            // Do something
         }
      );

The `$event` returns an `$event->athlete` and `$event->request` property. 

The `$event->athlete` property contains the `userId`, `athleteId` and `accessToken` of the validated Strava user.
The `$event->request` property contains all `Event` data from the Strava Webhook e.g. `object_type` which is either athlete, or activity aswell as `aspect_type` which returns whether it's new, updated etc (See `Event Data` on https://developers.strava.com/docs/webhooks/)

## Support

If you have any issues (Surely not!) then I'll aim to reply to these as soon as possible. If it's a site-breaking-oh-no-what-has-happened moment, then hit me up on the Craft CMS Slack / Discord - @bymayo

## Roadmap

* Ability to set an admin user Strava credentials in the CP.
* Convertors (Distance to mi / km)

## Credits

Brought to you by [ByMayo](http://bymayo.co.uk)
