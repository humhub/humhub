<?php

/**
 *
 * @package humhub.modules_core.user
 * @since 0.5
 * @author Luke
 */
class UserModule extends CWebModule {

    public function init() {
        $this->setImport(array(
            'user.models.*',
            'user.components.*',
        ));
    }

    /**
     * On rebuild of the search index, rebuild all user records
     *
     * @param type $event
     */
    public static function onSearchRebuild($event) {

        foreach (User::model()->findAll() as $obj) {
            HSearch::getInstance()->addModel($obj);
            print "u";
        }
    }

}