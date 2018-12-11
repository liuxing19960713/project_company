<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\admin\model;

use think\Model;

class ProfileModel extends Model
{
    protected $table = 'yyb_consultation_profile';

    /**
     * @return ConsultationProfile
     */
    public static function build()
    {
        return new self();
    }

    /**
     * @param array $dataMap
     * @return int|string
     */
    public static function whereCount($dataMap = [])
    {
        return self::where($dataMap)->count();
    }
	
	
	/**
     * 根据文章的id 获取文章的信息
     * @param $id
     */
    public function getOneProfile($id)
    {
        return $this->where('cp_id', $id)->find();
    }
}
