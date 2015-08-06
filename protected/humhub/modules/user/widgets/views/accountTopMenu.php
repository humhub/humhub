<?php

use \yii\helpers\Html;
use \yii\helpers\Url;
?>
<?php if (Yii::$app->user->isGuest): ?>
    <a href="<?php echo Url::toRoute('/user/auth/login'); ?>" class="btn btn-enter" data-target="#globalModal">Sign in / up</a>
<?php else: ?>
    <ul class="nav">
        <li class="dropdown account">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <div class="user-title pull-left hidden-xs">
                    <strong><?php echo Html::encode($user->displayName); ?></strong><br/><span class="truncate"><?php echo Html::encode($user->profile->title); ?></span>
                </div>

                <img id="user-account-image" class="img-rounded"
                     src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                     height="32" width="32" alt="32x32" data-src="holder.js/32x32"
                     style="width: 32px; height: 32px;"/>

                <b class="caret"></b>
            </a>
            <ul class="dropdown-menu pull-right">
                <li>
                    <a href="<?php echo $user->createUrl('/user/profile'); ?>"><i
                            class="fa fa-user"></i> <?php echo Yii::t('base', 'My profile'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo Url::toRoute('/user/account/edit'); ?>"><i
                            class="fa fa-edit"></i> <?php echo Yii::t('base', 'Account settings'); ?>
                    </a>
                </li>

                <?php if (Yii::$app->user->isAdmin()) : ?>
                    <li class="divider"></li>
                    <li>
                        <a href="<?php echo Url::toRoute('/admin'); ?>"><i
                                class="fa fa-cogs"></i> <?php echo Yii::t('base', 'Administration'); ?>
                        </a>
                    </li>
                <?php endif; ?>


                <!-- if the current user has admin rights -->
                <?php if ($showUserApprovals) : ?>
                    <li>
                        <a href="<?php echo Url::toRoute('/admin/approval'); ?>"><i
                                class="fa fa-check-circle"></i> <?php echo Yii::t('base', 'User Approvals'); ?>
                        </a>
                    </li>
                <?php endif; ?>


                <li class="divider"></li>
                <li>
                    <a href="<?php echo Url::toRoute('/user/auth/logout'); ?>"><i
                            class="fa fa-sign-out"></i> <?php echo Yii::t('base', 'Logout'); ?>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
<?php endif; ?>