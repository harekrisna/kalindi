<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(['78.108.107.248',
							 '78.108.107.249',
							 '78.108.107.250', 
							 '78.108.107.251', 
							 '78.108.107.252', 
							 '78.108.107.253', 
							 '78.108.107.254', 
							 '78.108.107.255', 
							 '94.112.79.165', 
							 '77.236.208.61',
							 '89.103.182.50',
							 '212.20.99.146',
							 '89.103.182.26',
							 '89.103.183.74',
							 '94.112.86.211',
							]); // enable for your remote IP

$configurator->enableDebugger(__DIR__ . '/../log', "bh.majkl@gmail.com");

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
