<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use think\facade\App;
use think\facade\Db;

class Setting extends BaseController
{
    

    public function initialize()
    {
        parent::initialize();
       
    }

     /**
     * 广告列表
     */
    public function adsList()
    {
        if (!$this->access)  exit('无此访问权限！');

        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['ads_title'])) $whereCond[] = array('ads_title','like','%'.$data['ads_title'].'%');
        if (!empty($data['ads_pos'])) $whereCond[] = array('ads_pos','=',$data['ads_pos']);
        if (!empty($data['is_show'])) $whereCond[] = array('is_show','=',$data['is_show']);
        
        //搜索默认值
        $return_data['ads_title']     = empty($data['ads_title'])?'':$data['ads_title'];
        $return_data['ads_pos']     = empty($data['ads_pos'])?'':$data['ads_pos'];
        $return_data['is_show']     = empty($data['is_show'])?'':$data['is_show'];

        //获取列表
        $data_list = Db::name('yphp_system_ads')->where($whereCond)->order('id', 'desc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ))->each(function($item, $key){

            $item['pos_name'] = Db::name('yphp_system_ads_pos')->where("id",$item['ads_pos'])->value('pos_name');
            return $item;
        });
        
        $return_data['pos_list']    = Db::name('yphp_system_ads_pos')->order('id', 'desc')->select();
        $return_data['data_list']   = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("system/ads_list",$return_data);
    }
    /**
     * 广告删除
     */
    public function adsDel()
    {
        if (!$this->access)  return json(array('status'=>'FAIL','msg'=>'无此访问权限！')); 

        $id  = request()->param('id');

       if (!empty($id)) 
       {
           Db::name('yphp_system_ads')->where("id",$id)->delete();
           return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
       }
    }
    /**
     * 添加广告
     */
    public function adsAdd()
    {
        if (!$this->access)  exit('无此访问权限！');

       //获取广告位
       $pos_list = Db::name('yphp_system_ads_pos')->order('id', 'desc')->select();
       
       return view("system/ads_add",array('pos_list'=>$pos_list));
    }
    /**
     * 修改广告
     */
    public function adsEdit()
    {
        if (!$this->access)  exit('无此访问权限！');

       $id = request()->param('id');

       $pos_list = Db::name('yphp_system_ads_pos')->order('id', 'desc')->select();

       $info = Db::name('yphp_system_ads')->where('id',$id)->find();

        return view("system/ads_edit",array('info'=>$info,'pos_list'=>$pos_list));
    }

    /**
     *  添加广告操作
     */
    public function adsAddAct()
    {
        $data = request()->param();

    
        if(empty($data['id']))
       {

        $data['add_datetime'] = date("Y-m-d H:i:s");

         $id = Db::name('yphp_system_ads')->strict(false)->insertGetId($data);
         if ($id > 0) 
         {
             return json(array('status'=>'SUCCESS','msg'=>'添加成功'));
         }
         else
         {
            return json(array('status'=>'FAIL','msg'=>'添加失败'));
         }
       }
       else
       {
         //修改
         Db::name('yphp_system_ads')->strict(false)->update($data);
         return json(array('status'=>'SUCCESS','msg'=>'修改成功！'));
       }
    }

    /**
     * 广告位列表
     */
    public function adsPosList()
    {
        if (!$this->access)  exit('无此访问权限！');

        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['pos_name'])) $whereCond[] = array('pos_name','like','%'.$data['pos_name'].'%');
        
        //搜索默认值
        $return_data['pos_name']     = empty($data['pos_name'])?'':$data['pos_name'];
      

        //获取列表
        $data_list = Db::name('yphp_system_ads_pos')->where($whereCond)->order('id', 'desc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ))->each(function($item, $key){

            $item['ads_count'] = Db::name('yphp_system_ads')->where("ads_pos",$item['id'])->count();
            return $item;
        });


        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("system/ads_pos_list",$return_data);
    }

    /**
     * 删除广告位
     */
    public function adsPosDel()
    {
        if (!$this->access)  return json(array('status'=>'FAIL','msg'=>'无此访问权限！')); 

       $id  = request()->param('id');

       if (!empty($id)) 
       {

          $count = Db::name('yphp_system_ads')->where("ads_pos",$id)->count();
          if ($count > 0) 
          {
              return json(array('status'=>'FAIL','msg'=>'删除失败，该广告位还有'.$count.'个广告'));
          }
          else
          {
            Db::name('yphp_system_ads_pos')->where("id",$id)->delete();
            return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
          }
       }
    }

    /**
     * 添加广告位
     */
    public function adsPosAdd()
    {
       if (!$this->access)  exit('无此访问权限！');

       return view("system/ads_pos_add");
    }
    /**
     * 编辑广告位
     */
    public function adsPosEdit()
    {

        if (!$this->access)  exit('无此访问权限！');

       $id = request()->param('id');
       $info = Db::name('yphp_system_ads_pos')->where('id',$id)->find();

        return view("system/ads_pos_edit",array('info'=>$info));
    }
    /**
     * 添加/修改广告位
     */
    public function adsPosAddAct()
    {
       $data = request()->param();

       if(empty($data['id']))
       {
         $id = Db::name('yphp_system_ads_pos')->strict(false)->insertGetId($data);
         if ($id > 0) 
         {
             return json(array('status'=>'SUCCESS','msg'=>'添加成功'));
         }
         else
         {
            return json(array('status'=>'FAIL','msg'=>'添加失败'));
         }
       }
       else
       {
         //修改
         Db::name('yphp_system_ads_pos')->strict(false)->update($data);
         return json(array('status'=>'SUCCESS','msg'=>'修改成功！'));
       }
    }

    /**
     * 系统设置
     */
    public function systemSetting()
    {

     if (!$this->access)  exit('无此访问权限！');

       $id = 1;
       $info = Db::name('yphp_system_setting')->where('id',$id)->find();

        return view("system/system_setting",array('info'=>$info));
    }
    /**
     * 系统设置保存
     */
    public function systemSettingAct()
    {
        if (!$this->access)  return json(array('status'=>'FAIL','msg'=>'无此访问权限！')); 

         $data = request()->param();
         //修改
         Db::name('yphp_system_setting')->strict(false)->update($data);
         return json(array('status'=>'SUCCESS','msg'=>'修改成功！'));
    }

    /**
     * 公告管理
     */
    public function systemNoticeList()
    {
        if (!$this->access)  exit('无此访问权限！');

        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );
        //搜索条件
        $whereCond = array();
        if (!empty($data['notice_title'])) $whereCond[] = array('notice_title','like','%'.$data['notice_title'].'%');
        if (!empty($data['notice_status'])) $whereCond[] = array('notice_status','=',$data['notice_status']);
        
        //搜索默认值
        $return_data['notice_title']     = empty($data['notice_title'])?'':$data['notice_title'];
        $return_data['notice_status'] = empty($data['notice_status'])?'':$data['notice_status'];

        //获取列表
        $data_list = Db::name('yphp_system_notice')->where($whereCond)->order('id', 'desc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ));
        
        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("system/notice_list",$return_data);
    }
    /**
     * 删除公告
     */
    public function systemNoticeDel()
    {
       if (!$this->access)  return json(array('status'=>'FAIL','msg'=>'无此访问权限！')); 

       $id  = request()->param('id');
       if (!empty($id)) 
       {
           Db::name('yphp_system_notice')->where("id",$id)->delete();
           return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
       }
    }
    /**
     * 添加公告
     */
    public function systemNoticeAdd()
    {
       if (!$this->access)  exit('无此访问权限！');

       return view("system/notice_add");
    }
    /**
     * 编辑公告
     */
    public function systemNoticeEdit()
    {
        if (!$this->access)  exit('无此访问权限！');

        $id = request()->param('id');
       $info = Db::name('yphp_system_notice')->where('id',$id)->find();

        return view("system/notice_edit",array('info'=>$info));
    }
    /**
     * 保存公告
     */
    public function systemNoticeAddAct()
    {
        $data = request()->param();

        if(empty($data['id']))
       {

        $data['add_datetime'] = date("Y-m-d H:i:s");
        $id = Db::name('yphp_system_notice')->strict(false)->insertGetId($data);
         if ($id > 0) 
         {
             return json(array('status'=>'SUCCESS','msg'=>'添加成功'));
         }
         else
         {
            return json(array('status'=>'FAIL','msg'=>'添加失败'));
         }
       }
       else
       {
         //修改
         Db::name('yphp_system_notice')->strict(false)->update($data);
         return json(array('status'=>'SUCCESS','msg'=>'修改成功！'));
       }
    }
    /**
     * 地区列表
     */
    public function regionList()
    {
        if (!$this->access)  exit('无此访问权限！');

        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );
        //搜索条件
        $whereCond = array();
        if (!empty($data['city_id']))
        {
            $whereCond[] = array('parent_id','=',$data['city_id']);
        }
        elseif(!empty($data['province_id']))
        {
            $whereCond[] = array('parent_id','=',$data['province_id']);
        }
        if (!empty($data['region_name'])) $whereCond[] = array('region_name','like',"%".$data['region_name']."%");

        if (!empty($data['region_type'])) $whereCond[] = array('region_type','=',$data['region_type']);
        //搜索默认值
        $return_data['region_name']     = empty($data['region_name'])?'':$data['region_name'];
        $return_data['province_id']     = empty($data['province_id'])?'':$data['province_id'];
        $return_data['city_id']         = empty($data['city_id'])?'':$data['city_id'];
        $return_data['region_type']     = empty($data['region_type'])?'':$data['region_type'];

        //获取列表
        $data_list = Db::name('yphp_system_region')->where("region_type",">",0)->where($whereCond)->order('region_id', 'ASC')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ))->each(function($item, $key){

            $item['city_info'] = [];
            $item['province_info'] = [];

            if($item['region_type'] == 3)
            {
                $item['city_info']      = Db::name('yphp_system_region')->where("region_id",$item['parent_id'])->find();
                $item['province_info']  = Db::name('yphp_system_region')->where("region_id",$item['city_info']['parent_id'])->find();
            }
            elseif($item['region_type'] == 2)
            {
                $item['province_info']  = Db::name('yphp_system_region')->where("region_id",$item['parent_id'])->find();
            }
            
            return $item;
        });

        //获取省份列表
        $province_list = Db::name('yphp_system_region')->where('region_type',1)->order('region_name', 'ASC')->select();
        //获取城市列表
        if(!empty($data['province_id']))
        {
            $city_list = Db::name('yphp_system_region')->where('parent_id',$data['province_id'])->order('region_name', 'ASC')->select();
            $return_data['city_list'] = $city_list;

        }elseif(!empty($data['city_id'])){

            $city_info = Db::name('yphp_system_region')->where('region_id',$data['city_id'])->order('region_name', 'ASC')->find();

            $city_list = Db::name('yphp_system_region')->where('parent_id',$city_info['parent_id'])->order('region_name', 'ASC')->select();

            $return_data['province_id'] = $city_info['parent_id'];
            $return_data['city_list'] = $city_list;
        }
        else{
            $return_data['city_list'] = [];
        }

        
        $return_data['data_list'] = $data_list;
        $return_data['province_list'] = $province_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("system/region_list",$return_data);
    }

    /**
     * 获取城市
     */
    public function regionCitys()
    {
        $province_id = request()->param('province_id');

        $city_list = Db::name('yphp_system_region')->where('region_type',2)->where('parent_id',$province_id)->order('region_name', 'ASC')->select();

        return json(array('status'=>'SUCCESS','data'=>$city_list));

    }

    /**
     * 获取区域
     */
    public function regionAreas()
    {
        $city_id = request()->param('city_id');

        $area_list = Db::name('yphp_system_region')->where('region_type',3)->where('parent_id',$city_id)->order('region_name', 'ASC')->select();

        return json(array('status'=>'SUCCESS','data'=>$area_list));

    }

     /**
     * 删除地区
     */
    public function regionDel()
    {

      if (!$this->access)  return json(array('status'=>'FAIL','msg'=>'无此访问权限！')); 

       $id  = request()->param('id');

       if (!empty($id)) 
       {
          $count = Db::name('yphp_system_region')->where("parent_id",$id)->count();
          if ($count > 0) 
          {
              return json(array('status'=>'FAIL','msg'=>'删除失败，该地区还有下级地区，请先删除下级地区'));
          }
          else
          {
            Db::name('yphp_system_region')->where("region_id",$id)->delete();
            return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
          }
       }
    }

    /**
     * 添加地区
     */
    public function regionAdd()
    {
      if (!$this->access)  exit('无此访问权限！');
      //获取省份列表
      $province_list = Db::name('yphp_system_region')->where('region_type',1)->order('region_name', 'ASC')->select();
      
       return view("system/region_add",array('province_list'=>$province_list,'city_list'=>[]));
    }
    /**
     * 编辑地区
     */
    public function regionEdit()
    {
        if (!$this->access)  exit('无此访问权限！');
        
       $id = request()->param('id');
       $info = Db::name('yphp_system_region')->where('region_id',$id)->find();
       $return_data['info'] = $info;

       //获取省份，获取城市
       $province_list = Db::name('yphp_system_region')->where('region_type',1)->order('region_name', 'ASC')->select();
       $return_data['province_list'] = $province_list;

       $return_data['city_list'] = [];

       if ($info['region_type']  == 1) {

           $return_data['province_id']  = $info['region_id'];
           $return_data['city_id']      = 0;

       }elseif($info['region_type'] == 2){

            $return_data['province_id'] = $info['parent_id'];
            $return_data['city_id'] = $info['region_id'];


            $city_list = Db::name('yphp_system_region')->where('parent_id',$info['parent_id'])->order('region_name', 'ASC')->select();
            $return_data['city_list'] = $city_list;
       }elseif($info['region_type'] == 3){

          $city_info = Db::name('yphp_system_region')->where('region_id',$info['parent_id'])->find();

          $city_list = Db::name('yphp_system_region')->where('parent_id',$city_info['parent_id'])->order('region_name', 'ASC')->select();
            $return_data['city_list'] = $city_list;

          $return_data['province_id'] = $city_info['parent_id'];
          $return_data['city_id'] = $info['parent_id'];
       }
        return view("system/region_edit",$return_data);
    }
    /**
     * 添加/修改地区
     */
    public function regionAddAct()
    {
       $data = request()->param();

       
       if ($data['region_type'] == 1) 
       {
           $data['parent_id']    = 1;
       }
       elseif ($data['region_type'] == 2) 
       {
           $data['parent_id']    = $data['province_id'];
       }
       elseif ($data['region_type'] == 3) 
       {
            $data['parent_id']    = $data['city_id'];
       }

       $data['area_name'] = '';

       if(empty($data['region_id']))
       {
        
         $id = Db::name('yphp_system_region')->strict(false)->insertGetId($data);
         if ($id > 0) 
         {
             return json(array('status'=>'SUCCESS','msg'=>'添加成功'));
         }
         else
         {
            return json(array('status'=>'FAIL','msg'=>'添加失败'));
         }
       }
       else
       {
         //修改
         Db::name('yphp_system_region')->strict(false)->update($data);
         return json(array('status'=>'SUCCESS','msg'=>'修改成功！'));
       }
    }
}
