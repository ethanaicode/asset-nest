<?php

namespace app\admin\model\asset;

use think\Model;


class Plans extends Model
{

    

    

    // 表名
    protected $name = 'asset_plans';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'billing_cycle_text'
    ];
    

    
    public function getTypeList()
    {
        return ['one_time' => __('One_time'), 'recurring' => __('Recurring')];
    }

    public function getBillingCycleList()
    {
        return ['monthly' => __('Monthly'), 'yearly' => __('Yearly')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getBillingCycleTextAttr($value, $data)
    {
        $value = $value ?: ($data['billing_cycle'] ?? '');
        $list = $this->getBillingCycleList();
        return $list[$value] ?? '';
    }




}
