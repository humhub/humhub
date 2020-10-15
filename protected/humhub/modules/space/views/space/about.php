<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\AboutPageSidebar;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\user\widgets\Image;

/**
 * @var Space $space
 * @var array $userGroups
 */
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('SpaceModule.base', '<strong>About</strong> the Space') ?>
    </div>
    <div class="panel-body">
        <?php if ($space->about || $space->description): ?>
            <div>
                <div data-ui-markdown data-ui-show-more data-collapse-at="600">
                    <?= RichText::output(empty($space->about) ? $space->description : $space->about) ?>
                </div>
            </div>
            <br>
        <?php endif; ?>

        <div class="row">

            <div class="col-md-4">
                <?php if (!empty($userGroups[Space::USERGROUP_OWNER])): ?>
                    <div class="media">
                        <div class="media-heading"><p><strong><?= Yii::t('SpaceModule.base', 'Owner'); ?></strong>
                            </p></div>
                        <div class="media-body">
                            <?php foreach ($userGroups[Space::USERGROUP_OWNER] as $user) {
                                echo Image::widget([
                                    'showTooltip' => true,
                                    'user' => $user, 'width' => 40,
                                    'htmlOptions' => ['style' => 'padding: 3px'],
                                    'imageOptions' => ['style' => 'border:1px solid ' . $this->theme->variable('success')]
                                ]);
                            }
                            ?>
                        </div>
                    </div>

                    <br/>
                <?php endif; ?>
            </div>

            <div class="col-md-8">
                <?php if (!empty($userGroups[Space::USERGROUP_ADMIN])): ?>
                    <div class="media">
                        <div class="media-heading"><p><strong><?= Yii::t('SpaceModule.base', 'Admin'); ?></strong></p>
                        </div>
                        <div class="media-body">
                            <?php foreach ($userGroups[Space::USERGROUP_ADMIN] as $user) {
                                echo Image::widget([
                                    'showTooltip' => true,
                                    'user' => $user, 'width' => 40,
                                    'htmlOptions' => ['style' => 'padding: 3px'],
                                    'imageOptions' => ['style' => 'border:1px solid ' . $this->theme->variable('success')]
                                ]);
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <?php if (!empty($userGroups[Space::USERGROUP_MODERATOR])): ?>
            <div class="media">
                <div class="media-heading"><p><strong><?= Yii::t('SpaceModule.base', 'Moderator'); ?></strong></p></div>
                <div class="media-body">
                    <?php foreach ($userGroups[Space::USERGROUP_MODERATOR] as $user) {
                        echo Image::widget([
                            'showTooltip' => true,
                            'user' => $user, 'width' => 40,
                            'htmlOptions' => ['style' => 'padding: 3px'],
                            'imageOptions' => ['style' => 'border:1px solid ' . $this->theme->variable('success')]
                        ]);
                    }
                    ?>
                </div>
            </div>
            <br/>
        <?php endif; ?>

        <br/>

        <div class="row">
            <div class="col-md-4">
                <p><strong><?= Yii::t('SpaceModule.base', 'Join Policy') ?></strong></p>
                <p><i class="fa fa-users colorInfo"></i> <?= Space::joinPolicyOptions()[$space->join_policy] ?></p>
                <br/>
            </div>
            <div class="col-md-8">
                <p><strong><?= Yii::t('SpaceModule.base', 'Space Visibility') ?></strong></p>
                <p><i class="fa fa-globe colorInfo"></i> <?= Space::visibilityOptions()[$space->visibility] ?></p>
            </div>
        </div>


    </div>
</div>

<?php $this->beginBlock('sidebar');
echo AboutPageSidebar::widget(['space' => $space]);
$this->endBlock(); ?>
