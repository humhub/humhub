<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui;


/**
 * Event Handling Callbacks
 *
 * @package humhub\modules\ui
 */
class Events
{
    public static function onConsoleApplicationInit($event)
    {
        $application = $event->sender;
        $application->controllerMap['theme'] = commands\ThemeController::class;
    }

}
