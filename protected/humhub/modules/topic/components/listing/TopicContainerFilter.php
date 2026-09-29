<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\components\listing;

use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListFilter;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\topic\components\TopicList;
use Yii;

/**
 * `containerId`: the topics of one content container (a space or a profile) and the global ones
 * — what a topic picker inside that container offers — a context filter of the
 * {@see TopicList}, without UI.
 *
 * It authorizes itself while parsing: the container must be one whose topics the list shows the
 * caller ({@see TopicList::readableContainers()}: a space the space list shows them as a
 * picker, or their own profile). Another one, an id of no container and anything but an id is
 * "Container not found.", so a caller cannot probe which containers exist. The value is the
 * content container id.
 *
 * @since 1.20
 */
class TopicContainerFilter extends ListFilter
{
    public function __construct(private readonly string $key = 'containerId')
    {
    }

    /**
     * @inheritdoc
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function parse(array $raw, ListContext $context): FilterValue
    {
        $value = $raw[$this->key] ?? null;

        if ($value === null || $value === '') {
            return FilterValue::absent();
        }

        $id = is_int($value) || is_string($value)
            ? filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;

        $readable = $id !== false && ContentContainer::find()
            ->where(['contentcontainer.id' => $id])
            ->andWhere(TopicList::readableContainers('contentcontainer.id', $context->user))
            ->exists();

        if (!$readable) {
            return $this->invalid(Yii::t('TopicModule.base', 'Container not found.'));
        }

        return FilterValue::of($id);
    }

    /**
     * @inheritdoc
     */
    public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void
    {
        /** @var QueryListBuilder $list */
        $list->query()->andWhere(['or',
            ['content_tag.contentcontainer_id' => $value->value],
            ['content_tag.contentcontainer_id' => null],
        ]);
    }
}
