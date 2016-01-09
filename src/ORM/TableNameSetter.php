<?php
namespace Syzygy\FrameworkBundle\ORM;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class TableNameSetter implements \Doctrine\Common\EventSubscriber {
	protected $filter = null;

	/**
	 * @param callback $filter Function to generate new table name
	 * @throws \InvalidArgumentException
	 */
	public function setCallback($filter) {
		if(!is_callable($filter)) {
			throw new \InvalidArgumentException('$filter must be callable');
		}
		$this->filter = $filter;
	}

	/**
	 * Use given pattern to generate table name using sprintf function.
	 * Original name will be first argument for sprintf.
	 * @param string $pattern Pattern for sprintf.
	 */
	public function setPattern($pattern) {
		$this->filter = function($tableName) use($pattern) {
			return sprintf($pattern, $tableName);
		};
	}

	public function setIdentity() {
		$this->filter = null;
	}

	/**
	 * @param string $prefix
	 * @param string $suffix
	 */
	public function setAffix($prefix, $suffix='') {
		$this->filter = function($tableName) use($prefix, $suffix) {
			return $prefix. $tableName. $suffix;
		};
	}

	public function encodeTableName($tableName) {
		if(is_null($this->filter)) {
			return $tableName;
		}

		return call_user_func($this->filter, $tableName);
	}

	public function getSubscribedEvents() {
		return array('loadClassMetadata');
	}

	public function loadClassMetadata(LoadClassMetadataEventArgs $args) {
		$classMetadata = $args->getClassMetadata();
		$classMetadata->setPrimaryTable(array(
			'name' => $this->encodeTableName( $classMetadata->getTableName() ),
		));

		foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
			if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY) {
				$mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
				$classMetadata->associationMappings[$fieldName]['joinTable']['name'] =  $this->encodeTableName( $mappedTableName );
			}
		}
	}

}
