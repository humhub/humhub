<?php

namespace humhub\tests\codeception\unit\components\icon;

use humhub\components\icon\LegacyIconMap;
use humhub\components\icon\TablerIconProvider;
use humhub\widgets\Icon;
use tests\codeception\_support\HumHubDbTestCase;

class LegacyIconMapTest extends HumHubDbTestCase
{
    public function testResolvesRenamedNames()
    {
        $this->assertEquals('settings', LegacyIconMap::resolve('cog'));
        $this->assertEquals('x', LegacyIconMap::resolve('times'));
        $this->assertEquals('star-filled', LegacyIconMap::resolve('star'));
        $this->assertEquals('star', LegacyIconMap::resolve('star-o'));
        $this->assertEquals('trash', LegacyIconMap::resolve('trash-o'));
    }

    public function testUnknownNameIsReturnedUnchanged()
    {
        $this->assertEquals('pencil', LegacyIconMap::resolve('pencil'));
        $this->assertEquals('no-such-icon', LegacyIconMap::resolve('no-such-icon'));
    }

    public function testNameWithoutCounterpartIsReturnedUnchanged()
    {
        $this->assertTrue(LegacyIconMap::isLegacyName('adn'));
        $this->assertEquals('adn', LegacyIconMap::resolve('adn'));
    }

    public function testEveryFontAwesomeNameHasAnEntry()
    {
        foreach (Icon::$names as $name) {
            $this->assertTrue(LegacyIconMap::isLegacyName($name), "Missing legacy map entry for '$name'");
        }
    }

    public function testEveryTargetExistsInThePackage()
    {
        foreach (LegacyIconMap::MAP as $name => $target) {
            if ($target !== null) {
                $this->assertTrue(TablerIconProvider::hasName($target), "'$name' maps to unknown Tabler icon '$target'");
            }
        }
    }
}
