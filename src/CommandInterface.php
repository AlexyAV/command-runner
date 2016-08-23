<?php

namespace av\commandRunner;

/**
 * Interface CommandInterface
 *
 * @package av\commandRunner
 */
interface CommandInterface
{
    /**
     * Execute specified command.
     *
     * @return mixed Result of command execute
     */
    public function execute();
}