<?php

namespace Syzygy\FrameworkBundle\Entity;

abstract class BaseEntity {
	static function getClassName() {
		return get_called_class();
	}
}
