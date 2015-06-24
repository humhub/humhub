<?php

print HHtml::Link(Yii::t("UserModule.widgets_views_profileEditButton", "Edit account"), $this->createUrl('//user/account/edit'), array('class' => 'btn btn-primary'));
