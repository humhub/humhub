<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */


namespace humhub\tests\codeception\unit;

use Codeception\Test\Unit;
use humhub\components\export\ExportResult;
use humhub\components\export\SpreadsheetExport;
use yii\data\ArrayDataProvider;

/**
 * Class SpreadsheetExportTest
 *
 * This class was originally developed by Paul Klimov <klimov.paul@gmail.com> and his
 * project csv-grid (https://github.com/yii2tech/csv-grid).
 */
class SpreadsheetExportTest extends Unit
{
    const TEST_FILE = 'test.csv';

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @Override
     */
    protected function _after()
    {
        if (is_writable(self::TEST_FILE)) {
            unlink(self::TEST_FILE);
        }
    }

    /**
     * @param array $config SpreadsheetExport configuration
     * @return SpreadsheetExport Export instance
     */
    protected function createSpreadsheetExport(array $config = [])
    {
        if (!isset($config['dataProvider']) && !isset($config['query'])) {
            $config['dataProvider'] = new ArrayDataProvider();
        }
        return new SpreadsheetExport($config);
    }

    /**
     * Test export
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function testExport()
    {
        $exporter = new SpreadsheetExport([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [
                    [
                        'id' => 1,
                        'name' => 'first',
                    ],
                    [
                        'id' => 2,
                        'name' => 'second',
                    ],
                ],
            ])
        ]);

        $result = $exporter->export();
        $this->assertTrue($result instanceof ExportResult);

        $result->saveAs(self::TEST_FILE);
        $this->assertFileExists(self::TEST_FILE, 'Result file does not exist.');

        $data = file_get_contents(self::TEST_FILE);
        $this->assertStringContainsString('"Id","Name"', $data, 'Header not present in content.');
        $this->assertStringContainsString('"1","first"', $data, 'Data not present in content.');
        $this->assertStringContainsString('"2","second"', $data, 'Data not present in content.');
    }
}
