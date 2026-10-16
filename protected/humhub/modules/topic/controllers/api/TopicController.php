<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\controllers\api;

use humhub\components\api\BaseController;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListValidationException;
use humhub\modules\topic\components\TopicList;
use humhub\modules\topic\serializers\TopicSerializer;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * The topics of the HTTP API (see `docs/develop/concept-api.md`). So far only the topic picker
 * ({@see self::actionPicker()}): what a topic picker suggests and how it resolves the topic ids
 * of a page URL (the filter bar's `topic` filter). `GET /api/v2/topic` stays free for a general
 * topic list.
 *
 * Visibility is never optional: every query runs through {@see TopicList}, so a topic of a
 * container the caller may not read cannot appear whatever the parameters. There is no guest
 * access, as for the user picker: topic filters are not offered to guests.
 *
 * @since 1.20
 */
class TopicController extends BaseController
{
    /**
     * @var string[] the parameters of {@see self::actionPicker()}
     */
    public const PICKER_PARAMS = ['q', 'ids', 'containerId', 'page', 'pageSize'];

    /**
     * @var int the page size of {@see self::actionPicker()}, its largest page and the most `ids`
     * it takes — a picker shows a handful of suggestions, and resolves every id of a topic
     * filter's value on one page
     */
    public const PICKER_PAGE_SIZE = TopicList::MAX_IDS;

    /**
     * @inheritdoc
     */
    protected bool $allowSessionAuth = true;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'picker' => ['GET', 'HEAD'],
                ],
            ],
        ]);
    }

    /**
     * The topics a topic picker suggests — those the caller may see ({@see TopicList}: global
     * topics, those of the spaces the space list shows them as a picker and those of their own
     * profile), by name. The picker rules of the user picker
     * ({@see \humhub\modules\user\controllers\api\UserController::actionPicker()},
     * {@see BaseController::pickerErrors()}) apply.
     *
     * Parameters, exactly: `q` (a part of the name), `ids` (repeated or comma-separated topic
     * ids — the values of a page URL to resolve), `containerId` (a content container id: its
     * topics and the global ones; a container the caller may not read is `422` "Container not
     * found.") and `page`/`pageSize`. Any other parameter, `purpose` included, answers `422`
     * "Unknown parameter.".
     *
     * A non-empty `q`, `ids` or `containerId` is required (else `422` under `q`), as a picker
     * suggests from a search, ids or a container. At most {@see self::PICKER_PAGE_SIZE} topics
     * per page (the default; a larger `pageSize` is capped) and as many `ids` (else `422`).
     * Answered with the short shape ({@see TopicSerializer::short()}), the containers of the
     * page loaded at once.
     */
    public function actionPicker()
    {
        $params = $this->listParams();
        $errors = $this->pickerErrors(
            $params,
            self::PICKER_PARAMS,
            ['q', 'ids', 'containerId'],
            Yii::t('TopicModule.base', 'Enter a search, or name topics or a container.'),
            self::PICKER_PAGE_SIZE,
        );

        if ($errors !== []) {
            return $this->validationErrors($errors);
        }

        try {
            $topics = (new TopicList())->build($params, ListContext::forCurrentUser())->query();
        } catch (ListValidationException $e) {
            return $this->validationErrors($e->errors);
        }

        $pagination = $this->handlePagination($topics, self::PICKER_PAGE_SIZE, self::PICKER_PAGE_SIZE);

        return $this->returnPagination($pagination, TopicSerializer::batch($topics->all()));
    }
}
