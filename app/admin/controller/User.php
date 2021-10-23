<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use think\facade\App;
use think\facade\Db;



class User extends BaseController
{
    

    public function initialize()
    {
        parent::initialize();
       
    }
    
    /**
     * 用户列表
     */
    public function userList()
    {
        if (!$this->access)  exit('无此访问权限！');

        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['user_name']))  $whereCond[] = array('user_name','like','%'.$data['user_name'].'%');
        if (!empty($data['user_type'])) $whereCond[] = array('user_type','=',$data['user_type']);
        if (!empty($data['user_mobile'])) $whereCond[] = array('user_mobile','=',$data['user_mobile']);
        if (!empty($data['user_truename'])) $whereCond[] = array('user_truename','=',$data['user_truename']);
        if (!empty($data['user_status'])) $whereCond[] = array('user_status','=',$data['user_status']);
        if (!empty($data['start'])) $whereCond[] = array('add_datetime','>',$data['start']);
        if (!empty($data['end'])) $whereCond[] = array('add_datetime','<=',$data['end']." 23:59:59");

        //搜索默认值
        $return_data['user_name']     = empty($data['user_name'])?'':$data['user_name'];
        $return_data['user_type'] = empty($data['user_type'])?'':$data['user_type'];
        $return_data['user_mobile']   = empty($data['user_mobile'])?'':$data['user_mobile'];
        $return_data['user_truename']   = empty($data['user_truename'])?'':$data['user_truename'];
        $return_data['user_status']   = empty($data['user_status'])?'':$data['user_status'];
        $return_data['start']   = empty($data['start'])?'':$data['start'];
        $return_data['end']   = empty($data['end'])?'':$data['end'];

        //获取角色列表
         $role_list = Db::name('yphp_user_role')->order('id', 'asc')->select();
         $return_data['role_list'] = $role_list;

        //获取列表
        $data_list = Db::name('yphp_user')->where($whereCond)->order('user_id', 'desc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ))->each(function($item,$key){

            $item['user_type_name'] = Db::name('yphp_user_role')->where("role_type",$item['user_type'])->value('role_name');
            if (empty($item['parent_uid'])) 
            {
                $item['parent_name'] = "无上级";
            }
            else
            {
                 $item['parent_name'] = Db::name('yphp_user')->where("user_id",$item['parent_uid'])->value('user_name');
            }
           
            return  $item;
        });

        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("user/user_list",$return_data);
    }

    /**
     * 删除用户
     */
    public function userDel()
    {

       if (!$this->access)  return json(array('status'=>'FAIL','msg'=>'无此访问权限！')); 

       $user_id  = request()->param('user_id');

       //有下级则无法删除
       $num  = Db::name('yphp_user')->where("parent_uid",$user_id)->count();

       if ($num > 0) {
           return json(array('status'=>'FAIL','msg'=>'该用户还有下级用户，无法被删除'));
       }
       Db::name('yphp_user')->where("user_id",$user_id)->delete();
       //删除关系
       Db::name('yphp_user_relation')->where("user_id",$user_id)->delete();

       return json(array('status'=>'SUCCESS','msg'=>'用户删除成功'));
    }

    /**
     * 添加用户
     */
    public function userAdd()
    {
        if (!$this->access)  exit('无此访问权限！');

       //获取角色列表
       $role_list = Db::name('yphp_user_role')->order('id', 'asc')->select();
       $return_data['role_list'] = $role_list;

       //获取省份
       $province_list = Db::name('yphp_system_region')->where('region_type',1)->order('region_name', 'ASC')->select();
       $return_data['province_list'] = $province_list;

       return view("user/user_add",$return_data);
    }

    /**
     * 修改用户
     */
    public function userEdit()
    {
        if (!$this->access)  exit('无此访问权限！');

       $user_id = request()->param('user_id');
       $info = Db::name('yphp_user')->where('user_id',$user_id)->find();
       $return_data['info'] = $info;

       $role_list = Db::name('yphp_user_role')->order('id', 'asc')->select();
       $return_data['role_list'] = $role_list;


       //获取省份
       $province_list = Db::name('yphp_system_region')->where('region_type',1)->order('region_name', 'ASC')->select();
       $return_data['province_list'] = $province_list;

       //获取城市
       if (!empty($info['user_province'])) {
           $city_list = Db::name('yphp_system_region')->where('parent_id',$info['user_province'])->order('region_name', 'ASC')->select();
            $return_data['city_list'] = $city_list;
       }else{
            $return_data['city_list'] = [];
       }
       
       //获取区域
       if (!empty($info['user_city'])) {
           $area_list = Db::name('yphp_system_region')->where('parent_id',$info['user_city'])->order('region_name', 'ASC')->select();
            $return_data['area_list'] = $area_list;
       }else{
            $return_data['area_list'] = [];
       }

       //获取上级账号 
       $return_data['parent_info'] =  Db::name('yphp_user')->where("user_id",$info['parent_uid'])->find();

        return view("user/user_edit",$return_data);
    }
    /**
     * 添加用户操作
     */
    public function userAddAct()
    {
       $data = request()->param();

       if (!empty($data['pass'])) 
       {
           if($data['pass'] != $data['repass'])
           {
             return json(array('status'=>'FAIL','msg'=>'两次密码输入不一致！'));
           }

         //处理密码
         $data['user_pwd'] = password_hash($data['pass'], PASSWORD_DEFAULT);
       }
       else
       {
         unset($data['user_pwd']);
       }

       //获取上级ID
       if (!empty($data['parent_name'])) 
       {
            $parent_info = Db::name('yphp_user')->where("user_name",$data['parent_name'])->find();
            if (empty($parent_info)) 
            {
                return json(array('status'=>'FAIL','msg'=>'上级账号不存在，请核实上级账号！'));
            }
            //获取关系
            $parent_relation = Db::name('yphp_user_relation')->where("user_id",$parent_info['user_id'])->find();

            if($parent_info['user_type'] <= $data['user_type'])
            {
                return json(array('status'=>'FAIL','msg'=>'上级账号级别不能低于当前账号的级别'));
            }

            $data['parent_uid'] = $parent_info['user_id'];
       }
       else
       {
         $data['parent_uid'] = 0;
         $parent_relation = array(
            'user_id1'  => 0,
            'user_id2'  => 0,
            'user_id3'  => 0,
            'user_id4'  => 0,
            'user_id5'  => 0,
            'user_id6'  => 0,
            'user_id7'  => 0,
         );
       }
       if(empty($data['user_id']))
       {
         $data['add_datetime'] = date("Y-m-d H:i:s");

         $user_exist = Db::name('yphp_user')->where("user_name",$data['user_name'])->find();
         if (!empty($user_exist)) 
         {
             return json(array('status'=>'FAIL','msg'=>'添加失败：您输入的账号已存在，请更换其他账号'));
         }

         $user_id = Db::name('yphp_user')->strict(false)->insertGetId($data);
         if ($user_id > 0) 
         {
            //插入关系表
            $relation = array(
                'user_id'   => $user_id,
                'user_type' => $data['user_type'],
                'user_id1'  => intval($parent_relation['user_id1']),
                'user_id2'  => intval($parent_relation['user_id2']),
                'user_id3'  => intval($parent_relation['user_id3']),
                'user_id4'  => intval($parent_relation['user_id4']),
                'user_id5'  => intval($parent_relation['user_id5']),
                'user_id6'  => intval($parent_relation['user_id6']),
                'user_id7'  => intval($parent_relation['user_id7']),
            );
            $user_type_str = "user_id".$data['user_type'];

            $relation[$user_type_str] = $user_id;
            $relation_id = Db::name('yphp_user_relation')->strict(false)->insertGetId($relation);

            return json(array('status'=>'SUCCESS','msg'=>'添加成功'));
         }
         else
         {
            return json(array('status'=>'FAIL','msg'=>'添加失败'));
         }

       }
       else
       {

         $user_exist = Db::name('yphp_user')->where("user_name",$data['user_name'])->where("user_id","<>",$data['user_id'])->find();
         if (!empty($user_exist)) 
         {
             return json(array('status'=>'FAIL','msg'=>'修改失败：您输入的账号已存在，请更换其他账号'));
         }

         //修改关系 比较复杂
         $user_info = Db::name('yphp_user')->where("user_id",$data['user_id'])->find();
         if ($user_info['parent_uid'] != $data['parent_uid']) 
         {

            return json(array('status'=>'FAIL','msg'=>'修改失败，当前选项不支持修改上级'));

            //获取当前用户关系
            $relation1 = Db::name('yphp_user_relation')->where("user_id",$data['user_id'])->find();
            //修改当前用户的关系
            $relation = array(
                'user_type' => $data['user_type'],
                'user_id1'  => intval($parent_relation['user_id1']),
                'user_id2'  => intval($parent_relation['user_id2']),
                'user_id3'  => intval($parent_relation['user_id3']),
                'user_id4'  => intval($parent_relation['user_id4']),
                'user_id5'  => intval($parent_relation['user_id5']),
                'user_id6'  => intval($parent_relation['user_id6']),
                'user_id7'  => intval($parent_relation['user_id7']),
            );
            $user_type_str = "user_id".$data['user_type'];
            
            $relation[$user_type_str] = $data['user_id'];
            Db::name('yphp_user_relation')->where("id",$relation1['id'])->strict(false)->update($relation);

            $bind_relation = [];
            //修改绑定的其他用户关系
            if ($user_info['user_type'] == 1) 
            {
                $bind_relation = array(
                    'user_id1'  => intval($relation['user_id1']),
                    'user_id2'  => intval($relation['user_id2']),
                    'user_id3'  => intval($relation['user_id3']),
                    'user_id4'  => intval($relation['user_id4']),
                    'user_id5'  => intval($relation['user_id5']),
                    'user_id6'  => intval($relation['user_id6']),
                    'user_id7'  => intval($relation['user_id7']),
                );
            }
            elseif ($user_info['user_type'] == 2) 
            {
                $bind_relation = array(
                    'user_id2'  => intval($relation['user_id2']),
                    'user_id3'  => intval($relation['user_id3']),
                    'user_id4'  => intval($relation['user_id4']),
                    'user_id5'  => intval($relation['user_id5']),
                    'user_id6'  => intval($relation['user_id6']),
                    'user_id7'  => intval($relation['user_id7']),
                );
            }
            elseif ($user_info['user_type'] == 3) 
            {
                $bind_relation = array(
                    'user_id3'  => intval($relation['user_id3']),
                    'user_id4'  => intval($relation['user_id4']),
                    'user_id5'  => intval($relation['user_id5']),
                    'user_id6'  => intval($relation['user_id6']),
                    'user_id7'  => intval($relation['user_id7']),
                );
            }
            elseif ($user_info['user_type'] == 4) 
            {
                $bind_relation = array(
                    'user_id4'  => intval($relation['user_id4']),
                    'user_id5'  => intval($relation['user_id5']),
                    'user_id6'  => intval($relation['user_id6']),
                    'user_id7'  => intval($relation['user_id7']),
                );
            }
            elseif ($user_info['user_type'] == 5) 
            {
                $bind_relation = array(
                    'user_id5'  => intval($relation['user_id5']),
                    'user_id6'  => intval($relation['user_id6']),
                    'user_id7'  => intval($relation['user_id7']),
                );
            }
            elseif ($user_info['user_type'] == 6) 
            {
                $bind_relation = array(
                    'user_id6'  => intval($relation['user_id6']),
                    'user_id7'  => intval($relation['user_id7']),
                );
            }
            elseif ($user_info['user_type'] == 7) 
            {
                $bind_relation = array(
                    'user_id7'  => intval($relation['user_id7'])
                );
            }
            Db::name('yphp_user_relation')->where("user_id".$user_info['user_type'],$data['user_id'])->update($bind_relation);
         }
         //修改用户
         Db::name('yphp_user')->strict(false)->update($data);
         return json(array('status'=>'SUCCESS','msg'=>'修改成功！'));
       }
    }

     /**
     * 用户角色列表
     */
    public function userRoleList()
    {
        if (!$this->access)  exit('无此访问权限！');

        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['role_name']))  $whereCond[] = array('role_name','=',$data['role_name']);
      
        //搜索默认值
        $return_data['role_name']     = empty($data['role_name'])?'':$data['role_name'];

        //获取列表
        $data_list = Db::name('yphp_user_role')->where($whereCond)->order('role_type', 'asc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ));
        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();


       return view("user/user_role_list",$return_data);
    }

   
     /**
     * 修改用户角色
     */
    public function userRoleEdit()
    {
       if (!$this->access)  exit('无此访问权限！');

        $role_id = request()->param('id');

     $info = Db::name('yphp_user_role')->where('id',$role_id)->find();
    

     return view("user/user_role_edit",array('info'=>$info));
       
    }
    /**
     * 添加/修改角色操作
     */
    public function userRoleAddAct()
    {
       $data = request()->param();

         //修改管理员
        Db::name('yphp_user_role')->strict(false)->update($data);
        return json(array('status'=>'SUCCESS','msg'=>'修改成功！'));
    }

     /**
     * 用户消息列表
     */
    public function userMsgList()
    {
        if (!$this->access)  exit('无此访问权限！');

        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['msg_type']))  $whereCond[] = array('msg_type','=',$data['msg_type']);
        if (!empty($data['msg_title']))  $whereCond[] = array('msg_title','=',$data['msg_title']);
        if (!empty($data['user_name']))  {

            $user_id = Db::name('yphp_user')->where("user_name",$data['user_name'])->value('user_id');
            if (!empty($user_id)) 
            {
               $whereCond[] = array('user_id','=',$user_id);
            }
        }
        if (!empty($data['start'])) $whereCond[] = array('add_datetime','>',$data['start']);
        if (!empty($data['end'])) $whereCond[] = array('add_datetime','<=',$data['end']." 23:59:59");
      
        //搜索默认值
        $return_data['msg_type']     = empty($data['msg_type'])?'':$data['msg_type'];
        $return_data['msg_title']     = empty($data['msg_title'])?'':$data['msg_title'];
        $return_data['user_name']     = empty($data['user_name'])?'':$data['user_name'];
        $return_data['start']   = empty($data['start'])?'':$data['start'];
        $return_data['end']   = empty($data['end'])?'':$data['end'];

        //获取列表
        $data_list = Db::name('yphp_user_msg')->where($whereCond)->order('id', 'desc')->paginate(array(
            'list_rows' => 15,
            'query'     => $data
        ))->each(function($item,$key){

            $item['user_name'] = Db::name('yphp_user')->where("user_id",$item['user_id'])->value('user_name');
            return $item;
        });
        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("user/user_msg_list",$return_data);
    }

    /**
     * 添加消息内容
     */
    public function userMsgAdd()
    {
        if (!$this->access)  exit('无此访问权限！');

        return view("user/user_msg_add"); 
    }

    /**
     * 修改消息内容
     */
    public function userMsgEdit()
    {

     if (!$this->access)  exit('无此访问权限！');
       
     $id = request()->param('id');
     $info = Db::name('yphp_user_msg')->where('id',$id)->find();
     $info['user_name'] = Db::name('yphp_user')->where("user_id",$info['user_id'])->value('user_name');

     return view("user/user_msg_edit",array('info'=>$info)); 
    }

     /**
     * 删除消息
     */
    public function userMsgDel()
    {

      if (!$this->access)  return json(array('status'=>'FAIL','msg'=>'无此访问权限！')); 

       $id  = request()->param('id');

       if (!empty($id)) 
       {

            Db::name('yphp_user_msg')->where("id",$id)->delete();
            return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
       }
    }

    /**
     * 添加/修改消息
     */
    public function userMsgAddAct()
    {
       
       $data = request()->param();

       //将user_name 转换为 user_id
       if(!empty($data['user_name']))
       {
            $data['user_id'] = Db::name('yphp_user')->where("user_name",$data['user_name'])->value('user_id');
       }
       else
       {
            return json(array('status'=>'FAIL','msg'=>'账号不能为空'));
       }

        if(empty($data['id']))
       {
         $id = Db::name('yphp_user_msg')->strict(false)->insertGetId($data);
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
         Db::name('yphp_user_msg')->strict(false)->update($data);
         return json(array('status'=>'SUCCESS','msg'=>'修改成功！'));
       }
    }

    /**
     * 用户登录列表
     */
    public function userLoginList()
    {
        if (!$this->access)  exit('无此访问权限！');

        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['ip_address']))  $whereCond[] = array('ip_address','=',$data['ip_address']);
        if (!empty($data['login_status']))  $whereCond[] = array('login_status','=',$data['login_status']);
        if (!empty($data['user_name']))  {

            $user_id = Db::name('yphp_user')->where("user_name",$data['user_name'])->value('user_id');
            if (!empty($user_id)) 
            {
               $whereCond[] = array('user_id','=',$user_id);
            }
        }
        if (!empty($data['start'])) $whereCond[] = array('add_datetime','>',$data['start']);
        if (!empty($data['end'])) $whereCond[] = array('add_datetime','<=',$data['end']." 23:59:59");
      
        //搜索默认值
        $return_data['ip_address']     = empty($data['ip_address'])?'':$data['ip_address'];
        $return_data['login_status']     = empty($data['login_status'])?'':$data['login_status'];
        $return_data['user_name']     = empty($data['user_name'])?'':$data['user_name'];
        $return_data['start']   = empty($data['start'])?'':$data['start'];
        $return_data['end']   = empty($data['end'])?'':$data['end'];

        //获取列表
        $data_list = Db::name('yphp_user_login')->where($whereCond)->order('id', 'desc')->paginate(array(
            'list_rows' => 15,
            'query'     => $data
        ))->each(function($item,$key){

            $item['user_name'] = Db::name('yphp_user')->where("user_id",$item['user_id'])->value('user_name');
            return $item;
        });
        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("user/user_login_list",$return_data);
    }

   

     /**
     * 删除登录记录
     */
    public function userLoginDel()
    {

       if (!$this->access)  return json(array('status'=>'FAIL','msg'=>'无此访问权限！')); 

       $id  = request()->param('id');

       if (!empty($id)) 
       {

            Db::name('yphp_user_login')->where("id",$id)->delete();
            return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
       }
    }

    /**
     * 用户关系列表
     */
    public function userRelationList()
    {
        if (!$this->access)  exit('无此访问权限！');
        
        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['user_name']))  {

            $user_id = Db::name('yphp_user')->where("user_name",$data['user_name'])->value('user_id');
            if (!empty($user_id)) 
            {
               $whereCond[] = array('user_id','=',$user_id);
            }
        }
        if (!empty($data['user_type']))  $whereCond[] = array('user_type','=',$data['user_type']);
        if (!empty($data['user_id1']))  $whereCond[] = array('user_id1','=',$data['user_id1']);
        if (!empty($data['user_id2']))  $whereCond[] = array('user_id2','=',$data['user_id2']);
        if (!empty($data['user_id3']))  $whereCond[] = array('user_id3','=',$data['user_id3']);
        if (!empty($data['user_id4']))  $whereCond[] = array('user_id4','=',$data['user_id4']);
        if (!empty($data['user_id5']))  $whereCond[] = array('user_id5','=',$data['user_id5']);
        if (!empty($data['user_id6']))  $whereCond[] = array('user_id6','=',$data['user_id6']);
        if (!empty($data['user_id7']))  $whereCond[] = array('user_id7','=',$data['user_id7']);
      
        //搜索默认值
        $return_data['user_name']     = empty($data['user_name'])?'':$data['user_name'];
        $return_data['user_type']     = empty($data['user_type'])?'':$data['user_type'];
        $return_data['user_id1']     = empty($data['user_id1'])?'':$data['user_id1'];
        $return_data['user_id2']     = empty($data['user_id2'])?'':$data['user_id2'];
        $return_data['user_id3']     = empty($data['user_id3'])?'':$data['user_id3'];
        $return_data['user_id4']     = empty($data['user_id4'])?'':$data['user_id4'];
        $return_data['user_id5']     = empty($data['user_id5'])?'':$data['user_id5'];
        $return_data['user_id6']     = empty($data['user_id6'])?'':$data['user_id6'];
        $return_data['user_id7']     = empty($data['user_id7'])?'':$data['user_id7'];

        //获取列表
        $data_list = Db::name('yphp_user_relation')->where($whereCond)->order('id', 'desc')->paginate(array(
            'list_rows' => 15,
            'query'     => $data
        ))->each(function($item,$key){

            $item['user_name'] = Db::name('yphp_user')->where("user_id",$item['user_id'])->value('user_name');
            if (!empty($item['user_id1'])) 
            {
                $item['user_id1_name'] = Db::name('yphp_user')->where("user_id",$item['user_id1'])->value('user_name');
            }
            else
            {
                $item['user_id1_name'] ="-";
            }

            if (!empty($item['user_id2'])) 
            {
                $item['user_id2_name'] = Db::name('yphp_user')->where("user_id",$item['user_id2'])->value('user_name');
            }
            else
            {
                $item['user_id2_name'] ="-";
            }

            if (!empty($item['user_id3'])) 
            {
                $item['user_id3_name'] = Db::name('yphp_user')->where("user_id",$item['user_id3'])->value('user_name');
            }
            else
            {
                $item['user_id3_name'] ="-";
            }

            if (!empty($item['user_id4'])) 
            {
                $item['user_id4_name'] = Db::name('yphp_user')->where("user_id",$item['user_id4'])->value('user_name');
            }
            else
            {
                $item['user_id4_name'] ="-";
            }

            if (!empty($item['user_id5'])) 
            {
                $item['user_id5_name'] = Db::name('yphp_user')->where("user_id",$item['user_id5'])->value('user_name');
            }
            else
            {
                $item['user_id5_name'] ="-";
            }

            if (!empty($item['user_id6'])) 
            {
                $item['user_id6_name'] = Db::name('yphp_user')->where("user_id",$item['user_id6'])->value('user_name');
            }
            else
            {
                $item['user_id6_name'] ="-";
            }

            if (!empty($item['user_id7'])) 
            {
                $item['user_id7_name'] = Db::name('yphp_user')->where("user_id",$item['user_id7'])->value('user_name');
            }
            else
            {
                $item['user_id7_name'] ="-";
            }
            $item['user_type_name'] =Db::name('yphp_user_role')->where("role_type",$item['user_type'])->value('role_name');;
            return $item;
        });
        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

        //获取角色列表
         $role_list = Db::name('yphp_user_role')->order('id', 'asc')->select();
         $return_data['role_list'] = $role_list;

       return view("user/user_relation_list",$return_data);
    }



    
   

    
}
