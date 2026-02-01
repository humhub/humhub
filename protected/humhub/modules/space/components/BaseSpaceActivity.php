<?php

namespace humhub\modules\space\components;

use humhub\helpers\Html;
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
        return Yii::$app->controller instanceof ContentContainerController &&
            Yii::$app->controller->contentContainer !== null;
    }

    protected function getMessageParamsText(): array
    {
        return array_merge(parent::getMessageParamsText(), [
            'spaceName' => $this->space->name,
        ]);
    }

    protected function getMessageParamsHtml(): array
    {
        return array_merge(parent::getMessageParamsHtml(), [
            'spaceName' => Html::strong(Html::encode($this->space->name)),
        ]);
    }
}
