<?php

declare(strict_types=1);

namespace Oro\Component\Sys;

class MutexProcess implements MutexProcessInterface
{
    protected static $_PATH_PROC_SYSV_SEM = '/proc/sysvipc/sem';

    /// https://github.com/torvalds/linux/blob/master/include/uapi/linux/sem.h
    protected static $_LINUX_SEM_H_GETVAL = 12;

    /**
     * @var \SysvSemaphore|null
     */
    protected $resource;

    /**
     * @var bool
     */
    protected $isLocked = false;

    /**
     * @var object
     */
    protected static $ffiSemctl;

    /**
     * {@inheritdoc}
     */
    public function lock(string|int $processName/*, $args$*/): bool
    {
        if ($this->isLocked) {
            return true;
        }

        if (func_num_args() > 1) {
            foreach (\func_get_args() as $arg) {
                $processName .= $arg;
            }
        }

        $key = is_numeric($processName) ? (int) $processName : static::genKey($processName);
        $this->resource = \sem_get($key);
        if (false === $this->resource) {
            throw new ProcessLockerException("Unable to get System V Semaphore");
        }

        // as function like sem_has does not exist, we shoule use @ to avoid notice
        return $this->isLocked = @\sem_acquire($this->resource, true);
    }

    /**
     * {@inheritdoc}
     */
    public function unlock(): void
    {
        if ($this->isLocked === false || !$this->resource) {
            return;
        }

        if (\sem_release($this->resource)) {
            @\sem_remove($this->resource);
            $this->resource = null;
        }
    }

    public static function getIpcSemaphores(): array
    {
        $list = @file_get_contents(static::$_PATH_PROC_SYSV_SEM);
        if (empty($list)) {
            return [];
        }

        $list = array_filter(explode("\n", $list));
        $headers = trim(array_shift($list));

        $semaphores = array_map(fn($line) => preg_split("/\s+/", trim($line)), $list);
        $headers = preg_split("/\s+/", $headers);

        return array_map(fn($line) => array_combine($headers, $line), $semaphores);
    }

    public static function isLockedSem(int $semId): bool
    {
        return static::getSemValue($semId) === 0;
    }

    public static function isLockedProcess(string|int $processName, bool $throw = false): bool
    {
        if (null === ($semId = static::getSemaphoreId($processName))) {
            return false;
        }

        try {
            return static::isLockedSem($semId);
        } catch (\Throwable $e) {
            if (true === $throw) {
                throw $e;
            }
            return false;
        }
    }

    public static function getSemaphoreId(string|int $key): ?int
    {
        $key = is_numeric($key) ? $key : static::genKey($key);
        $semaphores = static::getIpcSemaphores();

        foreach ($semaphores as $semaphore) {
            if ((int)($semaphore['key'] ?? 0) === $key) {
                return (int) $semaphore['semid'];
            }
        }

        return null;
    }

    public static function getSemValue(int $semId): int
    {
        static::$ffiSemctl ??= \FFI::cdef("
#include <sys/sem.h>
int semctl(int semid, int semnum, int cmd, ...);
", "libc.so.6");

        $value = static::$ffiSemctl->semctl($semId, 0, static::$_LINUX_SEM_H_GETVAL);

        return is_int($value) ? $value : -1;
    }

    public static function genKey(string $processName): int
    {
        return \array_values(\unpack('n', \sha1($processName, true)))[0];
    }

    // Auto release semaphore
    public function __destruct()
    {
        //Fix Warning: sem_get(): failed for key 0x610157b8: No space left on device
        if ($this->resource && $this->isLocked) {
            @\sem_remove($this->resource);
        }
        $this->resource = null;
    }
}
