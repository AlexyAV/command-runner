<?php

namespace av\commandRunner;

use av\commandRunner\exceptions\CommandQueueException;

/**
 * Class CommandQueue
 *
 * @package av\commandRunner
 */
class CommandQueue implements CommandInterface
{
    /**
     * @var array Queue of command. Will be executed synchronously.
     */
    private $_commandQueue = [];

    /**
     * @var CommandRunner|null
     */
    private $_currentCommand;

    /**
     * CommandQueue constructor.
     *
     * @param array $commandList
     */
    public function __construct(array $commandList = [])
    {
        if ($commandList) {
            $this->setCommandQueue($commandList);
        }
    }

    /**
     * Set new commands queue.
     *
     * Example of commands list config:
     * [
     *     'command'   => 'commandName',
     *     'arguments' => ['arg_1', 'arg_2'], //optional
     *     'escape'    => false,              //optional
     *     'options'   => [
     *         'saveOutputName' => 'fileName', //optional
     *         'waitForOutput'  => false,      //optional
     *         'rawOutput'      => true        //optional
     *     ]
     * ]
     *
     * @param array $commandList
     *
     * @throws CommandQueueException
     */
    public function setCommandQueue(array $commandList)
    {
        if (empty($commandList)) {
            throw new CommandQueueException('Command list for queue is empty!');
        }

        $this->_validateCommandList($commandList);

        $this->_commandQueue = $commandList;
    }

    /**
     * Validate command list config for correct syntax.
     *
     * @param array $commandList
     *
     * @return bool
     */
    private function _validateCommandList(array $commandList)
    {
        $validateFunction = function ($commandData) {

            // Only arrays are allowed
            if (!is_array($commandData)) {
                throw new CommandQueueException(
                    'Invalid command list params. '
                    . 'Command list should consist of arrays.'
                );
            }

            // Check for required param
            if (!array_key_exists('command', $commandData)) {
                throw new CommandQueueException(
                    'Invalid command list params. '
                    . 'Can\'t find \'command\' param.'
                );
            }
        };

        array_map($validateFunction, $commandList);

        return true;
    }

    /**
     * Execute command queue.
     *
     * @return array|bool
     * @throws CommandQueueException
     */
    public function execute()
    {
        if (!$this->_commandQueue) {
            return false;
        }

        // Current command data.
        $commandData = null;

        foreach ($this->_commandQueue as &$commandData) {

            // Create new instance of command runner for each command data
            $this->_currentCommand = new CommandRunner;

            $this->_prepareCommandRunnerOptions($commandData);

            $this->_prepareCommand($commandData);

            $commandData = array_merge(
                $commandData, $this->_currentCommand->execute()
            );
        }

        return $this->_commandQueue;
    }

    /**
     * Prepare command runner instance command and corresponding arguments.
     *
     * @param array $commandData
     *
     * @return CommandRunner|null
     */
    private function _prepareCommand(array $commandData)
    {
        $this->_currentCommand->setCommand(
            $commandData['command'],
            !array_key_exists('escape', $commandData)
                ?: $commandData['escape']
        );

        if (array_key_exists('arguments', $commandData)) {
            $this->_currentCommand->setArgument($commandData['arguments']);
        }

        return $this->_currentCommand;
    }

    /**
     * Prepare command runner instance with set additional options.
     *
     * @param array $commandData
     *
     * @return bool|CommandRunner
     * @throws CommandQueueException
     */
    private function _prepareCommandRunnerOptions(array $commandData)
    {
        if (!array_key_exists('options', $commandData)) {
            return false;
        }

        $commandOptions = $commandData['options'];

        if (!is_array($commandOptions)) {
            throw new CommandQueueException(
                'Command options must be an array. '
                . gettype($commandOptions) . ' passed.'
            );
        }

        foreach ($commandOptions as $optionName => $optionValue) {
            call_user_func_array(
                [$this->_currentCommand, 'set' . ucfirst($optionName)],
                [$optionValue]
            );
        }

        return $this->_currentCommand;
    }

    /**
     * Get current command queue.
     *
     * @return array
     */
    public function getCommandQueue()
    {
        return $this->_commandQueue;
    }
}