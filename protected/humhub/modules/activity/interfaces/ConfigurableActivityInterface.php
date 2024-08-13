<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\interfaces;

/**
 * Interface for configurable activities
 *
 * All activities which implements this interface can be switched of by the user
 * or admin in e-mail summaries.
 *
 * @version 1.2
 * @author Luke
 */
interface ConfigurableActivityInterface
{

    /**
     * Returns the title of the activity, which is displayed on the configuration page.
     *
     * @return string the title of the activity
     */
    public function getTitle();

    /**
     * Returns the description of the activity, which is displayed on the configuration page.
     *
     * @return string the description of the activity
     */
    public function getDescription();

}
