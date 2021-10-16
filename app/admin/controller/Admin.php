<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use think\facade\App;
use think\facade\Db;



class Admin extends BaseController
{
    

    public function initialize()
    {
        parent::initialize();
       
    }
    
    /**
     * 管理员列表
     */
    public function adminList()
    {
        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['s_admin_name']))  $whereCond[] = array('admin_name','like','%'.$data['s_admin_name'].'%');
        if (!empty($data['s_admin_truename'])) $whereCond[] = array('admin_truename','=',$data['s_admin_truename']);
        if (!empty($data['s_admin_mobile'])) $whereCond[] = array('admin_mobile','=',$data['s_admin_mobile']);
        if (!empty($data['s_admin_status'])) $whereCond[] = array('admin_status','=',$data['s_admin_status']);

        //搜索默认值
        $return_data['s_admin_name']     = empty($data['s_admin_name'])?'':$data['s_admin_name'];
        $return_data['s_admin_truename'] = empty($data['s_admin_truename'])?'':$data['s_admin_truename'];
        $return_data['s_admin_mobile']   = empty($data['s_admin_mobile'])?'':$data['s_admin_mobile'];
        $return_data['s_admin_status']   = empty($data['s_admin_status'])?'':$data['s_admin_status'];


        //获取列表
        $admin_list = Db::name('yphp_admin')->where($whereCond)->order('admin_id', 'desc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ));
        $return_data['admin_list'] = $admin_list;
        // 获取分页显示
        $return_data['page'] = $admin_list->render();



       return view("admin/admin_list",$return_data);
    }

    /**
     * 删除管理员
     */
    public function adminDel()
    {
       $s_admin_id  = request()->param('s_admin_id');
       if (empty($s_admin_id)) return json(array('status'=>'FAIL','msg'=>'ID不能为空！'));
       if($s_admin_id == $this->admin_id) return json(array('status'=>'FAIL','msg'=>'你不能删除自己！'));
       if($s_admin_id == 1) return json(array('status'=>'FAIL','msg'=>'超级管理员不能被删除！'));

       Db::name('yphp_admin')->where("admin_id",$s_admin_id)->delete();

       return json(array('status'=>'SUCCESS','msg'=>'管理员删除成功'));
    }

    /**
     * 添加管理员
     */
    public function adminAdd()
    {
       $s_admin_id = request()->param('s_admin_id');

       if (empty($s_admin_id)) 
       {
           return view("admin/admin_add");
       }
       else
       {
         $info = Db::name('yphp_admin')->where('admin_id',$s_admin_id)->find();

         return view("admin/admin_edit",array('info'=>$info));
       }
       
    }
    /**
     * 添加管理员操作
     */
    public function adminAddAct()
    {
       $data = request()->param();

       if (!empty($data['pass'])) 
       {
           if($data['pass'] != $data['repass'])
           {
             return json(array('status'=>'FAIL','msg'=>'两次密码输入不一致！'));
           }
       }

       if(empty($data['s_admin_id']))
       {
         $add_res = $this->adminModel->addItem($data);
         return json($add_res);

       }
       else
       {
         //修改管理员
         $add_res = $this->adminModel->updateItem($data);
         return json($add_res);
       }
    }

     /**
     * 角色列表
     */
    public function roleList()
    {
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
        $data_list = Db::name('yphp_admin_role')->where($whereCond)->order('role_id', 'desc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ));
        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();


       return view("admin/role_list",$return_data);
    }

    /**
     * 删除角色
     */
    public function roleDel()
    {
       $role_id  = request()->param('role_id');
       if (empty($role_id)) return json(array('status'=>'FAIL','msg'=>'ID不能为空！'));

       //判断该角色下面没有管理员，则可进行删除
       $num = Db::name('yphp_admin')->where("admin_role_id",$role_id)->count();
       if ($num > 0) {
          return json(array('status'=>'FAIL','msg'=>'该角色下面有管理员存在，不能被删除！'));
       }

       if($role_id == 1)
       {
            return json(array('status'=>'FAIL','msg'=>'系统管理员不能被删除！'));
       }

       Db::name('yphp_admin_role')->where("role_id",$role_id)->delete();

       return json(array('status'=>'SUCCESS','msg'=>'角色删除成功'));
    }

     /**
     * 添加角色
     */
    public function roleAdd()
    {
       $role_id = request()->param('role_id');

       //获取当前可用的所有菜单权限
       //获取第一级菜单
        $data_list = Db::name('yphp_admin_power')->where("pstatus",1)->where("ptype",1)->order('porder', 'desc')->select()->toArray();
        foreach ($data_list as $key => $val) 
        {
            //获取二级菜单
            $data_list[$key]['child'] = Db::name('yphp_admin_power')->where("pstatus",1)->where("parent_id",$val['id'])->where("ptype",2)->order('porder', 'desc')->select()->toArray();
            //获取三级菜单
            foreach ($data_list[$key]['child'] as $key2 => $val2) 
            {
                $data_list[$key]['child'][$key2]['child'] = Db::name('yphp_admin_power')->where("pstatus",1)->where("parent_id",$val2['id'])->where("ptype",3)->order('porder', 'desc')->select()->toArray();
            }
        }
        $return_data['power_list'] = $data_list;

       if (empty($role_id)) 
       {
           return view("admin/role_add",$return_data);
       }
       else
       {

         $info = Db::name('yphp_admin_role')->where('role_id',$role_id)->find();

         if ($info['role_powers'] != 'all') {

             $info['role_powers_check'] = explode(",", $info['role_powers']);
         }else{
            $info['role_powers_check'] = [];
         }

         return view("admin/role_edit",array('info'=>$info,'power_list'=>$return_data['power_list']));
       }
       
    }
    /**
     * 添加/修改角色操作
     */
    public function RoleAddAct()
    {
       $data = request()->param();

       if (empty($data['role_powers'])) 
       {
          return json(array('status'=>'FAIL','msg'=>'请选择权限类型'));
       }

       if(empty($data['role_id']))
       {
         $info = Db::name('yphp_admin_role')->where("role_name",$data['role_name'])->find();
         if(!empty($info))
         {
            return json(array('status'=>'FAIL','msg'=>'该角色已存在，请更换角色名'));
         }

         if($data['role_powers'] == 'custom')
         {
            $data['role_powers'] = implode(",", $data['ids']);
         }

         $role_id = Db::name('yphp_admin_role')->strict(false)->insertGetId($data);
         if(empty($role_id)){
            return json(array('status'=>'FAIL','msg'=>'添加角色失败！'));
         }else{
            return json(array('status'=>'SUCCESS','msg'=>'添加角色成功！'));
         }
       }
       else
       {

         if($data['role_powers'] == 'custom')
         {
            $data['role_powers'] = implode(",", $data['ids']);
         }

         //修改管理员
         Db::name('yphp_admin_role')->strict(false)->update($data);
         return json(array('status'=>'SUCCESS','msg'=>'修改角色成功！'));
       }
    }

    /**
     * 菜单权限列表
     */
    public function powerList()
    {
        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['parent_id']))  $whereCond[] = array('id','=',$data['parent_id']);
      
        //搜索默认值
        $return_data['parent_id'] = empty($data['parent_id'])?'':$data['parent_id'];

        //搜索项
        $data_lista = Db::name('yphp_admin_power')->where("ptype",1)->order('porder', 'desc')->select()->toArray();

        //获取第一级菜单
        $data_list = Db::name('yphp_admin_power')->where($whereCond)->where("ptype",1)->order('porder', 'desc')->select()->toArray();
        foreach ($data_list as $key => $val) 
        {
            //获取二级菜单
            $data_list[$key]['child'] = Db::name('yphp_admin_power')->where("parent_id",$val['id'])->where("ptype",2)->order('porder', 'desc')->select()->toArray();
            //获取三级菜单
            foreach ($data_list[$key]['child'] as $key2 => $val2) 
            {
                $data_list[$key]['child'][$key2]['child'] = Db::name('yphp_admin_power')->where("parent_id",$val2['id'])->where("ptype",3)->order('porder', 'desc')->select()->toArray();
            }
        }
        $return_data['data_list'] = $data_list;
        $return_data['data_lista'] = $data_lista;
        // 获取分页显示
        //$return_data['page'] = $data_list->render();


       return view("admin/powders_list",$return_data);
    }

    /**
     * 删除菜单或权限
     */
    public function powerDel()
    {
       $id  = request()->param('id');
       if (empty($id)) return json(array('status'=>'FAIL','msg'=>'ID不能为空！'));

       //判断该角色下面没有管理员，则可进行删除
       $num = Db::name('yphp_admin_power')->where("parent_id",$id)->count();
       if ($num > 0) {
          return json(array('status'=>'FAIL','msg'=>'删除失败，该菜单下面还有子菜单！'));
       }

       if($this->admin_info['admin_role_id'] > 1)
       {
            return json(array('status'=>'FAIL','msg'=>'删除失败，仅超级管理员或系统管理员可删除菜单'));
       }

       Db::name('yphp_admin_power')->where("id",$id)->delete();

       return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
    }

    /**
     * 添加菜单
     */
    public function powerAdd()
    {
       $parent_id = request()->param('parent_id');

        //获取第一级菜单
        $data_list = Db::name('yphp_admin_power')->where("ptype",1)->order('porder', 'desc')->select()->toArray();
        foreach ($data_list as $key => $val) 
        {
            //获取二级菜单
            $data_list[$key]['child'] = Db::name('yphp_admin_power')->where("parent_id",$val['id'])->where("ptype",2)->order('porder', 'desc')->select()->toArray();
           
        }
        $return_data['parent_id'] = $parent_id;
        $return_data['data_list'] = $data_list;


       return view("admin/power_add",$return_data);
    }
    /**
     * 编辑菜单
     */
    public function powerEdit()
    {
       $id = request()->param('id');

        //获取第一级菜单
        $data_list = Db::name('yphp_admin_power')->where("ptype",1)->order('porder', 'desc')->select()->toArray();
        foreach ($data_list as $key => $val) 
        {
            //获取二级菜单
            $data_list[$key]['child'] = Db::name('yphp_admin_power')->where("parent_id",$val['id'])->where("ptype",2)->order('porder', 'desc')->select()->toArray();
        }
        $info = Db::name('yphp_admin_power')->where("id",$id)->find();

        $return_data['data_list'] = $data_list;
        $return_data['info'] = $info;


       return view("admin/power_edit",$return_data);
    }
    /**
     * 添加菜单操作
     */
    public function powerAddAct()
    {
        $data = request()->param();

        //确定菜单类别
         if ($data['parent_id'] == 0) {
             $data['ptype'] = 1;
         }else{
            $info = Db::name('yphp_admin_power')->where("id",$data['parent_id'])->find();
            $data['ptype'] = $info['ptype']+1;
         }


       if(empty($data['id']))
       {
         $info = Db::name('yphp_admin_power')->where("pname",$data['pname'])->find();
         if(!empty($info))
         {
            return json(array('status'=>'FAIL','msg'=>'该菜单已存在，请更换菜单名'));
         }

         $id = Db::name('yphp_admin_power')->strict(false)->insertGetId($data);
         if(empty($id)){
            return json(array('status'=>'FAIL','msg'=>'添加菜单失败！'));
         }else{
            return json(array('status'=>'SUCCESS','msg'=>'添加菜单成功！'));
         }
       }
       else
       {
         //修改管理员
         Db::name('yphp_admin_power')->strict(false)->update($data);
         return json(array('status'=>'SUCCESS','msg'=>'修改菜单成功！'));
       }
    }
    


    
   

    
}
