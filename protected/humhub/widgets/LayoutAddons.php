<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\modules\admin\widgets\TrackingWidget;
use humhub\modules\tour\widgets\Tour;
use humhub\modules\ui\form\widgets\MarkdownModals;
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
        if (!Yii::$app->request->isPjax) {
            $this->addWidget(GlobalModal::class);
            $this->addWidget(GlobalConfirmModal::class);

            if(Yii::$app->params['installed']) {
                $this->addWidget(Tour::class);
                $this->addWidget(TrackingWidget::class);
            }

            $this->addWidget(LoaderWidget::class, ['show' => false, 'id' => "humhub-ui-loader-default"]);
            $this->addWidget(StatusBar::class);
            if (Yii::$app->params['installed']) {

                // Temporary fix until v1.6 then bundled with css pack
                Yii::$app->view->registerCss('img { image-orientation: from-image; }', [], 'fix-orient');

                $this->addWidget(BlueimpGallery::class);
                $this->addWidget(MarkdownModals::class);

                if (Yii::$app->params['enablePjax']) {
                    $this->addWidget(Pjax::class);
                }
            }
        }
        parent::init();
    }

}
