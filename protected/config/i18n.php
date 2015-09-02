<?php

/**
 * This is the configuration for generating message translations
 * for the Yii framework. It is used by the 'yiic message' command.
 */
return array(
    //'sourcePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'../..',
    'sourcePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'messagePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'messages',
    'languages' => array('de', 'fr', 'nl', 'pt', 'pl', 'pt_br', 'es', 'it', 'tr', 'ru', 'th', 'uk', 'el', 'hu', 'ja', 'nb_no', 'zh_cn', 'ca', 'an', 'cs', 'vi', 'sv', 'da', 'uz', 'fa_ir', 'bg', 'sk', 'en_uk', 'zh_tw', 'ro', 'ar', 'id', 'ko', 'lt', 'hr'),
    'fileTypes' => array('php'),
    'overwrite' => true,
    'removeOld' => false,
    'exclude' => array(
        '.svn',
        '.gitignore',
        'yiilite.php',
        'yiit.php',
        '/i18n/data',
        '/messages',
        '/vendors',
        '/web/js',
        'yii',
    ),
);
