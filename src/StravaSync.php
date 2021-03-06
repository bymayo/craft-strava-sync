<?php

namespace bymayo\stravasync;

use bymayo\stravasync\variables\StravaSyncVariable;
use bymayo\stravasync\models\Settings;
use bymayo\stravasync\assetbundles\StravaSync\StravaSyncAsset;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
use craft\helpers\FileHelper;
use craft\services\Elements;
use craft\elements\User as UserElement;

use yii\base\Event;
use yii\web\User;
use yii\web\UserEvent;


class StravaSync extends Plugin
{
    // Static Properties
    // =========================================================================

    public static $plugin;

    // Public Properties
    // =========================================================================

    public $schemaVersion = '1.0.2';

    // Public Methods
    // =========================================================================

    public static function log($message)
    {
        $file = Craft::getAlias('@storage/logs/stravasync.log');
        $log = date('Y-m-d H:i:s'). ' ' . $message . "\n";
        FileHelper::writeToFile($file, $log, ['append' => true]);
    }

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->setComponents([
            'oauthService' => \bymayo\stravasync\services\OauthService::class,
            'userService' => \bymayo\stravasync\services\UserService::class,
            'webhookService' => \bymayo\stravasync\services\WebhookService::class,
         ]);

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['strava-sync/oauth/login'] = 'strava-sync/oauth/login';
                $event->rules['strava-sync/webhook/sync'] = 'strava-sync/webhook/sync';
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

        Craft::info(
            Craft::t(
                'strava-sync',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );

         Event::on(
            UserElement::class,
            UserElement::EVENT_REGISTER_TABLE_ATTRIBUTES,
            function(RegisterElementTableAttributesEvent $event) {
               $event->tableAttributes['stravaSync'] = 'Strava';
            }
         );

         Event::on(
            UserElement::class,
            UserElement::EVENT_SET_TABLE_ATTRIBUTE_HTML,
            function(SetElementTableAttributeHtmlEvent $event) {
               if ($event->attribute === 'stravaSync') {
                  Craft::$app->getView()->registerAssetBundle(StravaSyncAsset::class);
                  $user = $event->sender;
                  $userConnected = StravaSync::getInstance()->userService->checkUserLinkExists($user->id);
                  $event->html = Craft::$app->getView()->renderTemplate(
                     'strava-sync/table-attribute',
                     [
                        'status' => $userConnected,
                     ]
                  );
               }
            }
         );

         Craft::$app->getView()->hook(
            'cp.users.edit.details',
            function(&$context) {
               if ($context['user'] && $context['user']->id) {
                  Craft::$app->getView()->registerAssetBundle(StravaSyncAsset::class);
                  return Craft::$app->getView()->renderTemplate('strava-sync/user-pane', $context);
               }
            }
         );

    }

    // Protected Methods
    // =========================================================================


    protected function createSettingsModel()
    {
        return new Settings();
    }

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
