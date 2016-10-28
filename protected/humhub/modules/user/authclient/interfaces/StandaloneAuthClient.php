<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient\interfaces;

use humhub\modules\user\authclient\AuthAction;

/**
 * StandaloneAuthClient allows implementation of custom authclients
 * which not relies on auth handling by AuthAction
 *
 * @see \yii\authclient\AuthAction
 * @since 1.1.2
 * @author Luke
 */
interface StandaloneAuthClient
{

    /**
     * Custom auth action implementation
     * 
     * @param AuthAction $authAction
     * @return Response response instance.
     */
    public function authAction($authAction);
}
