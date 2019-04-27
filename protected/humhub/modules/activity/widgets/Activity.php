<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\widgets;

use humhub\modules\activity\components\ActivityWebRenderer;
use Yii;
use humhub\modules\content\widgets\WallEntry;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;

/**
 * ActivityWidget shows an activity.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity
 * @since 0.5
 */
class Activity extends WallEntry
{

    protected $themePath = 'modules/activity';

    /**
     * @var \humhub\modules\activity\models\Activity is the current activity object.
     */
    public $activity;

    /**
     * @var integer If the Widget is linked to a wall entry id
     */
    public $wallEntryId = 0;

    /**
     * Runs the Widget
     */
    public function run()
    {
        // The render logic is overwritten by models\Activity::getWallOut()
        return '';
    }

}
