<?php

    use yii\helpers\Html;
?>
<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('UserModule.views_profile_about', '<strong>About</strong> this user'); ?></div>

    <div class="panel-body">

        <?php $firstClass = "active"; ?>


        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            <?php foreach ($user->profile->getProfileFieldCategories() as $category): ?>
                <li class="<?php echo $firstClass;
                $firstClass = ""; ?>"><a href="#profile-category-<?php echo $category->id; ?>"
                                         data-toggle="tab"><?php echo Html::encode(Yii::t($category->getTranslationCategory(), $category->title)); ?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php $firstClass = "active"; ?>

        <div class="tab-content">
            <?php foreach ($user->profile->getProfileFieldCategories() as $category): ?>

                <div class="tab-pane <?php echo $firstClass;
                $firstClass = ""; ?>" id="profile-category-<?php echo $category->id; ?>">
                    <form class="form-horizontal" role="form">
                        <?php foreach ($user->profile->getProfileFields($category) as $field) : ?>
                             <div class="form-group">
                                <label
                                    class="col-sm-3 control-label"><?php echo Html::encode(Yii::t($field->getTranslationCategory(), $field->title)); ?></label>


                                <?php

                                $fieldType = $field->field_type_class;
                                //print_r($field);
                                if (strtolower($field->title) == 'about') {

                                    ?>
                                    <div class="col-sm-9">
                                        <?php

                                        if (strpos($fieldType, 'Markdown') !== false) {
                                            $briefText = \yii\helpers\Markdown::process($field->getUserValue($user, false));
                                            ?>
                                            <p class="form-control-static"
                                               style="min-height: 0 !important;padding-top:0;"><?php echo $briefText; ?></p>
                                        <?php } else {
                                            ?>
                                            <p class="form-control-static"><?php echo humhub\widgets\RichText::widget(['text' => $field->getUserValue($user, true)]); ?></p>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <?php

                                } else {

                                    ?>
                                    <div class="col-sm-9">

                                        <?php

                                        if (strpos($fieldType, 'Markdown') !== false) {
                                            $briefText = \yii\helpers\Markdown::process($field->getUserValue($user, false));
                                            ?>
                                            <p class="form-control-static"
                                               style="min-height: 0 !important;padding-top:0;"><?php echo $briefText; ?></p>
                                        <?php } else {
                                            ?>
                                            <p class="form-control-static"><?php echo $field->getUserValue($user, false); ?></p>                                            <?php
                                        }
                                        ?>

                                    </div>
                                <?php } ?>
                            </div>

                        <?php endforeach; ?>

                    </form>
                </div>
            <?php endforeach; ?>
        </div>

    </div>


</div>
