<?php

/**
 * File Module
 *
 * @package humhub.modules_core.file
 * @since 0.5
 */
class FileModule extends HWebModule
{

    public $isCoreModule = true;

    /**
     * Inits the Module
     */
    public function init()
    {

        $this->setImport(array(
        ));
    }

}
