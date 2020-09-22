<?php

use yii\helpers\Url;

/* @var $this \humhub\modules\ui\view\components\View */
?>
<script <?= \humhub\libs\Html::nonce() ?>>
    $(document).one('humhub:ready', function () {
        humhub.require('tour').start(
            {
                name: 'administration',
                steps: [
                    {
                        orphan: true,
                        backdrop: true,
                        title: <?php echo json_encode(Yii::t('TourModule.administration', '<strong>Administration</strong>')); ?>,
                        content: <?php echo json_encode(Yii::t('TourModule.administration', "As an admin, you can manage the whole platform from here.<br><br>Apart from the modules, we are not going to go into each point in detail here, as each has its own short description elsewhere.")); ?>
                    },
                    {
                        element: ".list-group-item.modules",
                        title: <?php echo json_encode(Yii::t('TourModule.administration', '<strong>Modules</strong>')); ?>,
                        content: <?php echo json_encode(Yii::t('TourModule.administration', 'You are currently in the tools menu. From here you can access the HumHub online marketplace, where you can install an ever increasing number of tools on-the-fly.<br><br>As already mentioned, the tools increase the features available for your space.')); ?>,
                        placement: "right"
                    },
                    {
                        orphan: true,
                        backdrop: true,
                        title: <?php echo json_encode(Yii::t('TourModule.administration', "<strong>Hurray!</strong> That's all for now.")); ?>,
                        content: <?php echo json_encode(Yii::t('TourModule.administration', 'You have now learned about all the most important features and settings and are all set to start using the platform.<br><br>We hope you and all future users will enjoy using this site. We are looking forward to any suggestions or support you wish to offer for our project. Feel free to contact us via www.humhub.org.<br><br>Stay tuned. :-)')); ?>
                    }

                ]
            }
        );
    });
</script>
