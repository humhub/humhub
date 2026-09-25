<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\space;

use humhub\modules\space\models\Space;
use humhub\modules\space\serializers\MembershipSerializer;
use humhub\modules\space\serializers\SpaceSerializer;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The batch paths of the space serializers answer a page with a fixed number of queries,
 * however many spaces it has.
 *
 * @since 1.20
 */
class SpaceStatesBatchTest extends HumHubDbTestCase
{
    /**
     * @return int the database queries `$call` ran
     */
    private function queries(callable $call): int
    {
        // The server's own statement counter of this session - independent of logging setup.
        $count = fn() => (int)Yii::$app->db->createCommand("SHOW SESSION STATUS LIKE 'Questions'")->queryOne()['Value'];

        $before = $count();
        $call();

        // The second SHOW counts itself.
        return $count() - $before - 1;
    }

    private function spaces(array $ids): array
    {
        return Space::find()->where(['id' => $ids])->all();
    }

    private function warmUp(): void
    {
        // Settings, the identity and other per-request lookups are loaded once, whatever the
        // batch size - load them before measuring.
        MembershipSerializer::states($this->spaces([1, 2, 3, 4, 5]));
        SpaceSerializer::counts($this->spaces([1, 2, 3, 4, 5]));
        Yii::$app->runtimeCache->flush();
    }

    public function testMembershipStatesDoNotQueryPerSpace()
    {
        $this->becomeUser('User2');
        $this->warmUp();

        // Space 2 is one User2 is not a member of, which makes `canJoin()` load the user once
        // (runtime-cached) - so the single-space batch has that one-off query as well.
        $one = $this->spaces([2]);
        $many = $this->spaces([1, 2, 3, 4]);

        $forOne = $this->queries(fn() => MembershipSerializer::states($one));
        Yii::$app->runtimeCache->flush();
        $forMany = $this->queries(fn() => MembershipSerializer::states($many));

        $this->assertGreaterThan(0, $forOne, 'the measurement sees queries');
        $this->assertSame($forOne, $forMany);

        // Without the batch, every space asks for its own membership and follow.
        Yii::$app->runtimeCache->flush();
        $perSpace = $this->queries(function () use ($many) {
            foreach ($many as $space) {
                MembershipSerializer::state($space);
            }
        });
        $this->assertGreaterThan($forMany, $perSpace);
    }

    public function testMembershipStatesMatchTheSingleState()
    {
        $this->becomeUser('User2');
        $spaces = $this->spaces([1, 3, 4]);
        $states = MembershipSerializer::states($spaces);
        Yii::$app->runtimeCache->flush();

        foreach ($this->spaces([1, 3, 4]) as $space) {
            $this->assertSame(MembershipSerializer::state($space), $states[$space->id]);
        }
    }

    public function testCountsDoNotQueryPerSpace()
    {
        $this->becomeUser('User2');
        $this->warmUp();

        $one = $this->spaces([1]);
        $many = $this->spaces([1, 2, 3, 4]);

        $this->assertSame(
            $this->queries(fn() => SpaceSerializer::counts($one)),
            $this->queries(fn() => SpaceSerializer::counts($many)),
        );
    }
}
