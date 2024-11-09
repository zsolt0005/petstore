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
		$this->configurator->enableTracy($this->rootDir . '/log');

        if(getenv('NETTE_DEBUG_MODE'))
        {
            $this->configurator->setDebugMode(true);
        }
        else
        {
            $this->configurator->setDebugMode(false);
        }

		$this->configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();
	}

	private function setupContainer(): void
	{
		$configDir = $this->rootDir . '/config';
		$this->configurator->addConfig($configDir . '/config.neon');
	}
}
