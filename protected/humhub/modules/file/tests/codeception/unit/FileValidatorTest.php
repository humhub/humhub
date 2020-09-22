<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\models\FileUpload;
use humhub\modules\file\Module;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\UploadedFile;

class FileValidatorTest extends HumHubDbTestCase
{
    public function testFilenameValidation()
    {
        $this->assertTrue($this->createFile('shouldwork.jpg')->validate());
        $this->assertTrue($this->createFile('should¡work.jpg')->validate());
        $this->assertTrue($this->createFile('shouldÿwork.jpg')->validate());
        $this->assertTrue($this->createFile("lästig.jpg")->validate());
        $this->assertTrue($this->createFile("lä123stig.jpg")->validate());
        $this->assertTrue($this->createFile("test-this.jpg")->validate());
        $this->assertTrue($this->createFile("test_this.jpg")->validate());
        $this->assertTrue($this->createFile("test this.jpg")->validate());
        $this->assertTrue($this->createFile("test@this.jpg")->validate());
        $this->assertTrue($this->createFile("test€this.jpg")->validate());
        $this->assertTrue($this->createFile("hârt.jpg")->validate());
        $this->assertTrue($this->createFile("我.jpg")->validate());
        $this->assertTrue($this->createFile("昨夜の.jpg")->validate());
        $this->assertTrue($this->createFile("ْعَرَبِيَّة.jpg")->validate());
        $this->assertTrue($this->createFile("test.this.jpg")->validate());
        $this->assertTrue($this->createFile("çore.jpg")->validate());

        $this->assertFalse($this->createFile("testChar\x00.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\x1f.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\xc2\x80.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\xc2\x9f.jpg")->validate());

        $this->assertFalse($this->createFile("testChar\fst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\nst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\rst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\tst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\0st.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\1st.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\2st.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\3st.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\4st.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\5st.jpg")->validate());
        $this->assertFalse($this->createFile("testChar\6st.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(7) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(8) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(9) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(10) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(11) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(12) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(13) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(14) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(15) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(16) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(17) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(18) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(19) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(20) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(21) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(22) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(23) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(24) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(25) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(26) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(27) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(28) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(29) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(30) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("testChar" . chr(31) . "bst.jpg")->validate());
        $this->assertFalse($this->createFile("tes|st.jpg")->validate());
        $this->assertFalse($this->createFile("tes]st.jpg")->validate());
        $this->assertFalse($this->createFile("tes[st.jpg")->validate());
        $this->assertFalse($this->createFile("tes{st.jpg")->validate());
        $this->assertFalse($this->createFile("tes}st.jpg")->validate());
        $this->assertFalse($this->createFile("tes?st.jpg")->validate());
        $this->assertFalse($this->createFile("tes:st.jpg")->validate());
        $this->assertFalse($this->createFile("tes*st.jpg")->validate());
        $this->assertFalse($this->createFile("tes<st.jpg")->validate());
        $this->assertFalse($this->createFile("tes>st.jpg")->validate());

        $this->assertFalse($this->createFile("<svgonload=alert(1)>.jpg")->validate());

        $this->assertTrue($this->createFile("test.jpg.exe")->validate());
        /** @var Module $module */
        $module = Yii::$app->getModule('file');
        $module->denyDoubleFileExtensions = true;
        $this->assertFalse($this->createFile("test.jpg.exe")->validate());

    }

    private function createFile($name)
    {
        $file = new FileUpload();
        $file->setUploadedFile(new UploadedFile(['name' => $name]));
        return $file;
    }
}