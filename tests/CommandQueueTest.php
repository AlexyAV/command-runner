<?php

use av\commandRunner\CommandQueue;
use av\commandRunner\exceptions\CommandQueueException;

class CommandQueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CommandQueue
     */
    private $_commandQueue;

    public function setUp()
    {
        parent::setUp();

        $this->_commandQueue = new CommandQueue;
    }

    public function testSetCommandQueueExceptionEmpty()
    {
        $this->setExpectedException(
            CommandQueueException::class, 'Command list for queue is empty!'
        );

        $this->_commandQueue->setCommandQueue([]);
    }

    public function testSetCommandQueueExceptionValidateSyntax()
    {
        $this->setExpectedExceptionRegExp(
            CommandQueueException::class,
            '/.*Command list should consist of arrays.$/'
        );

        $this->_commandQueue->setCommandQueue(['command']);
    }

    public function testSetCommandQueueExceptionValidateRequired()
    {
        $this->setExpectedExceptionRegExp(
            CommandQueueException::class,
            '/.*Can\'t find \'command\' param.$/'
        );

        $this->_commandQueue->setCommandQueue([['commandData']]);
    }

    public function testSetCommandQueue()
    {
        $commandList = [
            [
                'command'   => 'ls -la',
                'arguments' => ['arg_1', 'arg_2'],
                'escape'    => false,
                'options'   => [
                    'saveOutputName' => 'fileName',
                    'waitForOutput'  => false,
                    'rawOutput'      => true
                ]
            ]
        ];

        $this->_commandQueue->setCommandQueue($commandList);

        $this->assertEquals(
            $commandList, $this->_commandQueue->getCommandQueue()
        );
    }

    public function testExecute()
    {
        $commandList = [
            [
                'command'   => 'pwd',
                'arguments' => [],
                'escape'    => false,
                'options'   => [
                    'waitForOutput' => true,
                    'rawOutput'     => false
                ]
            ]
        ];

        $this->_commandQueue->setCommandQueue($commandList);

        $result = $this->_commandQueue->execute();

        $this->assertEquals(dirname(__DIR__), $result[0]['output'][0]);
    }

    public function testExecuteExceptionOptions()
    {
        $this->setExpectedExceptionRegExp(
            CommandQueueException::class,
            '/Command options must be an array.*/'
        );

        $commandList = [
            [
                'command'   => 'pwd',
                'arguments' => [],
                'escape'    => false,
                'options'   => true
            ]
        ];

        $this->_commandQueue->setCommandQueue($commandList);

        $this->_commandQueue->execute();
    }

    public function testExecuteWithoutOptions()
    {
        $commandList = [
            [
                'command'   => 'pwd',
                'arguments' => [],
                'escape'    => false,
            ]
        ];

        $this->_commandQueue->setCommandQueue($commandList);

        $result = $this->_commandQueue->execute();

        $this->assertEquals(dirname(__DIR__), $result[0]['output'][0]);
    }

    public function testExecuteEmpty()
    {
        $this->assertFalse($this->_commandQueue->execute());
    }

    public function testConstruct()
    {
        $commandList = [
            [
                'command'   => 'ls -la',
                'arguments' => ['arg_1', 'arg_2'],
                'escape'    => false,
                'options'   => [
                    'saveOutputName' => 'fileName',
                    'waitForOutput'  => false,
                    'rawOutput'      => true
                ]
            ]
        ];

        $commandQueue = new CommandQueue($commandList);

        $this->assertEquals(
            $commandList, $commandQueue->getCommandQueue()
        );
    }
}