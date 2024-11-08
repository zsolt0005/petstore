<?php

declare(strict_types=1);

namespace PetStore;

use Nette;
use Nette\Bootstrap\Configurator;
use Nette\NotSupportedException;
use Nette\DI\Container;

class Bootstrap
{
	private Configurator $configurator;
	private string $rootDir;

	public function __construct()
	{
		$this->rootDir = dirname(__DIR__);
		$this->configurator = new Configurator;
		$this->configurator->setTempDirectory($this->rootDir . '/temp');

        if(getenv('NETTE_DEBUG_MODE'))
        {
            $this->configurator->setDebugMode(true); // enable for your remote IP
        }
        else
        {
            $this->configurator->setDebugMode(false); // disable
        }

        $this->configurator->enableTracy(__DIR__ . '/../log');
	}

    /**
     * @return Container
     * @throws NotSupportedException
     */
	public function bootWebApplication(): Nette\DI\Container
	{
		$this->initializeEnvironment();
		$this->setupContainer();
		return $this->configurator->createContainer();
	}

    /**
     * @return void
     * @throws NotSupportedException
     */
	public function initializeEnvironment(): void
	{
		//$this->configurator->setDebugMode('secret@23.75.345.200'); // enable for your remote IP
		$this->configurator->enableTracy($this->rootDir . '/log');

		$this->configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();
	}

	private function setupContainer(): void
	{
		$configDir = $this->rootDir . '/config';
		$this->configurator->addConfig($configDir . '/common.neon');
	}
}
