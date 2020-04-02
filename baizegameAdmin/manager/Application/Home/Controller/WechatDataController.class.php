<?php
namespace Home\Controller;
use Think\Controller;
class WechatDataController extends CommonController {
    
    public function base_data(){
        
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
           
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
           
            foreach (array_fill(0,($date+1),$_GET['channel']) as $k=>$v){   // 这个什么意思啊
                foreach ($v as $vv){
                    $page_where[]=array(
                        'channel'=>$vv,
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                        'product'=>$_GET['product']
                    );
                }
            }
                
            $count      = count($page_where);// 查询满足要求的总记录数
           
           
            $Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->lastSuffix = false;//最后一页不显示为总页数
            $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('last','末页');
            $Page->setConfig('first','首页');
            $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里
            
//             $data=array();
            $data=self::base_data_core($page_where);
            $data_sum=self::base_data_sum($data);
            $data=array_slice($data,$Page->firstRow,$Page->listRows);
            array_unshift($data,$data_sum);
            
            //print_r($comma_separated);EXIT;
//             for ($i=$Page->firstRow;$i<$Page->firstRow+$Page->listRows;$i++){
//                 if($Page->totalRows<=$i){
//                     break;
//                 }
                
               
//             }
            
            self::assign('page',$page_show);
            self::assign('data',$data);
        }
        
        $product_list=M('mini_programs','','DB_CONFIG1')->where(array('mini_id'=>array('neq',2)))->cache(500)->getField('mini_appid,mini_name');
        
        self::assign('product_list',$product_list);
        self::display();
    }
    private  function base_data_core($parma){
        $data=array();
        foreach ($parma as $v){
            $parmas=array('start_time'=>$v['start_time'],
                'product'=>$v['product'],
                'channel'=>$v['channel']
            );
            $DATA=self::combination_data($parmas);
            $DATA['time']=$parmas['start_time'];
            $data[]=$DATA;
        }
        return $data;
    }
    private function  base_data_sum($parma){
        
        $data= array(
            'time'=>'汇总',
            '渠道'=>'-',
            'product'=>'-',
            '新增注册'=>array_sum(array_column($parma, '新增注册')),
            '新增创角'=>array_sum(array_column($parma, '新增创角')),
            '总登陆用户'=>array_sum(array_column($parma, '总登陆用户')),
            '老用户登陆'=>array_sum(array_column($parma, '老用户登陆')),
            '新用户付费金额'=>array_sum(array_column($parma, '新用户付费金额')),
            '老用户付费金额'=>array_sum(array_column($parma, '老用户付费金额')),
            '老用户付费人数'=>array_sum(array_column($parma, '老用户付费人数')),
            '新增付费人数'=>array_sum(array_column($parma, '新增付费人数')),
            '总付费人数'=>array_sum(array_column($parma, '总付费人数')),
            '总付费金额'=>array_sum(array_column($parma, '总付费金额')),
            );
        $data['创角转化率']=round(($data['新增创角']/$data['新增注册'])*100,2).'%';
        $data['新增付费率']=round(($data['新增付费人数']/$data['新增注册'])*100,2).'%';
        $data['新增arppu']=round(($data['新用户付费金额']/$data['新增付费人数']),2);
        $data['老用户付费率']=round(($data['老用户付费人数']/$data['老用户登陆'])*100,2).'%';
        $data['老用户arppu']=round(($data['老用户付费金额']/$data['老用户付费人数']),2);
        $data['总付费率']=round(($data['总付费人数']/$data['总登陆用户'])*100,2).'%';
        $data['总arpu']=round(($data['总付费金额']/$data['总登陆用户']),2);
        $data['总arppu']=round(($data['总付费金额']/$data['总付费人数']),2);
        $data['ltv1']=round(($data['新用户付费金额']/$data['新增注册']),2);
        return $data;
    }
    private function  combination_data($parmas){
        
        $new_user_count_where=array('appid'=>array('eq',$parmas['product']),
                              'channel'=>array('eq',$parmas['channel']),
                              'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
                              );
                
        $new_user_count=M('mini_user','','DB_CONFIG1')
                        ->where($new_user_count_where)
                        ->cache(500)
                        ->count();//新增用户数
        
        $active_user_count=M('mini_login_log','','DB_CONFIG1')
            ->where(array('appid'=>array('eq',$parmas['product']),
                'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
                ))
                //->fetchSql()
            ->cache(500)
            ->count('DISTINCT mini_user_id'); //活跃用户数
            
            
         $new_user_role=M('mini_role_report','','DB_CONFIG1')
            ->where(array('appid'=>array('eq',$parmas['product']),
                'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','in '.M('mini_user','','DB_CONFIG1')->where($new_user_count_where)->field('mini_user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //新增创角色数
            
         $old_user_count=M('mini_login_log','','DB_CONFIG1')
            ->where(array('appid'=>array('eq',$parmas['product']),
                'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'mini_user_id'=>array('exp','not in '.M('mini_user','','DB_CONFIG1')->where($new_user_count_where)->field('mini_user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->count('DISTINCT mini_user_id'); //老用户登录数
        //订单表的渠道维度，给不给都无所谓，经过测试，准确无误。
       $new_user_order= M('mini_game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                'mini_appid'=>array('eq',$parmas['product']),
//                 'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','in '.M('mini_user','','DB_CONFIG1')->where($new_user_count_where)->field('mini_user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->field('sum(money)as new_user_pay,count(DISTINCT user_id) as new_user_pay_count')
            ->find(); //新增付费金额，新增付费人数
            
        $old_user_order= M('mini_game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                'mini_appid'=>array('eq',$parmas['product']),
                 'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','not in '.M('mini_user','','DB_CONFIG1')->where($new_user_count_where)->field('mini_user_id')->buildSql())
            ))
            ->cache(500)
            ->field('sum(money)as old_user_pay,count(DISTINCT user_id) as old_user_pay_count')
            ->find(); //老用户付费金额，老用户付费人数
         
         $order=M('mini_game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                'mini_appid'=>array('eq',$parmas['product']),
                'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
            ))
            ->cache(500)
            ->field('sum(money)as user_pay,count(DISTINCT user_id) as user_pay_count')
            ->find(); //付费金额,付费人数
    
    
     // 上面方法到时候可模型化
         
            $new_user_order['new_user_pay']=$new_user_order['new_user_pay']/100;
            $old_user_order['old_user_pay']=$old_user_order['old_user_pay']/100;
            $order['user_pay']=$order['user_pay']/100;
            
            $data=array(
                '渠道'=>$parmas['channel'],
                '新增注册'=>$new_user_count?sprintf("%.0f",$new_user_count):"-",
                '新增创角'=>$new_user_role?sprintf("%.0f",$new_user_role):"-",
                '创角转化率'=>$new_user_role/$new_user_count?round(($new_user_role/$new_user_count)*100,2).'%':"-",
                '总登陆用户'=>$active_user_count?sprintf("%.0f",$active_user_count):"-",
                '老用户登陆'=>$old_user_count?sprintf("%.0f",$old_user_count):"-",
                '新增付费率'=>$new_user_order['new_user_pay_count']/$new_user_count?round(($new_user_order['new_user_pay_count']/$new_user_count)*100,2).'%':"-",
                '新增付费人数'=>$new_user_order['new_user_pay_count'],
                '新增arppu'=>$new_user_order['new_user_pay']/$new_user_order['new_user_pay_count']?round($new_user_order['new_user_pay']/$new_user_order['new_user_pay_count'],2):"-",
                '新用户付费金额'=>$new_user_order['new_user_pay']?sprintf("%.2f",$new_user_order['new_user_pay']):"-",
                '老用户付费金额'=>$old_user_order['old_user_pay']?sprintf("%.2f",$old_user_order['old_user_pay']):"-",
                '老用户付费率'=>$old_user_order['old_user_pay_count']/$old_user_count?round(($old_user_order['old_user_pay_count']/$old_user_count)*100,2).'%':"-",
                '老用户付费人数'=>$old_user_order['old_user_pay_count'],
                '老用户arppu'=>$old_user_order['old_user_pay']/$old_user_order['old_user_pay_count']?round($old_user_order['old_user_pay']/$old_user_order['old_user_pay_count'],2):"-",
                '总付费人数'=>$order['user_pay_count']?sprintf("%.0f",$order['user_pay_count']):"-",
                '总付费金额'=>$order['user_pay']?sprintf("%.2f",$order['user_pay']):"-",
                '总付费率'=>$order['user_pay_count']/$active_user_count?round(($order['user_pay_count']/$active_user_count)*100,2).'%':"-",
                '总arpu'=>$order['user_pay']/$active_user_count?round($order['user_pay']/$active_user_count,2):"-",
                '总arppu'=>$order['user_pay']/$order['user_pay_count']?round($order['user_pay']/$order['user_pay_count'],2):"-",
                'ltv1'=>$new_user_order['new_user_pay']/$new_user_count?round($new_user_order['new_user_pay']/$new_user_count,2):"-"
            );    
            return $data;   
    }
    
    public function get_mini_appid_channel(){
        $channel_list=M('mini_game_channel','','DB_CONFIG1')->where(array('appid'=>array('eq',$_POST['appid'])))->group('channel')->cache(500)->getField('channel',true);
        // foreach ($channel_list as $key => $value) {
        //     $channel = M('mini_game_channel','','DB_CONFIG1')->where(array('appid'=>array('eq',$_POST['appid']),'channel'=>$value))->find();
        //     if(!$channel){
        //         $data = array(
        //             'appid'=>$_POST['appid'],
        //             'channel'=>$value,
        //         );
        //         M('mini_game_channel','','DB_CONFIG1')->add($data);
        //         unset($data);
        //         unset($channel);
        //     }
        // }
        // M()->getField($field)
        self::success($channel_list);
    }
    
    public function retain(){
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
            
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            
            $count      = $date;// 查询满足要求的总记录数
            
            $Page       = new \Think\Page($count+1,15);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->lastSuffix = false;//最后一页不显示为总页数
            $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('last','末页');
            $Page->setConfig('first','首页');
            $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里
            
            $data=array();
            //print_r($comma_separated);EXIT;
            for ($i=$Page->firstRow;$i<$Page->firstRow+$Page->listRows;$i++){
                if($Page->totalRows<=$i){
                    break;
                }
                $parmas=array('start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$i.'day')),
                    'product'=>$_GET['product'],
                    'channel'=>$_GET['channel'],
                   
                );
                
                $DATA=self::retain_combination_data($parmas);
                
                $DATA['time']=$parmas['start_time'];

                $data[]=$DATA;
            }
            
            self::assign('page',$page_show);
            self::assign('data',$data);
        }
        $product_list=M('mini_programs','','DB_CONFIG1')->where(array('mini_id'=>array('neq',2)))->cache(500)->getField('mini_appid,mini_name');
        
        self::assign('product_list',$product_list);
        self::display();
    }
    private function  retain_combination_data($parmas){
        
        $new_user_count_where=array('appid'=>array('eq',$parmas['product']),
            'channel'=>array('in',$parmas['channel']),
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
        );
        $new_user_count=M('mini_user','','DB_CONFIG1')
                ->where($new_user_count_where)
                ->cache(500)
                ->count();//新增用户数
        
        $date=ceil((time()-strtotime($parmas['start_time']))/86400);
        $data=array();
        for($i=0;$i<$date+1;$i++){
           $old_user_count=M('mini_login_log','','DB_CONFIG1')
            ->where(array('appid'=>array('eq',$parmas['product']),
                'channel'=>array('in',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'].' +'.$i.' day')),array('lt',strtotime($parmas['start_time'].' +'.($i+1).' day')),'and'),
                'mini_user_id'=>array('exp','in '.M('mini_user','','DB_CONFIG1')->where($new_user_count_where)->field('mini_user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->count('DISTINCT mini_user_id');
            $data['retain']['retain'.($i)]=$old_user_count/$new_user_count?round(($old_user_count/$new_user_count)*100,2).'%':'-';
        }
        $data['用户数量']=$new_user_count;
        return $data;
    }
    
    public function get_ltv(){
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
            
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            
            $count      = $date;// 查询满足要求的总记录数
            
            $Page       = new \Think\Page($count+1,15);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->lastSuffix = false;//最后一页不显示为总页数
            $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('last','末页');
            $Page->setConfig('first','首页');
            $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里
            
            $data=array();
            //print_r($comma_separated);EXIT;
            for ($i=$Page->firstRow;$i<$Page->firstRow+$Page->listRows;$i++){
                if($Page->totalRows<=$i){
                    break;
                }
                $parmas=array('start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$i.'day')),
                    'product'=>$_GET['product'],
                    'channel'=>$_GET['channel'],
                    
                );
                
                $DATA=self::ltv_combination_data($parmas);
                
                $DATA['time']=$parmas['start_time'];
                
                $data[]=$DATA;
            }
            
            self::assign('page',$page_show);
            self::assign('data',$data);
        }
        $product_list=M('mini_programs','','DB_CONFIG1')->where(array('mini_id'=>array('neq',2)))->cache(500)->getField('mini_appid,mini_name');
        
        self::assign('product_list',$product_list);
        self::display();
    }
    
    private function ltv_combination_data($parmas){
        $new_user_count_where=array('appid'=>array('eq',$parmas['product']),
            'channel'=>array('eq',$parmas['channel']),
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
        );
        $new_user_count=M('mini_user','','DB_CONFIG1')
        ->where($new_user_count_where)
        ->cache(500)
        ->count();//新增用户数
        
        $sum_new_user_order= M('mini_game_order','','DB_CONFIG1')
        ->where(array(
            'status'=>array('egt',1),
            'mini_appid'=>array('eq',$parmas['product']),
//             'channel'=>array('eq',$parmas['channel']),
            'user_id'=>array('exp','in '.M('mini_user','','DB_CONFIG1')->where($new_user_count_where)->field('mini_user_id')->buildSql())
        ))
        //->fetchSql()
        ->cache(500)
        ->field('sum(money)as new_user_pay,count(DISTINCT user_id) as new_user_pay_count')
        ->find(); //新增付费金额，新增付费人数
        
        $date=floor((time()-strtotime($parmas['start_time']))/86400);
        $data=array();
        for($i=0;$i<$date;$i++){
            $new_user_order= M('mini_game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                'mini_appid'=>array('eq',$parmas['product']),
                'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +'.($i+1).' day')),'and'),
                'user_id'=>array('exp','in '.M('mini_user','','DB_CONFIG1')->where($new_user_count_where)->field('mini_user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->field('sum(money)as new_user_pay,count(DISTINCT user_id) as new_user_pay_count')
            ->find(); //新增付费金额，新增付费人数
            $new_user_order['new_user_pay']=$new_user_order['new_user_pay']/100;
            $data['ltv']['ltv'.($i+1)]=$new_user_order['new_user_pay']/$new_user_count?round(($new_user_order['new_user_pay']/$new_user_count)*100,2).'%':'-';
        }
        $data['新增注册人数']=$new_user_count;
        $data['充值总额']=$sum_new_user_order['new_user_pay']/100?round($sum_new_user_order['new_user_pay']/100,2):'-';
        return $data;
    }
    
    public function wx_list(){
        if($_POST){
            $gameList = M('mini_programs','','DB_CONFIG1')->where(array('mini_id'=>$_POST['miniId']))->find();
            $reserve = json_decode($gameList['reserve'],true);
            if ($_POST['inputValue']){ //游戏总开关
                $reserve['switch'] = $_POST['inputValue'];
            }elseif($_POST['inputValueIp']){ //游戏白名单开关
                $reserve['switchIp'] = $_POST['inputValueIp'];
            }
            $save['reserve'] = json_encode($reserve);
            $res = M('mini_programs','','DB_CONFIG1')->where(array('mini_id'=>$gameList['mini_id']))->save($save);
            if ($res){
                self::ajaxReturn(array('type'=>'1'),'json');
            }else{
                self::ajaxReturn(array('type'=>'2'),'json');
            }
        }else{
            //获取除支付壳外的小程序列表
            $list = M('mini_programs','','DB_CONFIG1')->where(array('mini_id'=>array('neq',2)))->select();
            foreach ($list as $k=>$v ){
                $reserve = json_decode($v['reserve'],true);
                $list[$k]['switch'] = $reserve['switch'];
                $list[$k]['switchIp'] = $reserve['switchIp'];
            }
            self::assign('list',$list);
            self::display();
        }
    }

    //查询充值总额
    public function pay_query(){

        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
       
        $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
        
        $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
       
        foreach (array_fill(0,($date+1),$_GET['channel']) as $k=>$v){
            foreach ($v as $vv){
                $page_where[]=array(
                    'channel'=>$vv,
                    'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                    'product'=>$_GET['product']
                );
            }
        }
            
        $count      = count($page_where);// 查询满足要求的总记录数
       
       
        $Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->lastSuffix = false;//最后一页不显示为总页数
        $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('last','末页');
        $Page->setConfig('first','首页');
        $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里
        
//             $data=array();
        $data=self::base_query_core($page_where);
        $data_sum=self::base_query_sum($data);
        $data=array_slice($data,$Page->firstRow,$Page->listRows);
        array_unshift($data,$data_sum);
        
        //print_r($comma_separated);EXIT;
//             for ($i=$Page->firstRow;$i<$Page->firstRow+$Page->listRows;$i++){
//                 if($Page->totalRows<=$i){
//                     break;
//                 }
            
           
//             }
        
        self::assign('page',$page_show);
        self::assign('data',$data);
    }
    
    $product_list=M('mini_programs','','DB_CONFIG1')->where(array('mini_id'=>array('neq',2)))->cache(500)->getField('mini_appid,mini_name');
    
    self::assign('product_list',$product_list);
    self::display();
    }

    private  function base_query_core($parma){
        $data=array();
        foreach ($parma as $v){
            $parmas=array('start_time'=>$v['start_time'],
                'product'=>$v['product'],
                'channel'=>$v['channel']
            );
            $DATA=self::combination_query($parmas);
            $DATA['time']=$parmas['start_time'];
            $data[]=$DATA;
        }
        return $data;
    }

    private function  base_query_sum($parma){
        $data= array(
            'time'=>'汇总',
            '渠道'=>'-',
            'product'=>'-',
            '总付费金额'=>array_sum(array_column($parma, '总付费金额')),
            );
        return $data;
    }

    private function combination_query($parmas){
         
         $order=M('mini_game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                'mini_appid'=>array('eq',$parmas['product']),
                'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
            ))
            ->cache(500)
            ->field('sum(money)as user_pay,count(DISTINCT user_id) as user_pay_count')
            ->find(); //付费金额,付费人数
    
     // 上面方法到时候可模型化
         
            $order['user_pay']=$order['user_pay']/100;
            
            $data=array(
                '渠道'=>$parmas['channel'],
                '总付费金额'=>$order['user_pay']?sprintf("%.2f",$order['user_pay']):"-",
            );    
            return $data;   
    }

    public function query_sectionExecl(){
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
            $_GET['channel'] = json_decode($_GET['channel'],ture);

            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
           
            foreach (array_fill(0,($date+1),$_GET['channel']) as $k=>$v){
                foreach ($v as $vv){
                    $page_where[]=array(
                        'channel'=>$vv,
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                        'product'=>$_GET['product']
                    );
                }
            }
                
            // $count      = count($page_where);// 查询满足要求的总记录数
           // print_r($count);die;
           
           //  $Page       = new \Think\Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
           //  $Page->lastSuffix = false;//最后一页不显示为总页数
           //  $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
           //  $Page->setConfig('prev','上一页');
           //  $Page->setConfig('next','下一页');
           //  $Page->setConfig('last','末页');
           //  $Page->setConfig('first','首页');
           //  $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
           //  $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里
            
            $data=self::base_query_core($page_where);
            $data_sum=self::base_query_sum($data);
            // $data=array_slice($data,$Page->firstRow,$Page->listRows);
            array_unshift($data,$data_sum);
            self::queryPay_execlData($data);
            
        }
    }

    //导出Excel方法
    public function queryPay_execlData($data){
        // require_once  LIB_PATH . 'Org/Util/PHPExcel/PHPExcel.php';
        $game = M('mini_programs','','DB_CONFIG1')->where(array('mini_appid'=>$_GET['product']))->find();
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Worksheet.Drawing");
        import("Org.Util.PHPExcel.Writer.Excel2007");
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);//宽度

        $objActSheet->setCellValue('A1', '时间');
        $objActSheet->setCellValue('B1', '产品');
        $objActSheet->setCellValue('C1', '渠道');
        $objActSheet->setCellValue('D1', '总付费金额');
        foreach($data as $k=>$val){
            $k +=2;
            $objActSheet->setCellValue('A'.$k, $val['start_time']);
            $objActSheet->setCellValue('B'.$k, $game['mini_name']);  
            $objActSheet->setCellValue('C'.$k, $val['渠道']);  
            $objActSheet->setCellValue('D'.$k, $val['总付费金额']);
        }
        $fileName = '总付费金额数据表';
        $date = date("Y-m-d",time());
        $fileName .= "_{$date}.xlsx";
        //将输出重定向到一个客户端web浏览器(Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $objWriter->save('php://output');
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        exit;
    }

    public function query_order(){
        if($_GET['gameType']){
            if ($_GET['role_id'] && !$_GET['order_no']) {
                $user = M('mini_role_report','','DB_CONFIG1')->where(array('cproleid'=>$_GET['role_id']))->find();
                $order = M('mini_game_order','','DB_CONFIG1')->where(array('cp_role_id'=>$user['cproleid'],'user_id'=>$user['user_id'],'server_id'=>$user['srvid'],'mini_appid'=>$user['appid'],'status'=>array('lt','2')))->order('mini_game_order_id desc')->select();
            }elseif(!$_GET['role_id'] && $_GET['order_no']){
                $order = M('mini_game_order','','DB_CONFIG1')->where(array('u_order_id'=>$_GET['order_no'],'status'=>array('lt','2')))->order('mini_game_order_id desc')->select();
                $user = M('mini_role_report','','DB_CONFIG1')->where(array('cproleid'=>$order['cp_role_id'],'srvid'=>$order['server_id'],'appid'=>$order['mini_appid']))->find();
            }else if($_GET['role_id'] && $_GET['order_no']){
                $user = M('mini_role_report','','DB_CONFIG1')->where(array('cproleid'=>$_GET['role_id']))->find();
                $order = M('mini_game_order','','DB_CONFIG1')->where(array('cp_role_id'=>$user['cproleid'],'user_id'=>$user['user_id'],'server_id'=>$user['srvid'],'mini_appid'=>$user['appid'],'u_order_id'=>$_GET['order_no'],'status'=>array('lt','2')))->order('mini_game_order_id desc')->select();
            }

            $count      = count($order);// 查询满足要求的总记录数
           
           
            $Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->lastSuffix = false;//最后一页不显示为总页数
            $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('last','末页');
            $Page->setConfig('first','首页');
            $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里
            

            foreach ($order as $key => $value) {
                $data[$key] = array(
                    'gameName'=>$value['mini_name'],
                    'ext'=>$value['server_id'],
                    'roleName'=>$user['nickname'],
                    'roleId'=>$value['cp_role_id'],
                    'orderSn'=>$value['u_order_id'],
                    'orderMoney'=>round($value['money']/100),
                    'orderState'=>$value['status'],
                    'orderTime'=>date('Y-m-d H:i:s',$value['create_date']),
                );
            }
            $data=array_slice($data,$Page->firstRow,$Page->listRows);
            self::assign('page',$page_show);
            self::assign('data',$data);
        }
        self::display();
    }

    public function kefupay(){
        $orderNo = $_POST['orderNo'];
        $order = M('mini_game_order','','DB_CONFIG1')->where(array('u_order_id'=>$orderNo))->find();
        if($order['status']!='1'){
            $save['status'] = '1';
            M('mini_game_order','','DB_CONFIG1')->where(array('u_order_id'=>$orderNo))->save($save);
        }
        $notify_url = 'https://api.baizegame.com/Admin_game_switch/cp_notify';
        $content = $this->curl_post($notify_url,$order);
        print_r($content);die;
        if($content){
            self::success('1');//补单成功
        }else{
            self::success('4');//研发补单失败，请联系研发处理
        }
    }


    public function curl_post($url, $data, $header = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        if (gettype($data) == 'array' || gettype($data) == 'object') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->to_params($data));
        } elseif (gettype($data) == 'string') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        if ($header) {
            $header_list = array();
            foreach ($header as $key => $value) {
                $header_list[] = "$key: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header_list);
        }

        $str = curl_exec($ch);
        curl_close($ch);

        return $str;
    }

    public function to_params($input)
    {
        $index = 0;
        $pair = '';
        foreach ($input as $key => $value) {
            if ($index != 0) {
                $pair .= '&';
            }
            $pair .= "$key=".$value;
            ++$index;
        }

        return $pair;
    }

}