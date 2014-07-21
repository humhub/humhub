<?php

/**
 * This module provides like support for Content and Content Addons
 * Each wall entry will get a Like Button and a overview of likes.
 *
 * @package humhub.modules_core.like
 * @since 0.5
 */
class TourModule extends HWebModule
{

    public $isCoreModule = true;


    /**
     * On Init of Dashboard Sidebar, add the widget
     *
     * @param type $event
     */
    public static function onTourWidgetInit($event)
    {
/*        if (HSetting::Get('active', 'breakingnews') && UserSetting::Get(Yii::app()->user->id, 'seen', 'breakingnews') != 1) {
            UserSetting::Set(Yii::app()->user->id, 'seen', true, 'breakingnews');
            $event->sender->addWidget('application.modules.breakingnews.widgets.BreakingNewsWidget', array(), array('sortOrder' => 1));
        }*/

          $event->sender->addWidget('application.modules_core.tour.widgets.TourWidget', array(), array());
    }

}
