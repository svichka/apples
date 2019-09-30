<?php


namespace common\models\Fruit;


class Apple extends FruitBase
{
	const STR_TO_GENERATE = '-10 hours';

	/**
	 * @return string
	 */
	public static function tableName()
	{
		return '{{%apple}}';
	}

	const COLORS = [
		'green',
		'red',
		'yellow',
	];

	/**
	 * @return Apple
	 */
	public static function generateRandomApple() {
		$apple = new static();
		$time = time();

		$apple->color = static::COLORS[mt_rand(0, count(static::COLORS) - 1)];

		$sizes = [Apple::DEFAULT_SIZE, mt_rand(0, FruitBase::DEFAULT_SIZE)];
		$apple->size = $sizes[mt_rand(0, count($sizes) - 1)];
		$apple->created_at = $time - mt_rand(0, $time - strtotime(Apple::STR_TO_GENERATE));

		$apple->fallen_at = 0;
		if ($apple->size < FruitBase::DEFAULT_SIZE) {
			$apple->fallen_at = mt_rand($apple->created_at, $time);
		}

		return $apple;
	}

}
