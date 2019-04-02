<img src="https://raw.githubusercontent.com/bymayo/craft-strava-sync/master/resources/icon.png" width="70">

# Strava Sync Plugin for Craft CMS 3.x

Strava Sync is a Craft CMS plugin that lets you connect Strava with Craft CMS. Allowing users to login with Strava oAuth, get data from the Strava API (Athletes, activities, segments, routes etc)

https://plugins.craftcms.com/strava-sync

## Features

- Login via oAuth
- Automatically fills profile data (First Name, Last Name, Profile Photo etc)
- Map additional athlete data to user fields (City, Country, Sex etc)
- Get data from the Strava API (Athletes, activities, segments etc)
- Sync data directly in to Craft CMS via Webhooks [Soon]

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

You may not require access to all Strava data, e.g. You may only require to get an Athletes Details and not upload Activities. To put trust in users authorising your website to connecto their Strava account it's important to choose the correct `scope` value when we make a oAuth call to Strava.

You can see a list of available scope options at https://developers.strava.com/docs/oauth-updates/ under `Details about requesting access`.

## Templating

### Onboard (Required)
Because the Strava API doesn't give us access to the authorised users email address, we need to collect this to create a Craft CMS user. This is done by placing the following code on to a template and ensuring this template is the one accessible from the `Onboard Redirect` plugin setting:

```
<form method="post" accept-charset="UTF-8">

   {{ getCsrfInput() }}
   {{ actionInput('strava-sync/user/register') }}

   <label for="email">Email Address</label>
   <input type="email" name="email" id="email" required>

   <button>Continue</button>

</form>
```

### Connect (Login / Register)
Use the `connectUrl` method to register and login a user via Strava oAuth.

```
<a href="{{ craft.stravaSync.connectUrl() }}">Login with Strava</a>
```

Optionally pass a `redirect` param to the method to overwrite the `Login Redirect` plugin setting, and redirect them when they have successfully logged in:

```
<a href="{{ craft.stravaSync.connectUrl({ redirect: '/success'  }) }}">Login with Strava</a>
```

### Disconnect
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

## Support

If you have any issues (Surely not!) then I'll aim to reply to these as soon as possible. If it's a site-breaking-oh-no-what-has-happened moment, then hit me up on the Craft CMS Slack / Discord - @bymayo

## Roadmap

* Webhooks.
* Ability to set an admin user Strava credentials in the CP.

## Credits

Brought to you by [ByMayo](http://bymayo.co.uk)
