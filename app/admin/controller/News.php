<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use think\facade\App;
use think\facade\Db;

class News extends BaseController
{
    

    public function initialize()
    {
        parent::initialize();
       
    }

     /**
     * 文章列表
     */
    public function newsList()
    {
        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['news_title'])) $whereCond[] = array('news_title','like','%'.$data['news_title'].'%');
        if (!empty($data['cate_id'])) $whereCond[] = array('cate_id','=',$data['cate_id']);
        if (!empty($data['is_show'])) $whereCond[] = array('is_show','=',$data['is_show']);
        if (!empty($data['is_recommed'])) $whereCond[] = array('is_recommed','=',$data['is_recommed']);
        
        //搜索默认值
        $return_data['news_title']     = empty($data['news_title'])?'':$data['news_title'];
        $return_data['cate_id'] = empty($data['cate_id'])?'':$data['cate_id'];
        $return_data['is_show'] = empty($data['is_show'])?'':$data['is_show'];
        $return_data['is_recommed'] = empty($data['is_recommed'])?'':$data['is_recommed'];

        //获取列表
        $data_list = Db::name('yphp_news')->where($whereCond)->where("is_del","1")->order('id', 'desc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ))->each(function($item, $key){

            $item['cate_name'] = Db::name('yphp_news_cate')->where("id",$item['cate_id'])->value('cate_name');
            return $item;
        });
        
        $return_data['cate_list'] = Db::name('yphp_news_cate')->where("is_show",1)->order('cate_orders', 'desc')->select();

        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("news/news_list",$return_data);
    }

    /**
     * 文章回收站
     */
    public function newsTrash()
    {
        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['news_title'])) $whereCond[] = array('news_title','like','%'.$data['news_title'].'%');
        if (!empty($data['cate_id'])) $whereCond[] = array('cate_id','=',$data['cate_id']);
        if (!empty($data['is_show'])) $whereCond[] = array('is_show','=',$data['is_show']);
        if (!empty($data['is_recommed'])) $whereCond[] = array('is_recommed','=',$data['is_recommed']);
        
        //搜索默认值
        $return_data['news_title']     = empty($data['news_title'])?'':$data['news_title'];
        $return_data['cate_id'] = empty($data['cate_id'])?'':$data['cate_id'];
        $return_data['is_show'] = empty($data['is_show'])?'':$data['is_show'];
        $return_data['is_recommed'] = empty($data['is_recommed'])?'':$data['is_recommed'];

        //获取列表
        $data_list = Db::name('yphp_news')->where($whereCond)->where("is_del","2")->order('id', 'desc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ))->each(function($item, $key){

            $item['cate_name'] = Db::name('yphp_news_cate')->where("id",$item['cate_id'])->value('cate_name');
            return $item;
        });
        
        $return_data['cate_list'] = Db::name('yphp_news_cate')->where("is_show",1)->order('cate_orders', 'desc')->select();

        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("news/news_trash_list",$return_data);
    }

     /**
     * 文章删除 放回收站
     */
    public function newsDel()
    {
       $id  = request()->param('id');

       if (!empty($id)) 
       {

          $data = array(
            'is_del'        => 2,
            'del_datetime'  => date("Y-m-d H:i:s")
          );
           Db::name('yphp_news')->where("id",$id)->update($data);

           return json(array('status'=>'SUCCESS','msg'=>'删除成功，文章已放入回收站'));
       }
    }
    /**
     * 文章删除  彻底删除
     */
    public function newsDelReal()
    {
        $id  = request()->param('id');

       if (!empty($id)) 
       {


           Db::name('yphp_news')->where("id",$id)->delete();

           return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
       }
    }

    /**
     * 文章恢复
     */
    public function newsDelRestore()
    {
        $id  = request()->param('id');

       if (!empty($id)) 
       {


           $data = array(
            'is_del'        => 1,
            'del_datetime'  => null
          );
           Db::name('yphp_news')->where("id",$id)->update($data);

           return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
       }
    }

   

    /**
     * 添加文章
     */
    public function newsAdd()
    {
       //获取文章分类
       $cate_list = Db::name('yphp_news_cate')->where("is_show",1)->order('cate_orders', 'desc')->select();
       
       return view("news/news_add",array('cate_list'=>$cate_list));
    }
    /**
     * 修改文章
     */
    public function newsEdit()
    {
       $id = request()->param('id');

       $cate_list = Db::name('yphp_news_cate')->where("is_show",1)->order('cate_orders', 'desc')->select();

       $info = Db::name('yphp_news')->where('id',$id)->find();

        return view("news/news_edit",array('info'=>$info,'cate_list'=>$cate_list));
    }

    /**
     * 上传图片
     */
    public function uploadImg()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        $fiels = request()->file();
        // 上传到本地服务器
         try {
            validate(['image'=>'fileSize:5120|fileExt:jpg,png,gif,jpeg,bmp|fileMime:image/jpeg,image/gif,image/png,image/bmp'])->check($fiels);

            $savename = \think\facade\Filesystem::disk('public')->putFile( 'news', $file);

            return json(array('status'=>'SUCCESS','msg'=>"上传成功",'filename'=>"/uploads/".$savename));

        } catch (\think\exception\ValidateException $e) {

            return json(array('status'=>'FAIL','msg'=>"上传失败".$e->getMessage()));
        }
    }

    /**
     *  添加文件操作
     */
    public function newsAddAct()
    {
        $data = request()->param();

        $data['news_desc'] = htmlspecialchars($data['news_desc']);
        $data['news_content'] = htmlspecialchars($data['news_content']);

        if(empty($data['id']))
       {

        $data['add_datetime'] = date("Y-m-d H:i:s");

         $id = Db::name('yphp_news')->strict(false)->insertGetId($data);
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
         Db::name('yphp_news')->strict(false)->update($data);
         return json(array('status'=>'SUCCESS','msg'=>'修改成功！'));
       }
    }
    /**
     * 文章分类列表
     */
    public function newsCateList()
    {
        $data = request()->param();

        $return_data = array(
            'admin_info' => $this->admin_info,
            'admin_id'   => $this->admin_id
        );

        //搜索条件
        $whereCond = array();
        if (!empty($data['cate_name'])) $whereCond[] = array('cate_name','like','%'.$data['cate_name'].'%');
        if (!empty($data['is_show'])) $whereCond[] = array('is_show','=',$data['is_show']);
        
        //搜索默认值
        $return_data['cate_name']     = empty($data['cate_name'])?'':$data['cate_name'];
        $return_data['is_show'] = empty($data['is_show'])?'':$data['is_show'];
      

        //获取列表
        $data_list = Db::name('yphp_news_cate')->where($whereCond)->order('cate_orders', 'desc')->paginate(array(
            'list_rows' => 10,
            'query'     => $data
        ))->each(function($item, $key){

            $item['news_count'] = Db::name('yphp_news')->where("cate_id",$item['id'])->count();
            return $item;
        });


        $return_data['data_list'] = $data_list;
        // 获取分页显示
        $return_data['page'] = $data_list->render();

       return view("news/news_cate_list",$return_data);
    }

    /**
     * 文章分类
     */
    public function newsCateDel()
    {
       $id  = request()->param('id');

       if (!empty($id)) 
       {

          $news_count = Db::name('yphp_news')->where("cate_id",$id)->count();
          if ($news_count > 0) 
          {
              return json(array('status'=>'FAIL','msg'=>'删除失败，该分类下还有'.$news_count.'篇文章'));
          }
          else
          {
            Db::name('yphp_news_cate')->where("id",$id)->delete();
            return json(array('status'=>'SUCCESS','msg'=>'删除成功'));
          }
       }
    }

    /**
     * 添加文章分类
     */
    public function newsCateAdd()
    {
       return view("news/news_cate_add");
    }
    /**
     * 修改文章分类
     */
    public function newsCateEdit()
    {
       $id = request()->param('id');


       $info = Db::name('yphp_news_cate')->where('id',$id)->find();

        return view("news/news_cate_edit",array('info'=>$info));
    }
    /**
     * 添加/修改文章分类操作
     */
    public function newsCateAddAct()
    {
       $data = request()->param();

       if(empty($data['id']))
       {
         $cate_id = Db::name('yphp_news_cate')->strict(false)->insertGetId($data);
         if ($cate_id > 0) 
         {
             return json(array('status'=>'SUCCESS','msg'=>'添加成功'));
         }
         else
         {
            return json(array('status'=>'FAIL','msg'=>'添加分类失败'));
         }
       }
       else
       {
         //修改
         Db::name('yphp_news_cate')->strict(false)->update($data);
         return json(array('status'=>'SUCCESS','msg'=>'修改成功！'));
       }
    }



  

}
