<?php

namespace app\admin\validate\asset;

use think\Validate;

class Plans extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
    'item_id'                   => 'require|number|gt:0',
    'type'                      => 'require|in:one_time,recurring',
    'currency'                  => 'require|length:3',
    'default_payment_method_id' => 'require|number|gt:0',
    'one_time_price'            => 'checkOneTimePrice',
    'purchase_date'             => 'checkPurchaseDate',
    'recurring_price'           => 'checkRecurringPrice',
    'billing_cycle'             => 'checkBillingCycle',
    'billing_day'               => 'checkBillingDay',
    'start_date'                => 'checkStartDate',
    'end_date'                  => 'checkEndDate',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'item_id.require'                   => '请选择条目',
        'item_id.number'                    => '条目格式不正确',
        'item_id.gt'                        => '条目格式不正确',
        'type.require'                      => '请选择类型',
        'type.in'                           => '类型不正确',
        'currency.require'                  => '请选择货币',
        'currency.length'                   => '货币格式不正确',
        'default_payment_method_id.require' => '请选择默认支付方式',
        'default_payment_method_id.number'  => '默认支付方式格式不正确',
        'default_payment_method_id.gt'      => '默认支付方式格式不正确',
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['item_id', 'type', 'currency', 'default_payment_method_id', 'one_time_price', 'purchase_date', 'recurring_price', 'billing_cycle', 'billing_day', 'start_date', 'end_date'],
        'edit' => ['item_id', 'type', 'currency', 'default_payment_method_id', 'one_time_price', 'purchase_date', 'recurring_price', 'billing_cycle', 'billing_day', 'start_date', 'end_date'],
    ];

    protected function checkOneTimePrice($value, $rule, $data)
    {
        if (($data['type'] ?? '') !== 'one_time') {
            return true;
        }
        if ($value === '' || $value === null) {
            return '一次性计划必须填写单次费用';
        }
        return is_numeric($value) && (float)$value > 0 ? true : '单次费用必须大于0';
    }

    protected function checkPurchaseDate($value, $rule, $data)
    {
        if (($data['type'] ?? '') !== 'one_time') {
            return true;
        }
        if ($value === '' || $value === null) {
            return '一次性计划必须填写购买时间';
        }
        return $this->isDate($value) ? true : '购买时间格式不正确';
    }

    protected function checkRecurringPrice($value, $rule, $data)
    {
        if (($data['type'] ?? '') !== 'recurring') {
            return true;
        }
        if ($value === '' || $value === null) {
            return '订阅计划必须填写订阅费用';
        }
        return is_numeric($value) && (float)$value > 0 ? true : '订阅费用必须大于0';
    }

    protected function checkBillingCycle($value, $rule, $data)
    {
        if (($data['type'] ?? '') !== 'recurring') {
            return true;
        }
        return in_array($value, ['monthly', 'yearly'], true) ? true : '订阅计划必须选择月付或年付';
    }

    protected function checkBillingDay($value, $rule, $data)
    {
        if (($data['type'] ?? '') !== 'recurring' || ($data['billing_cycle'] ?? '') !== 'monthly') {
            return true;
        }
        if ($value === '' || $value === null) {
            return '月付订阅必须填写扣款日期';
        }
        if (!is_numeric($value)) {
            return '扣款日期必须是数字';
        }
        $day = (int)$value;
        return ($day >= 1 && $day <= 31) ? true : '扣款日期必须在1到31之间';
    }

    protected function checkStartDate($value, $rule, $data)
    {
        if (($data['type'] ?? '') !== 'recurring') {
            return true;
        }
        if ($value === '' || $value === null) {
            return '订阅计划必须填写开始日期';
        }
        return $this->isDate($value) ? true : '开始日期格式不正确';
    }

    protected function checkEndDate($value, $rule, $data)
    {
        if (($data['type'] ?? '') !== 'recurring') {
            return true;
        }
        if ($value === '' || $value === null) {
            return '订阅计划必须填写结束日期';
        }
        if (!$this->isDate($value)) {
            return '结束日期格式不正确';
        }
        if (!$this->isDate($data['start_date'] ?? '')) {
            return true;
        }
        return strtotime($value) >= strtotime($data['start_date']) ? true : '结束日期不能早于开始日期';
    }

    protected function isDate($value)
    {
        if (!is_string($value) || $value === '') {
            return false;
        }
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }
    
}
