<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

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
     * @var boolean global admin can see all content
     */
    public $adminCanViewAllContent = false;

    /**
     * @since 1.1
     * @var boolean global admin can edit/delete all content
     */
    public $adminCanEditAllContent = true;

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
    
    /**
     * @since 1.2
     * @var integer Maximum allowed file uploads for posts/comments 
     */
    public $maxAttachedFiles = 50;

    /**
     * @inheritdoc
     */
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
