<?php

namespace app\admin\model\asset;

use think\Model;


class Transactions extends Model
{

    

    

    // 表名
    protected $name = 'asset_transactions';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'status_text'
    ];
    

    
    public function getTypeList()
    {
        return ['expense' => __('Expense'), 'income' => __('Income'), 'refund' => __('Refund'), 'transfer' => __('Transfer')];
    }

    public function getStatusList()
    {
        return ['pending' => __('Pending'), 'success' => __('Success'), 'failed' => __('Failed')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }




}
