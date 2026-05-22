<?php

namespace humhub\modules\user\models\forms;

/**
 * Step 2 of the interactive login flow — collects only the password.
 *
 * The username is injected by the controller from the Step-1 session, never
 * trusted from form input. Inherits {@see Login::afterValidate()} for the
 * actual auth-client iteration.
 *
 * Shares the `Login` form name so browsers / password managers see Step 1 and
 * Step 2 as a single login form.
 *
 * @since 1.19
 */
class LoginPassword extends Login
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password', 'required'],
            [['rememberMe', 'rememberUsername'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'Login';
    }
}
