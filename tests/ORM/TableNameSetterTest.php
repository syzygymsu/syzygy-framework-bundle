<?php

namespace Syzygy\FrameworkBundle\Tests\ORM;

class TableNameSetterTest extends \PHPUnit_Framework_TestCase {
	/** @var \Syzygy\FrameworkBundle\ORM\TableNameSetter */
	protected $setter;

	public function __construct() {
		$this->setter = new \Syzygy\FrameworkBundle\ORM\TableNameSetter();
	}

	public function reverseFilter($x) {
		return strrev($x);
	}

	public function testCallbackGeneration() {
		/**
		 * Using closure
		 */
		$this->setter->setCallback(function($tableName) {
			return 'just_a_const_string_here';
		});
		$this->assertEquals('just_a_const_string_here', $this->setter->encodeTableName('some_table'));

		/**
		 * Using function name
		 */
		$this->setter->setCallback('strtoupper');
		$this->assertEquals('ANOTHER_TABLE', $this->setter->encodeTableName('another_table'));

		/**
		 * Using method
		 */
		$this->setter->setCallback(array($this, 'reverseFilter'));
		$this->assertEquals('elbat_erom_emos', $this->setter->encodeTableName('some_more_table'));
	}

	public function testPatternGeneration() {
		$this->setter->setPattern('asd_%s_qwe');
		$this->assertEquals('asd_table-one_qwe', $this->setter->encodeTableName('table-one'));

		$this->setter->setPattern('%1$sqwe');
		$this->assertEquals('table-twoqwe', $this->setter->encodeTableName('table-two'));
	}

	public function testAffixGeneration() {
		$this->setter->setPattern('%1$sqwe');
		$this->assertEquals('table-twoqwe', $this->setter->encodeTableName('table-two'));
	}

}
