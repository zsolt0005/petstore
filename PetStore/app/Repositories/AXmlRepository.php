<?php declare(strict_types=1);

namespace PetStore\Repositories;

use Nette\IOException;
use Nette\Utils\FileSystem;
use PetStore\Factories\SerializerFactory;
use Throwable;
use Tracy\Debugger;

/**
 * Class AXmlRepository
 *
 * @package PetStore\Repositories
 * @author  Zsolt Döme
 * @since   2024
 *
 * @template T of object
 */
abstract class AXmlRepository
{
    /**
     * Constructor.
     *
     * @param string $filePath
     * @param string $rootNodeName
     *
     * @throws IOException
     */
    public function __construct(private readonly string $filePath, private readonly string $rootNodeName)
    {
        $this->createDatabaseIfNotExists();

        $loadedData = $this->load();
        $this->setData($loadedData);
    }

    /**
     * Gets the data of the repository.
     *
     * @return T[]
     */
    abstract protected function getData(): array;

    /**
     * Sets the data that was loaded from the database file.
     *
     * @param T[] $data
     *
     * @return void
     */
    abstract protected function setData(array $data): void;

    /**
     * Gets the data type.
     *
     * @return class-string<T>
     */
    abstract protected function getDataType(): string;

    /**
     * Creates a new database file with an empty root node if not exists.
     *
     * @return void
     * @throws IOException
     */
    private function createDatabaseIfNotExists(): void
    {
        if(is_file($this->filePath))
        {
            return;
        }

        $serializer = SerializerFactory::buildSerializer();
        $xml = $serializer->serialize([], 'xml', [
            'xml_root_node_name' => $this->rootNodeName
        ]);

        FileSystem::write($this->filePath, $xml);
    }

    /**
     * Saves the repository data to the database file.
     *
     * @return void
     */
    protected function save(): void
    {
        $serializer = SerializerFactory::buildSerializer();
        $xml = $serializer->serialize($this->getData(), 'xml', [
            'xml_root_node_name' => $this->rootNodeName
        ]);

        try
        {
            FileSystem::write($this->filePath, $xml);
        }
        catch(IOException $e)
        {
            Debugger::log($e, Debugger::ERROR);
        }
    }

    /**
     * Load the data from the xml file.
     *
     * @return T[]
     */
    private function load(): array
    {
        if(!is_file($this->filePath))
        {
            return [];
        }

        try
        {
            $fileContents = FileSystem::read($this->filePath);
            $serializer = SerializerFactory::buildSerializer();

            /** @var T[] $mappedObject */
            $mappedObject = $serializer->deserialize($fileContents, $this->getDataType() . '[]', 'xml');
            return $mappedObject;
        }
        catch(Throwable)
        {
            return [];
        }
    }
}