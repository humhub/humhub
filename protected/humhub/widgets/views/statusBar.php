<?php

use humhub\widgets\VueComponent;

/* @var $this \humhub\components\View */

/**
 * The user-feedback bar is a Vue island (StatusBar.vue in the core component set).
 *
 * The `id` stays on the mount element: theme CSS and the acceptance test helpers
 * (`AcceptanceTester::seeSuccess()` and friends) address the bar as `#status-bar`.
 * The former `d-none` class is gone - visibility is component state now, and a
 * class on the mount element would keep the island invisible forever.
 */
?>
<?= VueComponent::widget([
    'name' => 'StatusBar',
    'options' => ['id' => 'status-bar', 'class' => 'clearfix'],
]) ?>
