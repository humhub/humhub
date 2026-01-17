<?php

namespace humhub\modules\space\components;

use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;
use Yii;
use yii\base\InvalidValueException;

abstract class BaseSpaceActivity extends BaseActivity
{

    protected Space $space;

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($record, $config);

        if (!$record->contentContainer->polymorphicRelation instanceof Space) {
            throw new InvalidValueException('Space activity content container must implement space');
        }

        $this->space = $record->contentContainer->polymorphicRelation;
    }

    protected function inSpaceContext(): bool
    {
        return Yii::$app->controller instanceof ContentContainerController;
    }
}
