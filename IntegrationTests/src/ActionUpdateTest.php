<?php declare(strict_types=1);

namespace PetStore\Tests;

use PetStore\Tests\Data\Pet;

/**
 * Class ActionUpdateTest
 *
 * @package PetStore\Tests
 * @author  Zsolt Döme
 * @since   2024
 */
final class ActionUpdateTest extends ATest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function provideTest_InvalidInput_ShouldReturn_405Data(): array
    {
        return [
            'Empty name' => [1, '', 'available'],
            'Empty status' => [1, 'Dog 1', '']
        ];
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $status
     *
     * @return void
     * @dataProvider provideTest_InvalidInput_ShouldReturn_405Data
     */
    public function test_InvalidInput_ShouldReturn_405(int $id, string $name, string $status): void
    {
        $pet = new Pet();
        $pet->id = $id;
        $pet->name = $name;
        $pet->status = $status;
        $pet->category = $this->prepareCategoryData(1, 'Dogs');
        $pet->tags = [$this->prepareTagData(1, 'Black')];

        HttpRequestTester::put('/pet')
            ->json($pet)
            ->assertResponseStatusCode(405)
            ->test();
    }

    public function test_InvalidId_ShouldReturn_400(): void
    {
        $pet = new Pet();
        $pet->id = 0;
        $pet->name = 'Dog 1';
        $pet->status = 'available';
        $pet->category = $this->prepareCategoryData(1, 'Dogs');
        $pet->tags = [$this->prepareTagData(1, 'Black')];

        HttpRequestTester::put('/pet')
            ->json($pet)
            ->assertResponseStatusCode(400)
            ->test();
    }

    public function test_NonExistingCategory_ShouldReturn_405(): void
    {
        $pet = new Pet();
        $pet->id = 1;
        $pet->name = 'Dog 1';
        $pet->status = 'available';
        $pet->category = $this->prepareCategoryData(1, 'Dogs');
        $pet->tags = [$this->prepareTagData(1, 'Black')];

        HttpRequestTester::put('/pet')
            ->json($pet)
            ->assertResponseStatusCode(405)
            ->test();
    }

    public function test_NonExistingTag_ShouldReturn_405(): void
    {
        $categoryId = 1;

        $pet = new Pet();
        $pet->id = 1;
        $pet->name = 'Dog 1';
        $pet->status = 'available';
        $pet->category = $this->createCategory($categoryId, 'Dogs');
        $pet->tags = [$this->prepareTagData(1, 'Black')];

        HttpRequestTester::put('/pet')
            ->json($pet)
            ->assertResponseStatusCode(405)
            ->test();

        // Cleanup
        $this->deleteCategory($categoryId);
    }

    public function test_OneNonExistingTagOfTwo_ShouldReturn_405(): void
    {
        $categoryId = 1;
        $tagId = 1;

        $pet = new Pet();
        $pet->id = 1;
        $pet->name = 'Dog 1';
        $pet->status = 'available';
        $pet->category = $this->createCategory($categoryId, 'Dogs');
        $pet->tags = [$this->createTag($tagId, 'Black'), $this->prepareTagData(2, 'Red')];

        HttpRequestTester::put('/pet')
            ->json($pet)
            ->assertResponseStatusCode(405)
            ->test();

        // Cleanup
        $this->deleteCategory($categoryId);
        $this->deleteTag($tagId);
    }

    public function test_NonExistingPet_ShouldReturn_404(): void
    {
        $categoryId = 1;
        $tagId = 1;
        $petId = 1;

        $pet = new Pet();
        $pet->id = $petId;
        $pet->name = 'Dog 1';
        $pet->status = 'available';
        $pet->category = $this->createCategory($categoryId, 'Dogs');
        $pet->tags = [$this->createTag(1, 'Black')];

        HttpRequestTester::put('/pet')
            ->json($pet)
            ->assertResponseStatusCode(404)
            ->test();

        // Cleanup
        $this->deleteCategory($categoryId);
        $this->deleteTag($tagId);
    }

    public function test_ExistingPetAndValidUpdateData_ShouldReturn_200(): void
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
        HttpRequestTester::put('/pet')
            ->json($pet)
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