<?php

namespace av\commandRunner;

use av\commandRunner\output\CommandOutput;
use av\commandRunner\output\AbstractOutput;
use av\commandRunner\exceptions\CommandRunnerException;

/**
 * Class CommandRunner
 *
 * @package av\commandRunner
 */
class CommandRunner implements CommandInterface
{
    /**
     * @var AbstractOutput
     */
    private $_commandOutput;

    /**
     * @var bool Whether to redirect all command output.
     */
    private $_waitForOutput = true;

    /**
     * @var string Current command to be executed.
     */
    private $_command;

    /**
     * @var bool Whether to show raw command output.
     */
    private $_rawOutput = false;

    /**
     * @var array List of command arguments.
     */
    private $_argument = [];

    /**
     * @var bool Whether to save output.
     */
    private $_saveOutput = false;

    /**
     * @var string Redirect error output to standard output.
     */
    private $_errorOutputRedirection = ' 2>&1';

    /**
     * @var string Redirect all command output.
     */
    private $_outputRedirection = ' >/dev/null';

    /**
     * CommandRunner constructor.
     *
     * @param string|null $command
     * @param bool        $rawOutput
     */
    public function __construct($command = null, $rawOutput = false)
    {
        if ($command) {
            $this->setCommand($command, $rawOutput);
        }
    }

    /**
     * Set an instance of the command output, which is responsible of save of
     * the output.
     *
     * @param AbstractOutput $output
     *
     * @return $this
     */
    public function setCommandOutput(AbstractOutput $output)
    {
        $this->_commandOutput = $output;

        return $this;
    }

    /**
     * Get current instance of Output class. If it was not specified
     * new instance of Output class will be created.
     *
     * @return AbstractOutput
     */
    public function getCommandOutput()
    {
        if (!$this->_commandOutput) {
            $this->setCommandOutput(new CommandOutput);
        }

        return $this->_commandOutput;
    }

    /**
     * Whether to wait command output. Default set to true. Change this param
     * in case of start daemon processes.
     *
     * @param bool $waitForOutput
     */
    public function setWaitForOutput($waitForOutput)
    {
        $this->_waitForOutput = (bool)$waitForOutput;
    }

    /**
     * Whether to show the raw output.
     *
     * @param bool $rawOutput
     *
     * @return $this
     */
    public function setRawOutput($rawOutput)
    {
        $this->_rawOutput = (bool)$rawOutput;

        return $this;
    }

    /**
     * Set command to be executed. Command string can be escaped depends on
     * $escapeCommand param. Default set to false.
     *
     * @param string $command
     * @param bool   $escapeCommand
     *
     * @return $this
     * @throws CommandRunnerException
     */
    public function setCommand($command, $escapeCommand = false)
    {
        if (!is_string($command)) {
            throw new CommandRunnerException(
                'Command must be a string.' . gettype($command) . ' passed.'
            );
        }

        $preparedCommand = trim($command);

        if (empty($preparedCommand)) {
            throw new CommandRunnerException('Command not set!');
        }

        // Escape command string
        if ($escapeCommand) {
            $preparedCommand = escapeshellcmd($preparedCommand);
        }

        $this->_command = $preparedCommand;

        return $this;
    }

    /**
     * Set arguments for command. This method is optional and all arguments
     * can be specified in command itself.
     *
     * @param array $argument
     *
     * @return $this
     */
    public function setArgument(array $argument)
    {
        $this->_argument = array_map('escapeshellarg', $argument);

        return $this;
    }

    /**
     * Execute current command and return results.
     *
     * @return array
     */
    public function execute()
    {
        $preparedCommand = $this->_buildCommand();

        $output = [];

        // Turn on output buffering if raw output was set
        if ($this->_rawOutput) {
            ob_start();

            passthru($preparedCommand, $resultCode);

            $output = ob_get_clean();
        } else {
            exec($preparedCommand, $output, $resultCode);
        }

        if ($this->_saveOutput) {
            $this->getCommandOutput()->save($output);
        }

        return [
            'output'     => $output,
            'resultCode' => $resultCode,
        ];
    }

    /**
     * Set command output file name.
     *
     * @param string $saveOutput
     *
     * @return AbstractOutput
     */
    public function setSaveOutputName($saveOutput)
    {
        $output = $this->getCommandOutput();

        $output->setOutputName($saveOutput);

        $this->_saveOutput = true;

        return $output;
    }

    /**
     * Prepare command including corresponding arguments before execute.
     *
     * @return string
     */
    private function _buildCommand()
    {
        $preparedArguments = implode(' ', $this->getArguments());

        // Include error output to standard output
        $preparedCommand = $this->getCommand() . $this->_errorOutputRedirection;

        // Update command for output redirection
        if (!$this->_waitForOutput) {
            $preparedCommand .= $this->_outputRedirection;
        }

        return $preparedCommand . ' ' . $preparedArguments;
    }

    /**
     * Get current command.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->_command;
    }

    /**
     * Get current command arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_argument;
    }
}