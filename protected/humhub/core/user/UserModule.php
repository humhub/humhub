<?php

/**
 *
 * @package humhub.modules_core.user
 * @since 0.5
 * @author Luke
 */
class UserModule extends HWebModule
{

    public $isCoreModule = true;

    public function init()
    {
        $this->setImport(array(
            'user.models.*',
            'user.components.*',
        ));
    }

}
