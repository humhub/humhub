<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\user\models\forms\SudoConfirmForm;
use humhub\widgets\modal\Modal;

/* @var View $this */
/* @var SudoConfirmForm $model */
/* @var string $returnUrl */
?>
<?php Modal::beginDialog([
    'id' => 'sudo-confirm-modal',
    'title' => $model->hasPasswordFactor()
        ? Yii::t('UserModule.auth', 'Confirm your password')
        : Yii::t('UserModule.auth', 'Sign in again'),
]) ?>
    <?= $this->render('_form', ['model' => $model, 'returnUrl' => $returnUrl, 'inModal' => true]) ?>
<?php Modal::endDialog() ?>
