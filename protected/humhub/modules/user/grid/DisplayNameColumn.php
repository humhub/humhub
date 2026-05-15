<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\grid;

use humhub\helpers\Html;
use humhub\modules\user\authclient\Collection;
use Yii;

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
        if ($user->user_source !== 'local' && Yii::$app->user->isAdmin()) {
            /** @var Collection $collection */
            $collection = Yii::$app->authClientCollection;
            $title = $collection->hasClient($user->user_source)
                ? $collection->getClient($user->user_source)->getTitle()
                : $user->user_source;
            $badge = '&nbsp;<span class="badge">' . Html::encode($title) . '</span>';
        }
        return '<div>' . Html::encode($user->displayName) . $badge . '<br> '
            . '<small>' . Html::encode($user->displayNameSub) . '</small></div>';
    }

}
