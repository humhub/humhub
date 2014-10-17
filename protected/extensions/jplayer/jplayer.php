<?php
/**
 * jPlayer html5 media player extension (http://jplayer.org/)
 *
 * @author andystrobel
 * @version 0.1
 * @package extensions.jplayer
 */

class jPlayer extends CWidget {

    /**
     * Specifies the file path
     * @var string $file
     */
    public $file;

    /**
     * Specifies an unique file id
     * @var int $id
     */
    public $id;


    public function run() {

        // load files
        $this->loadResources();

        // render view
        $this->render('jplayer', array('file'=>$this->file, 'id' => $this->id));

    }

    /**
     * load needed resources files
     */
    public function loadResources()
    {
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.jplayer.min.js');
        Yii::app()->clientScript->registerCssFile($assetPrefix . '/jplayer.css');
    }

}
