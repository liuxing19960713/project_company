<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/15/16
 * Time: 15:03
 */

namespace app\web\logic;

use app\web\model\CustomerCoupon;
use app\web\model\Doctor;
use app\web\model\Follow;
use think\Exception;

class FollowLogic
{
    /**
     * 添加关注
     * @param $cid
     * @param $did
     *
     * @return array
     */
    public function addFollow($cid, $did)
    {
        $follow = new Follow();
        // 检查是否已经关注过
        $res = $follow->where('c_id', $cid)->where('d_id', $did)->find();
        // 没关注过就关注, 关注过直接返回
        if (empty($res['f_id']) || $res['f_id'] == null) {
            $follow->c_id = $cid;
            $follow->d_id = $did;
            $follow->allowField(true)->save();
            $res = $follow;

            // 医生的粉丝数量加1
            $docotr = new Doctor();
            $res2 = $docotr->where('d_id', $did)->setInc('follower_count', '1');
            if (!$res2) {
                // todo: 错误处理
                // controller调用层并没有处理返回数据
            }
        }
        return $res->visible()->toArray();
    }

    /**
     * 取消关注
     * @param $cid
     * @param $did
     *
     * @return array
     */
    public function deleteFollow($cid, $did)
    {
        try {
            $follow = new Follow();
            // 检查是否已经关注过
            $res = $follow->where('c_id', $cid)->where('d_id', $did)->delete();
            // 没关注过就关注, 关注过直接返回
            // 医生的粉丝数量减1
            $docotr = new Doctor();
            $res2 = $docotr->where('d_id', $did)->setDec('follower_count', '1');

            return $res&&$res2;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取关注列表
     * @param     $cid
     * @param int $page
     * @param int $num
     *
     * @return array
     */
    public function getList($cid, $page = 1, $num = 20)
    {
        $page = "{$page}, {$num}";
        $order = 'f.create_time DESC';
        $field = [
            'f.create_time',
            'f.d_id',
            'd.real_name',
            'd.avatar',
            'd.title',
            'd.good_at',
            'd.image_price',
            'd.service_times',
            'd.area1',
            'd.area2',
            'de.hospital',
            'de.department1',
            'de.department2'
        ];
        $whereQuery = [
            'f.c_id' => $cid,
            'de.is_default' => 'yes'
        ];

        $follow = new Follow();

        // 联表查询, follow、doctor、department 表
        $list = $follow->alias('f')->field($field)
            ->join('__DOCTOR__ d', 'd.d_id = f.d_id')
            ->join('__DEPARTMENT__ de', 'f.d_id = de.d_id')
            ->where($whereQuery)->order($order)->page($page)->select();

        $res = array();
        foreach ($list as $k => $v) {
            $res[] = $v->visible()->toArray();
        }
        return $res;
    }

    public
    function getFollower(
        $did
    ) {

    }

    /**
     * 检查已关注
     * @param $cid
     * @param $did
     *
     * @return array
     */
    public
    function checkFollow(
        $cid,
        $did
    ) {
        $data = [];
        $follow = new Follow();
        // 检查已关注
        $res = $follow->where('c_id', $cid)->where('d_id', $did)->find();
        if (empty($res['f_id']) || $res['f_id'] == null) {
            $data['followed'] = 'no';
        } else {
            $data['followed'] = 'yes';
        }

        // 检查优惠券是否已获取
        $customerCoupon = new CustomerCoupon();
        // 扫描优惠券
        $res1 = $customerCoupon->alias('cc')
            ->join("__COUPON__ co", "co.co_id = cc.co_id")
            ->where("cc.c_id", $cid)
            ->where("cc.d_id", $did)
            ->where("co.type", "扫描")
            ->find();
        if (empty($res1)) {
            $data['gotScanCoupon'] = 'no';
        } else {
            $data['gotScanCoupon'] = 'yes';
        }
        // 分享优惠券
        $res2 = $customerCoupon->alias('cc')
            ->join("__COUPON__ co", "co.co_id = cc.co_id")
            ->where("cc.c_id", $cid)
            ->where("cc.d_id", $did)
            ->where("co.type", "分享")
            ->find();
        if (empty($res2)) {
            $data['gotShareCoupon'] = 'no';
        } else {
            $data['gotShareCoupon'] = 'yes';
        }

        return $data;
    }
}
