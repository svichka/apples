<?php

namespace common\tests\unit\models;

use common\exception\CantEatException;
use common\exception\IncorrectValueException;
use common\models\Fruit\Apple;

class AppleTest extends \Codeception\Test\Unit
{
	public function testEatOnTree()
	{
		$apple = new Apple();
		$this->expectException(CantEatException::class);
		$apple->eat(40);
	}

	public function testEatIncorrectPercent()
	{
		$apple = new Apple();
		$this->expectException(IncorrectValueException::class);
		$apple->fallToGround();
		$apple->eat(-40);
	}

	public function testEatDecayed()
	{
		$apple = new Apple();
		$apple->fallToGround();
		$apple->fallen_at = time() - strtotime("-6 hours");

		$this->expectException(CantEatException::class);
		$apple->eat(40);
	}
}
