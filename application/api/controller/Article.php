<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use app\common\model\ArticleNewest;
use app\common\model\ArticleSuccess;
use think\Db;


class Article extends Base
{
    /**
     * 获取成功案例列表
     * @return \think\response\Json
     */
    public function successIndex()
    {
        $field = [
            'as_id', 'title', 'img_url', 'views_count', 'create_time'
        ];
        $whereMap = [
            'is_deleted' => 'no',
        ];
        $orderMap = [
            'create_time' => 'desc'
        ];
        $articleModelList = ArticleSuccess::build()
            ->field($field)
            ->where($whereMap)
            ->order($orderMap)
            ->page($this->pageIndex, $this->pageSize)
            ->select();

        $this->addRenderData('article_list', $articleModelList, false);
        return $this->getRenderJson();
    }

    /**
     * 成功案例详情
     * @return \think\response\Json
     */
    public function successShow()
    {
        $id = $this->getParam('as_id');
        $this->checkSingle($id, 'id', 'Base.id');

        $field = [
            'as_id', 'title', 'img_url', 'content', 'views_count', 'create_time'
        ];
        $whereMap = [
            'as_id' => $id,
            'is_deleted' => 'no',
        ];
        $articleModel = ArticleSuccess::build()->field($field)->where($whereMap)->find();
        if (empty($articleModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到了');
            $this->addRenderData('info', "article not found, article_id: $id");
            return $this->getRenderJson();
        }

        # 阅读数量加1
        ArticleSuccess::build()->where($whereMap)->inc('views_count')->update();

        $this->addRenderData('article', $articleModel);

        return $this->getRenderJson();
    }

    /**
     * 获取最新资讯列表
     * @return \think\response\Json
     */
    public function newestIndex()
    {
        $field = [
            'an_id', 'title', 'img_url', 'views_count', 'create_time'
        ];
        $whereMap = [
            'is_deleted' => 'no',
        ];
        $orderMap = [
            'create_time' => 'desc'
        ];
        $articleModelList = ArticleNewest::build()
            ->field($field)
            ->where($whereMap)
            ->order($orderMap)
            ->page($this->pageIndex, $this->pageSize)
            ->select();

        $this->addRenderData('article_list', $articleModelList, false);
        return $this->getRenderJson();
    }

    /**
     * 最新资讯详情
     * @return \think\response\Json
     */
    public function newestShow()
    {
        $id = $this->getParam('an_id');
        $this->checkSingle($id, 'id', 'Base.id');

        $field = [
            'an_id', 'title', 'img_url', 'video_url', 'content', 'views_count', 'create_time'
        ];
        $whereMap = [
            'an_id' => $id,
            'is_deleted' => 'no',
        ];
        $articleModel = ArticleNewest::build()->field($field)->where($whereMap)->find();
        if (empty($articleModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到了');
            $this->addRenderData('info', "article not found, article_id: $id");
            return $this->getRenderJson();
        }

        # 阅读数量加1
        ArticleNewest::build()->where($whereMap)->inc('views_count')->update();

        $this->addRenderData('article', $articleModel);

        return $this->getRenderJson();
    }


    public function temp()
    {
        //todo:: CDN地址修改导致的产物，留着防止下次遇到同样的问题
        $old = 'cdn2.youyunbb.com';
        $new = 'cdn.uyihui.cn';
        $array = array(
            ['sql' => 'article_increase', 'field' => ['content', 'img_url', 'video_url'], 'key' => 'in_id'],
            ['sql' => 'article_success ', 'field' => ['content', 'img_url'], 'key' => 'as_id'],
            ['sql' => 'article_newest ', 'field' => ['content', 'img_url', 'video_url'], 'key' => 'an_id'],
            ['sql' => 'banner_doctor', 'field' => ['img_url'], 'key' => 'bad_id'],
            ['sql' => 'banner_user', 'field' => ['img_url'], 'key' => 'bau_id'],
            ['sql' => 'comm_posts', 'field' => ['content'], 'key' => 'post_id'],
            ['sql' => 'doctor', 'field' => ['avatar'], 'key' => 'doctor_id'],
            ['sql' => 'event', 'field' => ['doctor_avatar', 'video_url'], 'key' => 'event_id'],
            ['sql' => 'tips', 'field' => ['img_url'], 'key' => 'tip_id'],
            ['sql' => 'user', 'field' => ['avatar'], 'key' => 'user_id']
        );
       foreach ($array as $key0=>$value0){
           $model = Db::name($array[$key0]['sql']);
           $data = $model->select();
           foreach ($data as $key => $value) {
               foreach ($array[$key0]['field'] as $key1 => $value1) {
                   $data[$key][$value1] = str_replace($old, $new, $value[$value1]);
               }
               $bool = $model->where([$array[$key0]['key']=>$value[$array[$key0]['key']]])->update($data[$key]);
               $bool++;
           }
           echo $bool;
       }
    }
}
