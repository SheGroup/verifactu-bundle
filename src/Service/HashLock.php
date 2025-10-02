<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use Closure;
use SheGroup\VerifactuBundle\Exception\CannotAcquireLockException;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\Store\FlockStore;
use Throwable;

final class HashLock
{
    private const LOCK_NAME = 'she_group_verifactu';
    private const LOCK_TTL = 10.0;

    /** @throws Throwable */
    public function execute(Closure $closure)
    {
        $lock = $this->acquire();
        try {
            $result = $closure->__invoke();
        } catch (Throwable $e) {
            $lock->release();

            throw $e;
        }

        $lock->release();

        return $result;
    }

    /** @throws CannotAcquireLockException */
    private function acquire(): Lock
    {
        $lockStore = new FlockStore();
        $lockFactory = new Factory($lockStore);
        $lock = $lockFactory->createLock(self::LOCK_NAME, self::LOCK_TTL, false);
        if (!$lock->acquire(true)) {
            throw new CannotAcquireLockException();
        }

        return $lock;
    }
}
