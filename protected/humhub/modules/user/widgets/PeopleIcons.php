<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\Widget;
use humhub\modules\user\models\User;

/**
 * PeopleIcons shows footer icons for people cards
 *
 * @since 1.9
 * @author Luke
 * @deprecated since 1.20, will be removed in 1.21 — use UserList / the PeopleDirectory island
 *   (`humhub\modules\user\widgets\PeopleDirectory`). Unused by the core since 1.20; kept for
 *   modules building their own People-like page on it.
 */
class PeopleIcons extends Widget
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
        return $this->render('peopleIcons', [
            'user' => $this->user,
        ]);
    }

}
