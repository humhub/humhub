<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * GlobalModal is the standard modal which can be used in every layout.
 * This widget is automatically added to the page via the LayoutAddons.
 *
 * @see LayoutAddons
 * @author Luke
 * @since 1.1
 */
class GlobalModal extends Modal
{
    /**
     * @var string this id need to js scripts
     */
    public $id = 'globalModal';

    /**
     * @inheritdoc
     * It's false because it's often used for serious work, for example html forms,
     * accidental closing of which can lead to loss of user data.
     */
    public $backdrop = false;
}
