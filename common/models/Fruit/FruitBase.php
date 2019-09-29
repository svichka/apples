<?php


namespace common\models\Fruit;


use common\exception\CantEatException;
use common\exception\IncorrectValueException;
use common\exception\IncorrectActionException;
use yii\db\ActiveRecord;
/**
 * Fruit model
 *
 * @property integer $id
 * @property string $color
 * @property integer $created_at
 * @property integer $fallen_at
 * @property integer $position_state
 * @property float $size
 *
 */
abstract class FruitBase extends ActiveRecord
{
	const DEFAULT_SIZE = 100;
	const DECAYED_TIME = "+5 hours";

	/**
	 *
	 */
	public function init()
	{
		if ($this->isNewRecord) {
			$this->created_at = time();
			$this->fallen_at = 0;
			$this->size = self::DEFAULT_SIZE;
		}
		parent::init();
	}

	/**
	 * @return bool
	 */
	protected function isDecayed() {
		return $this->fallen_at > 0 && strtotime(self::DECAYED_TIME, $this->fallen_at) < time();
	}


	public function eat(float $percent) {
		if (0 === $this->fallen_at) {
			throw new CantEatException('Can not eat fruit on the tree');
		}

		if ($this->isDecayed()) {
			throw new CantEatException('Can not eat decayed fruit');
		}

		$percent = (float)$percent;
		if ($percent > self::DEFAULT_SIZE || $percent < 0) {
			throw new IncorrectValueException('Incorrect percentage');
		}
		$this->size = $this->size - $percent * $this->size / self::DEFAULT_SIZE;
	}

	/**
	 * @throws \IncorrectActionException
	 */
	public function fallToGround() {
		if ($this->fallen_at > 0) {
			throw new IncorrectActionException('Apple had been already fallen');
		}
		$this->fallen_at = time();
	}

	/**
	 * @return float
	 */
	public function getSize() {
		return round($this->size / self::DEFAULT_SIZE, 2);
	}
}