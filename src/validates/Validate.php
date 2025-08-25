<?php declare(strict_types=1);

namespace linron\thinkdto\validates;

use think\Model;

/**
 * 扩展验证器
 */
class Validate extends \think\Validate
{
    public function modelid($value, $rule, $data = []): bool
    {
        if($value > 0) {
            /** @var Model $model */
            $model = new $rule();
            if($model->withTrashed()->where($model->getPk(), '=', $value)->findOrEmpty()->isEmpty()){
                return false;
            }
            return true;
        }
        return true;
    }

    public function min($value, $rule, $data = []): bool
    {
        if($value < intval($rule)) {
            return false;
        }
        return true;
    }
}