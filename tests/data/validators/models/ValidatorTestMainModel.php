<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\data\validators\models;

use yii\tests\data\ar\ActiveRecord;

class ValidatorTestMainModel extends ActiveRecord
{
    public $testMainVal = 1;

    public static function tableName()
    {
        return 'validator_main';
    }

    public function getReferences()
    {
        return $this->hasMany(ValidatorTestRefModel::class, ['ref' => 'id']);
    }
}
