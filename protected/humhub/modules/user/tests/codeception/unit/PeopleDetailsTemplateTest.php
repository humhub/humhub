<?php

namespace tests\codeception\unit;

use humhub\modules\user\components\PeopleDetailsTemplate;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\serializers\UserSerializer;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class PeopleDetailsTemplateTest extends HumHubDbTestCase
{
    protected function _before()
    {
        parent::_before();
        Yii::$app->runtimeCache->flush();
    }

    protected function _after()
    {
        Yii::$app->getModule('user')->peopleCardDetailsTemplate = null;
        Yii::$app->settings->delete('people.detail1');
        parent::_after();
    }

    public function testInactiveWithoutTemplate()
    {
        $this->setTemplate("  \n ");

        $this->assertFalse(PeopleDetailsTemplate::isActive());
        $this->assertSame([], PeopleDetailsTemplate::render(User::findOne(['id' => 1])));
    }

    public function testRenderLines()
    {
        $this->setTemplate("{{ firstname }} {{ lastname }}\n\n{% if mobile %}{{ mobile }}{% endif %}\n<small>{{ unknown }}Room</small>");

        $this->assertSame(
            ['Admin Tester', '<small>Room</small>'],
            PeopleDetailsTemplate::render(User::findOne(['id' => 1])),
        );
    }

    public function testValuesAreEncodedAndRawValuesAvailable()
    {
        $user = User::findOne(['id' => 1]);
        $user->profile->firstname = '<b>A&B</b>';
        $user->profile->url = 'https://example.com/?a=1&b=2';

        $this->setTemplate('{{ firstname }}' . "\n" . '{{ url }}' . "\n" . '<a href="https://nav.example/?q={{ raw.firstname|url_encode }}">x</a>');

        $this->assertSame([
            '&lt;b&gt;A&amp;B&lt;/b&gt;',
            'https://example.com/?a=1&amp;b=2',
            '<a href="https://nav.example/?q=%3Cb%3EA%26B%3C%2Fb%3E">x</a>',
        ], PeopleDetailsTemplate::render($user));
    }

    public function testHiddenFieldsAreNotAvailable()
    {
        ProfileField::updateAll(['visible' => 0], ['internal_name' => 'lastname']);
        $this->setTemplate('{{ firstname }}|{{ lastname }}|{{ raw.lastname }}');

        $this->assertSame(['Admin||'], PeopleDetailsTemplate::render(User::findOne(['id' => 1])));
    }

    public function testSerializerUsesTemplateInsteadOfSelectedFields()
    {
        Yii::$app->settings->set('people.detail1', 'firstname');
        $user = User::findOne(['id' => 1]);

        $entry = UserSerializer::list($user);
        $this->assertSame(['Admin'], $entry['details']);
        $this->assertNull($entry['detailsHtml']);

        $user->profile->lastname = 'A&B';
        $this->setTemplate("<a href=\"https://nav.example/?q={{ raw.lastname|url_encode }}\">{{ lastname }}</a>\n{{ firstname }}");

        $entry = UserSerializer::list($user);
        $this->assertSame(['A&B', 'Admin'], $entry['details']);
        $this->assertSame(['<a href="https://nav.example/?q=A%26B">A&amp;B</a>', 'Admin'], $entry['detailsHtml']);
    }

    private function setTemplate(?string $template): void
    {
        Yii::$app->getModule('user')->peopleCardDetailsTemplate = $template;
    }
}
