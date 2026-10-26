<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\controllers\api;

use humhub\components\api\DataTableController;
use humhub\components\table\DataTable;
use humhub\modules\space\models\Space;
use humhub\modules\topic\components\TopicTable;
use humhub\modules\user\models\User;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * The topics to manage as rows of a topic table (`TopicTable`): `GET admin/topic` the global
 * topics, `GET space/<id>/topic` a space's, `GET user/<id>/topic` a user's — one page of them
 * (the list's search and sorts as query parameters) in the paginated envelope.
 *
 * For those who may manage them ({@see TopicTable::canAccess()}). Topics are edited and deleted
 * through the pages' own routes, which the rows' actions name.
 *
 * @since 1.20
 */
class TopicTableController extends DataTableController
{
    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        $request = Yii::$app->request;
        $container = null;
        if ($request->get('spaceId') !== null) {
            $container = Space::findOne(['id' => (int)$request->get('spaceId')]);
        } elseif ($request->get('userId') !== null) {
            $container = User::findOne(['id' => (int)$request->get('userId')]);
        }

        if ($container === null && ($request->get('spaceId') !== null || $request->get('userId') !== null)) {
            throw new NotFoundHttpException();
        }

        return Yii::createObject(['class' => TopicTable::class, 'container' => $container]);
    }

    /**
     * @inheritdoc
     * Without the space's or user's id of the route, which Yii puts among the query parameters.
     */
    protected function listParams(): array
    {
        return array_diff_key(parent::listParams(), ['spaceId' => true, 'userId' => true]);
    }
}
