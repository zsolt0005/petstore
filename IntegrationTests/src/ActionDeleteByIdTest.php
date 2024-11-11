<?php declare(strict_types=1);

namespace PetStore\Tests;

use PetStore\Tests\Data\Pet;

/**
 * Class ActionDeleteByIdTest
 *
 * @package PetStore\Tests
 * @author  Zsolt Döme
 * @since   2024
 */
final class ActionDeleteByIdTest extends ATest
{
    public function test_NonExistingPet_ShouldReturn_200(): void
    {
        HttpRequestTester::delete('/pet/1')
            ->assertResponseStatusCode(200)
            ->test();
    }

    public function test_ExistingPet_ShouldReturn_200(): void
    {
        // Preparation
        $categoryId = 1;
        $tagId = 1;
        $petId = 1;

        $pet = new Pet();
        $pet->id = $petId;
        $pet->name = 'Dog 1';
        $pet->status = 'available';
        $pet->category = $this->createCategory($categoryId, 'Dogs');
        $pet->tags = [$this->createTag($tagId, 'Black')];
        HttpRequestTester::post('/pet')
            ->json($pet)
            ->assertResponseStatusCode(200)
            ->test();

        // Assert pet exists before deletion
        HttpRequestTester::get('/pet/' . $petId)
            ->assertResponseStatusCode(200)
            ->assertResponseJsonData(Pet::class, $pet)
            ->test();

        // Test case
        HttpRequestTester::delete('/pet/' . $petId)
            ->assertResponseStatusCode(200)
            ->test();

        // Assert pet is deleted
        HttpRequestTester::get('/pet/' . $petId)
            ->assertResponseStatusCode(404)
            ->test();

        // Cleanup
        $this->deleteCategory($categoryId);
        $this->deleteTag($tagId);
    }
}