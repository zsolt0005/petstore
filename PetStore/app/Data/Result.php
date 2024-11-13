<?php declare(strict_types=1);

namespace PetStore\Data;

use InvalidArgumentException;

/**
 * Class Result
 *
 * @package PetStore\Data
 * @author  Zsolt Döme
 * @since   2024
 *
 * @template E of object|null
 * @template S of mixed
 */
final class Result
{
    /**
     * Constructor.
     *
     * @param E $failure
     * @param S $success
     */
    public function __construct(private ?object $failure, private mixed $success)
    {
    }

    /**
     * Creates a new isntance of {@see self}.
     *
     * @template E1 of object|null
     * @template S1 of mixed
     *
     * @param E1 $failure
     * @param S1 $success
     *
     * @return Result<E1, S1>
     */
    public static function of(?object $failure = null, mixed $success = null): Result
    {
        return new self($failure, $success);
    }

    /**
     * Match the result.
     *
     * @template C
     *
     * @param callable(S): C $success
     * @param callable(E): C $failure
     *
     * @return C
     *
     * @throws InvalidArgumentException
     */
    public function match(callable $success, callable $failure): mixed
    {
        if($this->success !== null)
        {
            return $success($this->success);
        }

        if($this->failure !== null)
        {
            return $failure($this->failure);
        }

        throw new InvalidArgumentException('Both success and failure are empty');
    }

    /**
     * Match the result.
     *
     * @param callable(S|null): void $success
     * @param callable(E|null): void $failure
     *
     * @return void
     */
    public function matchAll(callable $success, callable $failure): void
    {
        $success($this->success);
        $failure($this->failure);
    }
}