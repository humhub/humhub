<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\user\models\forms\SudoConfirmForm;
use humhub\widgets\ContentCard;

/* @var View $this */
/* @var SudoConfirmForm $model */
/* @var string $returnUrl */

$title = $model->hasPasswordFactor()
    ? Yii::t('UserModule.auth', 'Confirm your password')
    : Yii::t('UserModule.auth', 'Sign in again');
$this->pageTitle = $title;
?>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-lg-5 mx-auto">
            <?php ContentCard::begin(['title' => $title]) ?>
                <?= $this->render('_form', ['model' => $model, 'returnUrl' => $returnUrl, 'inModal' => false]) ?>
            <?php ContentCard::end() ?>
        </div>
    </div>
</div>
