<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\widgets;


use humhub\widgets\ModalButton;
use Yii;
use humhub\widgets\Modal;

class StreamTopicModal extends Modal
{
    /**
     * @inheritdoc
     */
    public $id = 'stream-topic-picker-modal';

    /**
     * @inheritdoc
     */
    public function init() {
        $this->size = 'small';
        $this->header = Yii::t('StreamModule.widgets_streamTopicModal', '<strong>Topic</strong> Filter');
        $this->body = $this->render('streamTopicPicker');
        $this->footer = ModalButton::primary(Yii::t('StreamModule.widgets_streamTopicModal', 'Select'))
            ->action('stream.chooseTopic')->loader(false)->close();
    }

}