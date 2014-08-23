<div class="panel panel-default animated fadeIn">

    <div class="install-header install-header-small" style="background-image: url('<?php echo $this->module->assetsUrl; ?>/humhub-install-header.jpg');">
        <h2 class="install-header-title"><?php echo Yii::t('InstallerModule.base', '<strong>System</strong> Check'); ?></h2>
    </div>

    <div class="panel-body">
        <p><?php echo Yii::t('InstallerModule.base', 'This overview shows all system requirements of HumHub.'); ?></p>

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
                    <?php echo Yii::t('InstallerModule.base', 'Congratulations! Everything is ok and ready to start over!'); ?>
                </div>
            <?php endif; ?>

            <hr>

        <?php echo HHtml::link('<i class="fa fa-repeat"></i> '. Yii::t('InstallerModule.base','Check again'), array('//installer/setup/prerequisites'), array('class' => 'btn btn-info')); ?>

        <?php if (!$hasError): ?>
                <?php echo HHtml::link(Yii::t('InstallerModule.base','Next'). ' <i class="fa fa-arrow-circle-right"></i>', array('//installer/setup/database'), array('class' => 'btn btn-primary')); ?>
            <?php endif; ?>







    </div>
</div>