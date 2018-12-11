<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\common\validate;

use think\Validate;

class Comment extends Validate
{
    protected $rule = [
        'post_id' => 'require|number|gt:0',
        'user_id' => 'require|number|gt:0',
        'content' => 'require|max:750',
        'comment_id' => 'require|number|gt:0',
        'from_user_id' => 'require|number|gt:0',
        'to_user_id' => 'require|number|gt:0',
    ];

    protected $message = [
        'post_id' => 'id参数不正确',
        'user_id' => 'id参数不正确',
        'content.require' => '内容不能为空',
        'content.max' => '内容太长了',
        'comment_id' => 'id参数不正确',
        'from_user_id' => 'id参数不正确',
        'to_user_id' => 'id参数不正确',
    ];

    protected $scene = [
        'store' => ['post_id', 'content'],
        'model_store' => ['post_id', 'content', 'user_id'],
        'reply_store' => ['comment_id', 'content', 'to_user_id'],
        'model_reply_store' => ['comment_id', 'content', 'from_user_id', 'to_user_id'],
    ];
}
