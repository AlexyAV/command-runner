<?php

namespace av\commandRunner\output;

use av\commandRunner\exceptions\OutputException;

/**
 * Class AbstractOutput
 *
 * @package av\commandRunner\output
 */
abstract class AbstractOutput implements OutputInterface
{
    /**
     * @var string Output path
     */
    protected $_outputPath = '/tmp/commandRunnerOutput';

    /**
     * @var string Output file name
     */
    protected $_outputName;

    /**
     * Set path of file for save command output.
     *
     * @param string $outputPath
     *
     * @return string
     * @throws OutputException
     */
    public function setOutputPath($outputPath)
    {
        if (!is_string($outputPath)) {
            throw new OutputException(
                'File path of saved output must be a string. '
                . gettype($outputPath) . ' passed.'
            );
        }

        if (!is_dir($outputPath)) {
            throw new OutputException(
                'Specified output path does not exist.'
            );
        }

        $this->_outputPath = $outputPath;

        return $this->_outputPath;
    }

    /**
     * Set name of file for save command output.
     *
     * @param string $outputName
     *
     * @return string
     * @throws OutputException
     */
    public function setOutputName($outputName)
    {
        if (!is_string($outputName)) {
            throw new OutputException(
                'File name of saved output must be a string. '
                . gettype($outputName) . ' passed.'
            );
        }

        $preparedOutputName = trim($outputName);

        if (!$preparedOutputName) {
            $preparedOutputName = uniqid('command_output');
        }

        $this->_outputName = $preparedOutputName;

        return $this->_outputName;
    }

    /**
     * Save command execute result into file.
     *
     * @param $commandOutput
     *
     * @return bool
     * @throws OutputException
     */
    public function save($commandOutput)
    {
        $outputPath = $this->_getOutputPath($this->_outputName);

        return (bool)file_put_contents(
            $outputPath, $this->prepareCommandOutput($commandOutput)
        );
    }

    /**
     * Prepare command output before save.
     *
     * @param array|string $commandOutput
     *
     * @return string
     * @throws OutputException
     */
    protected function prepareCommandOutput($commandOutput)
    {
        $preparedOutput = $commandOutput;

        if (!is_array($commandOutput) && !is_string($commandOutput)) {
            throw new OutputException(
                'Output for save must be a string or array. '
                . gettype($commandOutput) . ' passed.'
            );
        }

        // Convert array data to string
        if (is_array($commandOutput)) {
            $preparedOutput = implode("\n", $commandOutput);
        }

        return trim($preparedOutput);
    }

    /**
     * Get full path to output file.
     *
     * @param string $fileName
     *
     * @return string
     */
    protected function _getOutputPath($fileName)
    {
        return $this->_outputPath . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Get data of specified output filename.
     *
     * @param string $fileName
     *
     * @return string
     */
    public function get($fileName)
    {
        $outputPath = $this->_getOutputPath($fileName);

        $this->_checkForExist($outputPath);

        return file_get_contents($outputPath);
    }

    /**
     * Check for path existing.
     *
     * @param string $outputPath
     *
     * @return bool
     * @throws OutputException
     */
    protected function _checkForExist($outputPath)
    {
        if (!file_exists($outputPath)) {
            throw new OutputException(
                'Specified output path does not exist.'
            );
        }

        return true;
    }

    /**
     * Delete file of command output.
     *
     * @param string $fileName
     *
     * @return bool
     */
    public function delete($fileName)
    {
        $outputPath = $this->_getOutputPath($fileName);

        $this->_checkForExist($outputPath);

        return unlink($outputPath);
    }
}