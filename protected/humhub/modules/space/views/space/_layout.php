<?php
$space = $this->context->contentContainer;
?>
<div class="container">
    <div class="row">
        <div class="col-lg-3 visible-lg">
            <!-- load space chooser widget -->
            <?php echo \humhub\modules\space\widgets\Chooser::widget(); ?>
        </div>
        <div class="col-lg-9">
            <?php echo humhub\modules\space\widgets\Header::widget(['space' => $space]); ?>

            <div class="row">
                <div class="col-lg-8">
                    <?php echo $content; ?>
                </div>

                <div class="col-lg-4">
                    <?php
                    echo \humhub\modules\space\widgets\Sidebar::widget(['space' => $space, 'widgets' => [
                        [\humhub\modules\activity\widgets\Stream::className(), ['streamAction' => '/space/space/stream', 'contentContainer' => $space], ['sortOrder' => 10]],
                        [\humhub\modules\space\widgets\Members::className(), ['space' => $space], ['sortOrder' => 20]]
                    ]]);
                    ?>
                </div>
            </div>

        </div>

    </div>
</div>
