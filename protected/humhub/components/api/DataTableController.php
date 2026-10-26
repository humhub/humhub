<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\api;

use humhub\components\listing\ArrayListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListValidationException;
use humhub\components\listing\QueryListBuilder;
use humhub\components\table\DataTable;
use Yii;
use yii\data\Pagination;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The endpoint of a data table (`DataTable.vue`, see docs/ui/data-table.md): `actionIndex()`
 * answers one page of the {@see DataTable} rows of its list, the list's filters and sort as
 * query parameters (an invalid value or an unknown parameter answers `422 {errors}`), in the
 * paginated envelope `{results, total, page, pageSize, pages}`. A database list
 * ({@see QueryListBuilder}) is paged in the query, an array list ({@see ArrayListBuilder}) after
 * building it.
 *
 * Only for those who may see the table ({@see DataTable::canAccess()}); the table's page may be
 * used with the session cookie.
 *
 * @since 1.20
 */
abstract class DataTableController extends BaseController
{
    public const DEFAULT_PAGE_SIZE = 25;
    public const MAX_PAGE_SIZE = 100;
    public const MAX_BULK_IDS = 1000;

    /**
     * @inheritdoc
     */
    protected bool $allowSessionAuth = true;

    private ?DataTable $table = null;

    /**
     * The table, e.g. created through the DI container so a module may replace it.
     */
    abstract protected function createTable(): DataTable;

    public function getTable(): DataTable
    {
        return $this->table ??= $this->createTable();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET', 'HEAD'],
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!$this->getTable()->canAccess()) {
            throw new ForbiddenHttpException();
        }

        return true;
    }

    /**
     * Applies the row action `$actionId` (one with a `bulkUrl`) to the selected rows: `ids` (at
     * most {@see self::MAX_BULK_IDS}, found by `$findByIds`) or `all`, the rows of the list's
     * filters (the query parameters, as the index takes them). Each row offering the action is
     * changed by `$apply`, the others are skipped. Answers `{done, skipped, message}`, or the
     * validation errors.
     *
     * @param callable $findByIds `fn(int[] $ids): ActiveQuery` the selected rows' records
     * @param callable $apply `fn($model): bool` changes a record, false if it could not
     * @param callable|null $message `fn(int $done, int $skipped): string`
     * @since 1.20
     */
    protected function bulkAction(string $actionId, callable $findByIds, callable $apply, ?callable $message = null): array
    {
        $table = $this->getTable();
        $rowAction = $table->getAction($actionId);
        if ($rowAction === null || $rowAction->bulkUrl === null) {
            throw new NotFoundHttpException();
        }

        $request = Yii::$app->request;
        if (in_array($request->getBodyParam('all'), [true, 1, '1', 'true'], true)) {
            try {
                $query = $table->getList()->build($this->listParams(), ListContext::forCurrentUser())->query();
            } catch (ListValidationException $e) {
                return $this->validationErrors($e->errors);
            }
        } else {
            $ids = $request->getBodyParam('ids');
            if (!is_array($ids) || $ids === [] || count($ids) > self::MAX_BULK_IDS
                || array_filter($ids, static fn($id) => !is_numeric($id)) !== []) {
                return $this->validationErrors(['ids' => [Yii::t('base', 'Invalid value.')]]);
            }
            $query = $findByIds(array_map('intval', $ids));
        }

        $done = 0;
        $skipped = 0;
        foreach ($query->all() as $model) {
            if (!$rowAction->isVisibleFor($model)) {
                $skipped++;
                continue;
            }
            $apply($model) ? $done++ : $skipped++;
        }

        $message ??= static function (int $done, int $skipped): string {
            $text = Yii::t('base', '{count, plural, =1{1 entry was changed} other{# entries were changed}}.', ['count' => $done]);
            if ($skipped > 0) {
                $text .= ' ' . Yii::t('base', '{count, plural, =1{1 entry was left out} other{# entries were left out}}.', ['count' => $skipped]);
            }
            return $text;
        };

        return ['done' => $done, 'skipped' => $skipped, 'message' => $message($done, $skipped)];
    }

    /**
     * One page of the table's rows.
     */
    public function actionIndex()
    {
        $table = $this->getTable();

        try {
            $builder = $table->getList()->build($this->listParams(), ListContext::forCurrentUser());
        } catch (ListValidationException $e) {
            return $this->validationErrors($e->errors);
        }

        if ($builder instanceof QueryListBuilder) {
            $query = $builder->query();
            $pagination = $this->handlePagination($query, self::DEFAULT_PAGE_SIZE, self::MAX_PAGE_SIZE);

            return $this->returnPagination($pagination, array_map($table->row(...), $query->all()));
        }

        /** @var ArrayListBuilder $builder */
        $items = array_values($builder->items());
        $request = Yii::$app->request;
        $pagination = new Pagination(['totalCount' => count($items)]);
        $pagination->setPageSize(max(1, min((int)$request->get('pageSize', self::DEFAULT_PAGE_SIZE), self::MAX_PAGE_SIZE)));
        $pagination->setPage(max(1, (int)$request->get('page', 1)) - 1);

        return $this->returnPagination(
            $pagination,
            array_map($table->row(...), array_slice($items, $pagination->offset, $pagination->limit)),
        );
    }
}
