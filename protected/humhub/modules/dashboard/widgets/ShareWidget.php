<?php

namespace humhub\modules\dashboard\widgets;


/**
 * Shows newly registered members as sidebar widget on the dashboard
 *
 * @package humhub.modules_core.directory.widgets
 * @since 0.11
 * @author Andreas Strobel
 */
class ShareWidget extends \humhub\components\Widget
{

    /**
     * Execute widget
     */
    public function run()
    {
        return $this->render('share');
    }

}

?>
