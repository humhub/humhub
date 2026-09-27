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
 * @deprecated since 1.20, will be removed in 1.21 — use UserList / the PeopleDirectory island
 *   (`humhub\modules\user\widgets\PeopleDirectory`). Unused by the core since 1.20; kept for
 *   modules building their own People-like page on it.
 */
class PeopleCard extends Widget
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string HTML wrapper around card
     */
    public $template = '<div class="card card-people col-xl-3 col-lg-4 col-md-6 col-12">{card}</div>';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $card = $this->render('peopleCard', [
            'user' => $this->user,
        ]);

        return str_replace('{card}', $card, $this->template);
    }

    public static function config($name): string
    {
        $peopleSettingsForm = new PeopleSettingsForm();

        return $peopleSettingsForm->$name ?? '';
    }

}
