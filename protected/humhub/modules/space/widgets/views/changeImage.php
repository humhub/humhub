<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('SpaceModule.widgets_views_changeImage', 'Current space image'); ?></div>
    <div class="panel-body">
        <img src="<?= $this->getController()->getSpace()->getProfileImage()->getUrl(); ?>" alt=""/><br><br>
        <?= CHtml::link(Yii::t('SpaceModule.widgets_views_changeImage', "Change image"), $this->createUrl('//space/admin/changeImage', array('sguid' => $this->getController()->getSpace()->guid)), array('class' => 'btn btn-primary')); ?>
    </div>
</div>
<br>
