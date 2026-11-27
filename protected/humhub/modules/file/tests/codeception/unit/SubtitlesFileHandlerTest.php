<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\file\handler\SubtitlesFileHandler;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\Url;

class SubtitlesFileHandlerTest extends HumHubDbTestCase
{
    public function testRegisteredForMediaFilesOfAnEditor()
    {
        $this->becomeUser('Admin');

        foreach (['video/mp4', 'audio/mpeg'] as $mimeType) {
            $file = $this->createFile($mimeType);

            $handlers = FileHandlerCollection::getByType(FileHandlerCollection::TYPE_EDIT, $file);

            $this->assertCount(1, $handlers, $mimeType);
            $this->assertInstanceOf(SubtitlesFileHandler::class, $handlers[0]);
            $this->assertSame($file, $handlers[0]->file);
        }
    }

    public function testNotForOtherCollectionTypes()
    {
        $this->becomeUser('Admin');
        $file = $this->createFile('video/mp4');

        foreach ([FileHandlerCollection::TYPE_VIEW, FileHandlerCollection::TYPE_IMPORT, FileHandlerCollection::TYPE_EXPORT] as $type) {
            foreach (FileHandlerCollection::getByType($type, $file) as $handler) {
                $this->assertNotInstanceOf(SubtitlesFileHandler::class, $handler, $type);
            }
        }
    }

    public function testNotForOtherFiles()
    {
        $this->becomeUser('Admin');

        $this->assertSame([], FileHandlerCollection::getByType(FileHandlerCollection::TYPE_EDIT, $this->createFile('text/plain')));
        $this->assertSame([], FileHandlerCollection::getByType(FileHandlerCollection::TYPE_EDIT, $this->createFile('image/png')));
        $this->assertSame([], FileHandlerCollection::getByType(FileHandlerCollection::TYPE_EDIT));
    }

    public function testNotForAUserWhoCannotEditTheFile()
    {
        $this->becomeUser('Admin');
        $file = $this->createFile('video/mp4');

        $this->becomeUser('User3');
        $this->assertFalse($file->canDelete());

        $this->assertSame([], FileHandlerCollection::getByType(FileHandlerCollection::TYPE_EDIT, $file));
    }

    public function testNotForGuests()
    {
        $this->becomeUser('Admin');
        $file = $this->createFile('video/mp4');

        Yii::$app->user->logout();

        $this->assertSame([], FileHandlerCollection::getByType(FileHandlerCollection::TYPE_EDIT, $file));
    }

    public function testMediaFileGetsAViewUrlForTheEditorOnly()
    {
        $this->becomeUser('Admin');
        $file = $this->createFile('video/mp4');

        $this->assertSame(Url::to(['/file/view', 'guid' => $file->guid]), FileHelper::getViewUrl($file));

        $this->becomeUser('User3');
        $this->assertNull(FileHelper::getViewUrl($file));
    }

    public function testLinkOpensTheSubtitlesModal()
    {
        $this->becomeUser('Admin');
        $file = $this->createFile('video/mp4');

        $attributes = FileHandlerCollection::getByType(FileHandlerCollection::TYPE_EDIT, $file)[0]->getLinkAttributes();

        $this->assertSame(Url::to(['/file/subtitles', 'guid' => $file->guid]), $attributes['data-action-url']);
        $this->assertSame('ui.modal.load', $attributes['data-action-click']);
        $this->assertStringContainsString('Subtitles', $attributes['label']);
    }

    private function createFile(string $mimeType): File
    {
        $file = new File();
        $file->file_name = 'test.' . explode('/', $mimeType)[1];
        $file->mime_type = $mimeType;
        $this->assertTrue($file->save());

        Post::findOne(['id' => 1])->fileManager->attach($file);
        $file->refresh();

        return $file;
    }
}
