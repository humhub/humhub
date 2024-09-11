<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets;

/**
 * Extends `\yii\bootstrap\Tabs` by providing providing view based tab items.
 *
 * View based tabs usage:
 *
 * <?=
 * Tabs::widget([
 *  'viewPath' => '@myModule/views/common',
 *  'params' => $_params_,
 *  'items' => [
 *    [
 *      'label' => 'One',
 *      'view' => 'example',
 *      'active' => true
 *    ],
 *    [
 *      'label' => 'Two',
 *      'view' => '@myModule/views/example',
 *      'params' => ['model' => new SomeModel()]
 *    ],
 *  ]
 * ]);
 * ?>
 *
 * @deprecated since 1.17
 * @since 1.2.2
 * @see \yii\bootstrap\Tabs
 * @package humhub\widgets
 */
class Tabs extends \humhub\widgets\bootstrap\Tabs
{
}
