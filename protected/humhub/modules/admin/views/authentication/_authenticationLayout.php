<div class="panel-body">
    <h4><?= Yii::t('AdminModule.setting', 'User Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.setting', 'Here you can configurate the registration behaviour and additinal user settings of your social network.'); ?>
    </div>
</div>

<?= humhub\modules\admin\widgets\AuthenticationMenu::widget(); ?>
<?= $content; ?>