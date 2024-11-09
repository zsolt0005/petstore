<?php declare(strict_types=1);

namespace PetStore\Factories;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class SerializerFactory
 *
 * @package PetStore\Factories
 * @author  Zsolt Döme
 * @since   2024
 */
final class SerializerFactory
{
    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Builds a new JSON and XML serializer.
     *
     * @return Serializer
     */
    public static function buildSerializer(): Serializer
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [
            new ArrayDenormalizer(),
            new ObjectNormalizer(
                classMetadataFactory: new ClassMetadataFactory(new AttributeLoader()),
                propertyTypeExtractor: new PhpDocExtractor()
            )
        ];

        return new Serializer($normalizers, $encoders);
    }
}