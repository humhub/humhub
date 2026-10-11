<?php

namespace humhub\modules\space\components;

use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\space\models\Space;
use yii\base\InvalidValueException;

/**
 * Base class of an activity about a space, see {@see BaseActivity}.
 *
 * @deprecated since 1.20, extend {@see BaseActivity}: every activity of a space has
 * {@see BaseActivity::getSpace()}, {@see BaseActivity::inSpaceContext()} and the message parameter
 * `spaceName`
 */
abstract class BaseSpaceActivity extends BaseActivity
{
    /**
     * @deprecated since 1.20, use {@see BaseActivity::getSpace()}
     */
    protected Space $space;

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($record, $config);

        $space = $this->getSpace();
        if ($space === null) {
            throw new InvalidValueException('Space activity content container must implement space');
        }

        $this->space = $space;
    }
}
