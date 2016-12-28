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
        $this->addWidget(GlobalModal::className());
        $this->addWidget(GlobalConfirmModal::className());
        
        if(Yii::$app->params['installed']) {
            $this->addWidget(\humhub\modules\tour\widgets\Tour::className());
            $this->addWidget(\humhub\modules\admin\widgets\TrackingWidget::className());
        }
        
        $this->addWidget(LoaderWidget::className(), ['show' => false, 'id' => "humhub-ui-loader-default"]);
        $this->addWidget(StatusBar::className());
        $this->addWidget(BlueimpGallery::className());

        if (Yii::$app->params['enablePjax']) {
            $this->addWidget(Pjax::className());
        }

        parent::init();
    }

}
