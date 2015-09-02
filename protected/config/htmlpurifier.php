<?php if ( ! defined('YII_PATH')) exit('No direct script access allowed');

return array(

    /*'Attr.AllowedRel'        =>  array('noindex','nofollow'),
    'Attr.DefaultImageAlt'   =>  NULL,
    'Core.ColorKeywords'     =>  array(
        'maroon'    => '#800000',
        'red'       => '#FF0000',
        'orange'    => '#FFA500',
        'yellow'    => '#FFFF00',
        'olive'     => '#808000',
        'purple'    => '#800080',
        'fuchsia'   => '#FF00FF',
        'white'     => '#FFFFFF',
        'lime'      => '#00FF00',
        'green'     => '#008000',
        'navy'      => '#000080',
        'blue'      => '#0000FF',
        'aqua'      => '#00FFFF',
        'teal'      => '#008080',
        'black'     => '#000000',
        'silver'    => '#C0C0C0',
        'gray'      => '#808080',
    ),
    'Core.Encoding'          =>  Yii::app()->charset,
    'Core.EscapeInvalidTags' =>  FALSE,
    'HTML.AllowedElements'   =>  array(
        'a','b','em','small','strong','del','q','img','span','ul','ol','li','h1','h2','h3','h4','h5','h6'
    ),
    'HTML.AllowedAttributes' =>  array(
        'href','rel','target','src', 'style',
    ),
    */
    'HTML.Doctype'          =>  'XHTML 1.0 Transitional',
    'URI.AllowedSchemes'    =>  array(
        'http'      => true,
        'https'     => true,
        'mailto'    => true,
        'ftp'       => true,
        'nntp'      => true,
        'news'      => true,
    ),
    'URI.Base'=>NULL,
    
);