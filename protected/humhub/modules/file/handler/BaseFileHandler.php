<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

use humhub\modules\file\models\File;
use yii\base\Component;

/**
 * BaseFileHandler
 *
 * @since 1.2
 * @author Luke
 */
abstract class BaseFileHandler extends Component
{
    /**
     * Output list position
     */
    public const POSITION_TOP = '1';
    public const POSITION_STANDARD = '5';

    /**
     * @var int the position of the file handler
     */
    public $position = self::POSITION_STANDARD;

    /**
     * @var File the file
     */
    public $file;

    /**
     * The file handler link
     *
     * @return array the HTML attributes of the button.
     * @see \humhub\modules\file\widgets\FileHandlerButtonDropdown
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    abstract public function getLinkAttributes();
}
