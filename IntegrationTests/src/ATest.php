<?php declare(strict_types=1);

namespace PetStore\Tests;

use PetStore\Tests\Data\Category;
use PetStore\Tests\Data\Tag;
use PHPUnit\Framework\TestCase;

/**
 * Class ATest
 *
 * @package PetStore\Tests
 * @author  Zsolt Döme
 * @since   2024
 */
abstract class ATest extends TestCase
{
    /**
     * Creates a new tag and asserts 200 response.
     *
     * @param int $id
     * @param string $name
     *
     * @return Tag
     */
    protected function createTag(int $id, string $name): Tag
    {
        $tag = $this->prepareTagData($id, $name);

        HttpRequestTester::post('/tag')
            ->json($tag)
            ->assertResponseStatusCode(200)
            ->test();

        return $tag;
    }

    /**
     * Creates a new category and asserts 200 response.
     *
     * @param int $id
     * @param string $name
     *
     * @return Category
     */
    protected function createCategory(int $id, string $name): Category
    {
        $category = $this->prepareCategoryData($id, $name);

        HttpRequestTester::post('/category')
            ->json($category)
            ->assertResponseStatusCode(200)
            ->test();

        return $category;
    }

    /**
     * Deletes a  tag and asserts 200 response.
     *
     * @param int $id
     *
     * @return void
     */
    protected function deleteTag(int $id): void
    {
        HttpRequestTester::post('/tag/' . $id)
            ->assertResponseStatusCode(200)
            ->test();
    }

    /**
     * Deletes a category and asserts 200 response.
     *
     * @param int $id
     *
     * @return void
     */
    protected function deleteCategory(int $id): void
    {
        HttpRequestTester::post('/category/' . $id)
            ->assertResponseStatusCode(200)
            ->test();

    }

    /**
     * Prepares a category data.
     *
     * @param int $id
     * @param string $name
     *
     * @return Category
     */
    protected function prepareCategoryData(int $id, string $name): Category
    {
        $category = new Category();
        $category->id = $id;
        $category->name = $name;
        return $category;
    }

    /**
     * Prepares a tag data.
     *
     * @param int $id
     * @param string $name
     *
     * @return Tag
     */
    protected function prepareTagData(int $id, string $name): Tag
    {
        $tag = new Tag();
        $tag->id = $id;
        $tag->name = $name;
        return $tag;
    }
}