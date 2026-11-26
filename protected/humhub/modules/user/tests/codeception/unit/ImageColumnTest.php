<?php

namespace tests\codeception\unit;

use humhub\modules\user\grid\ImageColumn;
use humhub\modules\user\models\User;
use humhub\widgets\GridView;
use tests\codeception\_support\HumHubDbTestCase;
use yii\data\ArrayDataProvider;

class ImageColumnTest extends HumHubDbTestCase
{
    public function testHeaderNamesTheColumnForScreenReaders()
    {
        $html = $this->renderGrid(['class' => ImageColumn::class]);

        $this->assertStringContainsString('<th scope="col"><span class="visually-hidden">Image</span></th>', $html);
    }

    public function testOwnLabelIsKept()
    {
        $html = $this->renderGrid(['class' => ImageColumn::class, 'label' => 'Author']);

        $this->assertStringContainsString('<th scope="col">Author</th>', $html);
        $this->assertStringNotContainsString('visually-hidden', $html);
    }

    private function renderGrid(array $column): string
    {
        return GridView::widget([
            'dataProvider' => new ArrayDataProvider(['allModels' => [User::findOne(['id' => 1])]]),
            'filterUrl' => '/',
            'columns' => [$column],
        ]);
    }
}
