<?php
declare (strict_types = 1);

namespace app\admin;

use think\App;
use think\exception\ValidateException;
use think\exception\HttpResponseException;
use think\Validate;
use think\facade\Db;

use app\admin\model\AdminModel;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    //管理员model
    protected $adminModel;
    //管理员信息
    protected $admin_info;
    protected $admin_id;
    protected $access ;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        //登录验证
        $nologin_arr = array(
            'login'
        );
        $controller =  request()->controller(true);

        if(!in_array($controller,$nologin_arr))
        {
             //登录验证
            $check_res = $this->adminLoginCheck();
            if ($check_res['status']=='SUCCESS') {
                $this->admin_info = $check_res['data'];
                $this->admin_id = $this->admin_info['admin_id'];
            }else{
                return $this->redirectTo('/admin/login/index');
            }
        }

        //权限判断
        $action = request()->action();

        $powers_arr = array();
        $this->access  = true;

        if($this->admin_info['admin_role_id'] == 0)
        {
           $this->access  = true;
        }
        else
        {
            $role_powers = Db::name('yphp_admin_role')->where("role_id",$this->admin_info['admin_role_id'])->value('role_powers');
            if($role_powers == 'all')
            {
                $this->access  = true;
            }
            else
            {
                $powers = 'custom';
                $powers_arr = explode(",",$role_powers);

                //获取当前菜单
                $power_info = Db::name('yphp_admin_power')
                            ->where("pcontroller",$controller)
                            ->where("paction",$action)
                            ->where("ptype",3)->where("pstatus",1)->find();

                if (!empty($power_info) && !in_array($power_info['id'],$powers_arr)) 
                {
                   //无权限访问
                   $this->access  = false;
                }
            }
        }

       



        //常用model
        $this->adminModel = new AdminModel();

        //写入操作日志
        $this->adminModel->addOpLog($this->admin_info);

        

    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 登录验证
     */
    protected function adminLoginCheck()
    {

        $admin_id    = session('admin_id');
        $admin_shell = session('admin_shell');
        if (empty($admin_id)) 
        {
            $admin_id = cookie('admin_id');
            $admin_shell = cookie('admin_shell');
        }
        if (empty($admin_id)) {

           return array('status'=>'FAIL');
        }

        $admin = Db::table('yphp_admin')->where('admin_id',$admin_id)->findOrEmpty();
        if (empty($admin)) {

           return array('status'=>'FAIL');
        }

        if ($admin_shell != md5("LJAF&AFA".$admin['admin_id'].$admin['admin_pwd'])) {

            return array('status'=>'FAIL');
        }
        unset($admin['admin_pwd']);
        return array('status'=>'SUCCESS','data'=>$admin);
    }
    /**
     * 自定义重定向方法
     * @param $args
     */
    public function redirectTo(...$args)
    {
        // 此处 throw new HttpResponseException 这个异常一定要写
        throw new HttpResponseException(redirect(...$args));
    }

}
