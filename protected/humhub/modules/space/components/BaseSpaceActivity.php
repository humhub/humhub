<?php

namespace humhub\modules\space\components;

use humhub\components\message\MessageParam;
use humhub\helpers\Html;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;
use Yii;
use yii\base\InvalidValueException;

/**
 * Base class of an activity about a space, see {@see BaseActivity}. Adds the built-in message
 * parameter `spaceName`.
 *
 * @api
 */
abstract class BaseSpaceActivity extends BaseActivity
{
    /**
     * @api
     */
    protected Space $space;

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($record, $config);

        if (!$record->contentContainer?->polymorphicRelation instanceof Space) {
            throw new InvalidValueException('Space activity content container must implement space');
        }

        $this->space = $record->contentContainer->polymorphicRelation;
    }

    /**
     * Whether the activity is rendered inside a content container, e.g. "joined this Space" rather
     * than "joined the Space {spaceName}".
     *
     * @api
     */
    protected function inSpaceContext(): bool
    {
        return Yii::$app->controller instanceof ContentContainerController
            && Yii::$app->controller->contentContainer !== null;
    }

    /**
     * Adds `spaceName`, the name of the space (in `<strong>` for HTML).
     *
     * @inheritdoc
     * @internal
     */
    protected function getBuiltInMessageParams(): array
    {
        return array_merge(parent::getBuiltInMessageParams(), [
            'spaceName' => MessageParam::html(Html::strong(Html::encode($this->space->name)), $this->space->name),
        ]);
    }
}
