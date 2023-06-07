<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/style.css',
        'css/normalize.css',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
