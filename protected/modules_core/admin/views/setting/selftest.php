<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_selftest', '<strong>Self</strong> test'); ?></div>
    <div class="panel-body">

        <p><?php echo Yii::t('AdminModule.views_setting_selftest', 'Checking HumHub software prerequisites.'); ?></p>

        <div class="well">

            <ul>

                <?php foreach ($checks as $check): ?>
                    <li>
                        <strong><?php echo $check['title']; ?>:</strong>

                        <?php if ($check['state'] == 'OK') : ?>
                            <span style="color:green">Ok!</span>
                        <?php elseif ($check['state'] == 'WARNING') : ?>
                            <span style="color:orange">Warning!</span>
                            <?php else :
                            ?>
                            <span style="color:red">Error!</span>
                        <?php endif; ?>

                        <?php if (isset($check['hint'])): ?>
                            <span>(Hint: <?php echo $check['hint']; ?>)</span>
                        <?php endif; ?>

                    </li>
                <?php endforeach; ?>


            </ul>


        </div>
        <br>


        <div class="well">
            <pre>
                <?php echo $migrate; ?>
            </pre>
        </div>
        <hr>

        <?php echo HHtml::link(Yii::t('AdminModule.views_setting_selftest', 'Re-Run tests'), array('//admin/setting/selftest'), array('class' => 'btn btn-primary')); ?>

    </div>
</div>

