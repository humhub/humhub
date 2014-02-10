<?php

/**
 * This widget is used include the files functionality to a wall entry.
 *
 * @package humhub.modules_core.file
 * @since 0.5
 */
class ShowFilesWidget extends HWidget {

    /**
     * Model Name (e.g. Post) to identify which posts we shall show
     *
     * @var String
     */
    public $modelName = "";

    /**
     * The primary key of the Model
     *
     * @var Integer
     */
    public $modelId = "";

    /**
     * Executes the widget.
     */
    public function run() {

        $files = File::model()->findAllByAttributes(array('object_id' => $this->modelId, 'object_model' => $this->modelName));
        $this->render('showFiles', array('files'=>$files));
    }

}

?>