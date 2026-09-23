<?php

namespace humhub\tests\codeception\unit\components\icon;

use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\FileHelper;

/**
 * Font Awesome 4 markup must not come back into the core: icons are rendered through `Icon::get()`
 * with Tabler names, JavaScript templates write `ti ti-<name>`.
 */
class NoLegacyIconMarkupTest extends HumHubDbTestCase
{
    private const ALLOWED = [
        'components/icon/FontAwesomeIconProvider.php',
        'components/icon/LegacyIconMap.php',
        'tests/codeception/unit/components/icon/',
        'tests/codeception/unit/widgets/IconPickerTest.php',
        'resources/build/',
        'resources/js/humhub-app.js',
        'resources/js/humhub-bundle.js',
    ];

    public function testNoFontAwesomeMarkupInCore()
    {
        $base = Yii::getAlias('@humhub');
        $files = FileHelper::findFiles($base, [
            'only' => ['*.php', '*.js'],
            'except' => ['vendor/', 'messages/', 'node_modules/', '*.min.js'],
        ]);

        $hits = [];
        foreach ($files as $file) {
            $relative = substr($file, strlen($base) + 1);
            foreach (self::ALLOWED as $allowed) {
                if (str_starts_with($relative, $allowed)) {
                    continue 2;
                }
            }
            foreach (file($file) as $number => $line) {
                if (preg_match('/fa fa-|[\'"]fa-[a-z]|class="fa /', $line)) {
                    $hits[] = $relative . ':' . ($number + 1) . ' ' . trim($line);
                }
            }
        }

        $this->assertEmpty($hits, "Font Awesome markup found:\n" . implode("\n", $hits));
    }
}
