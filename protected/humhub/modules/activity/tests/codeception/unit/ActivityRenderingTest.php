<?php

namespace humhub\modules\activity\tests\codeception\unit;

use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\tests\codeception\activities\TestLegacyContentActivity;
use humhub\modules\activity\tests\codeception\activities\TestLegacyParamsActivity;
use humhub\modules\activity\tests\codeception\activities\TestMessageParamsActivity;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;

class ActivityRenderingTest extends HumHubDbTestCase
{
    public function testMessageParamsAreRenderedPerOutput()
    {
        $this->becomeUser('User2');
        $activity = ActivityManager::dispatch(TestMessageParamsActivity::class, Space::findOne(['id' => 1]));

        $html = '<strong>Sara Tester</strong> saw <strong>A &lt;b&gt;&amp;&lt;/b&gt; &quot;title&quot;</strong>'
            . ' by <strong>Admin Tester</strong> (x &lt;…, a &amp; b, 3)';

        $this->assertSame($html, $activity->asWeb());
        $this->assertSame($html, $activity->asMailHtml());
        $this->assertSame('Sara Tester saw “A <b>&</b> "title"” by Admin Tester (x <…, a & b, 3)', $activity->asMailText());
    }

    public function testDeprecatedParamMethodsStillWork()
    {
        $this->becomeUser('User2');
        $activity = ActivityManager::dispatch(TestLegacyParamsActivity::class, Space::findOne(['id' => 1]));

        // The override of getMessageParamsWeb() wins on the web and, as in 1.19, in the HTML mail;
        // a parameter added in getMessageParamsMailText() alone reaches the web too, as in 1.19.
        $this->assertSame('<em>Sara Tester</em>|<i>web</i>|from text', $activity->asWeb());
        $this->assertSame('<em>Sara Tester</em>|<i>web</i>|from text', $activity->asMailHtml());
        $this->assertSame('Sara Tester|text|from text', $activity->asMailText());
    }

    public function testDeprecatedParamMethodsOfContentActivityStillWork()
    {
        $this->becomeUser('User2');
        $post = new Post(Space::findOne(['id' => 1]), ['message' => 'Tom & Jerry']);
        $this->assertTrue($post->save());

        $activity = ActivityManager::dispatch(TestLegacyContentActivity::class, $post);

        $this->assertSame('<strong>Sara Tester</strong> post "Tom &amp; Jerry" [web]', $activity->asWeb());
        $this->assertSame('<strong>Sara Tester</strong> <strong>post "Tom &amp; Jerry"</strong> [html]', $activity->asMailHtml());
        $this->assertSame('Sara Tester post "Tom &amp; Jerry" [text]', $activity->asMailText());
    }
}
