<div class="text text-center animated fadeIn">

    <?php echo Yii::t('base', "Choose language:"); ?> &nbsp;
    <div class="langSwitcher">
        <?php
        $form = $this->beginWidget('HActiveForm', array(
            'id' => 'choose-language-form',
            'enableAjaxValidation' => false
        ));
        ?>

        <?php echo $form->dropDownList($model, 'language', $languages, array('submit' => '')); ?>

        <?php $this->endWidget(); ?>
    </div>
</div>