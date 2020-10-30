<?php

namespace bymayo\stravasync\assetbundles\StravaSync;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class StravaSyncAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================
    
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
