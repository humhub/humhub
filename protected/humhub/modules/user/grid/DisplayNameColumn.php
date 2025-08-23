<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\grid;

use Yii;
use yii\bootstrap\Html;

/**
 * DisplayNameColumn
 *
 * @author Luke
 */
class DisplayNameColumn extends BaseColumn
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->attribute === null) {
            $this->attribute = 'profile.lastname';
        }

        if ($this->label === null) {
            $this->label = Yii::t('UserModule.base', 'Name');
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $user = $this->getUser($model);

        $badge = '';
        if ($user->auth_mode !== 'local' && Yii::$app->user->isAdmin()) {
            $badge = '&nbsp;<span class="badge">' . $user->auth_mode . '</span>';
        }
        return '<div>' . Html::encode($user->displayName) . $badge . '<br> '
            . '<small>' . Html::encode($user->displayNameSub) . '</small></div>';
    }

}
