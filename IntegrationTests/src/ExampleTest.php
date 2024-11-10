<?php declare(strict_types=1);

namespace PetStore\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class ExampleTest
 *
 * @author  Zsolt Döme
 * @since   2024
 */
final class ExampleTest extends TestCase
{
    public function testExample(): void
    {
        HttpRequestTester::get('/')
            ->assertResponseStatusCode(200)
            ->test();

        HttpRequestTester::get('api/v1/pet/1')
            ->assertResponseStatusCode(200)
            ->test();

        self::assertTrue(true);
    }
}
