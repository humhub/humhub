<?php

/**
 * This widget is used include the files functionality to a wall entry.
 *
 * @package humhub.modules_core.file
 * @since 0.5
 */
class ShowFilesWidget extends HWidget
{

    /**
     * Object to show files from
     */
    public $object = null;

    /**
     * Executes the widget.
     */
    public function run()
    {
        $blacklisted_objects = explode(',', HSetting::GetText('showFilesWidgetBlacklist','file'));
        if (!in_array(get_class($this->object), $blacklisted_objects)) {
            $files = File::getFilesOfObject($this->object);
            $this->render('showFiles', array('files' => $files,
                            'maxPreviewImageWidth' => HSetting::Get('maxPreviewImageWidth', 'file'),
                            'maxPreviewImageHeight' => HSetting::Get('maxPreviewImageHeight', 'file'),
                            'hideImageFileInfo' => HSetting::Get('hideImageFileInfo', 'file')
            ));
        }
    }

}

?>