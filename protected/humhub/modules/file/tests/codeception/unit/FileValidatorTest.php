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

        $this->assertValidatedFileName("testChar\x00.jpg", 'testChar_.jpg');
        $this->assertValidatedFileName("testChar\x1f.jpg", 'testChar_.jpg');
        $this->assertValidatedFileName("testChar\xc2\x80.jpg", 'testChar_.jpg');
        $this->assertValidatedFileName("testChar\xc2\x9f.jpg", 'testChar_.jpg');

        $this->assertValidatedFileName("testChar\fst.jpg", 'testChar_st.jpg');
        $this->assertValidatedFileName("testChar\nst.jpg", 'testChar_st.jpg');
        $this->assertValidatedFileName("testChar\rst.jpg", 'testChar_st.jpg');
        $this->assertValidatedFileName("testChar\tst.jpg", 'testChar_st.jpg');
        $this->assertValidatedFileName("testChar\0st.jpg", 'testChar_st.jpg');
        $this->assertValidatedFileName("testChar\1st.jpg", 'testChar_st.jpg');
        $this->assertValidatedFileName("testChar\2st.jpg", 'testChar_st.jpg');
        $this->assertValidatedFileName("testChar\3st.jpg", 'testChar_st.jpg');
        $this->assertValidatedFileName("testChar\4st.jpg", 'testChar_st.jpg');
        $this->assertValidatedFileName("testChar\5st.jpg", 'testChar_st.jpg');
        $this->assertValidatedFileName("testChar\6st.jpg", 'testChar_st.jpg');
        $this->assertValidatedFileName("testChar" . chr(7) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(8) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(9) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(10) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(11) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(12) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(13) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(14) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(15) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(16) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(17) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(18) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(19) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(20) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(21) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(22) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(23) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(24) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(25) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(26) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(27) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(28) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(29) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(30) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("testChar" . chr(31) . "bst.jpg", 'testChar_bst.jpg');
        $this->assertValidatedFileName("tes|st.jpg", 'tes_st.jpg');
        $this->assertValidatedFileName("tes]st.jpg", 'tes_st.jpg');
        $this->assertValidatedFileName("tes[st.jpg", 'tes_st.jpg');
        $this->assertValidatedFileName("tes{st.jpg", 'tes_st.jpg');
        $this->assertValidatedFileName("tes}st.jpg", 'tes_st.jpg');
        $this->assertValidatedFileName("tes?st.jpg", 'tes_st.jpg');
        $this->assertValidatedFileName("tes:st.jpg", 'tes_st.jpg');
        $this->assertValidatedFileName("tes*st.jpg", 'tes_st.jpg');
        $this->assertValidatedFileName("tes<st.jpg", 'tes_st.jpg');
        $this->assertValidatedFileName("tes>st.jpg", 'tes_st.jpg');

        $this->assertValidatedFileName("<svgonload=alert(1)>.jpg", '_svgonload=alert(1)_.jpg');

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

    /**
     * @param string $sourceFileName
     * @param string $expectedFileName
     */
    protected function assertValidatedFileName($sourceFileName, $expectedFileName)
    {
        $file = $this->createFile($sourceFileName);
        $file->validate();
        $this->assertEquals($expectedFileName, $file->file_name);
    }
}