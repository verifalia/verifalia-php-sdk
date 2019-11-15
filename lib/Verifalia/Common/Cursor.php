<?php

namespace Verifalia\Common {

    class Cursor
	{
		public $cursor;
		public $direction;
		public $limit = 0;

		public function __construct($cursor, $direction = Direction::FORWARD)
		{
			$this->cursor = $cursor;
			$this->direction = $direction;
		}
	}
}
