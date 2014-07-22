<?php

/**
 * Will injected to dashboard sidebar to show a breaking news
 *
 * @package humhub.modules.breakingnews.widgets
 * @since 0.5
 * @author Luke
 */
class TourWidget extends HWidget {

    /**
     * Executes the widgets
     */
    public function run() {

        // check if tour is activated for new users
        if (HSetting::Get('enable', 'tour') == 1) {

            // save in variable, if this user seen the tour already
            $tourSeen = Yii::app()->user->getModel()->getSetting("seen", "tour");

            // if not...
            if ($tourSeen != "true") {

                // ...load resources
                $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
                Yii::app()->clientScript->registerScriptFile($assetPrefix . '/bootstrap-tour.min.js');
                Yii::app()->clientScript->registerCssFile($assetPrefix . '/bootstrap-tour.min.css');

                // ... render widget view
                $this->render('index', array());
            }
        }


    }

}

?>
