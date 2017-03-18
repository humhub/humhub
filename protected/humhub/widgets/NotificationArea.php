<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * NotificationAddonWidget is used to add items like Notification, Mail, Timer to the main layout
 *
 * @since 0.5
 * @author Luke
 */
class NotificationArea extends BaseStack
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->addNotificationOverview();
    }

    /**
     * Adds notification overview widget, if not already exists to provide
     * backward compatiblity.
     */
    protected function addNotificationOverview()
    {
        $found = false;
        $notificationOverviewClass = 'humhub\modules\notification\widgets\Overview';
        foreach ($this->getWidgets() as $widget) {
            if ($widget[0] == $notificationOverviewClass) {
                $found = true;
            }
        }

        if (!$found) {
            $this->addWidget($notificationOverviewClass, [], ['sortOrder' => 10]);
        }
    }

}

?>
