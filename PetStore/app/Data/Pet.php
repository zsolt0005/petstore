<?php declare(strict_types=1);

namespace PetStore\Data;

use Symfony\Component\Serializer\Annotation\SerializedPath;

/**
 * Data for representing a pet.
 *
 * @package PetStore\Data
 * @author  Zsolt Döme
 * @since   2024
 */
final class Pet
{
    /** @var int ID. */
    public int $id = 0;

    /** @var string Name. */
    public string $name;

    /** @var Category Category */
    public Category $category;

    /** @var string[] Photo urls. */
    public array $photoUrls = [];

    /** @var Tag[] Tags. */
    #[SerializedPath('[tags][tag]')]
    public array $tags = [];

    /** @var string Status. */
    public string $status;

    /**
     * Custom setter for the photo URLs.
     *
     * @param string[] $photoUrls
     *
     * @return void
     */
    public function setPhotoUrls(array $photoUrls): void
    {
        // Hot fix for a bug where the serializer serializes empty arrays as empty item tag, and after de-serializations, an empty string is returned as the first item
        if(count($photoUrls) === 1 && empty($photoUrls[0]))
        {
            $this->photoUrls = [];
            return;
        }

        $this->photoUrls = $photoUrls;
    }
}