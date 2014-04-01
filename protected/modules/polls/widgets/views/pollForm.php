
    <?php echo CHtml::textArea("question", "", array('id'=>'contentForm_question', 'class' => 'form-control autosize contentForm', 'rows' => '1', "tabindex" => "1", "placeholder" => Yii::t('PollsModule.base', "Ask something..."))); ?>

<div class="contentForm_options">
    <?php echo CHtml::textArea("answersText", "", array('id' => "contentForm_answersText", 'rows' => '5', "class" => "form-control contentForm", "tabindex" => "2", "placeholder" => Yii::t('PollsModule.base', "Possible answers (one per line)"))); ?>
    <div class="checkbox">
        <label>
            <?php echo CHtml::checkbox("contentForm_allowMultiple", "", array('class' => 'checkbox tick contentForm', "tabindex" => "4")); ?> <?php echo Yii::t('PollsModule.base', 'Allow multiple answers per user?'); ?>
        </label>
    </div>
    
</div>

