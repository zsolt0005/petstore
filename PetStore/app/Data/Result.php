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
 * @template R of object|null
 * @template T of mixed
 */
final class Result
{
    /**
     * Constructor.
     *
     * @param R $failure
     * @param T $success
     */
    public function __construct(private ?object $failure, private mixed $success)
    {
    }

    /**
     * Creates a new isntance of {@see self}.
     *
     * @template R1 of object|null
     * @template T1 of mixed
     *
     * @param R1 $failure
     * @param T1 $success
     *
     * @return Result<R1, T1>
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
     * @param callable(T): C $success
     * @param callable(R): C $failure
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
}