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
use craft\elements\User;

use yii\base\Event;

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
            'oauthSerivce' => \bymayo\stravasync\services\OauthService::class,
            'requestService' => \bymayo\stravasync\services\RequestService::class,
         ]);

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['connect'] = 'strava-sync/oauth/connect';
                $event->rules['register'] = 'strava-sync/oauth/register';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['connect'] = 'strava-sync/oauth/connect';
                $event->rules['register'] = 'strava-sync/oauth/register';
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
               if ($event->element instanceof User) {
                   StravaSync::getInstance()->oauthSerivce->removeUserFromStrava($event->element);
               }
           }
        );

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
