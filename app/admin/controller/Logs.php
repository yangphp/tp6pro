<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use think\facade\App;
use think\facade\Db;

class Logs extends BaseController
{
    

    public function initialize()
    {
        parent::initialize();
       
    }
    
    /**
     * 管理员登录日志
     */
    public function adminLoginList()
    {
        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['admin_name'])) 
        {
            $admin_id = Db::name('yphp_admin')->where("admin_name",$data['admin_name'])->value('admin_id');
            if (!empty($admin_id)) 
            {
               $whereCond[] = array('admin_id','=',$admin_id);
            }
            
        } 
        if (!empty($data['login_status'])) $whereCond[] = array('login_status','=',$data['login_status']);
        if (!empty($data['login_ip'])) $whereCond[] = array('login_ip','=',$data['login_ip']);
        if (!empty($data['start'])) $whereCond[] = array('add_datetime','>',$data['start']);
        if (!empty($data['end'])) $whereCond[] = array('add_datetime','<=',$data['end']." 23:59:59");

        
        //搜索默认值
        $return_data['admin_name']     = empty($data['admin_name'])?'':$data['admin_name'];
        $return_data['login_status'] = empty($data['login_status'])?'':$data['login_status'];
        $return_data['login_ip']   = empty($data['login_ip'])?'':$data['login_ip'];
        $return_data['start']   = empty($data['start'])?'':$data['start'];
        $return_data['end']   = empty($data['end'])?'':$data['end'];


        //获取列表
        $data_list = Db::name('yphp_admin_login')->where($whereCond)->order('id', 'desc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ))->each(function($item, $key){

            $item['admin_name'] = Db::name('yphp_admin')->where("admin_id",$item['admin_id'])->value('admin_name');
            return $item;
        });


        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("logs/admin_login_list",$return_data);
    }

    /**
     * 删除日志
     */
    public function adminLoginDel()
    {
       $id  = request()->param('id');
       $ids  = request()->param('ids');

       if (!empty($id)) 
       {
           if($this->admin_info['admin_role_id'] == 0 || $this->admin_info['admin_role_id'] == 1)
           {
                Db::name('yphp_admin_login')->where("id",$id)->delete();
                return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
           }
           else
           {
                return json(array('status'=>'FAIL','msg'=>'删除失败，日志记录仅超级管理员和系统管理员可以删除！'));
           }
       }elseif(!empty($ids)){

            if($this->admin_info['admin_role_id'] == 0 || $this->admin_info['admin_role_id'] == 1)
           {
                Db::name('yphp_admin_login')->where("id",'in',$ids)->delete();
                return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
           }
           else
           {
                return json(array('status'=>'FAIL','msg'=>'删除失败，日志记录仅超级管理员和系统管理员可以删除！'));
           }
       }
    }


    /**
     * 管理员操作列表
     */
    public function adminOpList()
    {
        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['admin_name']))  $whereCond[] = array('admin_name','=',$data['admin_name']);
        if (!empty($data['op_controller'])) $whereCond[] = array('op_controller','=',$data['op_controller']);
        if (!empty($data['op_action'])) $whereCond[] = array('op_action','=',$data['op_action']);
        if (!empty($data['login_ip'])) $whereCond[] = array('login_ip','=',$data['login_ip']);
        if (!empty($data['start'])) $whereCond[] = array('add_datetime','>',$data['start']);
        if (!empty($data['end'])) $whereCond[] = array('add_datetime','<=',$data['end']." 23:59:59");

        //搜索默认值
        $return_data['admin_name']     = empty($data['admin_name'])?'':$data['admin_name'];
        $return_data['op_controller'] = empty($data['op_controller'])?'':$data['op_controller'];
        $return_data['op_action'] = empty($data['op_action'])?'':$data['op_action'];
        $return_data['ip_address']   = empty($data['ip_address'])?'':$data['ip_address'];
        $return_data['start']   = empty($data['start'])?'':$data['start'];
        $return_data['end']   = empty($data['end'])?'':$data['end'];


        //获取列表
        $data_list = Db::name('yphp_admin_op_log')->where($whereCond)->order('id', 'desc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ));

        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("logs/admin_op_list",$return_data);
    }

    /**
     * 删除日志
     */
    public function adminOpDel()
    {
       $id  = request()->param('id');
       $ids  = request()->param('ids');

       if (!empty($id)) 
       {
           if($this->admin_info['admin_role_id'] == 0 || $this->admin_info['admin_role_id'] == 1)
           {
                Db::name('yphp_admin_op_log')->where("id",$id)->delete();
                return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
           }
           else
           {
                return json(array('status'=>'FAIL','msg'=>'删除失败，日志记录仅超级管理员和系统管理员可以删除！'));
           }
       }elseif(!empty($ids)){

            if($this->admin_info['admin_role_id'] == 0 || $this->admin_info['admin_role_id'] == 1)
           {
                Db::name('yphp_admin_op_log')->where("id",'in',$ids)->delete();
                return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
           }
           else
           {
                return json(array('status'=>'FAIL','msg'=>'删除失败，日志记录仅超级管理员和系统管理员可以删除！'));
           }
       }
    }

     /**
     * 查看日志
     */
     public function adminOpShow()
    {
       $id  = request()->param('id');

       $info = Db::name('yphp_admin_op_log')->where("id",$id)->find();
       
       return view("logs/admin_op_show",array('info'=>$info));
    }

}
