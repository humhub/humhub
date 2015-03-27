<div class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.views_setup_prerequisites', '<strong>System</strong> Check'); ?>
    </div>

    <div class="panel-body">
        <p><?php echo Yii::t('InstallerModule.views_setup_prerequisites', 'This overview shows all system requirements of HumHub.'); ?></p>

        <hr/>
            <div class="prerequisites-list">

                <ul>

                    <?php foreach ($checks as $check): ?>
                        <li>

                            <?php if ($check['state'] == 'OK') : ?>
                                <i class="fa fa-check-circle check-ok animated bounceIn"></i>
                            <?php elseif ($check['state'] == 'WARNING') : ?>
                                <i class="fa fa-exclamation-triangle check-warning animated swing"></i>
                            <?php else : ?>
                                <i class="fa fa-minus-circle check-error animated wobble"></i>
                            <?php endif; ?>

                            <strong><?php echo $check['title']; ?></strong>

                            <?php if (isset($check['hint'])): ?>
                                <span>(Hint: <?php echo $check['hint']; ?>)</span>
                            <?php endif; ?>

                        </li>
                    <?php endforeach; ?>

                </ul>
            </div>

            <?php if (!$hasError): ?>
                <div class="alert alert-success">
                    <?php echo Yii::t('InstallerModule.views_setup_prerequisites', 'Congratulations! Everything is ok and ready to start over!'); ?>
                </div>
            <?php endif; ?>

            <hr>

        <?php echo HHtml::link('<i class="fa fa-repeat"></i> '. Yii::t('InstallerModule.views_setup_prerequisites','Check again'), array('//installer/setup/prerequisites'), array('class' => 'btn btn-default')); ?>

        <?php if (!$hasError): ?>
                <?php echo HHtml::link(Yii::t('InstallerModule.views_setup_prerequisites','Next'). ' <i class="fa fa-arrow-circle-right"></i>', array('//installer/setup/database'), array('class' => 'btn btn-primary')); ?>
            <?php endif; ?>







    </div>
</div>