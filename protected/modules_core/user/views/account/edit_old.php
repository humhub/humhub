<h1><?php echo Yii::t('base', 'User details'); ?></h1>

<?php
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'user-form',
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
    'type' => 'horizontal',
));
?>

<?php echo $form->errorSummary($model); ?>

<ul class="nav nav-pills" id="accountTabs">
    <li class="active"><a href="#general">General</a></li>
    <li><a href="#communication">Communication</a></li>
    <li><a href="#social">Social bookmarks</a></li>
    <li><a href="#settings">Settings</a></li>
</ul>

<hr>
<div class="tab-content">
    <div class="tab-pane active" id="general">

        <?php echo $form->textField($model, 'firstname', array('class' => 'span12', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'lastname', array('class' => 'span12', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'title', array('class' => 'span12', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'street', array('class' => 'span12', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'zip', array('class' => 'span3', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'city', array('class' => 'span12', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'country', array('class' => 'span12', 'maxlength' => 45)); ?>

        <?php echo $form->textField($model, 'state', array('class' => 'span12', 'maxlength' => 45)); ?>

        <?php echo $form->textArea($model, 'about', array('rows' => 6, 'cols' => 50, 'class' => 'span12')); ?>

        <?php echo $form->textField($model, 'tags', array('rows' => 6, 'cols' => 50, 'class' => 'span12')); ?>


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

    </div>

</div>
<div class="form-actions">
    <?php
    $this->widget('bootstrap.widgets.TbButton', array(
        'buttonType' => 'submit',
        'type' => 'primary',
        'label' => 'Save',
    ));
    ?>
</div>


<?php $this->endWidget(); ?>


<script>
    $(document).ready(function () {
        $('#accountTabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        })


        /* Automagically jump on good tab based on anchor; for page reloads or links */
        if (location.hash) {
            $('a[href=' + location.hash + ']').tab('show');
        }

        /* Update hash based on tab, basically restores browser default behavior to
         fix bootstrap tabs */
        $(document.body).on("click", "a[data-toggle]", function (event) {
            location.hash = this.getAttribute("href");
        });


        /* on history back activate the tab of the location hash
         if exists or the default tab if no hash exists */
        $(window).on('popstate', function () {
            var anchor = location.hash || $("a[data-toggle=tab]").first().attr("href");
            $('a[href=' + anchor + ']').tab('show');
        });
    });
</script>