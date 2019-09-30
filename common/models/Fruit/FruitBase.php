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
 * @property float $size
 *
 */
abstract class FruitBase extends ActiveRecord
{
	const DEFAULT_SIZE = 100;
	const DECAYED_TIME = "+5 hours";

	protected $percentToEat;

	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			[['created_at', 'fallen_at'], 'number'],
			[['size'], 'double', 'min' => 0, 'max' => self::DEFAULT_SIZE],
			[['percentToEat'], 'double'],
			[['color'], 'string', 'max' => 64],
		];
	}

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
	 * @param bool $insert
	 * @return bool
	 * @throws CantEatException
	 * @throws IncorrectValueException
	 */
	public function beforeSave($insert) : bool
	{
		if (parent::beforeSave($insert)) {
			if (null !== $this->percentToEat) {
				$this->eat((float)$this->percentToEat);
			}

			if (0 === $this->fallen_at && $this->size < static::DEFAULT_SIZE) {
				$this->addError('content', 'Can not save with wrong data');
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function isDecayed() {
		return $this->fallen_at > 0 && strtotime(self::DECAYED_TIME, $this->fallen_at) < time();
	}


	/**
	 * @param float $percent
	 * @throws CantEatException
	 * @throws IncorrectValueException
	 */
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
	 * @throws IncorrectActionException
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
	public function getSize() : float {
		return round($this->size / self::DEFAULT_SIZE, 2);
	}

	/**
	 * @return array|ActiveRecord[]
	 */
	public static function getHaveNotEaten() {
		return self::find()->where('size > 0')->all();
	}

	/**
	 * @return float
	 */
	public function getPercentToEat() {
		return $this->percentToEat;
	}
}
