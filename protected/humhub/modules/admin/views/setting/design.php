<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\admin\assets\AdminAsset;
use humhub\modules\admin\models\forms\DesignSettingsForm;
use humhub\modules\ui\form\widgets\CodeMirrorInputWidget;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\mails\MailHeaderImage;
use yii\helpers\Url;

/**
 * @var $this View
 * @var $model DesignSettingsForm
 */

AdminAsset::register($this);

$this->registerJsConfig('admin', [
    'text' => [
        'confirm.deleteLogo.header' => Yii::t('AdminModule.settings', '<strong>Confirm</strong> image deletion'),
        'confirm.deleteLogo.body' => Yii::t('UserModule.account', 'Do you really want to delete your logo image?'),
        'confirm.deleteLogo.confirm' => Yii::t('AdminModule.settings', 'Delete'),
        'confirm.deleteIcon.header' => Yii::t('AdminModule.settings', '<strong>Confirm</strong> icon deletion'),
        'confirm.deleteIcon.body' => Yii::t('UserModule.account', 'Do you really want to delete your icon image?'),
        'confirm.deleteIcon.confirm' => Yii::t('AdminModule.settings', 'Delete'),
        'confirm.deleteLoginBg.header' => Yii::t('AdminModule.settings', '<strong>Confirm</strong> image deletion'),
        'confirm.deleteLoginBg.body' => Yii::t(
            'UserModule.account',
            'Do you really want to delete your login background image?'
        ),
        'confirm.deleteLoginBg.confirm' => Yii::t('AdminModule.settings', 'Delete'),
        'confirm.deleteMailHeader.header' => Yii::t('AdminModule.settings', '<strong>Confirm</strong> image deletion'),
        'confirm.deleteMailHeader.body' => Yii::t(
            'UserModule.account',
            'Do you really want to delete your mail header image?'
        ),
        'confirm.deleteMailHeader.confirm' => Yii::t('AdminModule.settings', 'Delete'),
    ],
]);

$iconUrl = Yii::$app->img->icon->getUrl(['square' => 140]);
$loginBgUrl = Yii::$app->img->loginBackground->getUrl();
$mailHeaderUrl = Yii::$app->img->mailHeader->getUrl(
    ['maxHeight' => MailHeaderImage::MAX_HEIGHT, 'maxWidth' => MailHeaderImage::MAX_WIDTH]
);
$themeVariables = Yii::$app->view->theme->variables;
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.settings', 'Appearance Settings') ?></h4>
    <div class="text-body-secondary">
        <?= Yii::t('AdminModule.settings', 'These settings refer to the appearance of your social network.') ?>
    </div>

    <br>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'acknowledge' => true]); ?>

    <?= $form->field($model, 'theme')->dropDownList($model->getThemes()); ?>

    <?= $form->field($model, 'paginationSize') ?>

    <div class="container gx-0 overflow-x-hidden">
        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($model, 'displayNameFormat')->dropDownList(
                    [
                        '{username}' => Yii::t('AdminModule.settings', 'Username (e.g. john)'),
                        '{profile.firstname} {profile.lastname}' => Yii::t(
                            'AdminModule.settings',
                            'Firstname Lastname (e.g. John Doe)'
                        )
                    ]
                ) ?>
            </div>
            <div class="col-lg-6">
                <?= $form->field($model, 'displayNameSubFormat')->dropDownList($model->getDisplayNameSubAttributes()) ?>
            </div>

        </div>
    </div>

    <?= $form->field($model, 'spaceOrder')->dropDownList([
        '0' => Yii::t('AdminModule.settings', 'Custom sort order (alphabetical if not defined)'),
        '1' => Yii::t('AdminModule.settings', 'Last visit'),
    ]) ?>

    <?= $form->field($model, 'defaultStreamSort')->dropDownList($model->getDefaultStreamSortOptions()) ?>

    <?= $form->field($model, 'dateInputDisplayFormat')->dropDownList([
        '' => Yii::t(
            'AdminModule.settings',
            'Auto format based on user language - Example: {example}',
            ['{example}' => Yii::$app->formatter->asDate(time(), 'short')]
        ),
        'php:d/m/Y' => Yii::t(
            'AdminModule.settings',
            'Fixed format (dd/mm/yyyy) - Example: {example}',
            ['{example}' => Yii::$app->formatter->asDate(time(), 'php:d/m/Y')]
        ),
    ]) ?>

    <div class="bg-light p-3 mt-2">
        <?= $form->field($model, 'logo')->fileInput(
            [
                'id' => 'admin-logo-file-upload',
                'data-action-change' => 'admin.changeLogo',
                'style' => 'display: none',
                'name' => 'logo[]'
            ]
        ); ?>
        <div class="image-upload-container" id="logo-upload">

            <img class="rounded" id="logo-image" src="<?= Yii::$app->img->logo->getUrl() ?>"
                 data-src="holder.js/140x140"
                 alt="<?= Yii::t(
                     'AdminModule.settings',
                     "You're using no logo at the moment. Upload your logo now."
                 ) ?>"
                 style="max-height: 40px;<?= Yii::$app->img->logo->exists() ? '' : 'display:none' ?>">

            <div class="image-upload-buttons" id="logo-upload-buttons" style="display: block;">
                <?= Button::accent()->icon('cloud-upload')->id('admin-logo-upload-button')->sm()->loader(false) ?>

                <?= Button::danger()->id('admin-delete-logo-image')
                    ->action('admin.deletePageLogo', Url::to(['/admin/setting/delete-logo-image']))
                    ->style(Yii::$app->img->logo->exists() ? '' : 'display:none')->icon('remove')->sm()->loader(
                        false
                    ) ?>
            </div>
        </div>
    </div>

    <div class="bg-light p-3 mt-2">
        <?= $form->field($model, 'icon')->fileInput(
            [
                'id' => 'admin-icon-file-upload',
                'data-action-change' => 'admin.changeIcon',
                'class' => 'd-none',
                'name' => 'icon[]'
            ]
        ) ?>
        <div class="image-upload-container" id="icon-upload">
            <img class="rounded" id="icon-image" src="<?= $iconUrl ?>"
                 alt="<?= Yii::t(
                     'AdminModule.settings',
                     "You're using no icon at the moment. Upload your logo now."
                 ) ?>"
                 style="max-height: 40px;">

            <div class="image-upload-buttons" id="icon-upload-buttons" style="display: block;">
                <?= Button::accent()->icon('cloud-upload')->id('admin-icon-upload-button')->sm()->loader(false) ?>

                <?= Button::danger()->id('admin-delete-icon-image')
                    ->action('admin.deletePageIcon', Url::to(['/admin/setting/delete-icon-image']))
                    ->style(Yii::$app->img->icon->exists() ? '' : 'display:none')->icon('remove')->sm()->loader(
                        false
                    ) ?>
            </div>
        </div>
    </div>


    <div class="bg-light p-3 mt-2">
        <?= $form->field($model, 'loginBackgroundImage')->fileInput(
            [
                'id' => 'admin-loginBg-file-upload',
                'data-action-change' => 'admin.changeLoginBg',
                'class' => 'd-none',
                'name' => 'loginBackgroundImage[]'
            ]
        ) ?>
        <div class="image-upload-container" id="loginBg-upload">
            <img class="rounded" id="loginBg-image" src="<?= $loginBgUrl ?>" style="max-height: 40px;">

            <div class="image-upload-buttons" id="loginBg-upload-buttons" style="display: block;">
                <?= Button::accent()->icon('cloud-upload')->id('admin-loginBg-upload-button')->sm()->loader(false) ?>

                <?= Button::danger()->id('admin-delete-loginBg-image')
                    ->action('admin.deleteLoginBg', Url::to(['/admin/setting/delete-login-background-image']))
                    ->style(Yii::$app->img->loginBackground->exists() ? '' : 'display:none')->icon('remove')->sm(
                    )->loader(false) ?>
            </div>
        </div>
    </div>


    <div class="bg-light p-3 mt-2">
        <?= $form->field($model, 'mailHeaderImage')->fileInput(
            [
                'id' => 'admin-mailHeader-file-upload',
                'data-action-change' => 'admin.changeMailHeader',
                'class' => 'd-none',
                'name' => 'mailHeaderImage[]'
            ]
        ) ?>
        <div class="image-upload-container" id="mailHeader-upload">
            <img class="rounded" id="mailHeader-image" src="<?= $mailHeaderUrl ?>" style="max-height: 40px;">

            <div class="image-upload-buttons" id="mailHeader-upload-buttons" style="display: block;">
                <?= Button::accent()->icon('cloud-upload')->id('admin-mailHeader-upload-button')->sm()->loader(false) ?>

                <?= Button::danger()->id('admin-delete-mailHeader-image')
                    ->action('admin.deleteMailHeader', Url::to(['/admin/setting/delete-mail-header-image']))
                    ->style(Yii::$app->img->mailHeader->exists() ? '' : 'display:none')->icon('remove')->sm()->loader(false) ?>
            </div>
        </div>
    </div>

    <br>

    <?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Theme customization')) ?>

    <?php $checkBoxOptions = ['options' => ['class' => 'pt-2']]; ?>

    <div class="container gx-0 overflow-x-hidden">
        <div class="row">
            <div class="col-lg-4 mb-3">
                <?= Html::activeLabel($model, 'themePrimaryColor') ?>
                <div class="input-group input-color-group bg-light p-3 pb-0">
                    <?= $form->field($model, 'themePrimaryColor')->colorInput(
                        ['disabled' => $model->useDefaultThemePrimaryColor]
                    ) ?>
                    <?= $form->field($model, 'useDefaultThemePrimaryColor', $checkBoxOptions)->checkbox() ?>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <?= Html::activeLabel($model, 'themeAccentColor') ?>
                <div class="input-group input-color-group bg-light p-3 pb-0">
                    <?= $form->field($model, 'themeAccentColor')->colorInput(
                        ['disabled' => $model->useDefaultThemeAccentColor]
                    ) ?>
                    <?= $form->field($model, 'useDefaultThemeAccentColor', $checkBoxOptions)->checkbox() ?>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <?= Html::activeLabel($model, 'themeSecondaryColor') ?>
                <div class="input-group input-color-group bg-light p-3 pb-0">
                    <?= $form->field($model, 'themeSecondaryColor')->colorInput(
                        ['disabled' => $model->useDefaultThemeSecondaryColor]
                    ) ?>
                    <?= $form->field($model, 'useDefaultThemeSecondaryColor', $checkBoxOptions)->checkbox() ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-3">
                <?= Html::activeLabel($model, 'themeSuccessColor') ?>
                <div class="input-group input-color-group bg-light p-3 pb-0">
                    <?= $form->field($model, 'themeSuccessColor')->colorInput(
                        ['disabled' => $model->useDefaultThemeSuccessColor]
                    ) ?>
                    <?= $form->field($model, 'useDefaultThemeSuccessColor', $checkBoxOptions)->checkbox() ?>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <?= Html::activeLabel($model, 'themeDangerColor') ?>
                <div class="input-group input-color-group bg-light p-3 pb-0">
                    <?= $form->field($model, 'themeDangerColor')->colorInput(
                        ['disabled' => $model->useDefaultThemeDangerColor]
                    ) ?>
                    <?= $form->field($model, 'useDefaultThemeDangerColor', $checkBoxOptions)->checkbox() ?>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <?= Html::activeLabel($model, 'themeWarningColor') ?>
                <div class="input-group input-color-group bg-light p-3 pb-0">
                    <?= $form->field($model, 'themeWarningColor')->colorInput(
                        ['disabled' => $model->useDefaultThemeWarningColor]
                    ) ?>
                    <?= $form->field($model, 'useDefaultThemeWarningColor', $checkBoxOptions)->checkbox() ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-3">
                <?= Html::activeLabel($model, 'themeInfoColor') ?>
                <div class="input-group input-color-group bg-light p-3 pb-0">
                    <?= $form->field($model, 'themeInfoColor')->colorInput(
                        ['disabled' => $model->useDefaultThemeInfoColor]
                    ) ?>
                    <?= $form->field($model, 'useDefaultThemeInfoColor', $checkBoxOptions)->checkbox() ?>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <?= Html::activeLabel($model, 'themeLightColor') ?>
                <div class="input-group input-color-group bg-light p-3 pb-0">
                    <?= $form->field($model, 'themeLightColor')->colorInput(
                        ['disabled' => $model->useDefaultThemeLightColor]
                    ) ?>
                    <?= $form->field($model, 'useDefaultThemeLightColor', $checkBoxOptions)->checkbox() ?>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <?= Html::activeLabel($model, 'themeDarkColor') ?>
                <div class="input-group input-color-group bg-light p-3 pb-0">
                    <?= $form->field($model, 'themeDarkColor')->colorInput(
                        ['disabled' => $model->useDefaultThemeDarkColor]
                    ) ?>
                    <?= $form->field($model, 'useDefaultThemeDarkColor', $checkBoxOptions)->checkbox() ?>
                </div>
            </div>
        </div>
    </div>

    <?= $form->field($model, 'themeCustomScss')->widget(CodeMirrorInputWidget::class, ['mode' => 'text/x-scss']) ?>

    <?= $form->endCollapsibleFields() ?>

    <hr>
    <?= Html::submitButton(
        Yii::t('AdminModule.settings', 'Save'),
        ['class' => 'btn btn-primary', 'data-ui-loader' => ""]
    ) ?>

    <?php ActiveForm::end(); ?>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        // Disable the color field pickers when the "default" checkbox is checked
        function setupColorFieldToggleDisabled(color, defaultColorValue) {
            const $checkbox = $('#designsettingsform-usedefaulttheme' + color + 'color');
            const $colorField = $('#designsettingsform-theme' + color + 'color');
            $checkbox.on('change', function () {
                if ($checkbox.is(':checked')) {
                    $colorField.prop('disabled', true).prop('value', defaultColorValue);
                } else {
                    $colorField.prop('disabled', false);
                }
            });
        }

        <?php foreach ([
        'primary',
        'accent',
        'secondary',
        'success',
        'danger',
        'warning',
        'info',
        'light',
        'dark'
    ] as $color) : ?>
        setupColorFieldToggleDisabled('<?= $color ?>', '<?= $themeVariables->get($color) ?>');
        <?php endforeach; ?>
    })
</script>
