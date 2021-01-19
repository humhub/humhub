<?php

namespace humhub\modules\admin\models\forms;

use humhub\modules\user\models\Password;

/**
 * PasswordEditForm
 * used to edit password of other users by admin
 */
class PasswordEditForm extends Password
{
    const SCENARIO_EDIT_ADMIN = 'editAdmin';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->scenario = self::SCENARIO_EDIT_ADMIN;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_EDIT_ADMIN] = ['newPassword', 'newPasswordConfirm', 'mustChangePassword'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge( parent::rules(), [
            ['newPassword', 'compare', 'compareAttribute' => 'newPasswordConfirm', 'on' => self::SCENARIO_EDIT_ADMIN],
            ['newPasswordConfirm', 'compare', 'compareAttribute' => 'newPassword', 'on' => self::SCENARIO_EDIT_ADMIN],
        ]);
    }
}
