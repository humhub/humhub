<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\queue\driver;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;
use Symfony\Component\Process\Process;
use yii\queue\db\Command;


/**
 * Class MySQLCommand
 * @package humhub\modules\queue\driver
 */
class MySQLCommand extends Command
{

    /**
     * Temporary version, fixes a problem with spaces in the PHP Path.
     *
     * {inheritdoc}
     */
    protected function handleMessage($id, $message, $ttr, $attempt)
    {
        // Command: php yii queue/exec "id" "ttr" "attempt" "pid"
        $command = [
            $this->phpBinary,
            $_SERVER['SCRIPT_FILENAME'],
            $this->uniqueId . '/exec',
            $id,
            $ttr,
            $attempt,
            $this->queue->getWorkerPid(),
        ];

        // Forward passed options to queue/exec command
        foreach ($this->getPassedOptions() as $name) {
            if (in_array($name, $this->options('exec'), true)) {
                $command[] = '--' . $name . '=' . $this->$name;
            }
        }

        // Add color command
        if (!in_array('color', $this->getPassedOptions(), true)) {
            $command[] = '--color=' . $this->isColorEnabled();
        }

        $process = new Process($command, null, null, $message, $ttr);
        try {
            $result = $process->run(function ($type, $buffer) {
                if ($type === Process::ERR) {
                    $this->stderr($buffer);
                } else {
                    $this->stdout($buffer);
                }
            });
            if (!in_array($result, [self::EXEC_DONE, self::EXEC_RETRY])) {
                throw new ProcessFailedException($process);
            }
            return $result === self::EXEC_DONE;
        } catch (ProcessRuntimeException $error) {
            $job = $this->queue->serializer->unserialize($message);
            return $this->queue->handleError($id, $job, $ttr, $attempt, $error);
        }
    }

}
