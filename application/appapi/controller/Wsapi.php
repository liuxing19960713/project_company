<?php
namespace app\appapi\controller;
//允许跨域请求该资源
header("Access-Control-Allow-Origin:*");
/**
* workerman外部API接口
* （查询当前连接的用户数量，向指定用户发送信息,向所有用户发送消息）
*/
class Wsapi {
    /**
     * @var string 目标用户id
     */
    protected $to_user = '';
    // function __construct($token) {
    //     //使用构造函数获取实例化类时传递的参数
    //     $this -> to_user = $token;
    // }  
 
    /**
     * @var string 推送服务地址
     */
    protected $push_api_url = 'http://0.0.0.0:2018/';
    
    /**
     * @var string 推送内容
     */
    protected $content = '';
 
    /**
     * 设置推送用户，若参数留空则推送到所有在线用户
     *
     * @param string $user
     * @return $this
     */
    public function setUser($user = '') {
        $this->to_user = $user ? : '';
        return $this;
    }
 
    /**
     * 设置推送内容
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content = '') {
        $this->content = $content;
        return $this;
    }
 
    /**
     * 推送
     */
    public function push() {
        $data = [
            'type' => 'publish',
            'content' => $this->content,
            'to' => $this->to_user,
        ];
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_URL, $this->push_api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $res = curl_exec($ch);
        curl_close($ch);
        dump($res);
 
    }
}
?>
