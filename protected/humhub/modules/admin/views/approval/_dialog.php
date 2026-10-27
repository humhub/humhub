<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\admin\models\forms\ApproveUserForm;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/**
 * The dialog of a single registration of the approval table: approve, decline or send a message,
 * with the subject and text of the mail to the user.
 *
 * @var View $this
 * @var ApproveUserForm $approveFormModel
 * @var string $title
 * @var string $submit
 * @var bool $destructive
 */

$submitButton = $destructive
    ? ModalButton::danger($submit)->submit()
    : ModalButton::save($submit)->submit();
?>

<?php $form = Modal::beginFormDialog([
    'title' => $title,
    'footer' => ModalButton::cancel() . ' ' . $submitButton,
]) ?>
    <?= $form->field($approveFormModel, 'subject') ?>
    <?= $form->field($approveFormModel, 'message')->widget(RichTextField::class, ['exclude' => ['oembed', 'upload']]) ?>
<?php Modal::endFormDialog(); ?>
