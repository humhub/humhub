<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\permissions\CreatePublicContent;
use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\space\models\Space;
use Yii;
use yii\web\HttpException;

/**
 * WallCreateContentFormFooter is the footer options widget under create content forms on Stream/Wall.
 *
 * @author luke
 */
class WallCreateContentFormFooter extends Widget
{
    /**
     * @var WallCreateContentForm null (required)
     */
    public ?WallCreateContentForm $wallCreateContentForm = null;

    /**
     * @var string form submit route/url (automatically set if `wallCreateContentForm` is provided)
     */
    public $submitUrl;

    /**
     * @var string submit button text
     */
    public $submitButtonText;

    /**
     * @var ContentContainerActiveRecord this content will belong to
     */
    public $contentContainer;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->submitButtonText == '') {
            $this->submitButtonText = Yii::t('ContentModule.base', 'Submit');
        }

        if (!($this->contentContainer instanceof ContentContainerActiveRecord)) {
            throw new HttpException(500, 'No Content Container given!');
        }

        if ($this->wallCreateContentForm !== null) {
            $this->submitUrl = $this->wallCreateContentForm->submitUrl;
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('@humhub/modules/content/widgets/views/wallCreateContentFormFooter', [
            'contentContainer' => $this->contentContainer,
            'fileList' => $this->wallCreateContentForm?->fileList ?? [],
            'isModal' => $this->wallCreateContentForm?->isModal ?? false,
            'submitUrl' => $this->contentContainer->createUrl($this->submitUrl),
            'submitButtonText' => $this->submitButtonText,
            'canSwitchVisibility' => $this->contentContainer->visibility !== Space::VISIBILITY_NONE && $this->contentContainer->can(CreatePublicContent::class),
            'fileHandlers' => FileHandlerCollection::getByType([FileHandlerCollection::TYPE_IMPORT, FileHandlerCollection::TYPE_CREATE]),
            'pickerUrl' => $this->contentContainer instanceof Space ? $this->contentContainer->createUrl('/space/membership/search') : null,
            'scheduleUrl' => $this->contentContainer->createUrl('/content/content/schedule-options'),
        ]);
    }
}
