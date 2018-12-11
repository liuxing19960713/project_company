<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use app\common\model\CommPosts;
use app\common\model\User as UserModel;
use app\common\model\UserFollow;

class User extends Base
{
    public function info()
    {
        $userId = $this->getParam('user_id');
        $this->checkSingle($userId, 'id', 'Base.id');

        $userModel = UserModel::get($userId);
        if (empty($userModel)) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "can't get user model");
            return $this->getRenderJson();
        }

        $userInfoField = [
            'user_id', 'nick_name', 'avatar', 'gender', 'province', 'city',
             'fans_count', 'follow_count', 'tube_stage', 'easemob_username'
        ];
        $userInfoArray = $userModel->visible($userInfoField)->toArray();

        $is_followed = UserFollow::isFollowed($this->getUserId(), $userId);
        $userInfoArray['is_followed'] = $is_followed;

        $this->addRenderData('user_info', $userInfoArray);

        return $this->getRenderJson();
    }

    public function posts()
    {
        $userId = $this->getParam('user_id');
        $this->checkSingle($userId, 'id', 'Base.id');

        $postType = trim($this->getParam('post_type', 'normal'));
        $this->checkSingle($postType, 'post_type', 'Post.post_type');

        $whereMap = [
            'user_id' => $userId,
            'post_type' => $postType,
            'is_deleted' => 'no',
            'is_for_hospital' => '0',
        ];
        $postModelList = CommPosts::build()
            ->field(CommPosts::defaultPostModelField())
            ->where($whereMap)
            ->order(CommPosts::defaultOrderMap())
            ->page($this->pageIndex, $this->pageSize)
            ->select();
        $this->addRenderData('posts', $postModelList, false);

        $userModel = UserModel::build()->field(UserModel::userMiniField())->find($userId);
        $this->addRenderData('user_info', $userModel);

        return $this->getRenderJson();
    }
}
