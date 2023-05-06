<?php

namespace Oro\Bundle\MessageQueueBundle\Log\Formatter;

use Oro\Component\MessageQueue\Client\Config;

class DmesgConsoleFormatter extends LegacyConsoleFormatter
{
    const SIMPLE_FORMAT = "%start_tag%%level_name%:%end_tag%%empty%" .
    "<comment>[<bg=default;options=bold>%memory_usage%</>%channel%|%processor%]</comment> "
    . "%message% %data% %context%\n";

    protected $terminalDimensions;

    /**
     * {@inheritdoc}
     */
    public function format(array $record): string
    {

        [$colums] = $this->getTerminalDimensions();

        if (isset($record['extra']['message_properties'][Config::PARAMETER_TOPIC_NAME])) {
            $record['processor'] = $record['extra']['message_properties'][Config::PARAMETER_TOPIC_NAME];
        } else {
            $record['processor'] = 'unknown';
        }

        if (isset($record['extra']['memory_usage'])) {
            $record['memory_usage'] = $record['extra']['memory_usage'];
            $length = 9 - strlen($record['memory_usage']);
            $record['memory_usage'] .= $length > 0 ? str_repeat(' ', $length) : '';
        } else {
            $record['memory_usage'] = 'na   ';
        }

        $length = 8 - strlen($record['level_name']);
        $record['empty'] = $length > 0 ? str_repeat(' ', $length) : '';

        $output = parent::format($record);
        $clear = preg_replace('/<[\s\w\/;=]+>/i', '', $output);

        $len = strlen($clear);
        $origLen = strlen($output);
        if ($colums !== null && $len > $colums && in_array($record['level_name'], ['DEBUG', 'INFO', 'NOTICE'])) {
            $output = substr($output, 0, ($origLen - $len) + $colums -1) . PHP_EOL;
        }

        return $output;

    }

    private function getTerminalDimensions()
    {
        if ($this->terminalDimensions) {
            return $this->terminalDimensions;
        }

        $this->terminalDimensions = $this->getSttyColumns();

        return $this->terminalDimensions;
    }


    private function getSttyColumns()
    {
        if (!function_exists('proc_open')) {
            return [null, null];
        }

        $sttyString = '';
        $descriptorspec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open('stty -a | grep columns', $descriptorspec, $pipes, null, null, ['suppress_errors' => true]);
        if (is_resource($process)) {
            $sttyString = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }

        // extract [w, h] from "rows h; columns w;"
        if (preg_match('/rows.(\d+);.columns.(\d+);/i', $sttyString, $matches)) {
            return  [(int) $matches[2], (int) $matches[1]];
        }
        // extract [w, h] from "; h rows; w columns"
        if (preg_match('/;.(\d+).rows;.(\d+).columns/i', $sttyString, $matches)) {
            return [(int) $matches[2], (int) $matches[1]];
        }

        return [null, null];
    }
}
//
