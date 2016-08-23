<?php

use org\bovigo\vfs\vfsStream;
use av\commandRunner\output\CommandOutput;
use av\commandRunner\exceptions\OutputException;

class CommandOutputTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CommandOutput
     */
    private $_commandOutput;

    public function setUp()
    {
        parent::setUp();

        $this->_commandOutput = new CommandOutput;
    }

    public function testSetOutputNameExceptionType()
    {
        $this->setExpectedExceptionRegExp(
            OutputException::class,
            '/File name of saved output must be a string.*/'
        );

        $this->_commandOutput->setOutputName(['fileName']);
    }

    public function testSetOutputNameEmpty()
    {
        $this->assertNotNull($this->_commandOutput->setOutputName('  '));
    }

    public function testSetOutputName()
    {
        $fileName = uniqid();

        $this->assertEquals(
            $fileName, $this->_commandOutput->setOutputName($fileName)
        );
    }

    public function testSetOutputPathExceptionType()
    {
        $this->setExpectedExceptionRegExp(
            OutputException::class,
            '/File path of saved output must be a string.*/'
        );

        $this->_commandOutput->setOutputPath(['path']);
    }

    public function testSetOutputPathExceptionDir()
    {
        $this->setExpectedException(
            OutputException::class,
            'Specified output path does not exist.'
        );

        $this->_commandOutput->setOutputPath('path');
    }

    public function testSaveExceptionOutput()
    {
        $this->setExpectedExceptionRegExp(
            OutputException::class,
            '/Output for save must be a string or array.*/'
        );

        $outputPath = vfsStream::setup(
            'root', 777, [
                'test.txt' => ''
            ]
        );

        $this->_commandOutput->setOutputPath($outputPath->url());

        $this->_commandOutput->setOutputName('test');

        $this->assertTrue($this->_commandOutput->save(12345));
    }

    public function testSave()
    {
        $outputPath = vfsStream::setup(
            'root', 777, [
                'test.txt' => ''
            ]
        );

        $this->_commandOutput->setOutputPath($outputPath->url());

        $this->_commandOutput->setOutputName('test.txt');

        $this->assertTrue($this->_commandOutput->save('test output'));

        $this->assertEquals(
            'test output',
            file_get_contents($outputPath->getChild('test.txt')->url())
        );

        $this->assertTrue($this->_commandOutput->save(['test', 'output']));

        $this->assertEquals(
            "test\noutput",
            file_get_contents($outputPath->getChild('test.txt')->url())
        );
    }

    public function testGetExceptionExist()
    {
        $this->setExpectedException(
            OutputException::class,
            'Specified output path does not exist.'
        );

        $outputPath = vfsStream::setup(
            'root', 777, [
                'test.txt' => 'test output'
            ]
        );

        $this->_commandOutput->setOutputPath($outputPath->url());

        $this->_commandOutput->get('invalidFile');
    }

    public function testGet()
    {
        $outputPath = vfsStream::setup(
            'root', 777, [
                'test.txt' => 'test output'
            ]
        );

        $this->_commandOutput->setOutputPath($outputPath->url());

        $this->assertEquals(
            'test output', $this->_commandOutput->get('test.txt')
        );
    }

    public function testDelete()
    {
        $outputPath = vfsStream::setup(
            'root', 777, [
                'test.txt' => 'test output'
            ]
        );

        $this->_commandOutput->setOutputPath($outputPath->url());

        $this->assertFalse(
            $this->_commandOutput->delete('test.txt')
        );
    }
}