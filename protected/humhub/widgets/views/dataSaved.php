<!-- check if flash message exists -->
<?php if(Yii::$app->getSession()->hasFlash('data-saved')): ?>

    <!-- <span> element to display the message -->
    <span class="data-saved"><i class="fa fa-check-circle"></i> <?php echo Yii::$app->getSession()->getFlash('data-saved'); ?></span>

    <script type="text/javascript">

        /* animate the flash message */
        $('.data-saved').hide();
        $('.data-saved').fadeIn('slow', function() {
            $('.data-saved').delay(1000).fadeOut('slow', function() {
                $(this).remove();
            });
        });

    </script>

<?php endif; ?>





