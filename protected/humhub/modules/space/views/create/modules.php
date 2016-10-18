<?php

use yii\helpers\Url;

?>
<div class="modal-dialog modal-dialog-medium animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('SpaceModule.views_create_modules', 'Add <strong>Modules</strong>') ?></h4>
        </div>
        <div class="modal-body">
            <br><br>

            <div class="row">

                <?php foreach ($availableModules as $moduleId => $module): ?>
                    <div class="col-md-6">
                        <div class="media well well-small ">
                            <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                                 style="width: 64px; height: 64px;"
                                 src="<?php echo $module->getContentContainerImage($space); ?>">

                            <div class="media-body">
                                <h4 class="media-heading"><?php echo $module->getContentContainerName($space); ?>
                                </h4>

                                <p style="height: 35px;"><?php echo \humhub\libs\Helpers::truncateText($module->getContentContainerDescription($space), 75); ?></p>

                                <?php
                                $enable = "";
                                $disable = "hidden";

                                if ($space->isModuleEnabled($moduleId)) {
                                    $enable = "hidden";

                                    if (!$space->canDisableModule($moduleId)) {
                                        $disable = "disabled";
                                    } else {
                                        $disable = "";
                                    }

                                }
                                ?>


                                <?php
                                echo \humhub\widgets\AjaxButton::widget([
                                    'label' => Yii::t('SpaceModule.views_admin_modules', 'Enable'),
                                    'ajaxOptions' => [
                                        'type' => 'POST',
                                        'success' => new yii\web\JsExpression('function(){
                                    $("#btn-enable-module-' . $moduleId . '").addClass("hidden");
                                    $("#btn-disable-module-' . $moduleId . '").removeClass("hidden");
                                    }'),
                                        'url' => $space->createUrl('/space/manage/module/enable', ['moduleId' => $moduleId]),
                                    ],
                                    'htmlOptions' => [
                                        'class' => 'btn btn-sm btn-primary '. $enable,
                                        'id' => 'btn-enable-module-' . $moduleId
                                    ]
                                ]);
                                ?>


                                <?php

                                echo \humhub\widgets\AjaxButton::widget([
                                    'label' => Yii::t('SpaceModule.views_admin_modules', 'Disable'),
                                    'ajaxOptions' => [
                                        'type' => 'POST',
                                        'success' => new yii\web\JsExpression('function(){
                                    $("#btn-enable-module-' . $moduleId . '").removeClass("hidden");
                                    $("#btn-disable-module-' . $moduleId . '").addClass("hidden");
                                     }'),
                                        'url' => $space->createUrl('/space/manage/module/disable', ['moduleId' => $moduleId]),
                                    ],
                                    'htmlOptions' => [
                                        'class' => 'btn btn-sm btn-info '. $disable,
                                        'id' => 'btn-disable-module-' . $moduleId
                                    ]
                                ]);
                                ?>


                            </div>

                        </div>
                        <br>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <div class="modal-footer">
            <hr>
            <br>
            <?php
            echo \humhub\widgets\AjaxButton::widget([
                'label' => Yii::t('SpaceModule.views_create_modules', 'Next'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                    'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
                    'url' => Url::to(['/space/create/invite', 'spaceId' => $space->id]),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
            ?>

            <?php echo \humhub\widgets\LoaderWidget::widget(['id' => 'invite-loader', 'cssClass' => 'loader-modal hidden']); ?>
        </div>

    </div>

</div>
