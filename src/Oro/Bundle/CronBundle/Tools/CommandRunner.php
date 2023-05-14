<?php

declare(strict_types=1);

namespace Oro\Bundle\CronBundle\Tools;

use Oro\Bundle\CronBundle\Entity\Schedule;
use Oro\Component\Sys\MutexProcess;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * This runner runs a console command with parameters in the background without locking the main thread.
 * Please note, that this class should be used only from the console commands as it uses $_SERVER['argv'][0]
 * to get the path to symfony "console" file.
 */
class CommandRunner
{
    protected static $binPath = null;

    public static function setConsoleBin(string $binPath)
    {
        static::$binPath = $binPath;
    }

    /**
     * Runs the command in background process without the lock of main stream.
     *
     * @param string $command
     * @param array  $params
     * @param string $outputFile
     * @param bool $lock
     */
    public static function runCommand(string $command, array $params = [], string $outputFile = '/dev/null', bool $lock = false)
    {
        $phpFinder = new PhpExecutableFinder();
        $phpPath   = $phpFinder->find();

        // convert command arguments to the string
        $parametersString = '';
        foreach ($params as $name => $value) {
            if ($name && '-' === $name[0]) {
                if ($value === true) {
                    $parametersString .= ' ' . $name;
                } elseif ($value !== false) {
                    $parametersString .= ' ' . sprintf('%s=%s', $name, $value);
                }
            } else {
                $parametersString .= ' ' . $value;
            }
        }

        // create command string
        $runCommand = sprintf(
            '%s %s %s%s',
            $phpPath,
            $_SERVER['argv'][0] ?? static::$binPath,
            $command,
            $parametersString
        );

        // workaround for Windows
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $wsh = new \COM('WScript.shell');
            $wsh->Run($runCommand, 0, false);
            return;
        }

        if (true === $lock) {
            $hash = Schedule::calculateHash($command, $params);
            $semKeyId = MutexProcess::genKey($hash);
            if (true === MutexProcess::isLockedProcess($semKeyId)) {
                return;
            }

            // run command
            shell_exec(sprintf('export LOCK_SEM_ID=%s; %s > %s 2>&1 & echo $!', $semKeyId, $runCommand, $outputFile));

            return;
        }

        // run command
        shell_exec(sprintf('%s > %s 2>&1 & echo $!', $runCommand, $outputFile));
    }
}
