<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets;

/**
 * BootstrapComponent is an abstract class used to define bootstrap based ui components and provides common
 * features as sizing, color, text and alignment configuration.
 *
 * This class follows the builder pattern for instantiation and configuration. By default this class provides the following
 * static initializers:
 *
 *  - none
 *  - primary
 *  - defaultType
 *  - info
 *  - warn
 *  - danger
 *
 * Example:
 *
 * ```
 * // Set only text
 * BootstrapComponent::instance('My Label')->right();
 *
 * // Component with primary color and text
 * BootstrapComponent::primary('My Label');
 * ```
 *
 *
 * @deprecated since 1.17
 * @package humhub\widgets
 */
abstract class BootstrapComponent extends bootstrap\BootstrapComponent
{
}
