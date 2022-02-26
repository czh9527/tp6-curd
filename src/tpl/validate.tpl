<?php
/**
 * Author: <user>
 * Date: <date>
 * Time: <time>
 */
 
namespace app<namespace>validate;

use think\Validate;

class <model> extends Validate
{ <addValidate>
    /**
     * Notes: 验证规则
	 * Author: <user>
     */
    protected $rule = <rule>;
    /**
     * Notes: 提示消息
	 * Author: <user>
     */
    protected $message = <attributes>;
    /**
     * Notes: 验证场景
	 * Author: <user>
     */
    protected $scene = [
        'add'  => <adds>,
        'edit' => <edits>,
    ];
}