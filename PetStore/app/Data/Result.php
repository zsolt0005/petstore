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
 * @template T of object|null
 * @template R of object|null
 */
final class Result
{
    /**
     * Constructor.
     *
     * @param R|null $failure
     * @param T|null $success
     */
    public function __construct(private ?object $failure, private ?object $success)
    {
    }

    /**
     * Creates a new isntance of {@see self}.
     *
     * @template T1 of object|null
     * @template R1 of object|null
     *
     * @param R1|null $failure
     * @param T1|null $success
     *
     * @return Result<T1, R1>
     */
    public static function of(?object $failure = null, ?object $success = null): Result
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