<?php

namespace Verifalia\Common {

	use Verifalia\Common\Direction;

	class Cursor
	{
		public $cursor;
		public $direction;
		public $limit = 0;

		function __construct(string $cursor, Direction $direction = Direction::FORWARD)
		{
			$this->cursor = $cursor;
			$this->direction = $direction;
		}
	}
}
