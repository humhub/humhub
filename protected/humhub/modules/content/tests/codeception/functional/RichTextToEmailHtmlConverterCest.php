<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace content\functional;

use content\FunctionalTester;
use humhub\modules\comment\models\forms\CommentForm;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;

class RichTextToEmailHtmlConverterCest
{

    public function testSendEmailWithImage(FunctionalTester $I)
    {
        $I->wantTo('see image in email message');
        $I->amUser1();

        $file = $this->createFile();
        $this->createComment($file);

        $I->assertMailSent(1);

        $commentMailText = $I->grabLastSentEmail()->toString();

        if (!$this->tokenIsDetectedInImageUrl($commentMailText)) {
            $I->see('Token is not detected in image URL');
        }
    }

    protected function tokenIsDetectedInImageUrl(string $emailMessage) : bool
    {
        return (bool) preg_match('/Test comment with image[ =\r\n]+<img.+?src=(3D)?".+?&amp;token=.+?".+?>/is', $emailMessage);
    }

    protected function createFile() : File
    {
        $file = new File([
            'file_name' => 'text.jpg',
            'title' => 'Test Image',
            'mime_type' => 'image/jpeg',
            'size' => 302176
        ]);
        $file->save();

        return $file;
    }

    protected function createComment(File $file)
    {
        $post = Post::findOne(['id' => 1]);
        $commentForm = new CommentForm($post);
        $commentForm->load([
            'objectModel' => get_class($post),
            'objectId' => $post->id,
            'Comment' => ['message' => 'Test comment with image ![' . $file->file_name . '](file-guid:' . $file->guid . ' "' . $file->title . '")'],
            'fileList' => [$file->guid],
        ]);
        $commentForm->save();
    }

}
