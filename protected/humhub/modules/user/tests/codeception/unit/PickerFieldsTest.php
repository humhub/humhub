<?php

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Chooser;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\GroupPicker;
use humhub\modules\user\widgets\UserPickerField;
use tests\codeception\_support\HumHubDbTestCase;

class PickerFieldsTestRoutedUserPicker extends UserPickerField
{
    public $defaultRoute = '/custom/user-search';
}

class PickerFieldsTestUrlSpacePicker extends SpacePickerField
{
    public function getUrl()
    {
        return '/custom/space-search';
    }
}

class PickerFieldsTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    private function optionImage(string $html, string $value): array
    {
        // Yii renders array attributes as single-quoted Json::htmlEncode() output
        $this->assertSame(1, preg_match('/<option value="' . preg_quote($value, '/') . '"[^>]*data-image=\'([^\']*)\'/', $html, $match), $html);
        return json_decode($match[1], true);
    }

    public function testUserPickerUsesTheUserSourceAndUserImages()
    {
        $this->becomeUser('Admin');
        $user = User::findOne(['username' => 'User1']);

        $html = UserPickerField::widget(['name' => 'users', 'selection' => [$user]]);

        $this->assertStringContainsString('data-picker-source="user"', $html);
        $this->assertStringNotContainsString('spaceId', $html, 'no space restriction, as the legacy search');
        $this->assertStringContainsString('data-picker-item-key="guid"', $html);
        $image = $this->optionImage($html, $user->guid);
        $this->assertSame('user', $image['type']);
        $this->assertSame($user->guid, $image['guid']);
        $this->assertNotEmpty($image['imageUrl']);
    }

    public function testUserPickerWithOwnUrlStaysLegacy()
    {
        $this->becomeUser('Admin');
        $html = UserPickerField::widget(['name' => 'users', 'url' => '/custom/search']);

        $this->assertStringContainsString('data-picker-url="/custom/search"', $html);
        $this->assertStringNotContainsString('data-picker-source', $html);
    }

    public function testCustomizedSubclassesStayLegacy()
    {
        $this->becomeUser('Admin');

        $html = PickerFieldsTestRoutedUserPicker::widget(['name' => 'users']);
        $this->assertMatchesRegularExpression('/data-picker-url="[^"]*custom%2Fuser-search/', $html);
        $this->assertStringNotContainsString('data-picker-source', $html);

        $html = PickerFieldsTestUrlSpacePicker::widget(['name' => 'spaces']);
        $this->assertStringContainsString('data-picker-url="/custom/space-search"', $html);
        $this->assertStringNotContainsString('data-picker-source', $html);

        $this->assertStringContainsString('data-picker-source="space"', SpacePickerField::widget(['name' => 'spaces']));
    }

    public function testSelfSelectUsesThePickerAction()
    {
        $this->becomeUser('Admin');
        $html = UserPickerField::widget(['name' => 'users', 'id' => 'self-test', 'selfSelect' => true]);

        $this->assertStringContainsString('data-action-click="ui.picker.selectSelf"', $html);
        $this->assertStringContainsString('data-action-click-target="#self-test"', $html);
    }

    public function testSpacePickerUsesTheSpaceSourceAndSpaceImages()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 1]);

        $html = SpacePickerField::widget(['name' => 'spaces', 'selection' => [$space]]);

        $this->assertStringContainsString('data-picker-source="space"', $html);
        $image = $this->optionImage($html, $space->guid);
        $this->assertSame(['type' => 'space', 'id' => $space->id, 'name' => $space->name, 'color' => $space->color], array_intersect_key($image, array_flip(['type', 'id', 'name', 'color'])));
        $this->assertStringNotContainsString('<img', html_entity_decode($html));
    }

    public function testChooserSpaceResultHasNoHtmlImage()
    {
        $space = Space::findOne(['id' => 1]);
        $result = Chooser::getSpaceResult($space, false);

        $this->assertSame('space', $result['image']['type']);
        $this->assertSame($space->name, $result['text']);
        $this->assertSame($space->name, $result['title']);
    }

    public function testGroupPickerShowsTheGroupIcon()
    {
        $this->becomeUser('Admin');
        $html = GroupPicker::widget(['name' => 'groups', 'items' => [5 => 'Sales'], 'selection' => [5]]);

        $this->assertMatchesRegularExpression('/<option value="5" selected[^>]*data-image="users-group"/', $html);
        $this->assertStringNotContainsString('data-tags', $html);
    }
}
