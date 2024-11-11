<?php declare(strict_types=1);

namespace PetStore\Tests;

use PetStore\Tests\Data\FileUploadResponse;
use PetStore\Tests\Data\Pet;

/**
 * Class ActionUploadImageTest
 *
 * @package PetStore\Tests
 * @author  Zsolt Döme
 * @since   2024
 */
final class ActionUploadImageTest extends TestsBase
{
    public function test_NoFilesUploaded_ShouldReturn_400(): void
    {
        HttpRequestTester::post('/pet/1/uploadImage')
            ->assertResponseStatusCode(400)
            ->assertResponseJsonData(FileUploadResponse::class, null)
            ->test();
    }

    public function test_NonExistingPet_ShouldReturn_404(): void
    {
        $files = [
            ['name' => 'image', 'type' => 'png', 'path' => 'images/dog1.png']
        ];

        HttpRequestTester::post('/pet/1/uploadImage')
            ->files($files)
            ->assertResponseStatusCode(404)
            ->assertResponseJsonData(FileUploadResponse::class, null)
            ->test();
    }

    public function test_InvalidFile_ShouldReturn_404(): void
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
        $files = [
            ['name' => 'image', 'type' => 'png', 'path' => 'images/notAnImage.txt']
        ];

        HttpRequestTester::post('/pet/1/uploadImage')
            ->files($files)
            ->assertResponseStatusCode(400)
            ->assertResponseJsonData(FileUploadResponse::class, null)
            ->test();

        // Cleanup
        $this->deleteCategory($categoryId);
        $this->deleteTag($tagId);
        HttpRequestTester::delete('/pet/' . $petId)
            ->assertResponseStatusCode(200)
            ->test();
    }

    public function test_OneInvalidFileFromTwo_ShouldReturn_404(): void
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
        $files = [
            ['name' => 'image', 'type' => 'png', 'path' => 'images/dog1.png'],
            ['name' => 'image', 'type' => 'png', 'path' => 'images/notAnImage.txt']
        ];

        HttpRequestTester::post('/pet/1/uploadImage')
            ->files($files)
            ->assertResponseStatusCode(400)
            ->assertResponseJsonData(FileUploadResponse::class, null)
            ->test();

        // Cleanup
        $this->deleteCategory($categoryId);
        $this->deleteTag($tagId);
        HttpRequestTester::delete('/pet/' . $petId)
            ->assertResponseStatusCode(200)
            ->test();
    }

    public function test_OneValidImage_ShouldReturn_200_AndPetHasImageUrl(): void
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
        $files = [
            ['name' => 'image', 'type' => 'png', 'path' => 'images/dog1.png']
        ];

        HttpRequestTester::post('/pet/1/uploadImage')
            ->files($files)
            ->assertResponseStatusCode(200)
            ->assertResponseJsonData(FileUploadResponse::class, null)
            ->test();

        $pet->photoUrls = [
            'images/1/1.png'
        ];
        HttpRequestTester::get('/pet/' . $petId)
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

    public function test_TwoValidImage_ShouldReturn_200_AndPetHasImageUrls(): void
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
        $files = [
            ['name' => 'image', 'type' => 'png', 'path' => 'images/dog1.png'],
            ['name' => 'image2', 'type' => 'png', 'path' => 'images/dog2.png']
        ];

        HttpRequestTester::post('/pet/1/uploadImage')
            ->files($files)
            ->assertResponseStatusCode(200)
            ->assertResponseJsonData(FileUploadResponse::class, null)
            ->test();

        $pet->photoUrls = [
            'images/1/1.png',
            'images/1/2.png'
        ];
        HttpRequestTester::get('/pet/' . $petId)
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