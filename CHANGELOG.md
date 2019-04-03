# Strava Sync Changelog

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
