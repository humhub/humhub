<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\InstallationState;
use humhub\helpers\MobileAppHelper;
use humhub\modules\admin\widgets\TrackingWidget;
use humhub\modules\tour\widgets\Tour;
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

            if (Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED)) {
                $this->addWidget(Tour::class);
                $this->addWidget(TrackingWidget::class);
            }

            $this->addWidget(LoaderWidget::class, ['show' => false, 'id' => "humhub-ui-loader-default"]);
            $this->addWidget(StatusBar::class);
            if (Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED)) {
                $this->addWidget(BlueimpGallery::class);

                if (Yii::$app->params['enablePjax']) {
                    $this->addWidget(PjaxLayoutContent::class);
                }
            }
        }

        parent::init();

        if (Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED)) {
            if (Yii::$app->session->has(MobileAppHelper::SESSION_VAR_SHOW_OPENER)) {
                MobileAppHelper::registerShowOpenerScript();
                Yii::$app->session->remove(MobileAppHelper::SESSION_VAR_SHOW_OPENER);
            }

            // Get info for the Share intend feature (uploading files from the mobile app)
            MobileAppHelper::getFileUploadSettings();
        }

    }
}
