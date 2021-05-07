<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\SpacesFilters;
use Yii;
use yii\data\Pagination;

/**
 * SpacesQuery is used to query Space records on the Spaces page.
 *
 * @author luke
 */
class SpacesQuery extends ActiveQuerySpace
{

    /**
     * @var Pagination
     */
    public $pagination;

    /**
     * @var int
     */
    public $pageSize = 10;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct(Space::class, $config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->visible();

        $this->filterByKeyword();

        $this->order();

        $this->paginate();
    }

    public function filterByKeyword()
    {
        $keyword = Yii::$app->request->get('keyword', '');

        return $this->search($keyword);
    }

    public function order()
    {
        switch (SpacesFilters::getValue('sort')) {
            case 'name':
                $this->addOrderBy('name');
                break;

            case 'newer':
                $this->addOrderBy('created_at DESC');
                break;

            case 'older':
                $this->addOrderBy('created_at');
                break;
        }

        return $this;
    }

    public function paginate()
    {
        $countQuery = clone $this;
        $this->pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $this->pageSize]);

        return $this->offset($this->pagination->offset)->limit($this->pagination->limit);
    }

}
