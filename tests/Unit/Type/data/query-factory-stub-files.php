<?php

namespace QueryFactoryStubFile;

use Psr\Container\ContainerInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryFactory;

class Model extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity { }

use function PHPStan\Testing\assertType;

function (): void {
	/** @var ConfigurationManager $configurationManager */
	$configurationManager = null;
	/** @var DataMapFactory $dataMapFactory */
	$dataMapFactory = null;
	/** @var ContainerInterface $containerInterface */
	$containerInterface = null;

	$queryFactory = new QueryFactory($configurationManager, $dataMapFactory, $containerInterface);
	$createResult = $queryFactory->create(Model::class);

	assertType('TYPO3\CMS\Extbase\Persistence\QueryInterface<QueryFactoryStubFile\Model>', $createResult);
	assertType(Model::class, $createResult->getType());
};