<?php

namespace av\commandRunner\output;

/**
 * Interface OutputInterface
 *
 * @package av\commandRunner\output
 */
interface OutputInterface
{
    /**
     * Save command execute result.
     *
     * @param array|string $commandOutput
     *
     * @return mixed
     */
    public function save($commandOutput);

    /**
     * Get command execute result.
     *
     * @param string $outputFileName
     *
     * @return mixed
     */
    public function get($outputFileName);

    /**
     * Delete command execute result.
     *
     * @param string $outputFileName
     *
     * @return mixed
     */
    public function delete($outputFileName);
}