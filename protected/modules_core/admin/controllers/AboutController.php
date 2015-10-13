<?php

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class AboutController extends Controller {

    public $subLayout = "/_layout";

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow',
                'expression' => 'Yii::app()->user->isAdmin()'
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex() {

        $isNewVersionAvailable = false;
        $isUpToDate = false;
        $latestVersion = "";
        
        if ($this->getModule()->marketplaceEnabled) {
            $onlineModuleManager = new OnlineModuleManager();
            $latestVersion = $onlineModuleManager->getLatestHumHubVersion();
            if ($latestVersion) {
                $isNewVersionAvailable = version_compare($latestVersion, HVersion::VERSION, ">");
                $isUpToDate = !$isNewVersionAvailable;
            }
        }
        
        $this->render('index', array(
            'currentVersion' => HVersion::VERSION,
            'latestVersion' => $latestVersion,
            'isNewVersionAvailable' => $isNewVersionAvailable,
            'isUpToDate' => $isUpToDate
        ));
    }


}