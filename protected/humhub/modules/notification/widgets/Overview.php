<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\widgets;

use Yii;

/**
 * Notificaiton overview widget.
 *
 * @author buddha
 * @since 1.1
 */
class Overview extends \yii\base\Widget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        return $this->render('overview', [
            'update' => \humhub\modules\notification\controllers\ListController::getUpdates(),
            'unseenCount' => \humhub\modules\notification\models\Notification::findUnseen()->count()]);
    }

}

?>