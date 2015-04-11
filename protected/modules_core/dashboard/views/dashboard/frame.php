<?php
/**
 * Created by Andreas Strobel
 * Date: 25.06.13
 */
?>
<div class="container">
    <!-- Example row of columns -->
    <div class="row">
        <div class="col-md-8">
            <?php
            $this->widget('application.modules_core.post.widgets.FrameFormWidget', array(
                'contentContainer' => Yii::app()->user->model,
                'url' => $url
            ));
            ?>
        </div>
    </div>

</div>
