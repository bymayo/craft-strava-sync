# Strava Sync Changelog

## 1.0.16 - 2020-11-11
### Fixed
- References to asset bundle

## 1.0.15 - 2020-11-11
### Fixed
- Loading asset bundle

## 1.0.14 - 2020-10-30
### Fixed
- Composer 2 Compatibility
- Disabled CSRF validation for Webhook controller
- Disabled CSRF validation for oAuth controller
- Disabled CSRF validation for Register / Disconect controller

### Updated
- Composer dependencies

## 1.0.13 - 2020-06-12
### Fixed
- Styling issues on Craft 3.4

## 1.0.12 - 2020-01-14
### Fixed
- Error where authorization_code was expiring instantly. Strava must have changed some security settings with authorization_code and tokens.

## 1.0.11 - 2019-09-17
### Added
- Added `EVENT_USER_CONNECTED` and `EVENT_USER_DISCONNECTED` events to check if a user has connected/disconnected Strava from Craft CMS.

## 1.0.10 - 2019-09-16
### Added
- Ability to get data from Strava webhooks, and sync with custom plugins via new `EVENT_WEBHOOK_SYNC` event.

## 1.0.9 - 2019-08-28
### Fixed
- Issue where webhook pulled in the userId, not the user element.

## 1.0.8 - 2019-06-24
### Fixed
- Issue where accessToken, refreshToken and expires column weren't being created on fresh installs.

## 1.0.7 - 2019-05-31
### Fixed
- Error where no userId was defined when checking to see if user connected to Strava

## 1.0.6 - 2019-05-31
### Added
- Table attribute to the users table view to see if user is connected/disconnected from Strava
- Pane inside a user to see if user is connected/disconnected from Strava and output Athlete ID

## 1.0.5 - 2019-04-26
### Fixed
- Fixed issue where new Strava users produced an error when trying to save profile photos (Because they didn't exist)

## 1.0.4 - 2019-04-03
### Added
- Webhook functionality (Alpha)

### Fixed
- Fixed an issue where refresh tokens were only refreshed for current user.

## 1.0.3 - 2019-04-02
### Fixed
- Fixed issue where refresh tokens weren't refreshing and saving correctly on a request
- Fixed a bug where getting the wrong `_tokens` var

### Changed
- Added more default scope settings to allow read of all Strava data

## 1.0.2 - 2019-04-02
### Added
- `request` method to pull data from Strava API methods

## 1.0.1 - 2019-04-02
### Added
- Access Tokens & Refresh Tokens now stored in the DB
- No longer uses "Forever Tokens"
- Refresh Tokens now conform with Strava's latest API changes (https://developers.strava.com/docs/oauth-updates/)
- A `scope` setting to allow you to change the authorisation type.

### Changed
- Access Tokens now are no longer stored as PHP session variables

## 1.0.0 - 2019-02-26
### Added
- Initial release
