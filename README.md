# Strava Sync Plugin for Craft CMS 3.x

Strava Sync is a Craft CMS plugin that lets you connect Strava with Craft CMS. Allowing users to login with Strava oAuth, get data from the API (E.g Athelete, activity, segment and route data) as well as sync data with Craft CMS via Strava webhooks.

https://plugins.craftcms.com/commerce-widgets

## Features

- Login via oAuth
- Automatically fills profile data (First Name, Last Name, Profile Photo etc)
- Map additonal athlete data to user fields
- Pull data from the Strava API
- Sync data directly in to Craft CMS via Webhooks (Soon)

## Requirements

- Craft CMS 3.x
- Strava Developer Account (https://developers.strava.com)

## Install

- Install via the Plugin Store in the Craft Admin CP by searching for `Strava Sync`.

OR

- Install with Composer via `composer require bymayo/strava-sync` from your project directory
- Install the plugin in the Craft Control Panel under `Settings > Plugins`

## Configuration

1. Go to `Settings -> Strava Sync` in Craft CMS.
2. Create API Application on Strava - https://www.strava.com/settings/api (Ensuring the `Authorization Callback Domain` is the domain your going to be connecting from)
3. Copy the `Client ID` and `Client Secret` and enter them in to the correct fields in the `Strava Sync` settings.
4. Also set your `Login Redirect` and `Onboard Redirect` route / paths (See `Onboard` below for an explanation regarding `Onboard Redirect`).
5. Hit save, and follow the templating instructions.

You can also set plugin settings by creating a `strava-sync.php` file in your `config` folder. See the `config.php` for an example.

## Templating

### Onboard (Required)
Because the Strava API doesn't give us access to the authorised users email address, we need to collect this to create a Craft CMS user. This is done by placing the following code on to a template and ensuring this template is the one accessible from the `Onboard Redirect` plugin setting:

```
<form method="post" accept-charset="UTF-8">

   {{ getCsrfInput() }}
   {{ actionInput('strava-sync/user/register') }}

   <label for="email"></label>
   <input type="email" name="email" id="email" required>

   <button>Continue</button>

</form>
```

### Connect (Login / Register)
Use the `craft.stravaSync.connect` method to output a link that will login and register users at the same time.

```
<a href="{{ craft.stravaSync.connect() }}">Login with Strava</a>
```

Optionally pass a `redirect` param to the method to overwrite the `Login Redirect` plugin setting, and redirect them when they have successfully logged in:

```
<a href="{{ craft.stravaSync.connect({ redirect: '/success'  }) }}">Login with Strava</a>
```

### Disconnect
When a user is logged in they have the option to disconnect their Strava account from their Craft CMS user:

```
<a href="{{ craft.stravaSync.disconnect() }}">Disconnect from Strava</a>
```

Optionally pass a `redirect` param to the method redirect them after they have disconnected:

```
<a href="{{ craft.stravaSync.disconnect({ redirect: '/account'  }) }}">Login with Strava</a>
```

### Connected
When a user is logged in, you might want to check to see if they are connected to Strava:

```
{% if craft.stravaSync.connected %}
   <a href="{{ craft.social.craft.stravaSync.disconnect() }}">Disconnect from Strava</a>
{% else %}
   <a href="{{ craft.stravaSync.connect() }}">Connect to Strava</a>
{% endif %}
```

## Support

If you have any issues (Surely not!) then I'll aim to reply to these as soon as possible. If it's a site-breaking-oh-no-what-has-happened moment, then hit me up on the Craft CMS Slack - @bymayo

## Roadmap

* Release it

Brought to you by [bymayo](http://bymayo.co.uk)
