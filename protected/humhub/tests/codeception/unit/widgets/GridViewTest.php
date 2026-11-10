<?php

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\GridView;
use tests\codeception\_support\HumHubDbTestCase;
use yii\data\ArrayDataProvider;

class GridViewTest extends HumHubDbTestCase
{
    /**
     * A header cell heads its column (WCAG 1.3.1)
     */
    public function testHeaderCellsHaveColumnScope()
    {
        $html = $this->renderGrid(['id', 'name']);

        $this->assertSame(2, substr_count($html, '<th scope="col"'));
    }

    public function testColumnKeepsItsOwnScope()
    {
        $html = $this->renderGrid(['id', ['attribute' => 'name', 'headerOptions' => ['scope' => 'colgroup']]]);

        $this->assertStringContainsString('<th scope="colgroup"', $html);
        $this->assertSame(1, substr_count($html, '<th scope="col"'));
    }

    private function renderGrid(array $columns): string
    {
        return GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [['id' => 1, 'name' => 'Alice']],
                'pagination' => false,
                'sort' => false,
            ]),
            'columns' => $columns,
            'filterUrl' => '/test', // no request in a unit test
        ]);
    }
}
