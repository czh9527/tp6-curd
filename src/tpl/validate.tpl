<?php

namespace app<namespace>validate;

use think\Validate;

class <model> extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = <rule>;
    /**
     * 提示消息
     */
    protected $message = <attributes>;
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => <adds>,
        'edit' => <edits>,
    ];
}