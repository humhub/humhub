<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Yii;

/**
 * LayoutAddons are inserted at the end of all layouts (standard or login).
 *
 * @since 1.1
 * @author Luke
 */
class LayoutAddons extends BaseStack
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        if(!Yii::$app->request->isPjax) {
            $this->addWidget(GlobalModal::class);
            $this->addWidget(GlobalConfirmModal::class);

            if(Yii::$app->params['installed']) {
                $this->addWidget(\humhub\modules\tour\widgets\Tour::class);
                $this->addWidget(\humhub\modules\admin\widgets\TrackingWidget::class);
            }

            $this->addWidget(LoaderWidget::class, ['show' => false, 'id' => "humhub-ui-loader-default"]);
            $this->addWidget(StatusBar::class);
            $this->addWidget(BlueimpGallery::class);
            $this->addWidget(MarkdownFieldModals::class);

            if (Yii::$app->params['enablePjax']) {
                $this->addWidget(Pjax::class);
            }
        }
        parent::init();
    }

}
