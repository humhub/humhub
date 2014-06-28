<div class="panel panel-default">
    <div class="panel-body">
        <p class="lead"><?php echo Yii::t('InstallerModule.base', '<strong>HumHub</strong> system check'); ?></p>
        <p><?php echo Yii::t('InstallerModule.base', 'In the following overview, you can see, if all requierements are ready.'); ?></p>

        <hr/>
            <div class="well">

                <ul>

                    <?php foreach ($checks as $check): ?>
                        <li>
                            <strong><?php echo $check['title']; ?>:</strong>

                            <?php if ($check['state'] == 'OK') : ?>
                                <span style="color:green">Ok!</span>
                            <?php elseif ($check['state'] == 'WARNING') : ?>
                                <span style="color:orange">Warning!</span>
                            <?php else : ?>
                                <span style="color:red">Error!</span>
                            <?php endif; ?>

                            <?php if (isset($check['hint'])): ?>
                                <span>(Hint: <?php echo $check['hint']; ?>)</span>
                            <?php endif; ?>

                        </li>
                    <?php endforeach; ?>


                </ul>
            </div>
        <br/>

            <?php if (!$hasError): ?>
                <div class="alert alert-success">
                    <?php echo Yii::t('InstallerModule.base', 'Congratulations! Everything is ok and ready to start over!'); ?>
                </div>
            <?php endif; ?>

            <hr>

        <?php echo HHtml::link('<i class="fa fa-repeat"></i> '. Yii::t('InstallerModule.base','Check again'), array('//installer/setup/prerequisites'), array('class' => 'btn btn-default')); ?>

        <?php if (!$hasError): ?>
                <?php echo HHtml::link(Yii::t('InstallerModule.base','Next'). ' <i class="fa fa-arrow-circle-right"></i>', array('//installer/setup/database'), array('class' => 'btn btn-primary')); ?>
            <?php endif; ?>







    </div>
</div>