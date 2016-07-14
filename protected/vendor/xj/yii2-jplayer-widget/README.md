yii2-jplayer-widget
============

composer.json
-----
```json
"require": {
        "xj/yii2-jplayer-widget": "*"
},
```

example
-----
```php
<?php
//CircleAudio Player
echo xj\jplayer\CircleAudioWidget::widget([
    'mediaOptions' => [
        'm4a' => yii\helpers\Url::base() . '/upload/jplayer-example/Miaow-07-Bubble.m4a',
        'oga' => yii\helpers\Url::base() . '/upload/jplayer-example/Miaow-07-Bubble.ogg',
    ],
]);

//Audio Player
echo xj\jplayer\AudioWidget::widget([
    'mediaOptions' => [
        'title' => "Bubble",
        'm4a' => yii\helpers\Url::base() . '/upload/jplayer-example/Miaow-07-Bubble.m4a',
        'oga' => yii\helpers\Url::base() . '/upload/jplayer-example/Miaow-07-Bubble.ogg',
    ],
    'jsOptions' => [
        'supplied' => "m4a, oga",
        'wmode' => "window",
        'smoothPlayBar' => true,
        'keyEnabled' => true,
        'remainingDuration' => true,
        'toggleDuration' => true
    ],
]);

//Video Player
echo xj\jplayer\VideoWidget::widget([
    'tagClass' => 'jp-video jp-video-360p',
    'skinAsset' => 'xj\jplayer\skins\PinkAssets', //OR xj\jplayer\skins\BlueAssets
    'mediaOptions' => [
        'title' => "Big Buck Bunny",
        'poster' => yii\helpers\Url::base() . '/upload/jplayer-example/Big_Buck_Bunny_Trailer_480x270.png',
        'm4v' => yii\helpers\Url::base() . '/upload/jplayer-example/Big_Buck_Bunny_Trailer.m4v',
        'ogv' => yii\helpers\Url::base() . '/upload/jplayer-example/Big_Buck_Bunny_Trailer.ogv',
        'webmv' => yii\helpers\Url::base() . '/upload/jplayer-example/Big_Buck_Bunny_Trailer.webm',
    ],
    'jsOptions' => [
        'supplied' => "webmv, ogv, m4v",
        'size' => [
            'width' => "640px",
            'height' => "360px",
            'cssClass' => "jp-video-360p"
        ],
        'smoothPlayBar' => true,
        'keyEnabled' => true,
        'remainingDuration' => true,
        'toggleDuration' => true
    ],
]);
?>
```

