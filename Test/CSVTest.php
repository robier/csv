<?php
include '../Source/CSV.php';
use Robier\CSV;

class CSVTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var $csv CSV
     */
    protected $csv = null;

    protected $testDataPlain =
        [
            'test',
            'test2',
            'test3'
        ];
    protected $testDataAssociative =
        [
            'column_1' => 'Data 1',
            'column_2' => 'Data 2',
            'column_3' => 'Data 3',
            'column_4' => 'Data 4'
        ];
    protected $testHeaderAssociative =
        [
            'column_1' => 'Column 1',
            'column_2' => 'Column 2',
            'column_3' => 'Column 3'
        ];

    protected static $testFilePath = '';

    public static function setUpBeforeClass()
    {
        $sha1TestFilePath = '/tmp/' . sha1(uniqid()) . '-unit_testing_test';
        self::$testFilePath = $sha1TestFilePath;
    }

    public static function tearDownAfterClass()
    {
        if (is_readable(self::$testFilePath)) {
            unlink(self::$testFilePath);
        }
    }

    public function setUp()
    {
        $this->csv = new CSV();
    }

    public function tearDown()
    {
        if (file_exists($this->csv->getPath())) {
            unlink($this->csv->getPath());
        }
        unset($this->csv);
    }

    public function testInstanceReturning()
    {
        $this->assertInstanceOf(CSV::class, $this->csv->setPath(self::$testFilePath));
        $this->assertInstanceOf(CSV::class, $this->csv->setRowKeys(array_keys($this->testHeaderAssociative)));
        $this->assertInstanceOf(CSV::class, $this->csv->setHeader($this->testHeaderAssociative));
        $this->assertInstanceOf(CSV::class, $this->csv->setDelimiter());
        $this->assertInstanceOf(CSV::class, $this->csv->setEncoding());
        $this->assertInstanceOf(CSV::class, $this->csv->setEnclosure());
        $this->assertInstanceOf(CSV::class, $this->csv->setSeparatorIdentifier());
        $this->assertInstanceOf(CSV::class, $this->csv->write($this->testDataAssociative));
        $this->assertInstanceOf(CSV::class, $this->csv->write($this->testDataPlain));
        $this->assertInstanceOf(CSV::class, $this->csv->replace('test', 'test2'));
    }

    public function testFilePathSetGet()
    {
        $this->csv->setPath(self::$testFilePath);

        $this->assertEquals(self::$testFilePath, $this->csv->getPath());

        return $this->csv;
    }

    public function testResourceReturning()
    {
        $this->csv->setPath(self::$testFilePath);
        $this->csv->write([]);
        $this->assertTrue(is_resource($this->csv->getHandle()));
    }

    /**
     * @depends testFilePathSetGet
     *
     * @param CSV $csv
     * @return CSV
     */
    public function testFileWriteSuccess($csv)
    {
        $csv->setSeparatorIdentifier(false);
        $csv->write($this->testDataPlain);
        return $csv;
    }

    /**
     * @depends testFileWriteSuccess
     *
     * @param CSV $csv
     * @return CSV
     */
    public function testFileExists($csv)
    {
        $this->assertFileExists($csv->getPath());

        return $csv;
    }

    /**
     * @depends testFileExists
     *
     * @param CSV $csv
     */
    public function testFileContent($csv)
    {
        $this->assertStringEqualsFile($csv->getPath(), implode(';', $this->testDataPlain) . "\n");
    }

    public function testFileSetFail()
    {
        $this->setExpectedException('LogicException', 'File path not set!');

        $this->csv->write(['test']);
    }

    public function testRowKeys()
    {
        $this->csv->setRowKeys(array_keys($this->testHeaderAssociative));
        $this->csv->setPath(self::$testFilePath);
        $this->csv->setSeparatorIdentifier(false);
        $this->csv->write($this->testHeaderAssociative);

        $data = '"' . implode('";"', array_merge(array_fill_keys(array_keys($this->testHeaderAssociative), ''), $this->testHeaderAssociative)) . '"' . "\n";

        $this->assertStringEqualsFile($this->csv->getPath(), $data);
    }

    public function testNotSettingRowKeys()
    {
        $this->csv->setPath(self::$testFilePath);
        $this->csv->setSeparatorIdentifier(false);
        $this->csv->write($this->testDataAssociative);

        $data = '"' . implode('";"', $this->testDataAssociative) . '"' . "\n";

        $this->assertStringEqualsFile($this->csv->getPath(), $data);
    }

    public function testAddHeaderLine()
    {
        $this->csv->setPath(self::$testFilePath);
        $this->csv->setHeader($this->testHeaderAssociative);
        $this->csv->setSeparatorIdentifier(false);
        $this->csv->write($this->testDataAssociative);

        $keys = array_fill_keys(array_keys($this->testHeaderAssociative), '');

        $testData = $this->testDataAssociative;
        unset($testData['column_4']);

        // making string like it should be in file
        $data = '"' . implode('";"', array_merge($keys, $this->testHeaderAssociative)) . '"' . "\n";
        $data .= '"' . implode('";"', array_merge($keys, $testData)) . '"' . "\n";

        $this->assertStringEqualsFile($this->csv->getPath(), $data);
    }

    public function testWithoutAddHeaderLine()
    {
        $this->csv->setPath(self::$testFilePath);
        $this->csv->setSeparatorIdentifier(false);
        $this->csv->write($this->testDataAssociative);

        // making string like it should be in file
        $data = '"' . implode('";"', $this->testDataAssociative) . '"' . "\n";

        $this->assertStringEqualsFile($this->csv->getPath(), $data);
    }

    public function testCsvOutput()
    {
        $this->csv->setPath(self::$testFilePath);
        $this->csv->setHeader($this->testHeaderAssociative);
        $this->csv->setSeparatorIdentifier(false);
        $this->csv->write($this->testDataAssociative);

        $keys = array_fill_keys(array_keys($this->testHeaderAssociative), '');

        $testData = $this->testDataAssociative;
        unset($testData['column_4']);

        // making string like it should be in file
        $data = '"' . implode('";"', array_merge($keys, $this->testHeaderAssociative)) . '"' . "\n";
        $data .= '"' . implode('";"', array_merge($keys, $testData)) . '"' . "\n";

        $this->expectOutputString($data);

        @$this->csv->output();
    }
}