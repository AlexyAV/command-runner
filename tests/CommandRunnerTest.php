<?php

use av\commandRunner\CommandRunner;
use av\commandRunner\output\CommandOutput;
use av\commandRunner\output\AbstractOutput;
use av\commandRunner\exceptions\CommandRunnerException;

class CommandRunnerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CommandRunner
     */
    private $_commandRunner;

    public function setUp()
    {
        parent::setUp();

        $this->_commandRunner = new CommandRunner;
    }

    public function testGetCommandOutput()
    {
        $this->assertInstanceOf(
            AbstractOutput::class, $this->_commandRunner->getCommandOutput()
        );

        /** @var CommandOutput $outputMock */
        $outputMock = $this->getMockBuilder(CommandOutput::class)->getMock();

        $this->_commandRunner->setCommandOutput($outputMock);

        $this->assertInstanceOf(
            get_class($outputMock), $this->_commandRunner->getCommandOutput()
        );
    }

    public function testSetCommandExceptionType()
    {
        $this->setExpectedExceptionRegExp(
            CommandRunnerException::class, '/Command must be a string.*/'
        );

        $this->_commandRunner->setCommand(['pwd']);
    }

    public function testSetCommandExceptionEmpty()
    {
        $this->setExpectedException(
            CommandRunnerException::class, 'Command not set!'
        );

        $this->_commandRunner->setCommand(' ');
    }

    public function testSetCommand()
    {
        $this->_commandRunner->setCommand('pwd');

        $this->assertEquals('pwd', $this->_commandRunner->getCommand());

        $this->_commandRunner->setCommand('ps aux | grep', true);

        $this->assertEquals(
            'ps aux \| grep', $this->_commandRunner->getCommand()
        );
    }

    public function testConstruct()
    {
        $commandRunner = new CommandRunner('pwd');

        $this->assertEquals('pwd', $commandRunner->getCommand());
    }

    public function testSetArgument()
    {
        $arguments = ['arg_1', 'arg_2', 'arg_3', '#$%^&*()'];

        $this->_commandRunner->setArgument($arguments);

        $this->assertEquals(
            ["'arg_1'", "'arg_2'", "'arg_3'", "'#$%^&*()'"],
            $this->_commandRunner->getArguments()
        );
    }

    public function testSetSaveOutputName()
    {
        $outputMock = $this->getMockBuilder(CommandOutput::class)
            ->setMethods(['setOutputName'])
            ->getMock();

        $outputMock->expects($this->once())
            ->method('setOutputName')
            ->with($this->equalTo('fileName'))
            ->willReturn(true);

        $this->_commandRunner->setCommandOutput($outputMock);

        $this->assertInstanceOf(
            CommandOutput::class,
            $this->_commandRunner->setSaveOutputName('fileName')
        );
    }

    public function testExecute()
    {
        $this->_commandRunner->setCommand('pwd');

        $result = $this->_commandRunner->execute();

        $this->assertArrayHasKey('output', $result);

        $this->assertArrayHasKey('resultCode', $result);

        $this->assertEquals(dirname(__DIR__), $result['output'][0]);

        $this->assertEquals(0, $result['resultCode']);

        $this->_commandRunner->setRawOutput(true);

        $result = $this->_commandRunner->execute();

        $this->assertArrayHasKey('output', $result);

        $this->assertArrayHasKey('resultCode', $result);

        $this->assertEquals(dirname(__DIR__) . "\n", $result['output']);

        $this->assertEquals(0, $result['resultCode']);
    }

    public function testExecuteSaveOutput()
    {
        $outputMock = $this->getMockBuilder(CommandOutput::class)
            ->setMethods(['save'])
            ->getMock();

        $outputMock->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $this->_commandRunner->setCommandOutput($outputMock);

        $this->_commandRunner->setCommand('pwd');

        $this->_commandRunner->setSaveOutputName('fileName');

        $this->_commandRunner->execute();
    }

    public function testExecuteWaitForOutput()
    {
        $this->_commandRunner->setWaitForOutput(false);

        $this->_commandRunner->setCommand('pwd');

        $this->_commandRunner->execute();
    }
}