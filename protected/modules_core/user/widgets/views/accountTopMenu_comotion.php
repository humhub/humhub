
<?php if (!Yii::app()->user->isGuest): ?>
<div>
  <img id="user-account-image" height="32" width="32" alt="32x32" style="width: 32px; height: 32px;"
    src="<?php echo Yii::app()->user->model->getProfileImage()->getUrl(); ?>">
  <p>
    <?php echo CHtml::encode(Yii::app()->user->displayName); ?>
  </p>
</div>
<?php endif; ?>

<ul class="nav navmenu-nav">
  <?php if (Yii::app()->user->isGuest): ?>
    <li><i class="fa fa-child"></i> <a href="<?php echo $this->createUrl('//user/auth/login'); ?>">Sign in</a>
  <?php else: ?>
    <li>
      <a href="<?php echo $this->createUrl('//user/profile', array('uguid' => Yii::app()->user->guid)); ?>">
        <i class="fa fa-user"></i> <?php echo Yii::t('base', 'My profile'); ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $this->createUrl('//user/account/edit'); ?>">
        <i class="fa fa-user"></i> <?php echo Yii::t("UserModule.widgets_views_profileEditButton", "Edit account"); ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $this->createUrl('//user/account/edit') ?>">
        <i class="fa fa-edit"></i> <?php echo Yii::t('base', 'Account settings'); ?>
      </a>
    </li>
    <li>
      <a href="#">
        <i class="fa fa-bell"></i> Notifications
      </a>
    </li>
    <?php if (Yii::app()->user->isAdmin()): ?>
      <li>
        <a href="<?php echo $this->createUrl('//admin/index') ?>">
          <i class="fa fa-cogs"></i> <?php echo Yii::t('base', 'Administration'); ?>
        </a>
      </li>
    <?php endif; ?>
    <?php if (!isset(Yii::app()->session['ntlm']) || Yii::app()->session['ntlm'] == false) : ?>
      <li>
        <a href="<?php echo $this->createUrl('//user/auth/logout') ?>">
          <i class="fa fa-sign-out"></i> <?php echo Yii::t('base', 'Logout'); ?>
        </a>
      </li>
    <?php endif; ?>
  <?php endif; ?>
</ul>
