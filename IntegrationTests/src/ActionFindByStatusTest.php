<?php declare(strict_types=1);

namespace PetStore\Tests;

use PetStore\Tests\Data\Pet;

/**
 * Class ActionFindByStatusTest
 *
 * @package PetStore\Tests
 * @author  Zsolt Döme
 * @since   2024
 */
final class ActionFindByStatusTest extends ATest
{
    public function test_StatusNotSupplied_ShouldReturn_400(): void
    {
        HttpRequestTester::get('/pet/findByStatus')
            ->assertResponseStatusCode(400)
            ->test();
    }

    public function test_InvalidStatusValue_ShouldReturn_400(): void
    {
        HttpRequestTester::get('/pet/findByStatus?status=')
            ->assertResponseStatusCode(400)
            ->test();
    }

    public function test_ZeroExistingPets_ShouldReturn_200_AndZeroPets(): void
    {
        HttpRequestTester::get('/pet/findByStatus?status=available')
            ->assertResponseStatusCode(200)
            ->assertResponseJsonArray(Pet::class, [])
            ->test();
    }

    public function test_OnePetWithStatusAvailable_ShouldReturn_200_AndOnePet(): void
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

        // Test
        HttpRequestTester::get('/pet/findByStatus?status=available')
            ->assertResponseStatusCode(200)
            ->assertResponseJsonArray(Pet::class, [$pet])
            ->test();

        // Cleanup
        $this->deleteCategory($categoryId);
        $this->deleteTag($tagId);
        HttpRequestTester::delete('/pet/' . $petId)
            ->assertResponseStatusCode(200)
            ->test();
    }

    public function test_TwoPetWithStatusAvailable_ShouldReturn_200_AndTwoPets(): void
    {
        // Preparation
        $categoryId = 1;
        $tagId = 1;
        $pet1Id = 1;
        $pet2Id = 2;

        $pet1 = new Pet();
        $pet1->id = $pet1Id;
        $pet1->name = 'Dog 1';
        $pet1->status = 'available';
        $pet1->category = $this->createCategory($categoryId, 'Dogs');
        $pet1->tags = [$this->createTag($tagId, 'Black')];

        $pet2 = new Pet();
        $pet2->id = $pet2Id;
        $pet2->name = 'Dog 2';
        $pet2->status = 'available';
        $pet2->category = $this->createCategory($categoryId, 'Dogs');
        $pet2->tags = [$this->createTag($tagId, 'Black')];
        HttpRequestTester::post('/pet')
            ->json($pet1)
            ->assertResponseStatusCode(200)
            ->test();
        HttpRequestTester::post('/pet')
            ->json($pet2)
            ->assertResponseStatusCode(200)
            ->test();

        // Test
        HttpRequestTester::get('/pet/findByStatus?status=available')
            ->assertResponseStatusCode(200)
            ->assertResponseJsonArray(Pet::class, [$pet1, $pet2])
            ->test();

        // Cleanup
        $this->deleteCategory($categoryId);
        $this->deleteTag($tagId);
        HttpRequestTester::delete('/pet/' . $pet1Id)
            ->assertResponseStatusCode(200)
            ->test();
        HttpRequestTester::delete('/pet/' . $pet2Id)
            ->assertResponseStatusCode(200)
            ->test();
    }

    public function test_TwoPetPetsOneWithStatusAvailable_ShouldReturn_200_AndOnePet(): void
    {
        // Preparation
        $categoryId = 1;
        $tagId = 1;
        $pet1Id = 1;
        $pet2Id = 2;

        $pet1 = new Pet();
        $pet1->id = $pet1Id;
        $pet1->name = 'Dog 1';
        $pet1->status = 'available';
        $pet1->category = $this->createCategory($categoryId, 'Dogs');
        $pet1->tags = [$this->createTag($tagId, 'Black')];

        $pet2 = new Pet();
        $pet2->id = $pet2Id;
        $pet2->name = 'Dog 2';
        $pet2->status = 'not-available';
        $pet2->category = $this->createCategory($categoryId, 'Dogs');
        $pet2->tags = [$this->createTag($tagId, 'Black')];
        HttpRequestTester::post('/pet')
            ->json($pet1)
            ->assertResponseStatusCode(200)
            ->test();
        HttpRequestTester::post('/pet')
            ->json($pet2)
            ->assertResponseStatusCode(200)
            ->test();

        // Test
        HttpRequestTester::get('/pet/findByStatus?status=available')
            ->assertResponseStatusCode(200)
            ->assertResponseJsonArray(Pet::class, [$pet1])
            ->test();

        // Cleanup
        $this->deleteCategory($categoryId);
        $this->deleteTag($tagId);
        HttpRequestTester::delete('/pet/' . $pet1Id)
            ->assertResponseStatusCode(200)
            ->test();
        HttpRequestTester::delete('/pet/' . $pet2Id)
            ->assertResponseStatusCode(200)
            ->test();
    }
}