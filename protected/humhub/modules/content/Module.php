<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\driver\MysqlDriver;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;

/**
 * Content Module
 *
 * @property AbstractDriver $search
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
     * @deprecated since 1.17 use $enableManageAllContentPermission form the admin module
     * @var bool global admin can see all content
     */
    public $adminCanViewAllContent = false;

    /**
     * @since 1.1
     * @deprecated since 1.17 use $enableManageAllContentPermission form the admin module
     * @var bool global admin can edit/delete all content
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
     * @var int Maximum allowed file uploads for posts/comments
     */
    public $maxAttachedFiles = 50;

    /**
     * @since 1.3
     * @var int Maximum allowed number of oembeds in richtexts
     */
    public $maxOembeds = 5;

    /**
     * @var int
     * @since 1.6
     */
    public $maxPinnedSpaceContent = 10;

    /**
     * @var int
     * @since 1.6
     */
    public $maxPinnedProfileContent = 2;

    /**
     * If true richtext extensions (oembed, emojis, mentionings) of legacy richtext (< v1.3) are supported.
     *
     * Note: In case the `richtextCompatMode` module db setting is also set, both settings need to be activated. New
     * installations since HumHub 1.8 deactivate the compat mode by default by module db setting.
     *
     * @var bool
     * @since 1.8
     */
    public $richtextCompatMode = true;

    /**
     * @var int Interval in minutes to run a publishing of the scheduled contents
     * @since 1.14
     */
    public $publishScheduledInterval = 10;

    /**
     * @var string Class name of the searching driver
     * @since 1.16
     */
    public $searchDriverClass = MysqlDriver::class;

    /**
     * @var string Column name for ordering of the searching results
     * @since 1.18
     */
    public string $searchOrderBy = SearchRequest::ORDER_BY_SCORE;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->set('search', ['class' => $this->searchDriverClass]);
    }

    /**
     * @param ContentContainerActiveRecord $container
     * @return int
     * @since 1.6
     */
    public function getMaxPinnedContent(ContentContainerActiveRecord $container)
    {
        if ($container instanceof User) {
            return $this->maxPinnedProfileContent;
        }

        if ($container instanceof Space) {
            return $this->maxPinnedSpaceContent;
        }

        return 0;
    }

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
                // Note: we do not return CreatePrivateContent Permission since its not writable at the moment
                new permissions\ManageContent(),
                new permissions\CreatePublicContent(),
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
            'humhub\modules\content\notifications\ContentCreated',
        ];
    }

    public function getSearchDriver(): AbstractDriver
    {
        return $this->search;
    }
}
