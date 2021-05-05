<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\Widget;
use humhub\modules\admin\models\forms\PeopleSettingsForm;
use humhub\modules\user\models\User;

/**
 * PeopleActionsButton shows directory options (following or friendship) for listed users
 * 
 * @since 1.9
 * @author Luke
 */
class PeopleCard extends Widget
{

    /**
     * @var User
     */
    public $user;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $html = '';

        if (self::hasFrontSide()) {
            $html .= $this->render('peopleCardFront', [
                'user' => $this->user
            ]);
        }

        if (self::hasBackSide()) {
            $html .= $this->render('peopleCardBack', [
                'user' => $this->user
            ]);
        }

        return $html;
    }

    public static function config($name): string
    {
        $peopleSettingsForm = new PeopleSettingsForm();

        return isset($peopleSettingsForm->$name) ? $peopleSettingsForm->$name : '';
    }

    public static function hasBothSides(): bool
    {
        return self::config('userDetails') === 'full';
    }

    public static function hasFrontSide(): bool
    {
        return self::hasBothSides() || self::config('userDetails') == 'front';
    }

    public static function hasBackSide(): bool
    {
        return self::hasBothSides() || self::config('userDetails') == 'back';
    }

}
