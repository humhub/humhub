<div class="btn-group">
    <a href="#" id="icon-messages" class="dropdown-toggle" data-toggle="dropdown"><i
            class="icon-envelope"></i></a>
    <span id="badge-messages" style="display:none;"
          class="label label-danger label-notification">1</span>
    <ul id="dropdown-messages" class="dropdown-menu">
    </ul>
</div>

<script type="text/javascript">
    // open the messages menu
    $('#icon-messages').click(function () {

        // remove all <li> entries from dropdown
        $('#dropdown-messages').find('li').remove();

        // append title and loader to dropdown
        $('#dropdown-messages').append('<li class="dropdown-header"><?php echo Yii::t('base', 'Messages'); ?></li> <ul class="media-list"><li id="loader_messages"><div class="loader"></div></li></ul><li><div class="dropdown-footer"><a class="btn btn-default col-md-12" href="<?php echo Yii::app()->createUrl('//mail/mail/index'); ?>"><?php echo Yii::t('base', 'Show all messages'); ?></a></div></li>');

        // load newest notifications
        $.ajax({
            'type': 'GET',
            'url': '<?php echo $this->createUrl('//mail/mail/list', array('ajax' => 1)); ?>',
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'success': function (html) {
                jQuery("#loader_messages").replaceWith(html)
            }});

    })
</script>