<?php declare(strict_types=1);

namespace PetStore\Tests;

use PetStore\Tests\Data\Pet;

/**
 * Class ActionPartialUpdateTest
 *
 * @package PetStore\Tests
 * @author  Zsolt Döme
 * @since   2024
 */
final class ActionPartialUpdateTest extends TestsBase
{
    public function test_NonExistingPet_ShouldReturn_404(): void
    {
        HttpRequestTester::post('/pet/1')
            ->assertResponseStatusCode(404)
            ->test();
    }

    public function test_ExistingPetWithNoData_ShouldReturn_200_AndStayUnchanged(): void
    {
        // Prepare
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
            ->assertResponseJsonData(Pet::class, $pet)
            ->test();

        // Test
        HttpRequestTester::post('/pet/' . $petId)
            ->assertResponseStatusCode(200)
            ->assertResponseJsonData(Pet::class, $pet)
            ->test();

        // Cleanup
        $this->deleteCategory($categoryId);
        $this->deleteTag($tagId);
        HttpRequestTester::delete('/pet/' . $petId)
            ->assertResponseStatusCode(200)
            ->test();
    }

    public function test_ExistingPetWithData_ShouldReturn_200_AndStayUnchanged(): void
    {
        // Prepare
        $categoryId = 1;
        $tagId = 1;
        $petId = 1;

        $pet = new Pet();
        $pet->id = $petId;
        $pet->name = 'Dog 1';
        $pet->status = 'available';
        $pet->category = $this->createCategory($categoryId, 'Dogs');
        $pet->tags = [$this->createTag(1, 'Black')];
        HttpRequestTester::post('/pet')
            ->json($pet)
            ->assertResponseStatusCode(200)
            ->assertResponseJsonData(Pet::class, $pet)
            ->test();

        // Test
        $pet->name = 'Dog 2';
        $pet->status = 'not-available';
        HttpRequestTester::post('/pet/' . $petId . '?name=Dog 2&status=not-available')
            ->assertResponseStatusCode(200)
            ->assertResponseJsonData(Pet::class, $pet)
            ->test();

        // Cleanup
        $this->deleteCategory($categoryId);
        $this->deleteTag($tagId);
        HttpRequestTester::delete('/pet/' . $petId)
            ->assertResponseStatusCode(200)
            ->test();
    }
}