<?php


namespace common\models\Fruit;


class Apple extends FruitBase
{
	public static function tableName()
	{
		return '{{%apple}}';
	}

	const COLORS = [
		'green',
		'red',
		'yellow',
	];

	public static function generateRandomApple() {
		$apple = new static();
		$time = time();

		$apple->color = static::COLORS[mt_rand(0, count(static::COLORS) - 1)];
		$apple->size = mt_rand(0, FruitBase::DEFAULT_SIZE);
		$apple->created_at = $time - mt_rand(0, $time);

		$fallen_ts = [0, mt_rand($apple->created_at, $time)];
		$apple->fallen_at = $fallen_ts[mt_rand(0, count($fallen_ts) - 1)];

		return $apple;
	}

}
