<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use app\common\model\CommPosts as PostModel;
use app\common\model\CommComments as CommentModel;
use app\common\model\CommReply as ReplyModel;
use app\common\model\Doctor;
use app\common\model\MessageUser;
use app\common\model\Report as ReportModel;
use app\common\model\User;

class Post extends Base
{

    /**
     * 查看热门推荐列表
     *
     * @return \think\response\Json
     */
    public function index()
    {
        # 获取参数，验证参数
        $requestData = $this->selectParam(['group_type', 'tab_type']);
        $requestData['tab_type'] = $requestData['tab_type'] ? trim($requestData['tab_type']) : 'all';
        $this->check($requestData, 'Post.index');

        # 查询置顶帖
        if ($this->pageIndex == 1 && $requestData['tab_type'] == 'all') {
            $topPostWhereMap = [
                'is_deleted' => 'no',
                'is_for_hospital' => '0',
                'group_type' => $requestData['group_type'],
                'is_top' => '1',
            ];
            $topList = PostModel::build()->selectWithUserInfo($topPostWhereMap);
            $this->addRenderData('topList', $topList, false);
        }

        # 构造查询条件
        $whereMap = [
            'is_deleted' => 'no',
            'is_for_hospital' => '0',
            'group_type' => $requestData['group_type'],
        ];
        switch ($requestData['tab_type']) {
            case 'all':
                $whereMap['is_top'] = '0';
                break;
            case 'newest':
                break;
            case 'exp':
                $whereMap['post_type'] = 'exp';
                break;
            case 'help':
                $whereMap['post_type'] = 'help';
                break;
            case 'best':
                $whereMap['is_best'] = '1';
                break;
        }

        # 查询帖子列表
        $postList = PostModel::build()->selectWithUserInfo($whereMap, $this->pageIndex, $this->pageSize);

        $this->addRenderData('postList', $postList, false);

        return $this->getRenderJson();
    }

    /**
     * 添加帖子(包括医院帖)
     *
     * @return \think\response\Json
     */
    public function store()
    {
        # 获取参数
        $requestData = $this->selectParam([
            'post_type', 'group_type', 'title', 'content', 'is_for_hospital', 'hospital_id'
        ]);
        $requestData['post_type'] = trim($requestData['post_type']); // 'exp'会被框架转换成'exp '

        # 验参
        $this->check($requestData, 'Post.store');

        # 保存数据
        $postModel = new PostModel();
        $requestData['user_id'] = $this->getUserId();
        $result = $postModel->store($requestData);
        if ($result === false) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "store fail: " . $postModel->getError());
            return $this->getRenderJson();
        }

        $this->addRenderData('post', $postModel);
        return $this->getRenderJson();
    }

    /**
     * 查看帖子详情
     *
     * @return \think\response\Json
     */
    public function show()
    {
        # 获取参数并验参
        $postId = $this->getParam('post_id');
        $this->checkSingle($postId, 'post_id', 'Post.show');

        # 指定需要的字段
        $postModelField = [
            'post_id', 'user_id', 'post_type', 'group_type', 'title', 'content',
            'views_count', 'comments_count', 'create_time', 'is_top', 'is_doctor'
        ];
        $commentField = [
            'comment_id','content', 'reply_count', 'create_time', 'user_id',
        ];
        $whereMap = [
            'post_id' => $postId,
            'is_deleted' => 'no',
        ];

        # 取出帖子详情
        $postModel = PostModel::build()->field($postModelField)->where($whereMap)->find();
        if (empty($postModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到了');
            return $this->addRenderData('info', "post not found, post_id: $postId");
        }

        # 阅读数量加1
        PostModel::build()->where($whereMap)->inc('views_count')->update();

        # 取相应的用户数据
        if ($postModel['is_doctor']) {
            $postModel['user_info'] = Doctor::build()->field(Doctor::doctorMiniField())->find($postModel['user_id']);
        } else {
            $postModel['user_info'] =  User::build()->field(User::userMiniField())->find($postModel['user_id']);
        }

        # 取相应的评论数据
        $commentModelList = CommentModel::build()
            ->field($commentField)
            ->where($whereMap)
            ->order(['create_time' => 'desc'])
            ->page($this->pageIndex, $this->pageSize)
            ->select();
        $commentModelList = CommentModel::addReplyModel($commentModelList);
        $postModel['comments_list'] = CommentModel::addUserInfo($commentModelList);

        $this->addRenderData('post', $postModel);

        return $this->getRenderJson();
    }

    /**
     * 查看医院帖子列表
     *
     * @return \think\response\Json
     */
    public function hospitalIndex()
    {
        # 获取参数并验参
        $hospitalId = $this->getParam('hospital_id');
        $this->checkSingle($hospitalId, 'hospital_id_req', 'Post.hospital');

        # 构造查询条件
        $whereMap = [
            'is_deleted' => 'no',
            'is_for_hospital' => '1',
            'hospital_id' => $hospitalId,
        ];
        # 查询数据
        $postList = PostModel::build()->selectWithUserInfo($whereMap, $this->pageIndex, $this->pageSize);

        $this->addRenderData('postList', $postList, false);
        return $this->getRenderJson();
    }

    /**
     * 查看帖子评论列表
     *
     * @return \think\response\Json
     */
    public function comments()
    {
        # 获取参数并验参
        $postId = $this->getParam('post_id');
        $this->checkSingle($postId, 'id', 'Base.id');

        # 获取数据
        $whereMap = [
            'post_id' => $postId,
            'is_deleted' => 'no',
        ];
        $commentList = CommentModel::build()->selectWithUserInfo($whereMap, $this->pageIndex, $this->pageSize);
        $commentList = CommentModel::addReplyModel($commentList);

        $this->addRenderData('commentList', $commentList, false);

        return $this->getRenderJson();
    }

    /**
     * 新建帖子评论
     *
     * @return \think\response\Json
     */
    public function commentStore()
    {
        # 获取参数并验参
        $requestData = $this->selectParam(['post_id', 'content']);
        $this->check($requestData, 'Comment.store');

        # 保存数据
        $commentModel = new CommentModel();
        $requestData['user_id'] = $this->getUserId();
        $result = $commentModel->store($requestData);

        if ($result === false) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "store fail: " . $commentModel->getError());
            return $this->getRenderJson();
        }

        $commentModel['user_info'] = $this->getUserModel()->visible(User::userMiniField())->toArray();

        $this->addRenderData('comment', $commentModel);

        # 推送消息给发帖人
        $userModel = $this->getUserModel();
        $postModel = PostModel::get($requestData['post_id']);
        $messageContent = "帖子有新回复";
        $exInfo = [
            'name' => $userModel['nick_name'],
            'gender' => $userModel['gender'],
            'avatar' => $userModel['avatar'],
            'type' => 'post',
            'post_id' => $requestData['post_id'],
            'event_id' => $requestData['post_id'],
            'sub_type' => '帖子'
        ];
        MessageUser::pushSystemMessage($postModel['user_id'], $messageContent, $exInfo, $requestData['post_id']);
        return $this->getRenderJson();
    }

    /**
     * 获取回复列表
     *
     * @return \think\response\Json
     */
    public function replies()
    {
        # 获取参数并验参
        $commentId = $this->getParam('comment_id');
        $this->checkSingle($commentId, 'id', 'Base.id');


        # 获取数据
        $commentModel = CommentModel::build()->findWithUserInfo($commentId);
        if (empty($commentModel)) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            return $this->getRenderJson();
        }
        $this->addRenderData('comment', $commentModel);

        # 获取数据
        $whereMap = [
            'comment_id' => $commentId,
            'is_deleted' => 'no',
        ];
        $replyList = ReplyModel::build()->selectReplies($whereMap, $this->pageIndex, $this->pageSize);

        $this->addRenderData('replyList', $replyList, false);

        return $this->getRenderJson();
    }

    /**
     * 新建回复
     * @return \think\response\Json
     */
    public function replyStore()
    {
        # 获取参数并验参
        $requestData = $this->selectParam(['comment_id', 'content', 'to_user_id', 'to_username']);
        $this->check($requestData, 'Comment.reply_store');

        # 保存数据
        $replyModel = new ReplyModel();
        $requestData['from_user_id'] = $this->getUserModel()['user_id'];
        $requestData['from_username'] = $this->getUserModel()['nick_name'];
        $result = $replyModel->store($requestData);

        if ($result === false) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "store fail: " . $replyModel->getError());
            return $this->getRenderJson();
        }

        $this->addRenderData('reply', $replyModel);

        # 推送消息给发帖人
        $userModel = $this->getUserModel();
        $commentModel = CommentModel::get($requestData['comment_id']);
        $messageContent = "评论有新回复";
        $exInfo = [
            'name' => $userModel['nick_name'],
            'gender' => $userModel['gender'],
            'avatar' => $userModel['avatar'],
            'type' => 'comment',
            'post_id' => $commentModel['post_id'],
            'comment_id' => $commentModel['comment_id'],
            'event_id' => $commentModel['comment_id'],
            'sub_type' => '评论'
        ];
        MessageUser::pushSystemMessage($requestData['to_user_id'], $messageContent, $exInfo, $commentModel['comment_id']);

        return $this->getRenderJson();
    }

    /**
     * 删除回复
     * @return \think\response\Json
     */
    public function replyDestroy()
    {
        $replyId = $this->getParam('reply_id');
        $this->checkSingle($replyId, 'id', 'Base.id');

        # 获取数据
        $whereMap = [
            'reply_id' => $replyId,
            'from_user_id' => $this->getUserId(),
        ];
        $updateData = [
            'is_deleted' => 'yes',
        ];
        $model = ReplyModel::build()->where($whereMap)->find();
        if (!empty($model) && $model['is_deleted'] == 'no') {
            $updateResult = $model->isUpdate(true)->allowField(true)->save($updateData);
            CommentModel::build()->where(['comment_id' => $model['comment_id']])->dec('reply_count')->update();
        }

        return $this->getRenderJson();
    }

    /**
     * 举报帖子或评论
     */
    public function report()
    {
        # 获取参数并验参
        $requestData = $this->selectParam(['report_type', 'report_type_id', 'reason']);
        $this->check($requestData, 'Report.store');

        # 保存数据
        $requestData['user_id'] = $this->getUserId();
        $reportModel = new ReportModel();
        $result = $reportModel->store($requestData);

        if ($result === false) {
            $this->setRenderCode(500);
            $this->setRenderMessage('网络异常');
            $this->addRenderData('info', "store fail: " . $reportModel->getError());
            return $this->getRenderJson();
        }

        $this->addRenderData('report', $reportModel);

        return $this->getRenderJson();
    }

    /**
     * 热门话题列表
     * @return \think\response\Json
     */
    public function topIndex()
    {
        # 查询帖子列表
        $field = [
            'post_id', 'title', 'post_type', 'group_type',
            'views_count', 'comments_count', 'is_top', 'is_doctor', 'create_time'
        ];
        $whereMap = [
            'is_top' => '1',
            'is_deleted' => 'no',
            'is_for_hospital' => '0',
        ];
        $orderMap = [ 'create_time' => 'desc'];
        $postModelList = PostModel::build()
            ->field($field)->where($whereMap)->order($orderMap)->page($this->pageIndex, $this->pageSize)->select();

        $this->addRenderData('postList', $postModelList, false);

        return $this->getRenderJson();
    }

    public function destroyPost()
    {
        # 获取参数并验参
        $postId = $this->getParam('post_id');
        $this->checkSingle($postId, 'post_id', 'Post.show');

        $whereMap = [
            'post_id' => $postId,
            'user_id' => $this->getUserId(),
            'is_deleted' => 'no',
        ];

        $postModel = PostModel::build()->where($whereMap)->find();

        if (empty($postModel)) {
            $this->setRenderCode(404);
            $this->setRenderMessage('找不到该帖子');
            return $this->getRenderJson();
        }

        $postModel->isUpdate(true)->save([ 'is_deleted' => 'yes' ]);

        $this->setRenderMessage('已删除');
        return $this->getRenderJson();
    }

    public function destroyComment()
    {
        # 获取参数并验参
        $commentId = $this->getParam('comment_id');
        $this->checkSingle($commentId, 'post_id', 'Post.show');

        $whereMap = [
            'comment_id' => $commentId,
            'user_id' => $this->getUserId(),
            'is_deleted' => 'no',
        ];

        $commentModel = CommentModel::build()->where($whereMap)->find();

        if (empty($commentModel)) {
            $this->setRenderCode(404);
            $this->setRenderMessage('找不到该帖子');
            return $this->getRenderJson();
        }

        $commentModel->isUpdate(true)->save([ 'is_deleted' => 'yes' ]);

        $this->setRenderMessage('已删除');
        return $this->getRenderJson();
    }

    public function destroyReply()
    {
        # 获取参数并验参
        $replyId = $this->getParam('reply_id');
        $this->checkSingle($replyId, 'post_id', 'Post.show');

        $whereMap = [
            'reply_id' => $replyId,
            'from_user_id' => $this->getUserId(),
            'is_deleted' => 'no',
        ];

        $replyModel = ReplyModel::build()->where($whereMap)->find();

        if (empty($replyModel)) {
            $this->setRenderCode(404);
            $this->setRenderMessage('找不到该帖子');
            return $this->getRenderJson();
        }

        $replyModel->isUpdate(true)->save([ 'is_deleted' => 'yes' ]);

        $this->setRenderMessage('已删除');
        return $this->getRenderJson();
    }
}
