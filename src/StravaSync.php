<?php
/**
 * Strava Sync plugin for Craft CMS 3.x
 *
 * Connect to Strava with oAuth and sync activities etc to Craft CMS
 *
 * @link      http://bymayo.co.uk
 * @copyright Copyright (c) 2019 bymayo
 */

namespace bymayo\stravasync;

use bymayo\stravasync\variables\StravaSyncVariable;
use bymayo\stravasync\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\FileHelper;
use craft\services\Elements;
use craft\elements\User as UserElement;

use yii\base\Event;
use yii\web\User;
use yii\web\UserEvent;

/**
 * Class StravaSync
 *
 * @author    bymayo
 * @package   StravaSync
 * @since     1.0.0
 *
 * @property  StravaSyncServiceService $stravaSyncService
 */
class StravaSync extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var StravaSync
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    public static function log($message)
    {
        $file = Craft::getAlias('@storage/logs/stravasync.log');
        $log = date('Y-m-d H:i:s'). ' ' . $message . "\n";
        FileHelper::writeToFile($file, $log, ['append' => true]);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->setComponents([
            'oauthService' => \bymayo\stravasync\services\OauthService::class,
            'userService' => \bymayo\stravasync\services\UserService::class,
         ]);

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['strava-sync/oauth/login'] = 'strava-sync/oauth/login';
                $event->rules['strava-sync/user/register'] = 'strava-sync/user/register';
                $event->rules['strava-sync/user/disconnect'] = 'strava-sync/user/disconnect';
                $event->rules['strava-sync/user/connect'] = 'strava-sync/user/connect';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('stravaSync', StravaSyncVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Event::on(
           Elements::class,
           Elements::EVENT_BEFORE_DELETE_ELEMENT,
           function (Event $event) {
               if ($event->element instanceof UserElement) {
                   StravaSync::getInstance()->userService->unlinkUserFromStrava($event->element);
               }
           }
        );

        Event::on(
            User::class,
            User::EVENT_AFTER_LOGOUT,
            function (UserEvent $event) {
                StravaSync::getInstance()->oauthService->clearToken();
            }
        );

        // Event::on(
        //     User::class,
        //     User::EVENT_BEFORE_LOGIN,
        //     function (UserEvent $event) {
        //         StravaSync::getInstance()->oauthService->clearToken();
        //     }
        // );

        Craft::info(
            Craft::t(
                'strava-sync',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'strava-sync/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
