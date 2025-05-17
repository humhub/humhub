<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets\bootstrap;

/**
 *  A HumHub enhanced version of native bootstrap NavBar
 *  by providing view-based navbar.
 *
 *  View-based navbar usage:
 *
 *  ```
 * <?php NavBar::begin(['brandLabel' => 'NavBar Test']); ?>
 * <?= Nav::widget([
 *     'items' => [
 *         ['label' => 'Home', 'url' => ['/site/index']],
 *         ['label' => 'About', 'url' => ['/site/about']],
 *     ],
 *     'options' => ['class' => 'navbar-nav'],
 * ]); ?>
 * <?php NavBar::end(); ?>
 *  ```
 *
 * @since 1.8
 */
class NavBar extends \yii\bootstrap5\NavBar
{
}
