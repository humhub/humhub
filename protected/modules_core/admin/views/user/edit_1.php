
<h1><?php echo Yii::t('AdminModule.base', 'Edit user'); ?></h1>
<br>

<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'admin-editUser-form',
    'enableAjaxValidation' => false,
));
?>



<?php echo $form->errorSummary($model); ?>



<ul class="nav nav-tabs" id="accountTabs">
    <li class="active"><a href="#general">General</a></li>
    <li><a href="#communication">Communication</a></li>
    <li><a href="#social">Social bookmarks</a></li>
    <li><a href="#settings">Settings</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane active" id="general">

        <?php
        $groupModels = Group::model()->findAll();
        $list = CHtml::listData($groupModels, 'id', 'name');
        ?>

        <?php echo $form->textField($model, 'firstname', array('class' => 'span8', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'lastname', array('class' => 'span8', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'title', array('class' => 'span8', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'street', array('class' => 'span8', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'zip', array('class' => 'span3', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'city', array('class' => 'span8', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'country', array('class' => 'span8', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'state', array('class' => 'span8', 'maxlength' => 45)); ?>

        <hr>
        <?php echo $form->fileFieldRow($model, 'fileField', array('labelOptions' => array('label' => 'Profile image'))); ?>
        <hr>

        <?php echo $form->textArea($model, 'about', array('rows' => 6, 'cols' => 50, 'class' => 'span10')); ?>

        <?php echo $form->textField($model, 'tags', array('rows' => 6, 'cols' => 50, 'class' => 'span10')); ?>


    </div>
    <div class="tab-pane" id="communication">

        <?php echo $form->textField($model, 'phone_private', array('class' => 'span8', 'maxlength' => 100)); ?>

        <?php echo $form->textField($model, 'phone_work', array('class' => 'span8', 'maxlength' => 100)); ?>

        <?php echo $form->textField($model, 'mobile', array('class' => 'span8', 'maxlength' => 100)); ?>

        <?php echo $form->textField($model, 'fax', array('class' => 'span8', 'maxlength' => 100)); ?>

        <?php echo $form->textField($model, 'im_skype', array('class' => 'span8', 'maxlength' => 100, 'labelOptions' => array('label' => 'Skype nickname'))); ?>

        <?php echo $form->textField($model, 'im_msn', array('class' => 'span8', 'maxlength' => 100)); ?>

        <?php echo $form->textField($model, 'im_icq', array('class' => 'span8', 'maxlength' => 100)); ?>

        <?php echo $form->textField($model, 'im_xmpp', array('class' => 'span8', 'maxlength' => 100)); ?>

        <?php echo $form->textField($model, 'url', array('class' => 'span8', 'maxlength' => 255)); ?>


    </div>
    <div class="tab-pane" id="social">

        <?php echo $form->textField($model, 'url_facebook', array('class' => 'span12', 'maxlength' => 255, 'labelOptions' => array('label' => 'Your Facebook Url'))); ?>

        <?php echo $form->textField($model, 'url_linkedin', array('class' => 'span12', 'maxlength' => 255)); ?>

        <?php echo $form->textField($model, 'url_xing', array('class' => 'span12', 'maxlength' => 255)); ?>

        <?php echo $form->textField($model, 'url_youtube', array('class' => 'span12', 'maxlength' => 255)); ?>

        <?php echo $form->textField($model, 'url_vimeo', array('class' => 'span12', 'maxlength' => 255)); ?>

        <?php echo $form->textField($model, 'url_flickr', array('class' => 'span12', 'maxlength' => 255)); ?>

        <?php echo $form->textField($model, 'url_myspace', array('class' => 'span12', 'maxlength' => 255)); ?>

        <?php echo $form->textField($model, 'url_googleplus', array('class' => 'span12', 'maxlength' => 255)); ?>

        <?php echo $form->textField($model, 'url_twitter', array('class' => 'span12', 'maxlength' => 255)); ?>

    </div>


    <div class="tab-pane" id="settings">

        <?php echo $form->dropDownList($model, 'language', array('en' => 'English', 'de' => 'Deutsch*', 'fr' => 'Francais*')); ?>

        <hr>

        <?php echo $form->dropDownList($model, 'group_id', $list); ?>

        <?php echo $form->dropDownList($model, 'super_admin', array('0' => "No", '1' => "Yes")); ?>

        <?php echo $form->dropDownList($model, 'status', array('0' => "Disabled", '1' => "Enabled", '2' => "Needs approval")); ?>

    </div>


</div>


<hr>
<?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

<?php $this->endWidget(); ?>



<script>
    $('#accountTabs a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    })
</script>
