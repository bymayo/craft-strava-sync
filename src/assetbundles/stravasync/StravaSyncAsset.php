<?php
/**
 * Strava Sync plugin for Craft CMS 3.x
 *
 * Connect to Strava with oAuth and sync activities etc to Craft CMS 
 *
 * @link      http://bymayo.co.uk
 * @copyright Copyright (c) 2019 bymayo
 */

namespace bymayo\stravasync\assetbundles\StravaSync;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    bymayo
 * @package   StravaSync
 * @since     1.0.0
 */
class StravaSyncAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@bymayo/stravasync/assetbundles/stravasync/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/StravaSync.js',
        ];

        $this->css = [
            'css/StravaSync.css',
        ];

        parent::init();
    }
}
