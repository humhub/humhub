<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu;

use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\menu\widgets\Menu;
use humhub\widgets\Button;
use Yii;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\helpers\VarDumper;

/**
 * Class DropdownDivider
 *
 * Used for rendering divider within a DropdownMenu.
 *
 * Usage:
 *
 * ```php
 * $dropdown->addEntry(new DropdownDivider(['sortOrder' => 100]);
 * ```
 *
 * @since 1.4
 * @see Menu
 */
class MenuImage extends MenuEntry
{


    /**
     * Returns the MenuEntry as array structure
     *
     * @return array the menu entry array representation
     * @deprecated since 1.4
     */

    public function renderEntry($extraHtmlOptions = [])
    {
        $imgUrl = Url::base(true) . '/uploads/logo_public/imagemega.png';
        $img = Html::img($imgUrl, $this->getHtmlOptions($extraHtmlOptions));
        $uid =  Yii::$app->user->getIdentity()->getId();
        $email = Yii::$app->user->getIdentity()->email;
        $name = Yii::$app->user->getIdentity()->username;
        $emailEnCoded = base64_encode($email);
        $idUserEnCoded = base64_encode($uid);
        $namelEnCoded = base64_encode($name);
        $url = Url::toRoute(['/p/1', 'name' => $namelEnCoded , 'email' => $emailEnCoded, 'iduser' => $idUserEnCoded ]);
        return Html::a($img, $url);
    }

}
