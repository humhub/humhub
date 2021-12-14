<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * Interface TabbedForm
 *
 * @property-read array $tabs
 *
 * @since 1.11.0
 */
interface TabbedForm
{
    /**
     * Initialize tabs for the Form
     *
     * Example of the result:
     * [
     *     [
     *         'label' => 'First tab',
     *         'view' => 'first-tab-view',
     *         'linkOptions' => ['class' => 'first-tab-style'],
     *         'fields' => ['name', 'email', 'password'], // Define all fields from the tab which may have errors after submit in order to make this tab active
     *     ],
     *     [
     *         'label' => 'Second tab',
     *         'view' => 'second-tab-view',
     *         'linkOptions' => ['class' => 'second-tab-style'],
     *         'fields' => ['description', 'tags'],
     *     ],
     * ]
     *
     * @return array
     */
    public function getTabs(): array;
}