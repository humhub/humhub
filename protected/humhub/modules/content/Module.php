<?php

namespace humhub\modules\content;

use Yii;

/**
 * Content Module
 * 
 * @author Luke
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\content\controllers';

    /**
     * @since 1.1
     * @var boolean admin can see all content
     */
    public $adminCanViewAllContent = false;

    /**
     * @since 1.1
     * @var string Custom e-mail subject for hourly update mails - default: Latest news
     */
    public $emailSubjectHourlyUpdate = null;

    /**
     * @since 1.1
     * @var string Custom e-mail subject for daily update mails - default: Your daily summary
     */
    public $emailSubjectDailyUpdate = null;

    public function getName()
    {
        return Yii::t('ContentModule.base', 'Content');
    }
    
    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer !== null) {
            return [
                new permissions\ManageContent(),
                new permissions\CreatePublicContent()
            ];
        }

        return [];
    }
    
    /**
     * @inheritdoc
     */
    public function getNotifications() 
    {
       return [
           'humhub\modules\content\notifications\ContentCreated'
       ];
    }

}
