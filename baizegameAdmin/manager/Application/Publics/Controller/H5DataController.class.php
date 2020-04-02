<?php
namespace Publics\Controller;
use Think\Controller;
class H5DataController extends Controller {
    public $channel=array(
        'Uj0O5527nKRG0A9q'=>array(
            'product'=>2,
            'channel'=>array(8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27)
        ),
        //英雄训练师[至劲]
        'jwpgAorzsuKyYhWZ'=>array(
            'product'=>3,
            'channel'=>7,
            'game_id'=>7,
            'platform'=>'gowanme',
            'type'=>'zhijin',
        ),
        //英雄训练师[枪火侏罗纪]
        'C3aLnPcXLWIbva1h'=>array(
            'product'=>3,
            'channel'=>array('QHZLJ-yxxls0001'),
            'game_id'=>6,
            'platform'=>'wxminigame',
            'type'=>'qianghuozhuluoji',
        ),
        //英雄训练师[香蕉]
        'opjoiIbSwHux6ip2'=>array(
            'product'=>3,
            'channel'=>array('XJcpt-yxxls0001','XJcpt-yxxls0002','XJcpt-yxxls0003'),
            'game_id'=>6,
            'platform'=>'wxminigame',
            'type'=>'xiangjiao',
        ),
        //英雄训练师[至劲]
        'gOteXeb2efs794n3'=>array(
            'product'=>3,
            'channel'=>array('ZJ-yxxls0001'),
            'game_id'=>6,
            'platform'=>'wxminigame',
            'type'=>'zhijin',
        ),
        //英雄训练师[星大陆]
        'COM8D7UQEIYMdD7E'=>array(
            'product'=>3,
            'channel'=>array('XDL-yxxls0001'),
            'game_id'=>6,
            'platform'=>'wxminigame',
            'type'=>'xingdalu',
        ),
        //英雄训练师[方块玩]
        'YDmmuCginipnbGLW'=>array(
            'product'=>3,
            'channel'=>array('FKWcpa-yxxls0001'),
            'game_id'=>6,
            'platform'=>'wxminigame',
            'type'=>'fankuaiwan',
        ),
        //口袋精灵王[方块玩]
        'oc99zH2OL9f8V2Gc'=>array(
            'product'=>7,
            'channel'=>array('FKWcpa-kdjlw0002'),
            'game_id'=>40,
            'platform'=>'wxminigame',
            'type'=>'fankuaiwan',
        ),
        //三国之定江山
        'ovZwR3FmX0ORYD2e'=>array(
            'product'=>4,
            'channel'=>28,
            'game_id'=>28,
            'platform'=>'wxminigame',
        ),
        //口袋精灵王[枪火侏罗纪]
        'TpXJmuyVfFD31I36'=>array(
            'product'=>7,
            'channel'=>array('QHZLJ-kdjlw0001'),
            'game_id'=>40,
            'platform'=>'wxminigame',
            'type'=>'qianghuozhuluoji',
        ),
        //口袋精灵王[]
        'At8c5Akl56kpOkKj'=>array(
            'product'=>7,
            'channel'=>array('JSCCC-kdjlw0001'),
            'game_id'=>40,
            'platform'=>'wxminigame',
        ),
        //玄元修仙[君海]
        'DDzZmizOHIeNLBP5'=>array(
            'product'=>11,
            'channel'=>array('allu'),
            'game_id'=>46,
            'platform'=>'wxminigame',
            'type'=>'junhai',
        ),
        //小精灵宝可萌新版[雨墨]
        'kTgl7cYmko5nYiDh'=>array(
            'product'=>14,
            'channel'=>array('allu'),
            'game_id'=>50,
            'platform'=>'wxminigame',
            'type'=>'yumo',
        ),
        //纵剑世界
        'V0hll3E7Q1HXBEBk'=>array(
            'product'=>2,
            'channel'=>2,
            'game_id'=>2,
            'platform'=>'azwdone',
        ),
    );
    
    public function base_data(){
        
        if($_GET['start_time']&&$_GET['channel']){
           
            if(!array_key_exists($_GET['channel'],$this->channel)){
                exit(self::error('非法访问'));
            }
            $product=$this->channel[$_GET['channel']]['product'];
            $channel=$this->channel[$_GET['cps']]['channel']!=''?$this->channel[$_GET['cps']]['channel']:$this->channel[$_GET['channel']]['channel'];
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            foreach (array_fill(0,($date+1),$channel) as $k=>$v){
                foreach ($v as $vv){
                    $page_where[]=array('channel'=>$vv,'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')));
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
            
            $data=array();
            //print_r($comma_separated);EXIT;
            for ($i=$Page->firstRow;$i<$Page->firstRow+$Page->listRows;$i++){
                if($Page->totalRows<=$i){
                    break;
                }
                
                $parmas=array('start_time'=>$page_where[$i]['start_time'],
                    'product'=>$product,
                    'channel'=>$page_where[$i]['channel'],
                    'game_id'=>$this->channel[$_GET['cps']]['game_id']?$this->channel[$_GET['cps']]['game_id']:$this->channel[$_GET['channel']]['game_id']
                );
                
                $DATA=self::combination_data($parmas);
                $DATA['time']=$parmas['start_time'];
                $data[]=$DATA;
            }
            $data_sum=self::base_data_sum($data);
            array_unshift($data,$data_sum);
            self::assign('page',$page_show);
            self::assign('data',$data);
        }
//         $product_list=M('game_father','','DB_CONFIG1')->cache(500)->getField('game_father_id,game_father_name');
        $gameList = M('game_cps','','DB_CONFIG1')->where(array('channel'=>$this->channel[$_GET['channel']]['type']))->select();
        foreach ($gameList as $key => $val){
            $cps[$val['cps']] .= $val['game_name'];
        }
        self::assign('product_list',$cps);
//         self::assign('product_list',$this->channel[$_GET['channel']]['channel']);
        self::display();
    }
    private  function sum_combination_data($parmas){
        
    }
    
    private function  base_data_sum($parma){
        $data= array(
            'time'=>'汇总',
            '渠道'=>'-',
            '来源'=>'-',
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
        
        $new_user_count_where=array(
                              'game_id'=>array('eq',$parmas['game_id']),
                              'channel'=>array('eq',$parmas['channel']),
                              'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
                              );
                
        $new_user_count=M('user','','DB_CONFIG1')
                        ->where($new_user_count_where)
                        ->cache(500)
                        ->count();//新增用户数
                        
        $active_user_count=M('login_report','','DB_CONFIG1')
            ->where(
                array('game_father_id'=>array('eq',$parmas['product']),
                'game_id'=>array('eq',$parmas['game_id']),
                'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
                ))
                //->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //活跃用户数
            
            
         $new_user_role=M('create_role_report','','DB_CONFIG1')
         ->where(array('game_father_id'=>array('eq',$parmas['product']),
             'game_id'=>array('eq',$parmas['game_id']),
//              'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
//             ->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //新增创角色数
            
            
         $old_user_count=M('create_role_report','','DB_CONFIG1')
         ->where(array('game_father_id'=>array('eq',$parmas['product']),
             'game_id'=>array('eq',$parmas['game_id']),
             'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','not in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
//             ->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //老用户登录数
            
            
        
       $new_user_order= M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                'game_father'=>array('eq',$parmas['product']),
                'game_id'=>array('eq',$parmas['game_id']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->field('sum(money)as new_user_pay,count(DISTINCT user_id) as new_user_pay_count')
            ->find(); //新增付费金额，新增付费人数
            
            
        $old_user_order= M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                'game_father'=>array('eq',$parmas['product']),
                'channel'=>array('eq',$parmas['channel']),
                'game_id'=>array('eq',$parmas['game_id']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','not in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
            ->cache(500)
            ->field('sum(money)as old_user_pay,count(DISTINCT user_id) as old_user_pay_count')
            ->find(); //老用户付费金额，老用户付费人数
            
            
         $order=M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                'game_father'=>array('eq',$parmas['product']),
                'channel'=>array('eq',$parmas['channel']),
                'game_id'=>array('eq',$parmas['game_id']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
            ))
            ->cache(500)
            ->field('sum(money)as user_pay,count(DISTINCT user_id) as user_pay_count')
            ->find(); //付费金额,付费人数
            
    
     // 上面方法到时候可模型化
         
            $new_user_order['new_user_pay']=$new_user_order['new_user_pay']/100;
            $old_user_order['old_user_pay']=$old_user_order['old_user_pay']/100;
            $order['user_pay']=$order['user_pay']/100;
//             $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$parmas['game_id'])))->cache(500)->field('concat(game_name,"-",game_id) as channel_name')->find();
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
                '老用户付费率'=>$old_user_order['old_user_pay_count']/$old_user_count?round(($old_user_order['old_user_pay_count']/$old_user_count)*100).'%':"-",
                '老用户付费人数'=>$old_user_order['old_user_pay_count'],
                '老用户arppu'=>$old_user_order['old_user_pay']/$old_user_order['old_user_pay_count']?round($old_user_order['old_user_pay']/$old_user_order['old_user_pay_count']):"-",
                '总付费人数'=>$order['user_pay_count']?sprintf("%.0f",$order['user_pay_count']):"-",
                '总付费金额'=>$order['user_pay']?sprintf("%.2f",$order['user_pay']):"-",
                '总付费率'=>$order['user_pay_count']/$active_user_count?round(($order['user_pay_count']/$active_user_count)*100,2).'%':"-",
                '总arpu'=>$order['user_pay']/$active_user_count?round($order['user_pay']/$active_user_count,2):"-",
                '总arppu'=>$order['user_pay']/$order['user_pay_count']?round($order['user_pay']/$order['user_pay_count'],2):"-",
                'ltv1'=>$new_user_order['new_user_pay']/$new_user_count?round($new_user_order['new_user_pay']/$new_user_count,2):"-"
            );    
            return $data;   
    }

    public function wechat_game(){
        $game_cps = M('game_cps','','DB_CONFIG1')->where(array('channel'=>array('eq',$_GET['cps_code'])))->select();
        // print_r($game_cps);die;
        foreach ($game_cps as $k=>$v){
            if($v['product_id']){
                $channel .= $v['product_id'].',';
            }
        }
        $channel = rtrim($channel, ',');
        $condition['channel'] = explode(',', $channel);
        for ($i=0; $i < count($condition['channel']); $i++) {  
            $condition['into'] .= "`game_father_id`='".$condition['channel'][$i]."' OR ";
        }
        $condition['_string'] = rtrim($condition['into'], " OR ");
        $product_list=M('game_father','','DB_CONFIG1')->where(array($condition['_string']))->getField('game_father_id,game_father_name');
        $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
        $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
        if($_GET['start_time']&&$_GET['channel']){
            if($_GET['cps_code']!='junhai' && $_GET['cps_code']!='zhijin' && !$_GET['cpscode'] && $_GET['cps_code']!='jklapp' && $_GET['cps_code']!='kejin0001' && $_GET['cps_code']!='kejin0002' && $_GET['cps_code']!='kejin0003' && $_GET['cps_code']!='kejin0004' && $_GET['cps_code']!='zishi'){
                exit(self::error('请选择小游戏渠道进行数据查询！'));
            }
            if ($_GET['cpscode']) {
                foreach (array_fill(0,($date+1),$_GET['cpscode']) as $k=>$v){
                    foreach ($v as $vv){
                        $page_where[]=array(
                            'channel'=>$vv,
                            'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                            'game_id'=>$_GET['channel']['0'],
                            'channel_name'=>'channel',
                            'game_father_name'=>'game_id',
                        );
                    }
                }
            }else{
                foreach (array_fill(0,($date+1),$_GET['channel']) as $k=>$v){
                    foreach ($v as $vv){
                        $page_where[]=array(
                            'channel'=>$vv,
                            'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                            // 'game_id'=>$_GET['channel']['0'],
                        );
                    }
                }
            }
            $count      = count($page_where);// 查询满足要求的总记录数
            $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->lastSuffix = false;//最后一页不显示为总页数
            $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('last','末页');
            $Page->setConfig('first','首页');
            $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里
            
            $data=array();
//            if($_SERVER['REMOTE_ADDR']=='113.67.157.101'){
//                print_r(array_chunk($page_where, 15));die;
//            }
//            $new_page_where = array_chunk($page_where, 10);
//            if($_GET['p']){
//                if($_GET['p']=='1'){
//                    $page_count = '0';
//                }else{
//                    $page_count = $_GET['p']-1;
//                }
//            }else{
//                $page_count = '0';
//            }
//            $new_page_where[$page_count]
            foreach ($page_where as $key => $value) {
                $parmas=array(
                    'start_time'=>$value['start_time'],
                    'product'=>$value['channel_name']?$_GET['channel'][0]:$_GET['product'],
                    'channel'=>$value['channel'],
                    'name'=>$value['channel_name']?$value['channel_name']:'game_id',
                    'father_name'=>$value['game_father_name']?$value['game_father_name']:'game_father_id',
                );
                
                $DATA=self::wehcta_combination_data($parmas);
                $DATA['time']=$parmas['start_time'];
                // $DATA['ltv']=self::ltv_combination_data($parmas);
                // $DATA['retain']=self::retain_combination_data($parmas);
                if($DATA['渠道']=='YM-xjlbkmxb0001'){
                    $DATA['来源'] = '成语文曲星';
                }else if($DATA['渠道']=='YM-xjlbkmxb0002'){
                    $DATA['来源'] = '英勇小炮手';
                }else if($DATA['渠道']=='YM-xjlbkmxb0003'){
                    $DATA['来源'] = '女帝升职记';
                }else if($DATA['渠道']=='YM-xjlbkmxb0004'){
                    $DATA['来源'] == '天天开铺子';
                }
                $data[]=$DATA;

            }
            $data_sum=self::base_data_sum($data);
            $data=array_slice($data,$Page->firstRow,$Page->listRows);
            array_unshift($data,$data_sum);
            self::assign('page',$page_show);
            self::assign('data',$data);
        }

        self::assign('product_list',$product_list);
        self::display();
    }

        private function  wehcta_combination_data($parmas){
        $new_user_count_where=array(
                              $parmas['father_name']=>array('eq',$parmas['product']),
                              $parmas['name']=>array('eq',$parmas['channel']),
                              'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
                              );
                
        $new_user_count=M('user','','DB_CONFIG1')
                        ->where($new_user_count_where)
                        ->cache(500)
                        ->count();//新增用户数
                        
        $active_user_count=M('login_report','','DB_CONFIG1')
            ->where(
                array(
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
                ))
                //->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //活跃用户数
            
            
         $new_user_role=M('create_role_report','','DB_CONFIG1')
         ->where(array(
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
//             ->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //新增创角色数
            
            
         $old_user_count=M('login_report','','DB_CONFIG1')
         ->where(array(
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','not in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
//             ->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //老用户登录数
            
        
       $new_user_order= M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->field('sum(money)as new_user_pay,count(DISTINCT user_id) as new_user_pay_count')
            ->find(); //新增付费金额，新增付费人数
            
            
        $old_user_order= M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','not in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
            ->cache(500)
            ->field('sum(money)as old_user_pay,count(DISTINCT user_id) as old_user_pay_count')
            ->find(); //老用户付费金额，老用户付费人数
            
            
         $order=M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
            ))
            ->cache(500)
            ->field('sum(money)as user_pay,count(DISTINCT user_id) as user_pay_count')
            ->find(); //付费金额,付费人数
            
    
     // 上面方法到时候可模型化
         
            $new_user_order['new_user_pay']=$new_user_order['new_user_pay']/100;
            $old_user_order['old_user_pay']=$old_user_order['old_user_pay']/100;
            $order['user_pay']=$order['user_pay']/100;
            if ($parmas['name']=='channel') {
                $channel_name['channel_name'] = $parmas['channel'];
            }else{
                $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$parmas['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();
                    
            }
//             $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$parmas['game_id'])))->cache(500)->field('concat(game_name,"-",game_id) as channel_name')->find();
            $data=array(
                '渠道'=>$channel_name['channel_name'],
                '新增注册'=>$new_user_count?sprintf("%.0f",$new_user_count):"-",
                '新增创角'=>$new_user_role?sprintf("%.0f",$new_user_role):"-",
                '创角转化率'=>$new_user_role/$new_user_count?round(($new_user_role/$new_user_count)*100,2).'%':"-",
                '总登陆用户'=>$active_user_count?sprintf("%.0f",$active_user_count):"-",
                '老用户登陆'=>$old_user_count?sprintf("%.0f",$old_user_count):"-",
                '新增付费人数'=>$new_user_order['new_user_pay_count'],
                '新增付费率'=>$new_user_order['new_user_pay_count']/$new_user_count?round(($new_user_order['new_user_pay_count']/$new_user_count)*100,2).'%':"-",
                '新增arppu'=>$new_user_order['new_user_pay']/$new_user_order['new_user_pay_count']?round($new_user_order['new_user_pay']/$new_user_order['new_user_pay_count'],2):"-",
                '新用户付费金额'=>$new_user_order['new_user_pay']?sprintf("%.2f",$new_user_order['new_user_pay']):"-",
                '老用户付费金额'=>$old_user_order['old_user_pay']?sprintf("%.2f",$old_user_order['old_user_pay']):"-",
                '老用户付费率'=>$old_user_order['old_user_pay_count']/$old_user_count?round(($old_user_order['old_user_pay_count']/$old_user_count)*100,2).'%':"-",
                '老用户付费人数'=>$old_user_order['old_user_pay_count'],
                '老用户arppu'=>$old_user_order['old_user_pay']/$old_user_order['old_user_pay_count']?round($old_user_order['old_user_pay']/$old_user_order['old_user_pay_count']):"-",
                '总付费人数'=>$order['user_pay_count']?sprintf("%.0f",$order['user_pay_count']):"-",
                '总付费金额'=>$order['user_pay']?sprintf("%.2f",$order['user_pay']):"-",
                '总付费率'=>$order['user_pay_count']/$active_user_count?round(($order['user_pay_count']/$active_user_count)*100,2).'%':"-",
                '总arpu'=>$order['user_pay']/$active_user_count?round($order['user_pay']/$active_user_count,2):"-",
                '总arppu'=>$order['user_pay']/$order['user_pay_count']?round($order['user_pay']/$order['user_pay_count'],2):"-",
                'ltv1'=>$new_user_order['new_user_pay']/$new_user_count?round($new_user_order['new_user_pay']/$new_user_count,2):"-"
            );
            return $data;
    }
    
    public function get_h5_channel(){
        if ($_POST['cps']=='jklapp') {
            $channel_list=M('game','','DB_CONFIG1')->where(array('game_father_id'=>array('eq',$_POST['id']),'platform'=>array('like','%'.$_POST['cps'].'%')))->cache(500)->getField('game_id,game_name,platform');
        }elseif($_POST['cps']=='kejin0001' || $_POST['cps']=='kejin0002' || $_POST['cps']=='kejin0003' || $_POST['cps']=='kejin0004'){
            $channel_list=M('game','','DB_CONFIG1')->where(array('game_father_id'=>array('eq',$_POST['id']),'platform'=>$_POST['cps']))->cache(500)->getField('game_id,game_name,platform');
        }else{
            $channel_list=M('game','','DB_CONFIG1')->where(array('game_father_id'=>array('eq',$_POST['id'])))->cache(500)->getField('game_id,game_name,platform');
        }
        // M()->getField($field)
        self::success($channel_list);
    }

    public function get_h5_cpscode(){
        if ($_POST['cps']=='yumo') {
            $code = 'YM';
            $cps_list=M('game_channel','','DB_CONFIG1')->where(array('game_id'=>array('eq',$_POST['code']),'platform'=>'wxminigame','channel'=>array('like','%'.$code.'%')))->group('channel')->cache(500)->getField('channel',true);
        }else if($_POST['cps']=='zhijin-cps'){
            $code = 'QX';
            $cps_list=M('game_channel','','DB_CONFIG1')->where(array('game_id'=>array('eq',$_POST['code']),'channel'=>array('like',$code.'%'),'platform'=>'wxminigame'))->group('channel')->getField('channel',true);
        }else{
            $cps_list=M('game_channel','','DB_CONFIG1')->where(array('game_id'=>array('eq',$_POST['code']),'platform'=>'wxminigame'))->group('channel')->cache(500)->getField('channel',true);
        }

        self::success($cps_list);
    }

    public function new_base_data(){
        
        if($_GET['start_time']&&$_GET['channel']){
           
            if(!array_key_exists($_GET['channel'],$this->channel)){
                exit(self::error('非法访问'));
            }
            $gameList = M('game_cps','','DB_CONFIG1')->where(array('game_id'=>$this->channe[$_GET['channel']]['game_id']))->find();
            $product=$this->channel[$_GET['channel']]['product'];
            $channel=$this->channel[$_GET['cps']]['channel']?$this->channel[$_GET['cps']]['channel']:$this->channel[$_GET['channel']]['channel'];
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            foreach (array_fill(0,($date+1),$channel) as $k=>$v){
                foreach ($v as $vv){
                    $page_where[]=array('channel'=>$vv,'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')));
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
            
            $data=array();
            //print_r($comma_separated);EXIT;
            for ($i=$Page->firstRow;$i<$Page->firstRow+$Page->listRows;$i++){
                if($Page->totalRows<=$i){
                    break;
                }
                
                $parmas=array(
                    'start_time'=>$page_where[$i]['start_time'],
                    'product'=>$product,
                    'channel'=>$page_where[$i]['channel'],
                    'game_id'=>$this->channel[$_GET['cps']]['game_id']?$this->channel[$_GET['cps']]['game_id']:$this->channel[$_GET['channel']]['game_id']
                );
                
                $DATA=self::new_combination_data($parmas);
                $DATA['time']=$parmas['start_time'];
                // $DATA['ltv']=self::ltv_combination_data($parmas);
                // $DATA['retain']=self::retain_combination_data($parmas);
                $data[]=$DATA;
            }
            $data_sum=self::new_base_data_sum($data);
            array_unshift($data,$data_sum);
            self::assign('page',$page_show);
            self::assign('data',$data);
        }
        
//         $product_list=M('game_father','','DB_CONFIG1')->cache(500)->getField('game_father_id,game_father_name');
        $gameList = M('game_cps','','DB_CONFIG1')->where(array('channel'=>$this->channel[$_GET['channel']]['type']))->select();
        foreach ($gameList as $key => $val){
            $cps[$val['cps']] .= $val['game_name'];
        }
        self::assign('product_list',$cps);
//         self::assign('product_list',$this->channel[$_GET['channel']]['channel']);
        self::display();
    }

    private function  new_base_data_sum($parma){
        $data= array(
            'time'=>'汇总',
            '渠道'=>'-',
            'product'=>'-',
            '新增注册'=>array_sum(array_column($parma, '新增注册')),
            '新增创角'=>array_sum(array_column($parma, '新增创角')),
            '总登陆用户'=>array_sum(array_column($parma, '总登陆用户')),
            '老用户登陆'=>array_sum(array_column($parma, '老用户登陆')),
        );
        $data['创角转化率']=round(($data['新增创角']/$data['新增注册'])*100,2).'%';
        return $data;
    }

    private function  new_combination_data($parmas){
        
        $new_user_count_where=array(
                              'game_id'=>array('eq',$parmas['game_id']),
                              'channel'=>array('eq',$parmas['channel']),
                              'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
                              );
                
        $new_user_count=M('user','','DB_CONFIG1')
                        ->where($new_user_count_where)
                        ->cache(500)
                        ->count();//新增用户数
                        
        $active_user_count=M('login_report','','DB_CONFIG1')
            ->where(
                array('game_father_id'=>array('eq',$parmas['product']),
                'game_id'=>array('eq',$parmas['game_id']),
                'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
                ))
                //->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //活跃用户数
            
            
         $new_user_role=M('create_role_report','','DB_CONFIG1')
         ->where(array('game_father_id'=>array('eq',$parmas['product']),
             'game_id'=>array('eq',$parmas['game_id']),
//              'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
//             ->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //新增创角色数
            
            
         $old_user_count=M('create_role_report','','DB_CONFIG1')
         ->where(array('game_father_id'=>array('eq',$parmas['product']),
             'game_id'=>array('eq',$parmas['game_id']),
             'channel'=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','not in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
//             ->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //老用户登录数
            
    
     // 上面方法到时候可模型化
//             $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$parmas['game_id'])))->cache(500)->field('concat(game_name,"-",game_id) as channel_name')->find();
            $data=array(
                '渠道'=>$parmas['channel'],
                '新增注册'=>$new_user_count?sprintf("%.0f",$new_user_count):"-",
                '新增创角'=>$new_user_role?sprintf("%.0f",$new_user_role):"-",
                '创角转化率'=>$new_user_role/$new_user_count?round(($new_user_role/$new_user_count)*100,2).'%':"-",
                '总登陆用户'=>$active_user_count?sprintf("%.0f",$active_user_count):"-",
                '老用户登陆'=>$old_user_count?sprintf("%.0f",$old_user_count):"-",
            );    
            return $data;   
    }


    
    public function retain(){
        $game_cps = M('game_cps','','DB_CONFIG1')->where(array('channel'=>array('eq',$_GET['cps_code'])))->select();
        // print_r($game_cps);die;
        foreach ($game_cps as $k=>$v){
            if($v['product_id']){
                $channel .= $v['product_id'].',';
            }
        }
        $channel = rtrim($channel, ',');
        $condition['channel'] = explode(',', $channel);
        for ($i=0; $i < count($condition['channel']); $i++) {  
            $condition['into'] .= "`game_father_id`='".$condition['channel'][$i]."' OR ";
        }
        $condition['_string'] = rtrim($condition['into'], " OR ");
        $product_list=M('game_father','','DB_CONFIG1')->where(array($condition['_string']))->cache(500)->getField('game_father_id,game_father_name');

        if($_GET['start_time']&&$_GET['channel']){
            unset($channel);
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);

            if ($_GET['cpscode']) {
                foreach (array_fill(0,1,$_GET['cpscode']) as $k=>$v){
                    foreach ($v as $vv){
                        $channel .= $vv.',';
                    }
                }
                $channel = rtrim($channel, ',');
                foreach (array_fill(0,($date+1),$_GET['cpscode']) as $key => $value) {
                    $page_where[]=array(
                        'channel'=>$channel,
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$key.'day')),
                        'channel_name'=>'channel',
                        'game_father_name'=>'game_id',
                    );
                }
            }else{
                foreach (array_fill(0,($date+1),$_GET['channel']) as $k => $v) {
                    $page_where[]=array(
                        'channel'=>$_GET['channel'],
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                    );
                }
            }
            
            $count      = count($page_where);// 查询满足要求的总记录数
            
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
            foreach ($page_where as $v) {
                $parmas=array(
                    'start_time'=>$v['start_time'],
                    'product'=>$v['channel_name']?$_GET['channel']:$_GET['product'],
                    'channel'=>$v['channel'],
                    'name'=>$v['channel_name']?$v['channel_name']:'game_id',
                    'father_name'=>$v['game_father_name']?$v['game_father_name']:'game_father_id',
                );

                if($parmas['name']=='channel'){
                    $DATA=self::retain_array_combination_data($parmas);
                }else{
                    $DATA=self::retain_combination_data($parmas);
                }
                
                $DATA['time']=$parmas['start_time'];
                $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$_GET['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();
                $DATA['channel_name']=$parmas['name']=='channel'?$v['channel']:$channel_name['channel_name'];
                $data[]=$DATA;
            }
            $time = date('Y-m-d',strtotime($comma_separated[0]));
            $data_sum=self::retain_data_sum($data,$time);
            $data=array_slice($data,$Page->firstRow,$Page->listRows);
            array_unshift($data,$data_sum);
            self::assign('page',$page_show);
            self::assign('data',$data);
        }
        self::assign('product_list',$product_list);
        self::display();
    }

    private function retain_data_sum($parma,$time){
        $count = count($parma);
        $data= array(
            'time'=>'汇总均值',
            '渠道'=>'-',
            'product'=>'-',
            '用户数量'=>sprintf("%.2f",array_sum(array_column($parma, '用户数量'))/$count),
        );
        $date=floor((time()-strtotime($time))/86400);
        $name_list = array();
        foreach ($parma as $value) {
            $name_list[] = $value['retain'];
        }
        // $kk=$date;
        for($i=0;$i<$date;$i++){
            $data['retain']['retain'.($i)]=sprintf("%.2f",array_sum(array_column($name_list, 'retain'.$i))/count(array_column($name_list, 'retain'.$i))).'%';
        }
        // print_r($date);die;
        return $data;
    }

    private function  retain_combination_data($parmas){
        
        $new_user_count_where=array(
            // 'appid'=>array('eq',$parmas['product']),
            $parmas['name']=>array('eq',$parmas['channel']),
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
        );
        $new_user_count=M('user','','DB_CONFIG1')
                ->where($new_user_count_where)
                ->cache(500)
                ->count();//新增用户数
        
        $date=floor((time()-strtotime($parmas['start_time']))/86400);
        $data=array();
        for($i=0;$i<$date;$i++){
            
           $old_user_count=M('login_report','','DB_CONFIG1')
            ->where(array(
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'].' +'.$i.' day')),array('lt',strtotime($parmas['start_time'].' +'.($i+1).' day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id');
            $data['retain']['retain'.($i)]=$old_user_count/$new_user_count?round(($old_user_count/$new_user_count)*100,2).'%':'-';
        }
//         print_r($data);exit;
        $data['用户数量']=$new_user_count;
        return $data;
    }

        private function  retain_array_combination_data($parmas){

        $parmas['channel'] = explode(',', $parmas['channel']);
        for ($i=0; $i < count($parmas['channel']); $i++) {  
            $parmas['into'] .= "`channel`='".$parmas['channel'][$i]."' OR ";
        }
        $parmas['_string'] = rtrim($parmas['into'], " OR ");

        $new_user_count_where=array(
            'game_id'=>array('eq',$parmas['product']),
            $parmas['_string'],
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
        );
        
        $new_user_count=M('user','','DB_CONFIG1')
                ->where($new_user_count_where)
                ->cache(500)
                ->count();//新增用户数
        
        $date=floor((time()-strtotime($parmas['start_time']))/86400);
        $data=array();
        for($i=0;$i<$date;$i++){
            
           $old_user_count=M('login_report','','DB_CONFIG1')
            ->where(array(
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['_string'],
                'create_date'=>array(array('egt',strtotime($parmas['start_time'].' +'.$i.' day')),array('lt',strtotime($parmas['start_time'].' +'.($i+1).' day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id');
            $data['retain']['retain'.($i)]=$old_user_count/$new_user_count?round(($old_user_count/$new_user_count)*100,2).'%':'-';
        }
//         print_r($data);exit;
        $data['用户数量']=$new_user_count;
        return $data;
    }

    public function get_ltv(){
        $game_cps = M('game_cps','','DB_CONFIG1')->where(array('channel'=>array('eq',$_GET['cps_code'])))->select();
        // print_r($game_cps);die;
        foreach ($game_cps as $k=>$v){
            if($v['product_id']){
                $channel .= $v['product_id'].',';
            }
        }
        $channel = rtrim($channel, ',');
        $condition['channel'] = explode(',', $channel);
        for ($i=0; $i < count($condition['channel']); $i++) {  
            $condition['into'] .= "`game_father_id`='".$condition['channel'][$i]."' OR ";
        }
        $condition['_string'] = rtrim($condition['into'], " OR ");
        $product_list=M('game_father','','DB_CONFIG1')->where(array($condition['_string']))->cache(500)->getField('game_father_id,game_father_name');
        if($_GET['start_time']&&$_GET['channel']){
            unset($channel);
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            
            if ($_GET['cpscode']) {
                foreach (array_fill(0,1,$_GET['cpscode']) as $k=>$v){
                    foreach ($v as $vv){
                        $channel .= $vv.',';
                    }
                }
                $channel = rtrim($channel, ',');
                foreach (array_fill(0,($date+1),$_GET['cpscode']) as $key => $value) {
                    $page_where[]=array(
                        'channel'=>$channel,
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$key.'day')),
                        'channel_name'=>'channel',
                        'game_father_name'=>'game_id',
                    );
                }
            }else{
               
                foreach (array_fill(0,($date+1),$_GET['channel']) as $k => $v) {
                    $page_where[]=array(
                        'channel'=>$_GET['channel'],
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
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
            $data=array();
            foreach($page_where as $v){
                $parmas=array(
                    'start_time'=>$v['start_time'],
                    'product'=>$v['channel_name']?$_GET['channel']:$_GET['product'],
                    'channel'=>$v['channel'],
                    'name'=>$v['channel_name']?$v['channel_name']:'game_id',
                    'father_name'=>$v['game_father_name']?$v['game_father_name']:'game_father_id',
                );
                if($parmas['name']=='channel'){
                    $DATA=self::ltv_array_combination_data($parmas);
                }else{
                    $DATA=self::ltv_combination_data($parmas);
                }
                $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$_GET['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();
                $DATA['channel_name']=$parmas['name']=='channel'?$v['channel']:$channel_name['channel_name'];

                $DATA['time']=$parmas['start_time'];
                
                $data[]=$DATA;
            }
            $time = date('Y-m-d',strtotime($comma_separated[0]));
            $data_sum=self::ltv_data_sum($data,$time);
            $data=array_slice($data,$Page->firstRow,$Page->listRows);
            array_unshift($data,$data_sum);
            self::assign('page',$page_show);
            self::assign('data',$data);
        }
        
        self::assign('product_list',$product_list);
        self::display();
    }

    private function ltv_data_sum($parma,$time){
        $count = count($parma);
        $data= array(
            'time'=>'汇总均值',
            '渠道'=>'-',
            'product'=>'-',
            '新增注册人数'=>sprintf("%.2f",array_sum(array_column($parma, '新增注册人数'))/$count),
            '充值总额'=>sprintf("%.2f",array_sum(array_column($parma, '充值总额'))/$count),
            '新增付费'=>array_sum(array_column($parma, '新增付费')),
        );
        $date=floor((time()-strtotime($time))/86400);
        $name_list = array();
        foreach ($parma as $value) {
            $name_list[] = $value['ltv'];
        }
        for($i=0;$i<$date;$i++){
            $data['ltv']['ltv'.($i)]=sprintf("%.2f",array_sum(array_column($name_list, 'ltv'.$i))/count(array_column($name_list, 'ltv'.$i)));
        }
        return $data;
    }
    
    private function ltv_combination_data($parmas){
        
        $new_user_count_where=array(
            $parmas['name']=>array('eq',$parmas['channel']),
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
        );

        $new_user_count=M('user','','DB_CONFIG1')
        ->where($new_user_count_where)
        ->cache(500)
        ->count();//新增用户数
        
        $sum_new_user_order= M('game_order','','DB_CONFIG1')
        ->where(array(
            'status'=>array('egt',1),
            $parmas['father_name']=>array('eq',$parmas['product']),
            $parmas['name']=>array('eq',$parmas['channel']),
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
            'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
        ))
        //->fetchSql()
        ->cache(500)
        ->field('sum(money)as new_user_pay,count(DISTINCT user_id) as new_user_pay_count')
        ->find(); //新增付费金额，新增付费人数
        
        $date=floor((time()-strtotime($parmas['start_time']))/86400);
        $data=array();
        for($i=0;$i<$date;$i++){
            $new_user_order= M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +'.($i+1).' day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->field('sum(money)as new_user_pay,count(DISTINCT user_id) as new_user_pay_count')
            ->find(); //新增付费金额，新增付费人数
            $new_user_order['new_user_pay']=$new_user_order['new_user_pay']/100;
            $data['ltv']['ltv'.($i)]=$new_user_order['new_user_pay']/$new_user_count?round(($new_user_order['new_user_pay']/$new_user_count),2):'-';
            $data['新增付费'] += $new_user_order['new_user_pay'];
        }
        
        $data['新增注册人数']=$new_user_count;
        $data['充值总额']=$sum_new_user_order['new_user_pay']/100?round($sum_new_user_order['new_user_pay']/100,2):'-';
        return $data;
    }
    

    private function ltv_array_combination_data($parmas){

        $parmas['channel'] = explode(',', $parmas['channel']);
        for ($i=0; $i < count($parmas['channel']); $i++) {  
            $parmas['into'] .= "`channel`='".$parmas['channel'][$i]."' OR ";
        }
        $parmas['_string'] = rtrim($parmas['into'], " OR ");
        $new_user_count_where=array(
            'game_id'=>array('eq',$parmas['product']),
            $parmas['_string'],
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
        );
            
        $new_user_count=M('user','','DB_CONFIG1')
        ->where($new_user_count_where)
        ->cache(500)
        ->count();//新增用户数
        $sum_new_user_order= M('game_order','','DB_CONFIG1')
        ->where(array(
            'status'=>array('egt',1),
            $parmas['father_name']=>array('eq',$parmas['product']),
            $parmas['_string'],
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
            'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
        ))
        //->fetchSql()
        ->cache(500)
        ->field('sum(money)as new_user_pay,count(DISTINCT user_id) as new_user_pay_count')
        ->find(); //新增付费金额，新增付费人数
        
        $date=floor((time()-strtotime($parmas['start_time']))/86400);
        $data=array();
        for($i=0;$i<$date;$i++){
            $new_user_order= M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['_string'],
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +'.($i+1).' day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->field('sum(money)as new_user_pay,count(DISTINCT user_id) as new_user_pay_count')
            ->find(); //新增付费金额，新增付费人数
            $new_user_order['new_user_pay']=$new_user_order['new_user_pay']/100;
            $data['ltv']['ltv'.($i)]=$new_user_order['new_user_pay']/$new_user_count?round(($new_user_order['new_user_pay']/$new_user_count),2):'-';
            $data['新增付费'] += $new_user_order['new_user_pay'];
        }
        
        $data['新增注册人数']=$new_user_count;
        $data['充值总额']=$sum_new_user_order['new_user_pay']/100?round($sum_new_user_order['new_user_pay']/100,2):'-';
        return $data;
    }


    //导出Excel表格
    public function get_baseExecl(){

        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
            $_GET['channel'] = json_decode($_GET['channel'],ture);
            $_GET['cpscode'] = json_decode($_GET['cpscode'],ture);
            //查询时间
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            // //充值区间
            // $pay_comma_separated = explode(" 到 ", urldecode($_GET['pay_start_time']));
            // $pay_dete = floor((strtotime($pay_comma_separated[1])-strtotime($pay_comma_separated[0]))/86400);
            if ($_GET['cpscode']) {
                foreach (array_fill(0,($date+1),$_GET['cpscode']) as $k=>$v){
                    foreach ($v as $vv){
                        $page_where[]=array(
                            'channel'=>$vv,
                            'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                            // 'start_time'=>date('Y-m-d',strtotime($comma_separated[0])),
                            'end_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                            'channel_name'=>'channel',
                            'game_father_name'=>'game_id',
                        );
                    }
                }
                // $_GET['product'] = $_GET['channel'][0];
            }else{
                foreach (array_fill(0,($date+1),$_GET['channel']) as $k=>$v){
                    foreach ($v as $vv){
                        $page_where[]=array(
                            'channel'=>$vv,
                            'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                            // 'start_time'=>date('Y-m-d',strtotime($comma_separated[0])),
                            'end_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                        );
                    }
                }
            }
            $count      = count($page_where);// 查询满足要求的总记录数
           
            $Page       = new \Think\Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->lastSuffix = false;//最后一页不显示为总页数
            $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('last','末页');
            $Page->setConfig('first','首页');
            $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里
            // print_r($page_where);
            $data=array();
            //print_r($comma_separated);EXIT;
            for ($i=$Page->firstRow;$i<$Page->totalRows;$i++){
                if($Page->totalRows<=$i){
                    break;
                }
                
                $parmas=array(
                    'start_time'=>$page_where[$i]['start_time'],
                    'end_time'=>$page_where[$i]['end_time'],
                    'product'=>$page_where[$i]['channel_name']?$_GET['channel'][0]:$_GET['product'],
                    'channel'=>$page_where[$i]['channel'],
                    'name'=>$page_where[$i]['channel_name']?$page_where[$i]['channel_name']:'game_id',
                    'father_name'=>$page_where[$i]['game_father_name']?$page_where[$i]['game_father_name']:'game_father_id',
                );
                
                $DATA=self::wehcta_combination_data($parmas);
                $DATA['start_time']=$parmas['start_time'];
                $DATA['end_time']=$parmas['end_time'];

                $data[]=$DATA;
            }
            // print_r($Page->totalRows);die;
            // for ($i=0; $i < count($data)/2 ; $i++) { 
            //     if($data[$i]['渠道']==$_GET['cpscode'][$i])
            // }

            $data_sum=self::base_data_sum($data);

            array_unshift($data,$data_sum);
            self::base_execlData($data);
        }
        
    }
    //导出Excel方法
    public function base_execlData($data){
        $game = M('game_father','','DB_CONFIG1')->where(array('game_father_id'=>$_GET['product']))->find();
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(23);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(23);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(23);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(23);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(23);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(28);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(28);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(28);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(28);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(28);//宽度

        $objActSheet->setCellValue('A1', '时间');
        $objActSheet->setCellValue('B1', '产品');
        $objActSheet->setCellValue('C1', '渠道');
        $objActSheet->setCellValue('D1', '新增注册');
        $objActSheet->setCellValue('E1', '新增创角');
        $objActSheet->setCellValue('F1', '创角转化率');
        $objActSheet->setCellValue('G1', '总登陆用户');
        $objActSheet->setCellValue('H1', '老用户登陆');
        $objActSheet->setCellValue('I1', '新增付费率');
        $objActSheet->setCellValue('J1', '新增付费人数');
        $objActSheet->setCellValue('K1', '新增arppu');
        $objActSheet->setCellValue('L1', '新用户付费金额');
        $objActSheet->setCellValue('M1', '老用户付费率');
        $objActSheet->setCellValue('N1', '老用户付费人数');
        $objActSheet->setCellValue('O1', '老用户arppu');
        $objActSheet->setCellValue('P1', '总付费率');
        $objActSheet->setCellValue('Q1', '总arpu');
        $objActSheet->setCellValue('R1', '总付费人数(去重)');
        $objActSheet->setCellValue('S1', '总付费金额');
        $objActSheet->setCellValue('T1', '总arppu');
        $objActSheet->setCellValue('U1', 'LTV1');

        foreach($data as $k=>$val){
            $k +=2;
            $objActSheet->setCellValue('A'.$k, $val['start_time']?$val['start_time']:$data[0]['time']);
            $objActSheet->setCellValue('B'.$k, $_GET['product']);  
            $objActSheet->setCellValue('C'.$k, $val['渠道']);  
            $objActSheet->setCellValue('D'.$k, $val['新增注册']);
            $objActSheet->setCellValue('E'.$k, $val['新增创角']); 
            $objActSheet->setCellValue('F'.$k, $val['创角转化率']);
            $objActSheet->setCellValue('G'.$k, $val['总登陆用户']); 
            $objActSheet->setCellValue('H'.$k, $val['老用户登陆']); 
            $objActSheet->setCellValue('I'.$k, $val['新增付费率']);
            $objActSheet->setCellValue('J'.$k, $val['新增付费人数']);
            $objActSheet->setCellValue('K'.$k, $val['新增arppu']); 
            $objActSheet->setCellValue('L'.$k, $val['新用户付费金额']); 
            $objActSheet->setCellValue('M'.$k, $val['老用户付费率']);
            $objActSheet->setCellValue('N'.$k, $val['老用户付费人数']);
            $objActSheet->setCellValue('O'.$k, $val['老用户arppu']);
            $objActSheet->setCellValue('P'.$k, $val['总付费率']);
            $objActSheet->setCellValue('Q'.$k, $val['总arpu']);
            $objActSheet->setCellValue('R'.$k, $val['总付费人数']);
            $objActSheet->setCellValue('S'.$k, $val['总付费金额']);
            $objActSheet->setCellValue('T'.$k, $val['总arppu']);
            $objActSheet->setCellValue('U'.$k, $val['ltv1']);
        }
        $fileName = '基础数据表';
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

    public function get_ltvExecl(){
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
            $_GET['channel'] = json_decode($_GET['channel'],ture);
            $_GET['cpscode'] = json_decode($_GET['cpscode'],ture);
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            if ($_GET['cpscode']) {
                foreach (array_fill(0,1,$_GET['cpscode']) as $k=>$v){
                    foreach ($v as $vv){
                        $channel .= $vv.',';
                    }
                }
                $channel = rtrim($channel, ',');
                foreach (array_fill(0,($date+1),$_GET['cpscode']) as $key => $value) {
                    $page_where[]=array(
                        'channel'=>$channel,
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$key.'day')),
                        'channel_name'=>'channel',
                        'game_father_name'=>'game_id',
                    );
                }
            }else{
               
                foreach (array_fill(0,($date+1),$_GET['channel']) as $k => $v) {
                    $page_where[]=array(
                        'channel'=>$_GET['channel'],
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                    );
                }
            }
            $count      = count($page_where);// 查询满足要求的总记录数
            
            $Page       = new \Think\Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
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
            for ($i=$Page->firstRow;$i<$Page->totalRows;$i++){
                if($Page->totalRows<=$i){
                    break;
                }
                $parmas=array(
                    'start_time'=>$page_where[$i]['start_time'],
                    'product'=>$page_where[$i]['channel_name']?$_GET['channel']:$_GET['product'],
                    'channel'=>$page_where[$i]['channel'],
                    'name'=>$page_where[$i]['channel_name']?$page_where[$i]['channel_name']:'game_id',
                    'father_name'=>$page_where[$i]['game_father_name']?$page_where[$i]['game_father_name']:'game_father_id',
                );
                if($parmas['name']=='channel'){
                    $DATA=self::ltv_array_combination_data($parmas);
                }else{
                    $DATA=self::ltv_combination_data($parmas);
                }
                $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$_GET['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();
                $DATA['channel_name']=$parmas['name']=='channel'?$page_where[$i]['channel']:$channel_name['channel_name'];

                $DATA['time']=$parmas['start_time'];
                
                $data[]=$DATA;
            }
            $time = date('Y-m-d',strtotime($comma_separated[0]));
            $data_sum=self::ltv_data_sum($data,$time);
            array_unshift($data,$data_sum);

            self::ltv_execlData($data);
        }
    }

    //导出Excel方法[ltv]
    public function ltv_execlData($data){
        // print_r(count($data[0]['ltv']));die;
        // require_once  LIB_PATH . 'Org/Util/PHPExcel/PHPExcel.php';
        $game = M('game_father','','DB_CONFIG1')->where(array('game_father_id'=>$_GET['product']))->find();
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);//宽度
        for ($i = 5; $i <= count($data[0]['ltv'])+4; $i++) {
            $y = ($i / 26);
            if ($y >= 1) {
                $y = intval($y);
                $objPHPExcel->getActiveSheet()->getColumnDimension(chr($y+64).chr($i-$y*26 + 65))->setWidth(8);//宽度
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension(chr($i+65))->setWidth(8);//宽度
            }
        }
        
        $objActSheet->setCellValue('A1', '时间');
        $objActSheet->setCellValue('B1', '产品');
        $objActSheet->setCellValue('C1', '渠道');
        $objActSheet->setCellValue('D1', '新增注册人数');
        $objActSheet->setCellValue('E1', '充值总额');

        
        $k='1';
        for ($i = 5; $i <= count($data[0]['ltv'])+4; $i++) {
            $y = ($i / 26);
            if ($y >= 1) {
                $y = intval($y);
                $objActSheet->setCellValue(chr($y+64).chr($i-$y*26 + 65).'1', 'ltv'.$k);
            } else {
                $objActSheet->setCellValue(chr($i+65).'1', 'ltv'.$k);
            }
            $k++;
        }
        $ii=0;
        foreach ($data as $kk => $value) {
            $ltv[$ii] = $value['ltv'];
            $ii++;
        }
        $io=0;
        foreach($data as $k=>$val){
            $k +=2;
            $objActSheet->setCellValue('A'.$k, $val['time']);
            $objActSheet->setCellValue('B'.$k, $game['game_father_name']);  
            $objActSheet->setCellValue('C'.$k, $val['渠道']?$val['渠道']:$val['channel_name']);  
            $objActSheet->setCellValue('D'.$k, $val['新增注册人数']);
            $objActSheet->setCellValue('E'.$k, $val['充值总额']);

            for ($i = 5; $i <= count($val['ltv'])+4; $i++) {
                $y = ($i / 26);
                if ($y >= 1) {
                    $y = intval($y);
                    $objActSheet->setCellValue(chr($y+64).chr($i-$y*26 + 65).$k, $ltv[$io]['ltv'.($i-5)]);
                } else {
                    $objActSheet->setCellValue(chr($i+65).$k, $ltv[$io]['ltv'.($i-5)]); 
                }
            }
            $io++;
        }
        $fileName = 'ltv数据表';
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

    public function get_retainExecl(){
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
            $_GET['channel'] = json_decode($_GET['channel'],ture);
            $_GET['cpscode'] = json_decode($_GET['cpscode'],ture);
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);

            if ($_GET['cpscode']) {
                foreach (array_fill(0,1,$_GET['cpscode']) as $k=>$v){
                    foreach ($v as $vv){
                        $channel .= $vv.',';
                    }
                }
                $channel = rtrim($channel, ',');
                // $channel = explode(',', $channel);
                // print_r($channel);die;
                foreach (array_fill(0,($date+1),$_GET['cpscode']) as $key => $value) {
                    $page_where[]=array(
                        // 'channel'=>array('in',$channe),
                        'channel'=>$channel,
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$key.'day')),
                        // 'start_time'=>date('Y-m-d',strtotime($comma_separated[0])),
                        // 'end_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                        'channel_name'=>'channel',
                        'game_father_name'=>'game_id',
                    );
                }
                // $_GET['product'] = $_GET['channel'][0];
            }else{
                foreach (array_fill(0,($date+1),$_GET['channel']) as $k => $v) {
                    $page_where[]=array(
                        'channel'=>$_GET['channel'],
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                    );
                }
            }
            
            $count      = count($page_where);// 查询满足要求的总记录数

            $Page       = new \Think\Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->lastSuffix = false;//最后一页不显示为总页数
            $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('last','末页');
            $Page->setConfig('first','首页');
            $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里
            
            $data=array();
            // print_r($page_where);die;
            for ($i=$Page->firstRow;$i<$Page->totalRows;$i++){
                if($Page->totalRows<=$i){
                    break;
                }
                $parmas=array(
                    'start_time'=>$page_where[$i]['start_time'],
                    // 'end_time'=>$page_where[$i]['end_time'],
                    'product'=>$page_where[$i]['channel_name']?$_GET['channel']:$_GET['product'],
                    'channel'=>$page_where[$i]['channel'],
                    'name'=>$page_where[$i]['channel_name']?$page_where[$i]['channel_name']:'game_id',
                    'father_name'=>$page_where[$i]['game_father_name']?$page_where[$i]['game_father_name']:'game_father_id',
                );

                if($parmas['name']=='channel'){
                    $DATA=self::retain_array_combination_data($parmas);
                }else{
                    $DATA=self::retain_combination_data($parmas);
                }
                
                $DATA['time']=$parmas['start_time'];
                $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$_GET['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();
                $DATA['channel_name']=$parmas['name']=='channel'?$page_where[$i]['channel']:$channel_name['channel_name'];
                $data[]=$DATA;
            }
            $time = date('Y-m-d',strtotime($comma_separated[0]));
            $data_sum=self::retain_data_sum($data,$time);
            // print_r($data_sum);die;
            array_unshift($data,$data_sum);

            self::retain_execlData($data);
        }
    }

    //导出Excel方法[留存]
    public function retain_execlData($data){
        // print_r(count($data[0]['retain']));die;
        // require_once  LIB_PATH . 'Org/Util/PHPExcel/PHPExcel.php';
        $game = M('game_father','','DB_CONFIG1')->where(array('game_father_id'=>$_GET['product']))->find();
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
        for ($i = 4; $i <= count($data[0]['retain'])+2; $i++) {
            $y = ($i / 26);
            if ($y >= 1) {
                $y = intval($y);
                $objPHPExcel->getActiveSheet()->getColumnDimension(chr($y+64).chr($i-$y*26 + 65))->setWidth(8);//宽度
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension(chr($i+65))->setWidth(8);//宽度
            }
        }
        
        $objActSheet->setCellValue('A1', '时间');
        $objActSheet->setCellValue('B1', '产品');
        $objActSheet->setCellValue('C1', '渠道');
        $objActSheet->setCellValue('D1', '用户数量');
        
        $k='2';
        for ($i = 4; $i <= count($data[0]['retain'])+2; $i++) {
            $y = ($i / 26);
            if ($y >= 1) {
                $y = intval($y);
                $objActSheet->setCellValue(chr($y+64).chr($i-$y*26 + 65).'1', $k.'日');
            } else {
                $objActSheet->setCellValue(chr($i+65).'1', $k.'日');
            }
            $k++;
        }
        $ii=0;
        foreach ($data as $kk => $value) {
            $retain[$ii] = $value['retain'];
            $ii++;
        }
        $io=0;
        foreach($data as $k=>$val){
            $k +=2;
            $objActSheet->setCellValue('A'.$k, $val['time']);
            $objActSheet->setCellValue('B'.$k, $game['game_father_name']);  
            $objActSheet->setCellValue('C'.$k, $val['渠道']?$val['渠道']:$val['channel_name']);  
            $objActSheet->setCellValue('D'.$k, $val['用户数量']);

            for ($i = 4; $i <= count($val['retain'])+2; $i++) {
                $y = ($i / 26);
                if ($y >= 1) {
                    $y = intval($y);
                    $objActSheet->setCellValue(chr($y+64).chr($i-$y*26 + 65).$k, $retain[$io]['retain'.($i-3)]);
                } else {
                    $objActSheet->setCellValue(chr($i+65).$k, $retain[$io]['retain'.($i-3)]); 
                }
            }
            $io++;
        }
        $fileName = '留存数据表';
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

    //推送微信小游戏客服会话登录文案
    public function weixinGameMsg(){
        $time = time();//获取当前时间
        $ymdTime = date('Y-m-d H:i:s',$time);//转换当前时间戳为yyyy-mm-dd hh:ii:ss
        $gameId = $_GET['game_id'];//获取游戏id
//        $game_father_id = $_GET['game_father_id'];//游戏大类id
        $game = M('game','','DB_CONFIG1')->where(array('game_id'=>$gameId))->find();
        //15h内登录过的玩家
        $new_user_count_where = array(
            'game_id'=>$gameId,
//            'game_father_id'=>$game_father_id,
            'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -15 hour')),'and'),
        );
//        $test = M('login_report','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql();
//        print_r($test);die;
        //查询48h内登录过的玩家
        $user['p_uid'] = M('login_report','','DB_CONFIG1')
            ->where(array(
                'game_id'=>$gameId,
//                'game_father_id'=>$game_father_id,
                'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -2 day')),'and'),
                'user_id'=>array('exp','not in '.M('login_report','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql()),//排除24h内登录过的玩家
            ))
            ->distinct(true)
            ->field('p_uid')
            ->select();

        $user['game_id'] = $gameId;
        $user['game_name'] = $game['game_name'];
//        $user['p_uid'][0]['p_uid']= 'o0nhp5A9FjGECqaKs00o0xIa-1IM';
//        $user['game_id'] = '6';
//        print_r($user);die;
        $user = json_encode($user,true);
        if($user){
            self::curl_post('http://api.baizegame.com/Wx_minigame/weixinMsg',$user);
        }
    }
    //平台监控预警 [登录&注册]
    public function monitoredSDK(){
        $time = time();
        $ymdTime = date('Y-m-d H:i:s',$time); // 转换当前时间戳
        $pay_min = $_GET['min'];
        $new_where = array(
            'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$pay_min.' min')),'and'),
        );
        //登录
        $login_log = M('login_report','','DB_CONFIG1')
            ->where($new_where)
            ->find();
        if (!$login_log){
            $msg['content'] = '老SDK'.$pay_min.'分钟未有人登录';
            self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
        }
        //注册
        $user_log = M('user','','DB_CONFIG1')
            ->where($new_where)
            ->find();
        if (!$user_log){
            $msg['content'] = '老SDK'.$pay_min.'分钟未有人注册';
            self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
        }
    }
    //平台监控预警 [下单]
    public function order_monitoredSDK(){
        $time = time();
        $ymdTime = date('Y-m-d H:i:s',$time); // 转换当前时间戳
        $pay_min = $_GET['min'];
        $new_where = array(
            'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$pay_min.' min')),'and'),
        );
        //下单
        $new_where['status']='0';
        $order_log = M('game_order','','DB_CONFIG1')
            ->where($new_where)
            ->find();
        if (!$order_log){
            $msg['content'] = '老SDK'.$pay_min.'分钟未有人下单';
            unset($new_where['status']);
            self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
        }
    }
    //平台监控预警 [充值]
    public function pay_monitoredSDK(){
        $time = time();
        $ymdTime = date('Y-m-d H:i:s',$time); // 转换当前时间戳
        $pay_min = $_GET['min'];
        $new_where = array(
            'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$pay_min.' min')),'and'),
        );
        //充值
        $new_where['status']=array('egt',1);
        $pay_log = M('game_order','','DB_CONFIG1')
            ->where($new_where)
            ->find();
        if (!$pay_log){
            $msg['content'] = '老SDK'.$pay_min.'分钟未有人充值';
            unset($new_where['status']);
            self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
        }
    }

    public function monitoredGame(){
        $time = time();//获取当前时间戳
        $game_id = $_GET['game_id'];
        $ymdTime = date('Y-m-d H:i:s',$time); // 转换当前时间戳
        $game = M('game','','DB_CONFIG1')
            ->where(array('game_id'=>$game_id))
            ->find();
        if($game['game_status']=='1'){
            $is_game_type = '投放包-';
            if(S('gameCache'.$game_id)>=1){
                $sum = S('gameCache'.$game_id)+1;
                S('gameCache'.$game_id,$sum,360);
                $gameValue = 6/$sum;
                $login_Time = '五';
                $role_Time = '五';
                $login_min = '4';//登录查询时间
                $role_min = '4';//创角查询时间
                if ($gameValue=='3'){
                    $user_Time = '十';
                    $user_min = '9';//注册查询时间
                }else if($gameValue=='1.5'){
                    $user_Time = '十';
                    $user_min = '9';//注册查询时间
                    $order_Time = '二十';
                    $order_min = '19';//下单查询时间
                }else if($gameValue=='1'){
                    $user_Time = '十';
                    $user_min = '9';//注册查询时间
                    $order_Time = '二十';
                    $pay_Time = '三十';
                    $order_min = '19';//下单查询时间
                    $pay_min = '29';//充值查询时间
                    S('gameCache'.$game_id,'');
                }
            }else{
                $sum = '1';
                S('gameCache'.$game_id,$sum,'360');
                $login_Time = '五';
                $user_Time = '五';
                $role_Time = '五';
                $login_min = '4';//登录查询时间
                $user_min = '4';//注册查询时间
                $role_min = '4';//创角查询时间
            }
        }else {
            $is_game_type = '停投包-';
            if(S('stop_gameCache'.$game_id)>=1){
                $sum = S('stop_gameCache'.$game_id)+1;
                S('gameCache'.$game_id,$sum,1860);
                $gameValue = 18/$sum;
                $login_Time = '三十';
                $login_min = '29';//登录查询时间
                if ($gameValue =='4.5'){
                    $order_Time = '一百二十';
                    $order_min = '119';//下单查询时间
                }else if($gameValue=='3'){
                    $pay_Time = '一百八十';
                    $pay_min = '179';//充值查询时间
                    S('stop_gameCache'.$game_id,'');
                }
            }else{
                $sum = '1';
                S('stop_gameCache'.$game_id,$sum,1860);
                $login_Time = '三十';
                $login_min = '29';//登录查询时间
            }
        }
        //登录
        $game_login = M('Login_report','','DB_CONFIG1')
            ->where(array(
                'game_id'=>$game_id,
                'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$login_min.' min')),'and'),
            ))
            ->find();
        if(!$game_login){
            $msg['content'] = $is_game_type.$game['game_name'].$login_Time.'分钟未有登录';
            self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
        }
        //停投包不参与注册/创角监控
        if($game['game_status']=='1'){
            if($user_min){
                //注册
                $game_user = M('user','','DB_CONFIG1')
                    ->where(array(
                        'game_id'=>$game_id,
                        'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$user_min.' min')),'and'),
                    ))
                    ->find();
                if(!$game_user){
                    $msg['content'] = $is_game_type.$game['game_name'].$user_Time.'分钟未有注册';
                    self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
                }
            }
            //创角
            $game_user_count = M('user','','DB_CONFIG1')
                ->where(array(
                    'game_id'=>$game_id,
                    'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$user_min.' min')),'and'),
                ))
                ->count();
            $game_role_count = M('create_role_report','','DB_CONFIG1')
                ->where(array(
                    'game_id'=>$game_id,
                    'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$role_min.' min')),'and'),
                ))
                ->count();
            $game_count = $game_role_count/$game_user_count;
            $value = sprintf("%.2f",$game_count);
            if($value<'0.50'){
                $msg['content'] = $is_game_type.$game['game_name'].$role_Time.'分钟内，创角数低于注册数50%';
                self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
            }
        }
        //下单
        if($order_min){
            $game_order = M('game_order','','DB_CONFIG1')
                ->where(array(
                    'game_id'=>$game_id,
                    'status'=>0,
                    'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$order_min.' min')),'and'),
                ))
                ->find();
            if(!$game_order){
                $msg['content'] = $is_game_type.$game['game_name'].$order_Time.'分钟未有下单';
                self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
            }
        }
        if($pay_min){
            //充值
            $game_pay = M('game_order','','DB_CONFIG1')
                ->where(array(
                    'game_id'=>$game_id,
                    'status'=>array('egt','1'),
                    'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$pay_min.' min')),'and'),
                ))
                ->find();
            if(!$game_pay){
                $msg['content'] = $is_game_type.$game['game_name'].$pay_Time.'分钟未有充值';
                self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
            }
        }
    }
    //23-00 监控投放包sdk
    public function new_monitoredGame(){
        $time = time();//获取当前时间戳
        $game_id = $_GET['game_id'];
        $ymdTime = date('Y-m-d H:i:s',$time); // 转换当前时间戳
        $game = M('game','','DB_CONFIG1')
            ->where(array('game_id'=>$game_id))
            ->find();
        if($game['game_status']=='1') {
            $is_game_type = '投放包-';
            if(S('new_gameCache')>=1){
                $sum = S('new_gameCache')+1;
                S('new_gameCache'.$game_id,$sum,1860);
                $gameValue = 2/$sum;
                $login_Time = '三十';
                $login_min = '29';//登录查询时间
                if($gameValue=='1'){
                    $login_Time = '三十';
                    $login_min = '29';//登录查询时间
                    $pay_Time = '六十';
                    $pay_min = '59';//充值查询时间
                    S('stop_gameCache'.$game_id,'');
                }
            }else{
                $sum = '1';
                S('new_gameCache'.$game_id,$sum,1860);
                $login_Time = '三十';
                $login_min = '29';//登录查询时间
            }
        }

        //登录
        $game_login = M('Login_report','','DB_CONFIG1')
            ->where(array(
                'game_id'=>$game_id,
                'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$login_min.' min')),'and'),
            ))
            ->find();
        if(!$game_login){
            $msg['content'] = $is_game_type.$game['game_name'].$login_Time.'分钟未有登录';
            self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
        }

        if($pay_min){
            //充值
            $game_pay = M('game_order','','DB_CONFIG1')
                ->where(array(
                    'game_id'=>$game_id,
                    'status'=>array('egt','1'),
                    'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$pay_min.' min')),'and'),
                ))
                ->find();
            if(!$game_pay){
                $msg['content'] = $is_game_type.$game['game_name'].$pay_Time.'分钟未有充值';
                self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
            }
        }
    }
    //23-00监控停投包 登录
    public function stop_monitoredGame(){
        $time = time();//获取当前时间戳
        $gameArr = array(
            '6',
            '40',
            '50',
            '58',
            '59',
        );
        foreach ($gameArr as $v) {
            $game_id = $v;
            $ymdTime = date('Y-m-d H:i:s', $time); // 转换当前时间戳
            $game = M('game', '', 'DB_CONFIG1')
                ->where(array('game_id' => $game_id))
                ->find();
            $is_game_type = '停投包-';
            $login_Time = '六十';
            $login_min = '59';//登录查询时间
            //登录
            $game_login = M('Login_report', '', 'DB_CONFIG1')
                ->where(array(
                    'game_id' => $game_id,
                    'create_date' => array(array('elt', $time), array('egt', strtotime($ymdTime . ' -' . $login_min . ' min')), 'and'),
                ))
                ->find();
            if (!$game_login) {
                $msg['content'] = $is_game_type . $game['game_name'] . $login_Time . '分钟未有登录';
                self::curl_post('http://api.baizegame.com/api/SDK_sms_code', json_encode($msg));
            }
        }
    }

    //23-00监控停投包 支付
    public function stop_monitoredGame_pay(){
        $time = time();//获取当前时间戳
        $gameArr = array(
            '6',
            '40',
            '50',
            '58',
            '59',
        );
        foreach ($gameArr as $v){
            $game_id = $v;
            $ymdTime = date('Y-m-d H:i:s',$time); // 转换当前时间戳
            $game = M('game','','DB_CONFIG1')
                ->where(array('game_id'=>$game_id))
                ->find();
            $is_game_type = '停投包-';
            $pay_Time = '九十';
            $pay_min = '89';//登录查询时间
            //充值
            $game_pay = M('game_order','','DB_CONFIG1')
                ->where(array(
                    'game_id'=>$game_id,
                    'status'=>array('egt','1'),
                    'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -'.$pay_min.' min')),'and'),
                ))
                ->find();
            if(!$game_pay){
                $msg['content'] = $is_game_type.$game['game_name'].$pay_Time.'分钟未有充值';
                self::curl_post('http://api.baizegame.com/api/SDK_sms_code',json_encode($msg));
            }
        }
    }

    function curl_post($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (! empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    public function testimg(){
        $TOKEN="31_9ywEsRycN_-G9aPHwZU2sAU2DbEvoHrElH3BPj1x9S6_Rc20i7GLD3GPlerIY0frK61DOsv2_66VDxn83kC-aYpv7M9iJrX-XUWxWpXYhGIaqnIX1JY4nPwrwGDuTirJ90REWJFAIoWyc4qGFSKjAGAKXP";
        $file = "https://s2.ax1x.com/2020/03/09/89eJhR.jpg";
        $data = array(
            'media'=> new CURLFile($file)
        );
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$TOKEN.'&type=image';
        $result = self::curl_posts($url,$data);
        print_r($result);die;
    }

    function add_material() {
        $file_info = array('filename' => '/Public/img/tes11.jpg', //国片相对于网站根目录的路径
            'content-type' => 'image/jpg', //文件类型
            'filelength' => filesize('/Public/img/tes11.jpg') //图文大小
        );
        dump($file_info);
        $access_token = '31_VLIjk74tbdHrL-2LuZoj1ethygeCsUfSmbeyeISr7qnB84xuHjvVIW6ZVGy9NTj2cAqCaAVyIwbOUtvvQu4ETw6gnM8rK73rb1KY6V8Su6MABBzFAnZF5t0EHW_2HeiQjskg_RNYoVnCoaojWOEdAJAJBR';
        $url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$access_token}&type=image";
        $ch1 = curl_init();
        $timeout = 5;
        $real_path = "{$_SERVER['DOCUMENT_ROOT']}{$file_info['filename']}";
        //$real_path=str_replace("/", "//", $real_path);
        $data = array("media" => "@{$real_path}", 'form-data' => $file_info);
        curl_setopt($ch1, CURLOPT_URL, $url);
        curl_setopt($ch1, CURLOPT_POST, 1);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch1, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch1, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch1);
        curl_close($ch1);
        if (curl_errno() == 0) {
            $result = json_decode($result, true);
            var_dump($result);
            return $result['media_id'];
        } else {
            return false;
        }
    }

    public function getImgJson(){
        $id = $_GET['id'];
        $data = M('kefu_feedback','','DB_CONFIG1')
            ->where(array(
                'id'=>$id
            ))
            ->cache(500)
            ->find();
        $img = explode(',',json_decode($data['image_url'],true));
        $json = array(
            'status'=>1,
            'title'=>'反馈图片',
            'id'=>time(),
            'start'=>0
        );
        foreach ($img as $k=>$v){
            $arr[$k] = array(
                'alt'=>'图'.$k,
                'pid'=>$k,
                'src'=>$v,
                'thumb'=>'',
            );
        }
        $json['data'] = $arr;
        header('Content-Type:application/json');
        $arrJson = json_encode($json,JSON_UNESCAPED_UNICODE);
        echo $arrJson;

    }

    public function get_money(){

        $orderList = M('game_order','','DB_CONFIG1')
            ->where(array(
                'game_id'=>array('in','50'),
                'create_date'=>array(array('egt','1569859200'),array('lt','1584374399'),'and'),
                'status'=>array('egt',1)
            ))
            ->field('DISTINCT cproleid,ext,game_id,game_father_id,user_id')
            ->cache(500)
            ->select();
        foreach ($orderList as $k=>$v){
            $new_user_pay = M('game_order','','DB_CONFIG1')
                ->where(
                    array(
                        'cproleid'=>$v['cproleid'],
                        'game_id'=>$v['game_id'],
                        'user_id'=>$v['user_id'],
                        'game_father_id'=>$v['game_father_id'],
                        'status'=>array('egt','1'),
                        'create_date'=>array(array('egt','1569859200'),array('lt','1584374399'),'and'),
                    )
                )
                ->cache(500)
                ->field('sum(money)as new_user_pay')
                ->find();
            if($new_user_pay['new_user_pay']<'10000'){
                $login = M('login_report','','DB_CONFIG1')
                    ->where(array(
                        'game_id'=>$v['game_id'],
                        'game_father_id'=>$v['game_father_id'],
                        'user_id'=>$v['user_id'],
                        'cproleid'=>$v['cproleid'],
                    ))
                    ->order('login_report_id DESC')
                    ->cache(500)
                    ->getField('create_date');
                $paytime = M('game_order','','DB_CONFIG1')
                    ->where(array(
                        'game_id'=>$v['game_id'],
                        'game_father_id'=>$v['game_father_id'],
                        'user_id'=>$v['user_id'],
                        'cproleid'=>$v['cproleid'],
                    ))
                    ->order('order_id DESC')
                    ->cache(500)
                    ->getField('create_date');
                if($login && $paytime){
                    $DATA[] = array(
                        'game_id'=>$v['game_id'],
                        'user_id'=>$v['user_id'],
                        'cproleid'=>$v['cproleid'],
                        'money'=>round(($new_user_pay['new_user_pay']/100),2),
                        'create_date'=>date('Y-m-d H:i:s',$login),
                        'pay_time'=>date('Y-m-d H:i:s',$paytime),
                    );
                }
            }
        }
        self::query_gameorder_excel($DATA);

    }

    //导出用户充值金额Excel
    public function query_gameorder_excel($data){
        // require_once  LIB_PATH . 'Org/Util/PHPExcel/PHPExcel.php';
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Worksheet.Drawing");
        import("Org.Util.PHPExcel.Writer.Excel2007");
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);//宽度

        $objActSheet->setCellValue('A1', '产品');
        $objActSheet->setCellValue('B1', '角色ID');
        $objActSheet->setCellValue('C1', '玩家ID');
        $objActSheet->setCellValue('D1', '累充金额/元');
        $objActSheet->setCellValue('E1', '最后登录日期');
        $objActSheet->setCellValue('F1', '最后充值日期');
        foreach($data as $k=>$val){
            $game = M('game','','DB_CONFIG1')->where(array('game_id'=>$val['game_id']))->cache(500)->find();
            $k +=2;
            $objActSheet->setCellValue('A'.$k, $game['game_name']);
            $objActSheet->setCellValue('B'.$k, ' '.$val['cproleid']);
            $objActSheet->setCellValue('C'.$k, ' '.$val['user_id']);
            $objActSheet->setCellValue('D'.$k, $val['money']);
            $objActSheet->setCellValue('E'.$k, $val['create_date']);
            $objActSheet->setCellValue('F'.$k, $val['pay_time']);
        }
        $fileName = '猴子偷桃';
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

    public function get_newrole(){
        $k_time = '2020-01-01';
        $game_id = '50';

        for ($i=0;$i<7;$i++){
            $kk_time = strtotime($k_time.' +'.$i.' day');
            $jj_time = strtotime($k_time.' 23:59:59'.' +'.$i.' day');

            $new_user_count_where=array(
                'game_id'=>array('eq',$game_id),
                'channel'=>array('like','WXMP'.'%'),
                'create_date'=>array(array('egt',$kk_time),array('elt',$jj_time),'and')
            );

            $new_user_count=M('user','','DB_CONFIG1')
                ->where($new_user_count_where)
                ->cache(500)
                ->count();//新增用户数

            $new_user_role=M('create_role_report','','DB_CONFIG1')
                ->where(array(
                    'game_father_id'=>array('eq','14'),
                    'game_id'=>array('eq',$game_id),
                    'channel'=>array('like','WXMP'.'%'),
                    'create_date'=>array(array('egt',$kk_time),array('elt',$jj_time),'and'),
                    'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
                ))
                ->cache(500)
                ->count('DISTINCT user_id'); //新增创角色数

            $data[] = array(
                'new_user_count'=>$new_user_count,
                'new_user_role'=>$new_user_role
            );
        }
        self::query_gameorder_excel1($data);
    }

    public function json_test(){
        $data = '[
    {
        "head_img": "http://wx.qlogo.cn/mmhead/PiajxSqBRaEJiacia2CgibmWq0bd0skMhCG3QW4ZL1kdw0zVUFDUFZLz3A/132",
        "nick_name": "大林木.",
        "remark_name": "G33冷酷特温\n15615668886",
        "user_name": "",
        "wxid": "sdwfhz"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/PwEhkWXLX0eicpjialsKmSuDemgN7mGbvIOoH9dgjnU9xgSbL7K6wKOMR6lAnDwsNgnoCVzbpr5ibKEibmTxjnT4qExibDVWpz107ElPB1gMiahbg/132",
        "nick_name": "赵育锋",
        "remark_name": "主109数码宝贝叄18790896039",
        "user_name": "zhaoyufengzyf",
        "wxid": "wxid_8t1vejdddgad21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/W5FW04XKBrgPAaTDFcdwAJjZwr20Rd1QlVt6th6E5XRjSbMaaOXKEFzDwXWEbOsuo8NEKsxfoehAYD5K3sxzeOspkshniaGIiaXjx7s5GCzick/132",
        "nick_name": "暉",
        "remark_name": "A75狗托si全家",
        "user_name": "hui563700818",
        "wxid": "wxid_vtdjq0c4gxgs22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/bSb5dSzPn0LXN7K1H3riaZbbzPo8tlQoFvwI1oG8GUGVQAQqnWsqIQQ/132",
        "nick_name": "🐱八八～",
        "user_name": "yangyuhong0122",
        "wxid": "ddddddd22007"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Ue4yVRVLZLDCM5u4m6iaJHDwQfr1SQa8H2vHkbtYdDo5iclibrVBrcgFBDLsPJaOJyPJiaw9baw1CvbWI6ByCK7s1Y1y5ia4iaQF3lpaj4DBkM5Ww/132",
        "nick_name": "黄飞腾",
        "remark_name": "主144小花菜15280812102",
        "user_name": "feit0026",
        "wxid": "wxid_niwvqfyismbp11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/XZbPK62aLHq4K6ZA82bnMFdeZtjy5aKQAWNrxgliaV8SV8pEIkb8BSIruEDWMqtCYm6YLaaDdTb9TU6iayepwnsNd1Q83oddjXYIqH2DYF998/132",
        "nick_name": "三哥",
        "user_name": "quite75",
        "wxid": "wxid_zof2xjjobba722"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/gxAia8gXtjmwAsZq90m95iajXc3cOmDic9sPuicse6ZLXJo2zgCsPk1dzfx1OgibL8TaZVODOu2UBxDxcAYudOxrF9A/132",
        "nick_name": "Quinn",
        "remark_name": "A93毛毛虫18959210593",
        "user_name": "",
        "wxid": "zhengkunyin"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/oMhc1GhCtwNkIshHd4WutD8LxBLN9VIeRWjsibtdE8ZeypY8P2d0POFdwnhLdDcF3uvenicSFh33ibTickKML6HlFZfFs8MAUicNYCjDDgvOnW84/132",
        "nick_name": "莫浮",
        "remark_name": "主144仰首托比15828061364",
        "user_name": "love_seven521",
        "wxid": "wxid_a5pfunekfutd21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/JMepupPT8tBmcjLx1bEmYVhibGL5PibG1ibf4wVkyibrhEMNm7rZsdxDG0icEXQWM1qEFd6DLRxAVBUEyycIUwcfMBUYNNiauezCDf1LkFlFWAHkM/132",
        "nick_name": "人生如戏",
        "remark_name": "主121莫尼塔177597979",
        "user_name": "muhouzhusiren2017",
        "wxid": "wxid_qv9wd3kr6qut22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Op6M9bvUS0QMdlR4En5USf2KAlFniaiccNl06aXJuj3xeb3uSWIPH2zvY0u6Jjdqg6dIWYyLwaxiamfS2nbqQbw91CmnYetU8P2AySS4CofYnU/132",
        "nick_name": "a-Z.",
        "remark_name": "主137换个名字15641775674",
        "user_name": "Tj44266101",
        "wxid": "wxid_hwmzumdh8gek22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/mwibzAzbsygb7KkmQ0don8xIpiazqkAdMXOAo9W3LrpWJ8ugHvDfoRByoSU2mfZzI6Mb6DQw8Z79qwibpNxJkIT5SYibjtv0HeLwpyyumEHGAKE/132",
        "nick_name": "X",
        "remark_name": "主135臭弟弟18876708009",
        "user_name": "Later18876708009",
        "wxid": "wxid_n50ojsf8350a12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/FPpgYetc6Aq6hMNictSrMUAp07Xq3CyFLo51pLKEDQuMxDCaKZYtSnYLRG1EPibY0p2uAQwLaTaib25oo1x0o2ExJDI0NJiaRb0HLfzbUI47SkM/132",
        "nick_name": "kxhp",
        "remark_name": "A88南宫小小彬15814570431",
        "user_name": "yy6398521",
        "wxid": "wxid_vudfdjr3yt6c22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/J2w3GTvyVueXBdFvaJ33coFwoUdyjGLPvuM5L0nKWAKQbZna3P6Gmu1KOY9J5fJpXfxXQvv0o7OhgM3oUMqv3mmB3GKco5o9QGoSWO9g5vU/132",
        "nick_name": "小开🇨🇳",
        "remark_name": "主93服\n有拖就弃13660588181",
        "user_name": "ckx13660588181",
        "wxid": "wxid_jjoleeixxok321"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/FX4v4dCTFib5cRAotYP1qa7EAMIgqp4fmFuQWB9libs0icZ7o5cXkfibicEOaHnHqNZ15NYxTscpA6sBKBN6aRfhsFKy9ufUJ6SL5wNT99bp5p5o/132",
        "nick_name": "0.0",
        "remark_name": "G32康斯坦丁18650464663",
        "user_name": "xjflovezy",
        "wxid": "wxid_18y0cmib13wv22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/HkoiaEO6cm7xlibStVxPEfUJuqyWKMfJmXDwyudHnMfkYZPfKeoMMqypRibwMvktyKHH5urs0LBK3fGYgMl1ic3GH7ZyBdDlHib2DiaJADEYQYZ9g/132",
        "nick_name": "慎独",
        "user_name": "GRH_0517",
        "wxid": "wxid_fzdx5nirm2xo51"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Rf4RHQke9XXLnefTjvAbgwst9sXdKCcWJs6vkPlfBSFpEoJKibstLIRgicWicPMcHK3a3o2eqgZ12ib7V8srjQg4Cg/132",
        "nick_name": "Nathaneil",
        "remark_name": "G21秦川德里奇\n15801273127",
        "user_name": "SpaceSheepMR",
        "wxid": "wxid_nsksr84j4r7v12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/oMNiboH5tx1EPLKibT2MR5MY6BpibJBXA3clTQL5J3ibyHicWg42oQvDIMHpHEASWF8zY8BlYW3afddK8NAIRAxp7Jmic2X7X4BWMa7LScLfs8aM8/132",
        "nick_name": "培生",
        "remark_name": "A68我是你霸霸15113939945",
        "user_name": "peissheng521",
        "wxid": "qq763190101"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/3CpGQxQMh27EiaXj15HIVvoPDIOxXdRo3Wc0fmw3mN3qEVgRGZRvDenXk5prF4K6f18waHv86mAe9efT0TPsK3nRbaV28y0Xt93ibqicoKicAgM/132",
        "nick_name": "   Louis(●°u°●)​ 」",
        "remark_name": "主152怪异摩菲13721026903",
        "user_name": "",
        "wxid": "luojin_vip"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/SHRlicfkSHgV2kvX2wLlYrLX0IULNjibAcrMEcVX5PWjrHgL3Y8hd2Tia7AOK8qK7pUicgoFmxIzuich4VIg45AQbh1HDWJ7WM6sibFImbst68iazg/132",
        "nick_name": "颜千喻",
        "remark_name": "B16皮卡大帝",
        "user_name": "",
        "wxid": "wxid_nofq8g9e42bt11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/OwiaxK7WW1TuwCcBEejzs9giaq3jvTITPPKh7bBzp6nOeibuicPFrmy6gxf8icwiaiaIygEzbwIC0BWNOlSD2JAKeibCCwOlj5QQHSyYBAIUV4z1ichM/132",
        "nick_name": "Unshackled",
        "remark_name": "A65神经埃姆斯15158949177",
        "user_name": "Dz-33770",
        "wxid": "wuhaoyang666"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/AibV7KcUfvjyqdRUGfP06AW7uoiaKsJ4RKnaf5bL3jjQpSpW7Bj73htutMcEYOVPfQGAe7Kb2GjCYkyAs0o6RaqocsoF8RrCVaotK06PU4Tbw/132",
        "nick_name": "一天 。",
        "remark_name": "主145一天c17620311312",
        "user_name": "QQ529474372",
        "wxid": "wxid_6771057711112"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/JJaaV7AIuKGGvJ9fU9OcX46anIvnJzb81YcUlrpTn1Z1tibZmF40YJEbxTJkJVRm6x6g8ibZLGy5HWcnKNmOhbMQ/132",
        "nick_name": "小美妞🐵",
        "remark_name": "主129\n佛系玩家\n17776243123",
        "user_name": "WuTian-ian",
        "wxid": "wutiantian4714"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/7KOkibicKdK4NtMaJ9hvY2e8B6dWopMj0gNlLNEkD5htWlKcgUTdVREI0MbMXAaZxIfBktwrNhicPYXAUdKSH8KCRbzACC5QQyT6vacJc3CaB0/132",
        "nick_name": "Alvin",
        "remark_name": "A65狄敏特18930932872",
        "user_name": "",
        "wxid": "wymas1130"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/gCBAceSkcicsHswzr5Rwiah1dia4vy5SRmIUcTXQ0Zm5pP9PicRnc84PK65uW6x7fwvgVea9VWTQOWscALQLe6LzibNwvMUe6hbXBWMiahbStX6d0/132",
        "nick_name": "",
        "remark_name": "A88无聊玩玩18024207532",
        "user_name": "wxhy0220",
        "wxid": "wxid_ibjnf8l35d7v22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/IWn7oYia8lmEP3M6lzCGmrEsRKfaHBXY9CDyhDrj3kKh22JQVUjmkk1ic1UxMFQXxDIsXgvv3XWmn6xOALIhVuibffiaMSs3QdaXBTGwOSyI1gQ/132",
        "nick_name": "喝茶的帽子",
        "remark_name": "G17小明是星矢18774009831",
        "user_name": "jzw_18774009841",
        "wxid": "wxid_8ifsr51qc9m511"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/mZK0bcqFUO5PwJVGFFe7pv8DOMhBtWImBiaSoQfViaWgzsasJbQvicicPhUZWZ1WgckF9b7tMVkrsVmtES2AU4KFCv3icjia5TF5RwSzotVXoY0ns/132",
        "nick_name": "_W",
        "user_name": "qq1024062774",
        "wxid": "wxid_lr4m9m43zo0q12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/qUt3hXNt3U2SdMiaxE0NBzv49ibSDI9EjiaOCKlRzVwccPibTkyXM4ZX77gsia2vDnS5nqxfjN54xqQnwE4ibAs34FK4FYtwmRclNfOmHfZ9Aic1ZQ/132",
        "nick_name": "Sinsaut",
        "remark_name": "A60服 逃亡、 13272897419",
        "user_name": "sinsaut",
        "wxid": "wxid_7345603456612"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/DlC3C4ticjrxeMZicO9YW7CkFultfMoTSMnoEmJ9fHbPCq3Es4ibwIMBPsILO4DxOF7Bvz8xAWlNwQLLQnVs37fgatYeia6xqvCKkrwBQLYjYCM/132",
        "nick_name": "Sup",
        "remark_name": "G23\n我也很爱笑\n13828401641",
        "user_name": "zbw6666668888",
        "wxid": "wxid_zsvnpyp4a1qw22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ZkoLwmUagqeLQ35HyWzChzyibfc9RsNNaFZSVTS5QAX5ZkEN1fdCbyGw7PMlTyzuHI8icLOufoUUw6FgGnt3qhYDScD4ddZDGmJ2D7zvyNDZc/132",
        "nick_name": "小陈",
        "user_name": "ckh19970118",
        "wxid": "wxid_efeayab0rjvf12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/AA4xoURAtGdW0YJJyVUnz7zW8SxpsNndhVE8bLcTyt9sIlD2vWVwlHJCibmgWOwZgowbQ9ryIl4urmhqd7tzjUByPRDJSmO8LiaHEoItWqNaE/132",
        "nick_name": "x.",
        "remark_name": "B34丶冷色系18976379655",
        "user_name": "xgl18889995730",
        "wxid": "wxid_6w8orltr1ude12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/PiajxSqBRaELNY6hysQKrcYiagGyIPiagbefuTLawXJKP2by4ppzv7FTA/132",
        "nick_name": "kobe",
        "remark_name": "G32粗心哈代13559923215",
        "user_name": "",
        "wxid": "kobehuangnan"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/fJ2W5rEZmZWxXGrdiaHQZaUEyavwr4SsMCqwEhF9XyjAxCHmuBWfcJAeyK60JEaiaPZCFRkOR6kcsmTQcp8B8riaQ/132",
        "nick_name": "woodzc",
        "remark_name": "主103鬼魅詹宁斯",
        "user_name": "",
        "wxid": "woodzc"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/dvaMfhwqM3qDUVsmBXiaAJyCkJK9FuicRpTicJp8xzZSbQR737cTALItmTNylawld0liaOroBaMjCurDEI2jicSG6Yg/132",
        "nick_name": "mg🙃",
        "remark_name": "主150断子绝孙腿13310895923",
        "user_name": "theoldman_mi",
        "wxid": "Migggggggga"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/RkcE4DqibL5r0ezRgXwDveLmrmDdxZtPZfPEfav5jVAjiafg5VZGmLccNy8P8Hk5B3PNtulgnZVibnI7auSqtv7LJqM2f3PmOG0jwgu8Ke3QoY/132",
        "nick_name": "xyxydbb",
        "remark_name": "主105\n杀心三藏\n13397575753",
        "user_name": "",
        "wxid": "yu_lovestory"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/IucFNyXVbgibfVDCoHPPmKPicJrCeHlwHSiapyBpEtkuib6mGA0f2OzhHN1cxgOU3zAU5wbkBRKO2Riazhme6lf4Vw1icLBq8DliaLNkbYQ8wXN7icY/132",
        "nick_name": "超萌",
        "remark_name": "主160十夏九黎18235131017",
        "user_name": "kuaidianjiaowobaba",
        "wxid": "yy1014997423"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/zGRKJBictV3GTY2T9AxicvBCgiagxfd31kf8xQ1jQ17ia17kicMia5vspyiaibOVaVRdsI2DfhndiajndNrmZNQscIyOicQ4cuichcQ1pYDMPhwtCrtgY0/132",
        "nick_name": "🙌三少🙌",
        "remark_name": "主143二哥来了18677987171",
        "user_name": "",
        "wxid": "maosan9608"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/vxGObWOChYYbOf5YMgnBl1iav5Vf1o1ekQr4fL3ROPcKIICIruFibWHWBel8VpGaic8Ctkcq34bJ23PCrTWs4lRBHHkHJcic86J4kiaxREYUTLog/132",
        "nick_name": "Wisyoss.",
        "remark_name": "A75猫南北18019701991",
        "user_name": "wsy1991111",
        "wxid": "wxid_k9zu806fdjzj11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/IlL7NMWLI2IwFI4aLYAvD6ZInedjU4Jibv5e4JlQBTwztUbUbaEiaYicD5etosDwlgH6B12uC0ROwLpOdbFliccMJwnfKYafE6HyicSaVuBOr13c/132",
        "nick_name": "CC",
        "remark_name": "G26一介书生13864186616",
        "user_name": "",
        "wxid": "cc8239739"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/tssOQFKD7jOHzPvGrfdyiaWajU6YQoBR5ujwYYlvXJnotqzDst3wP0toRY3waOZgARLAeuDdEql3MuwtoVIFIiaA/132",
        "nick_name": "小楼又东风",
        "remark_name": "A94愤怒尤金13476539488",
        "user_name": "cl571013915",
        "wxid": "wxid_tr35iqci8dh821"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/c6iaXXNFKP6xGoNGic1f5TyYjeIYBsLh58CNRquskLa0aEldcUcVmEib5axDveZtMib1xicicKjE7CbYKS0PWyicfBf8w/132",
        "nick_name": "习惯",
        "remark_name": "B27阳阳阳15958822788",
        "user_name": "",
        "wxid": "ruanchenyang"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/bT6hiaXY9d6TibVoSxSSBpQI7Za36tAxnsTLaUZfJKabWuyMNoXerqOJc1PkokXjCxMH9r3RlTIWolyuWkM3WQNGYl6YD1dFwKbDEtaLy393I/132",
        "nick_name": "🌔",
        "remark_name": "A93渔樵耕读18701137239",
        "user_name": "",
        "wxid": "wxid_ztzobke9ir5t12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/FFxtib1FicqH1Qtr98uP5k4fcYibxztGbGssep4j2bb63G5F57BiaWZ48oqiaP0FWxuicpkll2H53zHyXGCLDvp1eyHnBpHjluKyibpmia3F1bElgbg/132",
        "nick_name": "Azrael车",
        "remark_name": "G42棒棒没有糖13884966902",
        "user_name": "cheyixuan2008",
        "wxid": "wxid_5wbag5yhwunu11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/DbM1FVL1q7fRPaTehV8hI1TamrUuRr4esByYAgjvDSJDGacvtJe7PjiczcZ8hvD1wG3amU1n7ZCUVtiacbqxA6TZvc8EgkAOmlZobLD4rtf2Q/132",
        "nick_name": "L",
        "remark_name": "G31一一一一一18880441801",
        "user_name": "Luuuuuull",
        "wxid": "wxid_b4kyhfgugfpj12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/yXeib9TABSvq7QSbmS59icuuFYVlWBQEU0EiawLLy0f9VeSAic45OQB32ibhP1wTrohmGDe2q7Ll4iauJicQRo7ZaB4ibqPFxHfPob2DgTpaodffGia8/132",
        "nick_name": "Metal Heart",
        "remark_name": "G36小霸霸15122473085",
        "user_name": "yhd24789540",
        "wxid": "aini1314-love-you"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/jzdX3Tcttd4b1n4pmSr1UGkTRVoiaSvrgRicDGakialu9LgNKtRVyILibhPTGl5HQk5EF4XKxP3G42wlyIvF6IUmqHBoluJLmKCuHqLJQOYNXz0/132",
        "nick_name": "TTT",
        "remark_name": "A1性纯者15852595685",
        "user_name": "wt2270081494",
        "wxid": "wxid_45hxbbnkbwir12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/JQMwGaoorOtwAhb2q4icyiaSkSVrMzfZ4icXL2TjibHDKk6Fl9UMWicicz67CUkR37y6Fybwib3Wesstc8PM7AP8riaPpVU2xianzm18V2JYHSgIc4I0/132",
        "nick_name": "Lin",
        "remark_name": "A90洪哥夢裡看雪18620483182",
        "user_name": "l13813888898",
        "wxid": "wxid_th61mgret18l22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/TxMC4TISJGwxvmPv0Zo07GMng1Z80BfCUc5U6Qic7uicImOXgXT42S46iafiaNOTMUv37X3TcS2JPiarA2rMYvEibEtg/132",
        "nick_name": "无奈",
        "user_name": "xyz15106803345",
        "wxid": "wxid_v5sqi6rwvpzz22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/76AialrJXZibnjFLXsnsMuDNutPzk0HbTP36OdRe9OMdDPH8TajpYjJNDInj0K3ze6N5km5xgQdbGdMkibnvXmXKcyodKVNNbKnJ7HXlhlUGOo/132",
        "nick_name": "脑瓜子嗡嗡的",
        "remark_name": "主179蒜头王八17859511413",
        "user_name": "qi1771771177",
        "wxid": "wxid_cmn6bgii7bpn22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/7uJQheTBONibibmlWgbLLSgRCUvOHGL1OrmVNnYs7licY15NakaSIiasojMhNWheNmrWMiaGqzgr2FK9iccpzicAdg0LHdZuLzMg22WnNicvobiayrlM/132",
        "nick_name": "Tmh、",
        "remark_name": "G30天外天15800656071",
        "user_name": "waneenxuan",
        "wxid": "wxid_y8215aegugh512"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ugRPVhoFpTpCuM6dgFicMZIFcl3NNogWVptjU0pRjT4ODjgzKhOqmEgAP7eAWES6Rdzb5t50ice1e7BXq5dquXXxrjtstRAhN6ZNznibZvfddY/132",
        "nick_name": "执笔画浮尘",
        "user_name": "",
        "wxid": "hjx0208"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/1lb1PbbBPric9S7LNhb2aS8zqmdq4m9pEKYv7OKSs656rpuZH4498cwvfb1e2C4ZialIszhaxtQVpCN1IiavdpWyfGtSwlianffjrHUlO3yg0iaU/132",
        "nick_name": "文明",
        "remark_name": "主116klose18984949254",
        "user_name": "wgm5494",
        "wxid": "wxid_03ilat2v2rmi22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/r0rqWVeqavSPib1xnGxDImXhKkZ3zVtLaBpU9shIBPpPich35YiavKrAwsPKZAwcXzmhQ5DYbiaykB898ic4ibic6gIOpXEwVr7Bdp0hKC7ov4bXcE/132",
        "nick_name": "ncaaaaa",
        "user_name": "Xiang_ZhiYe",
        "wxid": "wxid_6053820538011"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/gNLX6ybAkktq0MoJgSyH7Ghzo1Jc2D8ZeSWMNicA84JsgI2jpf6wdWicSSEdE3hlzOOfO2xc0TMukSXZSA0pMyfRdhVbibIRF3tO4qFAYzQFlA/132",
        "nick_name": "啥玩意呢",
        "remark_name": "主133\n芒果叔叔\n18968714438",
        "user_name": "",
        "wxid": "x_549469"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/6JwAKy7lNcDVZlVcuaqqg1qxCq6SAuFPBoibz0PmNoU2Lic4kOd380dpeks6Vxg8B9ql3pbgsuib4z4iaUibS7upic7w/132",
        "nick_name": "为你吃错药",
        "remark_name": "A包57为你吃错药",
        "user_name": "iksatro",
        "wxid": "wxid_rbed7832lr9n21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/CWxocQiaw8vvSOCXpvkoZH4oDPUp6MwtP9UFtnNUj4kO9oshGVEMp5uGfetH6GnaOkeegeXKGsJFryDBBvNZTMAdt1ZNSMYM26BibehBMlaoE/132",
        "nick_name": "…X、许",
        "remark_name": "G12有神林奇13402075400",
        "user_name": "XH3355",
        "wxid": "wxid_mzu355klo8ws11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/umeOegLp3UO3yoHMkvXYrObe9lrRZPeTnicNiaJxWE5TRTtHibW39PKGQlH3Q6KLrHliaGeLYezafNoWXH5TubCJDFtQicy9fUkLYMhUS2rfmJLk/132",
        "nick_name": "李雨润",
        "remark_name": "G8不死文森特16657155180",
        "user_name": "lyrdwx-123789456",
        "wxid": "wxid_35kigf5qhu2o22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/mHiatdXVfeibhkCg7UsegME6wRDRO35tia9HSAw2xgE1wbpBMXps2gfrbCSmz1uuicdfPc8Iic3LibnX1zEYPibWkicT7z4wicUWMZHS5yBC4AzLibjrY/132",
        "nick_name": "🍂知秋🍁",
        "remark_name": "主157怪诞马蒂斯13598803110",
        "user_name": "",
        "wxid": "wmj2189"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/D1Zk2B8LnAB3A1UMEtGhZUuPLqaps8WMo7jqicb0g5CVlbvJJqb9RPZKIpoxRC4zlWHPuMD7cADSVFS70VY4tiayRCDiaCevCZia4vY855H9L74/132",
        "nick_name": "明",
        "user_name": "wzm-1991",
        "wxid": "wxid_3449884497912"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/puKkBGnPHqb61LeiczXHG6ot1rbxlDEaFeTrzjdvxQzicia7kqwQYZT0zUqyUyWsjlKPWsYWzJ186bDriahZ97ZuK515HdJYjiaMEqB94wDw8cTA/132",
        "nick_name": "young",
        "remark_name": "A83小小阚叼15850303198",
        "user_name": "young19870419",
        "wxid": "wxid_06249l9aan7p1"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/4q4S5CYiahJgzWr5rHrX40RBIviapgKH2WNR7c3aW7FUT6aunYXVCKup7MmvUbh1IYqHMB3VlUR0E3Hc63obIk1BWLHnlEcyPibN3libc7SbqCo/132",
        "nick_name": "归",
        "remark_name": "主121冰_焱18116423641",
        "user_name": "gui10251993",
        "wxid": "wxid_vwb4w6lth1e722"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/trmaXaiaPPo1me9BQRou0BAs2BveTmumloPvtDCGmGNoBOibvibfW2bcQcuGibib1M5tYB9yurm4hI0n0n4F5j8acCbUQaxUWe52HFUgXAibzuXRI/132",
        "nick_name": "🗣",
        "remark_name": "主119儿时的最爱18697976439",
        "user_name": "",
        "wxid": "taneva1"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/trhDGUficic4GOSNZO68I7EXp41lbILEWojlWYb0cqbYl7rHiany6crCu7xtEGDHm3EibBbHfOgcM8aBoc2KZmYBDBNCmibvQcZaNG4pZVMI1t2w/132",
        "nick_name": "云中的二舅",
        "remark_name": "主110给力杰森18983807696",
        "user_name": "",
        "wxid": "nicvswo"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/2piav18p0wJPC4cJAL6DPxmd8YYeGFj09C9hgxLiacPE1eibvuY1JkicHMibgG8v02giccicxxvZGBAOv626SAwfUQOOTYFArkR26POfciadPYympGI/132",
        "nick_name": "潘婷",
        "user_name": "",
        "wxid": "wxid_dog4y8afzh2s22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/cicVERk4yVpRtpm8bkJpBVdcNicNtDSiagWSaVh2rpZs0Q13q1AezMjkjbpHrFKcXxdYZfQtJoMqQUdTK5namQfmOlj9KXEicxbN11CibrTB3tes/132",
        "nick_name": "上官绝天",
        "remark_name": "主132忧郁的小乌龟15810073282",
        "user_name": "",
        "wxid": "zdl19890130"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ibOCk8H5eRrIGb8BSswMcsHd1Bicib3IKoCkjlxQDtUicWNW4K2raf6z5O5WC1Ps4Po3ibnnjHCnyicB9ukmK5VAa3ucm60dQUYBDe2Sx2T3ezIhc/132",
        "nick_name": "阿门纳姆",
        "remark_name": "A80阿门佛陀布15010127913",
        "user_name": "",
        "wxid": "amennamu506864844"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ckfibO7T8LDAbx8KwNLQFjTbXvJaUfwJ51xtCu0EgEpjIuG3CqVxeV7IKCAl1a0tpCVZm2yILQfHn1PPy95Ut1w/132",
        "nick_name": "smile阿希",
        "remark_name": "G34斯米勒",
        "user_name": "cx8987897",
        "wxid": "wxid_952vwx3pk4tx21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/B7MwDicUzRSkW9ibjbu6mqgib0HseqODuhluNYugwMPtQjF59N9FPunIPic9BCZVwr7vlsjkTHq9s4dMRhzKdIEaew/132",
        "nick_name": "周靖",
        "remark_name": "B28周指导13705169323",
        "user_name": "",
        "wxid": "z228299244"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/y3EGsGuC9enx8NLLaTHD3WTymhUaczJeUkWBkgLxhQuc4ic0rlwCzsugO0HIZYT3MZLBIkPOoW8ptepzqe7PNE1WDfsFqvc6ySj69eO8nRtI/132",
        "nick_name": "可爱又迷人的反派角色",
        "remark_name": "主148九九乘法表18728489613",
        "user_name": "catchy0613",
        "wxid": "wxid_e3hrnhnn163521"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ElDRYRSdtRTnMsPdKjd1Xc9DxFF6ZmHD24gibFnAhviaI5mZZudyQvcfcJqicLgP4pKTRDs1xgmBKmSUic5WGegsaw/132",
        "nick_name": "卡、",
        "remark_name": "主178服怪异利利18944947577",
        "user_name": "T_bin_378",
        "wxid": "wxid_vcxltevd9lb921"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/pc7z7mTL4deMI2icbjzKJ7vJF7Nqic1WFJVKFDibZtGFicYL6VlUia1CYhAtZEl3jHOUSyiaVE2ORBE3PWC5iau4Sjux7V1iaMowdyy1A9mhnVEyEJA/132",
        "nick_name": "水~好运.千里马轮胎",
        "remark_name": "主124闲得蛋疼13542097551",
        "user_name": "shui512004272",
        "wxid": "q512004272"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/PlzLAWXpCFbhDdzgCOwt6LLYPkFxHdWHocZU6myN98FicVYPrOzTDOJaR7DQH9FviaO0YCictL3SLgVUMEHEvqbcgO97xjdq2POdnm6fWAAPYw/132",
        "nick_name": "网名在长也不如一个备注i",
        "remark_name": "主137凹凸曼丶15925005332",
        "user_name": "sirui11",
        "wxid": "wxid_ndevje44ld3y21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/WdazaJCBySCtZ3leHAak3GGibu8b8n175xHrbt6ko0r54wCmu7CxS1qjiazvPQmgt73s4C17ibMtibazya3IE2xCKA/132",
        "nick_name": "MF_芫荽",
        "remark_name": "B32莫小亦18250818571",
        "user_name": "",
        "wxid": "mumuzhisanshi"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/zyhVaRWKeM1UCBjfrJibo8fVspbahZxGVVtQ68mvjiafnpKTleSX9gpUChYqhnlwc7NcZQg4xnRFjlxBLplAQY8w/132",
        "nick_name": "轨迹jay",
        "remark_name": "G10仙骨博格18167103185",
        "user_name": "",
        "wxid": "bianlonggg"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/hN9kU6lfjG1RUEiaEicMl6fZibVPjvwxgavQLFGaurVSFm3EEKsS3zINicK0nSVlbR9GPKMBwB6nXNmcPW6vXbh8NxXJlYPfPPCYcV92gWFrOGc/132",
        "nick_name": "石磊",
        "remark_name": "A82神经特纳13717656121",
        "user_name": "",
        "wxid": "shilei048551"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/VpZB9yTOkaaS1y42PuZNkHibjXv4bGwibcYUjCu5oe77HKoVtPEGZnaHlVc0WHw0icjRiaNVDvBUhrEzwZlI79UjrZXtqN0EvL3rcoAELmicFiblw/132",
        "nick_name": "A-唯有、努力",
        "remark_name": "A87丿轩辕1665511601",
        "user_name": "hh614569698",
        "wxid": "wxid_ex3af6qsy28v21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/KzeyicZwJPFX24aMlpPwLJVcVDaswfMLtaOhqf8rmT4bBPnGUfdIIia5YmDkP8Wp1yoLovicfibEtT9f8HT37YWby1UwHLp242eJde560eCdtos/132",
        "nick_name": "Traccy🐑",
        "remark_name": "G27Aya13349948007",
        "user_name": "lby723183660",
        "wxid": "wxid_nqhnbbdlzegm12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Y7YKAvdQHyyz7J0rCYdI49MugictpALeuV983QrlXibSZ9AwvRvxI5FfU0DeZguzX3ckYHUnUo6xCLROkSwxbI4plhvmZaPaRYgfDWPsjWeww/132",
        "nick_name": "唐勇一",
        "remark_name": "主132墨上花开19904452798",
        "user_name": "tyy19850220",
        "wxid": "wxid_z1u7ggpwff2i22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/02FNBj9tDDxfh16ibp6C9sFxl9ib8d9SG1k4WzUEgClDX3VqK9kRTOvMRzLRhibDWyvDwXn62LYm30EO0H5s20Phe8G31cP39HPAK2M75ZeyfU/132",
        "nick_name": "fish_sh",
        "remark_name": "B21fish13927208854",
        "user_name": "",
        "wxid": "fish_sh001"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/dUkVKhGLk7hRKPgUb41RicO74LrftSqbfwrdnUfRfD5JsMHXG57OqnWWHcibjdibutzhicpib7NpCLteWNg7cdJUq7fl7TibrDPbl1yhLjG4uV9bM/132",
        "nick_name": "nick🐱",
        "remark_name": "A64淡念一束花败",
        "user_name": "sz_Gorzy",
        "wxid": "zha505620731"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/VcqTgpibehNBcjiao5WYEObyhZyVh4zPmiaQYiaKSjSF4MIbSBhSydncSbGrCOH0cqYSib1YIu88KFMeIiaDWt0rVgLJKMALjDKaeqbyOTItvoDXg/132",
        "nick_name": "💋 口是心非",
        "remark_name": "A96F4丶道明寺15601888701",
        "user_name": "a1041922841",
        "wxid": "wxid_xgnydxrqr8r222"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/vWneJc37Tjcjqc70BQRvQn475mSSlN6JzUssTcsEENlicuHxnqocPibxVp3TNibNIyywrJWtKmTEI8p7DqlWjM0Ez4Cej9Op5EqkJibWPiayic8CU/132",
        "nick_name": "dW",
        "remark_name": "A1\nDinner\n18127763438",
        "user_name": "jdw19920511",
        "wxid": "wxid_8fjws07fh0ok21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/pTGvv5IxQEGYI1daESTnHkdCYRnVS7rUWhmiba3gQHaM7lb8vO2Vae2V0Yicp1xHZL4TXtjzX7qVwrRKUxtFTMF0wrslp21giakibuLyKgIrrWQ/132",
        "nick_name": "陈庆之",
        "remark_name": "主陈庆之",
        "user_name": "a15980517169",
        "wxid": "wxid_33angkh1hsp922"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/dwqBDazq7G88TicjtyshwicutZ7LYCTzjLdmyYwUIqGp5SA4MPDavSM0FcQFy7VIvzuFmiadjIVLFTAsWhtNnOpQA/132",
        "nick_name": " 江南",
        "remark_name": "B53小韭菜13672979283",
        "user_name": "",
        "wxid": "super_rapist"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/kzMeXalTOpbVUxpMj7BQ1D7HfIGWia5Cblt0wibnmZEKAHgqI2miaTj6an9Gkmp0JhFIicgk6AqkqCdHpbMzLIOsQlyYvetP2ColpMlLyFgF2hU/132",
        "nick_name": "三好青年～",
        "remark_name": "B33我爱取名字15151773537",
        "user_name": "",
        "wxid": "jun738848"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ObXcWBmU7dvZ673rWfw1798Ptfep4V7RvsRichpqUHmia443ibRubqvFzN36h8CU3CicGQyic3pxVh4mMIksK0RMBSBE97ibdpzt6IE1TRljUCicx4/132",
        "nick_name": "喵了个咪🐱的汪🐶的哼🐷",
        "user_name": "kt123521",
        "wxid": "libohan976224"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/DkwShthEx2GiawU4NSTb8M03sjtv065DdG81FbAPuPJaiaeI4zvj2Xl73ABqXrZjBfTJyMbibNibO2KmxUeQIaEia97JSLWibYa0MRkgdCKTSRmUc/132",
        "nick_name": "執迷、",
        "remark_name": "主171鬼门关13235678936",
        "user_name": "qq5455908",
        "wxid": "wxid_8mclcgsrqj6c11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/fAF2EeFWNwLeiamSYPoI2iaicvAgjfwf3OjxOHiaGbFeYlE0NzEnNOxIqzM3HGqHc6B44VjKB2eseBKfqouhpeichFw/132",
        "nick_name": "宇南zZ",
        "remark_name": "主112元旦快乐15689733300",
        "user_name": "",
        "wxid": "yunan5052"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/xgfIStJPxbNCKJ4ERgR6c2kKuCzWPvFOXZE3gUiayibL7MtRgxTgbavhx9mbuiaq3tQ0xGicKyZ097DhhGEZVaC6J7uTpwSGwa7oKrcb1hgf0Tw/132",
        "nick_name": "高",
        "remark_name": "主135\n踏平东瀛\n18166223115",
        "user_name": "",
        "wxid": "gaoshengxiang"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/BN2K1ZibBOmyxfWnYh7tHzQHxTfmZXsH1p8NPiczOZmiaibWwGjmQMdCXfn9dT4iaqVMaxpNunibH8uYBheNWQkichdjvgqvDOrPibneMSMnPCNvTwQ/132",
        "nick_name": "陈泽锋",
        "user_name": "xiaomaohenmeng147",
        "wxid": "wxid_3ddp7ltyokgs22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/icVNQMn9GjRPA3GiaFLNKzRaLEcyRbWdonzBemYtLQ3HRyeHHA7u4rdPNMRIaoFFPRGP9bibfj29tPKkNNUBgfINctFzrWpMYb3aGnuY9S1JAs/132",
        "nick_name": "🌞",
        "remark_name": "G23脱俗普里特13868002503",
        "user_name": "qiyuyu1-1",
        "wxid": "wxid_qyeg6fxkydpw22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Nv0D5IRia9QnWEfLf3lRicaY2nzNbSGnYmnuN2DbF0du0jMbreI5W06FzAW8nOEFcABQB8RmU6vw06nIbkmSBOBsNkJ0udBXwTBLLYJu4lKU8/132",
        "nick_name": "Samuel-_-",
        "remark_name": "G40服魂飞魄散18660790019",
        "user_name": "Gzh1000",
        "wxid": "wxid_2soomhofctqv21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/9OibHiaGH6z47iaGfb2pygJGnORZ4Hgt0zwyDM6AqICujS7faXGHqo36mZ3A6amHVR8kSEV3mTcy8JjCXpD3hGPB7k7yaMNWHqL28plGphicF2M/132",
        "nick_name": "John_林",
        "remark_name": "G39强壮里斯13120960588",
        "user_name": "",
        "wxid": "lyh6960"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/esxNA4gKHdsy16oBRuaTqLONWGZJRLkfQNjMGJpfAGwXMr9zibSzVFtJzPNYzS3v3csO8PfkMDA68YT16YIuyL9jKLiaapzTlrJDKKg6WZWiak/132",
        "nick_name": "Crayon",
        "remark_name": "主158雷迪一嘎嘎15050500590",
        "user_name": "",
        "wxid": "qiun6213684"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/8rdEPFBzv6dZDzIIbLibCDkYUEmzR76nmmjJL0E3fEHRPSS4yQZEFUdUbguPedPkm5gIXTgibnztcu27HLv1za4QZuZs1ibjV2hpLr6hL3BvWA/132",
        "nick_name": "残酷天使",
        "remark_name": "A81服殘酷天使13439420577",
        "user_name": "",
        "wxid": "wxid_tyg4jnz8671c12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/plFyvOzLf3QgQxAkcmR1Ubyicr0Lz2rt4Hq0HmYJuV9bsZseNbmiadHDK8dT2FeNY2HAInQP1X0cKXNksWrXAwZDQB4jpPz6eb7MJzOQbeicOE/132",
        "nick_name": "CLY🍀",
        "remark_name": "A88cly13301673345",
        "user_name": "chengliyu95831",
        "wxid": "wxid_10afu8ew7dy021"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/qediaCic6icwmhWuDHjXH3RPNGVRXGgX6Xucvg6A2yjUNpicQvNHkhvQbmYSsBXzvykJTicjtPtjppp8kcMUJIL7uDSTcA0oKQLLnTs0eIrTqHm0/132",
        "nick_name": "鹿鸣之什",
        "user_name": "",
        "wxid": "wxid_pmpsh2akesv522"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Ytg1FZYlL9zewlHvcEX81Ye3Najwk22kYEibCkLUibib0nOeaUJic23NenqIOticHvic9zbYjiaYpsJOrPaYe1Fh5Laow/132",
        "nick_name": "A链家 黎作栋",
        "user_name": "distrin",
        "wxid": "wxid_6goav85qyudh12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/CMrnPKZxialqhwsbaRdxEtTLR6OnicBItmIvFibIeUedZdwuqE2pRS6Qq2k1FGmGICt8CxLUw2QicicSwj7ShbFT1qhIia6oS1R18asia1AiaG4CVOE/132",
        "nick_name": "成都房产销售小张",
        "remark_name": "主122zzp\n13547954893",
        "user_name": "qwertyuiopzzp0904",
        "wxid": "wxid_5pmov9s6oh522"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/cKIFibrzhDj0g8Mfw8RO58NUG4gmofPBFtTx5ZItb2yJJzCfiayIeia5Z9TaPk0oaPpibRxE5NvIBXHy2NGtk02nOaHlrZHiaHRXsTcPsVaFFCR0/132",
        "nick_name": "天边看海",
        "remark_name": "A74爱你呦13307608932",
        "user_name": "Kissu_1010",
        "wxid": "wxid_6w0dp2k0k4n121"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/n3cyQ3OUyPsSQePLjicqxONhyh3daOqysibwNibCzFS50ofL2Jiaicze1krloUZlHlA9pSranfVhTSlVRfMJn388XANpiasZIvhU00m7X6mw4wL3A/132",
        "nick_name": "十三医",
        "remark_name": "B47广州涛哥13450367121",
        "user_name": "",
        "wxid": "bennett06395022"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ObzewraiawPcmdZZAsiaojoJsQX5icjYZLRMZn8bzTnNMZazRY3cZwTf46DgfM4EPCXF2QiaaWzXeqXNnt5zEkwBhnc3zNZsRcwdSLwGRZhTibdw/132",
        "nick_name": "峰",
        "user_name": "liao327941410",
        "wxid": "liaohaifng"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ibNatdV9ibwyInibCnkCe2H3Tn4ia3KVA9IwDN9lCY5AJpKQj8ic5UoRyxLa1XWpsxibv4ubNKKkEVKX97qHLj7fFFItHcFKgyickyF64sAkWKLAzA/132",
        "nick_name": "陈晓东",
        "remark_name": "G14哎呦喂13666667066",
        "user_name": "cxdfyq1226",
        "wxid": "wxid_jmsu276mr58o12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/yIvoqzarbt6ouUYPAAmUjQ3WbRRjekongXkfKx4fZEXWa6ejFSn3q0VkRM7hD9cs2dUWApnhHAC4YysUA6icCHlV4wvT4hxa3aJ0zXg0NWRE/132",
        "nick_name": "🌈宇宙超级无敌小凡凡💓",
        "remark_name": "主144\n高贵的凡儿\n18120138882",
        "user_name": "ffcd3128",
        "wxid": "yuermama520"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/8RX1N12bw86rFyic1bZCwKJEIeoiblVB1l9k8Ejxh3Xjx1hzOA56wByzDcjiapH9yoliadCI65FQN5S09KJQpbibmsMToEvEAAgdAXJ3JkRhcRc8/132",
        "nick_name": "又又",
        "remark_name": "G16阿西粑酱15601905850",
        "user_name": "EightYears_4Bee",
        "wxid": "wxid_mpdhvt7brd7f11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/OUmRZw8eR0k6e1miaxJWUqRDm695SVoqOEvBZK9o4PBv3sm59RKobRbxfcFxM8oRBnyVFcGQ3fQzfr6cS3kuG8MWkM2qo87WzjGoZ2Fdibicibk/132",
        "nick_name": "晗晗晗晗晗、",
        "remark_name": "主131\n冰丶云秀\n13974992890",
        "user_name": "",
        "wxid": "zouyihan002"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/7GwsBWD42mnuviaX4Xfx3z9Aec4TamWcjojCduyYS3cXUTD690HMstIY0DjIuEUTVNcEV8DTxmclMrjw28iaNpMQ/132",
        "nick_name": "A、moon",
        "remark_name": "B54Moon15811188593",
        "user_name": "LYH901210",
        "wxid": "fengqingyuesu"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/wbWNQCnAXibrKxic3MXSN3XankqWvActkFia3ybiaicGGhM9vErvccSeibicso04kSpkQf74cRE0VrEiciarsjOf5r3Y7r40OxmJLK9TDqpsYNVhibrOk/132",
        "nick_name": "hxz、",
        "remark_name": "B50永生赫尔曼13560168780",
        "user_name": "hxz683135",
        "wxid": "wxid_59zkf0fa4xqm21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/sHsMb0oGcJZ8dbQVTrcPacekjLSbIn9ttPJibjEpDzXNJibrmbicib0ibMUy4Jre9OUR9w5mRbvLn0CxH7VetxlMUNSRcunNOzkSNfjESyLPCwVE/132",
        "nick_name": "宋黎华",
        "remark_name": "A92服-Oo柒宝zZ-13816455102",
        "user_name": "soureika",
        "wxid": "q410212514"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/petXibMkg26NRV1ctWOic2prtUccpLrZqicXUnlAGf2RK7m9keOTZA9UQCezr8eXVpaju4GxBRYVsx7iaF7MYCj0n0DaIuXQH0RtAARdPwjicGEQ/132",
        "nick_name": "秋陌",
        "user_name": "h_jcheng",
        "wxid": "q495544"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/nAfFSicLHUAP2XLu6OibD92PIEOCpOvQaamibC9wByWm28fo50w2b5ee3aicjUT4faXcbuU770fp9PwKicMicuetlkmOwmcldNIgfibTAjGgVRYlwM/132",
        "nick_name": "杨晗博",
        "remark_name": "A57雪特兰悲歌13501617678",
        "user_name": "",
        "wxid": "hanbo-yang"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/eVAjnjUbwr9qptLfp3HawppAtL27tRR6VWCXDZ2Tbicp5Gvuia8tdA10W6NBdr3T9b5snXIoGzTWy1srxIFiaPEzaibxdxN5lpnibLnqkxCgrYZ8/132",
        "nick_name": "Gayoung",
        "remark_name": "A87醉后一梦13829655207",
        "user_name": "cjyforwechat",
        "wxid": "wxid_csd6igar9vzm12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/aFvX1btrmRW1Gbc64X1iaTmC9EMrsNYjUomibziaF5vqQRBwmicMtsnccRAFmmptlYZv6Wq6MjN7wEG9CCLjepCfcGVch5z3UYEKBccOUgHiaLsU/132",
        "nick_name": "小楼听雨",
        "remark_name": "A5生气哈维13917772043",
        "user_name": "",
        "wxid": "wxid_4ii745c69k7221"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/93dlX4ZKg5MSia0lDkQaG526sIsWiaNeRFURPNBUu4jjlcib6Y79GXB4jFQ5H0OOtQnGB5tZNKKXciaMNpm6Vl3oQFRTK8XL5LaXN7rta7BM6C0/132",
        "nick_name": "今天蕾姆醒了吗",
        "remark_name": "A97宇城訓18804203886",
        "user_name": "ForeverSaxKun",
        "wxid": "wxid_gkszcevbck3b21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/c9X9R3OSMUbRGT1GkuPXRibPF1E2Luep5eBD1LMjIF6runrTZjJicG69vAo3u3Axm1yJCcI2cbTy2Fiasuanv878FeoSFwko9vEwBqubJrlHpg/132",
        "nick_name": "Static丶雨辰",
        "remark_name": "G10static15110010825",
        "user_name": "cly941222",
        "wxid": "wxid_nqph3g53cfox21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/2icXgWCV5r9NjeoMGPEHeIkhMzcP1edUKGnJESkEFyPupsJ7PL22kLT281VSy7V119BWxejICrgjdxDTbs79KoQ/132",
        "nick_name": "IHaveaDreamTobeaFlyingMan!",
        "remark_name": "主117奕宝13645762163",
        "user_name": "",
        "wxid": "a46180132"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/0qF1MHc4yX65UK55Uw3V93sHjx7BOcp4yPbge4QWRHVJEyib17TFfkC9UTnlWBOdKyCplqkl6IfKs5o0ayicUoG817SbUQZvqQPI0JsNamcick/132",
        "nick_name": "招财进勹",
        "remark_name": "A99怪诞温泽15088681709",
        "user_name": "ttloveyang520",
        "wxid": "wxid_ao5w1bwtcbd811"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/iabHJ4PSYImmI4Xxmn5jAlVvCTLMOAtAeBOf7ynrmW7wMqJPlVTBBKbpLRKcBiaCOXoM20FyS0YgicwRDoECsUgzmNUkmlMibFVoTwwcGunU5c4/132",
        "nick_name": "lwj",
        "remark_name": "B34鬼崇肖恩15958103326",
        "user_name": "JJ060613",
        "wxid": "wxid_ie33rhdbestx21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/MkFu6YI8EBic2EEKBE8f09JgicI9As0EKLoUr5UKV5f4KzH6S3Xw9EoCdCqgpb6Qe32EkysKHY3hpwkRicAr4ic7myoFhljOPWC6EXN2OkZ6FBE/132",
        "nick_name": "峰",
        "remark_name": "主147奥利给13758992138",
        "user_name": "",
        "wxid": "wjf108403"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/WeOBBmIjbecKGlXuOsPicJIylhibhB834YLcvVbt0966A0GEd2ECkoIy7Ye3wVHTAu5kmeeBiaibONAtDyhQnUvicILD1nnOGNBNDwJurhmUibubo/132",
        "nick_name": "张包包",
        "remark_name": "A68张包包18363108150 ",
        "user_name": "",
        "wxid": "z350258583"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ThOHluwPeacMzEmibqMT1iaP4LIN7yNwH8nyNm0L1Jo2dDuQI6KOO8K8kTZSbk3etmbWQMJN8fr1BmgqAqCQiaXy4HI2PgMpppFBia4fhZmIuVY/132",
        "nick_name": "张景阳",
        "remark_name": "G32魔域娃娃18516139417",
        "user_name": "zjy6550",
        "wxid": "wxid_8quq15exi08521"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/RewxKQ9biazhia2yh0j0RYNuIh50q0hIN7tHZuzwNNNtfByDScVTRclkHSJ8mic1mkWVLtyywpR87461ewDAk3FnU5qTmFiaYngUpV3MFredRhk/132",
        "nick_name": "@仄言",
        "remark_name": "退-B67尔乃插标卖首15850808068",
        "user_name": "",
        "wxid": "xg971390539"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/PhK62lIGnxaRXA9vBg737DD5C29gTe31VWx0UzibuZuHXN1WqOQtHmq2RJkYt3SHkicgOC6meXUQKfibHmQFBrKp7ITibWtot9bTwHcgJxyYzic0/132",
        "nick_name": "站在世界空白处",
        "remark_name": "主113\n给力科里\n18770682014",
        "user_name": "luobiao18770682014",
        "wxid": "wxid_6j53ydbvv24k21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/H2V7WVJ13FGvMRXQmrPBicNCtsWTf1tnN9so5YiaMonHlkqlKmbtiakJ15aCp5hVAT8GheClnqaUfichicsEnea90bxd4wovveG4l6YxEX55qKNo/132",
        "nick_name": "12345677654321",
        "remark_name": "G41害羞史帝夫18754176655",
        "user_name": "LNTS_0229",
        "wxid": "wxid_23jntbkhiiqc31"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/iaFBZhbyKf1V3ibmSkor1RBsUspac6sCP6s8JEuib4EEJyM8VbGTZlQbBVOV50eqdLSvEpp8xIt82OOHKrPiaJaJz48AiaXjMszajjxmVae73A5c/132",
        "nick_name": "小宇",
        "remark_name": "A51欧皇15960020942",
        "user_name": "",
        "wxid": "wxid_22hoe8junvx422"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/eicyAcV0vxbZQK3eM5LVvRkQRMmeehB04yKYccHpFPaP7cSmT0Qz4ibAGwugLCn4wiaCrWNnAsxsGhHGIGXh8Hs2EQSibXkiaFeoWer4ErPdLVPw/132",
        "nick_name": "H.on1",
        "remark_name": "G29\n开胃啤酒人\n13255290866",
        "user_name": "c191136289",
        "wxid": "wxid_hfpgce4h7pcl12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/lS4DJdibyER1C87ojesOiavkD8ib2hTWk0kT0fibAGJUR6bxR8P09eGIrxZia7wbD3SFuwycdfiaDUicGR4Fm92iaYVP0Q/132",
        "nick_name": "南城小爷",
        "remark_name": "G40一刀999-18001108443",
        "user_name": "",
        "wxid": "captaindadam"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/73icicR9CjnT0MylD6605djEBibib1E9kGpOQoq6OKpvGiakicy4KmACibTa9S2xia8mIKAJibQZFxCQmy4TMaTABSUt1RYOaxxTqLT5jLVvpoWCF5ls/132",
        "nick_name": "电竞贝多芬",
        "remark_name": "A68落叶无根13395992496",
        "user_name": "x1203381309",
        "wxid": "wxid_bphs3n3urlt322"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/BUpRzCibboabbhCFKpibNqGuC0LGs3icA3vup6KsSv6dWJjvKSP0rH051A7vm95rppia5ic0QrSZwuQQWTDHXPibvof1cYSsUic9p0dibqHCicibaPVdo/132",
        "nick_name": "上班中的小晖🐶",
        "remark_name": "G28失神佩奇13818920237",
        "user_name": "Virgo_water",
        "wxid": "wxid_px3tmr6444ws21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/sdUe5iaic0qjWZIicwyY4iadLibRQ4mZibXc19iaIkV68LApK3n0WWLg3m5dUOTkxxz9KxibSdMadyKgY1jnR8ffXKHgKiaCzxpcicmdl1DquQo29MxGg/132",
        "nick_name": "🐻",
        "remark_name": "G38小萌新\n13656006949",
        "user_name": "admin650208",
        "wxid": "lwx92lxq"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/9M7ZDNlwFvzqh01DXAg4lgfYxkjr1NfQfuLGVdfvCFSlVZC7cbrCgNNpxgGHKshG4vhCDx05TCxibBBYTFFStNw/132",
        "nick_name": "余小鱼",
        "remark_name": "A95服\n舞小天\n18850222725",
        "user_name": "",
        "wxid": "wxid_h0njm4pnyivp12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/McYMgia19V0VicOXMstxGkQOGT7TR93V5jPrROZAfmbAeHCG8UlfOzGQ/132",
        "nick_name": "信博",
        "remark_name": "G25GGQQ13003205700",
        "user_name": "Ff29272809",
        "wxid": "wxid_waq5b9agrv5n12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/smcRa8wJW7q1O8VeJXDIknLRPibz5iaia5907IhcJXWUZIFtGoeRSibzUC7tJmtXryiaNdRhBjoB9Mxib2kJKkibt1zE9gShC2mBVs8MvGADZZHqCU/132",
        "nick_name": "觅青春",
        "remark_name": "B52觅青春13202555589",
        "user_name": "shu345994102",
        "wxid": "wxid_m707d9xzt99h22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/UibAQQ09QtaT6NzCiaQXmzGEsCroHiamYqAICeFicLGsiabfUGjqTmiaia9VNyyWqSauNZrvdEwxriaOksbjyGP8aLO5AA/132",
        "nick_name": "Michael",
        "remark_name": "G27悠悠极",
        "user_name": "laysj27",
        "wxid": "q10027"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/u8VlZHfliaB5T61FQ0n4vfiaKbe1bKML2ev6OdmmP6yLTavKOiaTRoBh97kZRvU3eyo1hzIm9sRP4MArfjEmEBNWICQSAuYVUCUqc8M6c6o8CA/132",
        "nick_name": "Sky.",
        "remark_name": "A91暴走的冥王13632777500",
        "user_name": "",
        "wxid": "zhen_com"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/3kXAxbnNYKDhGxiaiczicca3tkqCdPicbBxyvHqQ3jsKII5OWC4vqrWd1iaBW9xTppRUcaJTM3VKuxoBPQDawtSrficuviakiaFbL1r6mc3Inian97zU/132",
        "nick_name": "独家记忆",
        "remark_name": "G39轩辕小智13391836797",
        "user_name": "",
        "wxid": "abyss_1986"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/VsSzTqyuwd6P7mSvK7TIYibyleGHlsB3dAISzItf93l0j2dGBtzibmwWiaZWU73BF9JOfsfK1NgknE0HHIB13HBicVqPC2XITvjIWFouKaDgicbk/132",
        "nick_name": "ゞ提菈米囌灬 😱",
        "user_name": "",
        "wxid": "David880518"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/YQXZicpz6rGC0m3uJOaeGYvSxJ1GGibBLpicWuTMKEnQPTRBHlZeZOXaxsyZcPSaJwJQSu6GhdF3xOK01tIl1qPibg/132",
        "nick_name": "💤",
        "remark_name": "A85帅帅丶13959557949",
        "user_name": "",
        "wxid": "ruan931110"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/iaEDhEFU4ElJBtyDyu5tVaYKvhVU7J5DLlC0Hicj80UDTia2llRbdLibP96e9I15kojoY8TmjRia8ktiaB83vlvWAps6NtzYN3AOPnLl8ymlVu8SQ/132",
        "nick_name": "领航e家-技术",
        "remark_name": "A63地狱克拉彭13959257092",
        "user_name": "",
        "wxid": "wxid_jmekqf36isq222"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ulT6X8bLA76HNh67qmBIhCRibr0jyNEgvIIKvXAx4fLCPfuRRFGSqR0j1WTnRajhdmDdxiaXiadkhJvnEdiayTTe8lHDwHfykOZSxia2IrgkiaILc/132",
        "nick_name": "命",
        "remark_name": "G31冷酷高更13581533199",
        "user_name": "firstlove0403",
        "wxid": "wxid_7532795327812"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/817yr0bA4yFGLGichJD5af3htzNYdibEtmUjaaFdHnMYnHYT6OqkMNh9ZUmicrjbuEzYzkxV4ELS23TyM8uQHUGjg/132",
        "nick_name": "🇨🇳NV.KT",
        "remark_name": "主109King\n15061733205",
        "user_name": "kt253854255",
        "wxid": "xspcoms"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/wLxk2ODcqiasLzhrIBdt855KU90NwmYklA2lJA8Ypia04icOiavIkWMDh89dnibSrqIB8eqeMUiaFMciarSBiahYNU6ianvBR4ecR9juYMkqFuq0QFMA/132",
        "nick_name": "城南",
        "remark_name": "B70我要超梦啊15659778362",
        "user_name": "ran19900407",
        "wxid": "a331455878"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/1ctWa9s5I1GcRzYNPdOEdU6YLsb4zvgV1pINQjDsicfJ8n7Lic3WzOUWyuIMianXve5IF3Rv3lwicNvWXZx7MeS13g/132",
        "nick_name": "鸠摩智",
        "remark_name": "A87一个肉肉的人18646101122",
        "user_name": "z278440530",
        "wxid": "z740453546"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/RySsRyicRibYAgLWpruY7uOntKHtuXxvVDHgZtWNc6zqV5qc9AQABAh7aWeeHNI7gvgaNWEUPP8REC0blGKibtNC9tTttNtarDUXW7JtJtKwicI/132",
        "nick_name": "时光与你",
        "remark_name": "B47神纸者15171423250",
        "user_name": "sjf1995825",
        "wxid": "wxid_qijvsb2s33j422"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/MibQWYOVjbVibzFoiacBC44effcceSDtiba2uvXr6wA8nDR9jXpv2ZnArE8CBcTuV8ay98UJO88fYKdf2hibj1RmL9LF1JQLrUCSgdT3grl6ZSkI/132",
        "nick_name": "城潼",
        "remark_name": "G28吴氏城潼，13195635777",
        "user_name": "wu1987xuehai",
        "wxid": "wxid_3xl5k3pcihs412"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/UOCHvzUGAIUibibrR3nBxYO2oHjKwPILcp8AyftO79b34ibdZS5u0IeDw/132",
        "nick_name": "Ri",
        "remark_name": "G9門前菩提树18030600873 ",
        "user_name": "rienwa1666",
        "wxid": "wxid_kux9setuhge011"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ylRhrSjQb8iaooEogYXI63WSyvJYfD0DIXohpBjlFibqnE6QTfW2UYJw/132",
        "nick_name": "章晓天",
        "remark_name": "G8刘金银15888057744 ",
        "user_name": "",
        "wxid": "zhangxiaotian084976"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/08CkgFpxicxByR5dlMFKF3YpgdaGJ4iaUF57eiaQ0GTnpgqLzve1tA2q9XNeicCTQQiaQ9LneqZZI0xG1bfoMWSgEUtXiaHs3UFB3orFStDRa4BFI/132",
        "nick_name": "ζ.",
        "remark_name": "主106奥利给15252573127",
        "user_name": "susu_92008",
        "wxid": "wxid_ps14qwqahryk12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Naia7RXMLwhBTw5TjgMc32Vg9uxsxia1AT4wictB5JYlib5DVa3sPH0oPfyMMlJeKvM3HQCOWYvjtSsL5WichM882S1ibuZudtb5NvrPZx3Meoqdk/132",
        "nick_name": "老赵",
        "remark_name": "A88Screws13702978345",
        "user_name": "H_deQi",
        "wxid": "wxid_pyiigam3sn3b21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/tHQnaXfuRCib6NzwL7y5hFGpNaYs5pud0M6CUUdbFpwiaEng6Bmibfh58JyAbZVt9F7CRhZVic0lf4lhLQB2wyYxmJorfXJIzVQDGqABsDxAunE/132",
        "nick_name": "Jie",
        "remark_name": "主150降龙十八掌13145778470",
        "user_name": "",
        "wxid": "tanweijieyi"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/SVycUJNiaDibMBV6r2O9wZqA7smiavZINIbYGicfP53tKj678IWvPIybDawhLjGMn96BtIetpnm0GQVhq3SRsZwxrVPxO7qLEWjZlsCdFKTPfag/132",
        "nick_name": "Q",
        "remark_name": "G12小次郎18516062072",
        "user_name": "",
        "wxid": "qyf_0307"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/MNglAPAUV0TFfGFYgntiaIpWjzx8KZoCiaJFZIWm451v2lfLNwnMNdUnzUVM2yjlic0q2EJmXf2suYotpfNNhKCxhYLzp6vicy23gOD7QbQKh2A/132",
        "nick_name": "«♠ssss5υρ２®»",
        "remark_name": "A81强制中出丶",
        "user_name": "ssss5up2r",
        "wxid": "a39703281"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/SV8BXJFoEJiadiaTaH9QicIEGxib8kEEA8ia1Jsg6Y5AicjLkBGxupRGpeVK1JA1iaSlSHdSiaSyo6rB8WicRl1AlNhA6hALb7WvDcLBdRdgUPsafiaGQ/132",
        "nick_name": "篠颋",
        "remark_name": "主123可爱艾默特18522234494",
        "user_name": "gutt1029",
        "wxid": "wxid_7874578746312"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/TVf5boxmx8yiaiaNv7lPpZ2sn8BgWOWCYa5Da03e7DuyCeuvfbJHM2jAkcfUwqmiasrJ47icdSmFCL0GibZGN2WuqmBzRQO9XyB3Pic9kDf9gkB8A/132",
        "nick_name": "Wilber威",
        "user_name": "xzwade",
        "wxid": "wxid_gu211c859l2i22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/eMmnk8UcXeglkFhu9eibQELGb0Jq54kNYE8WX7SBfmCBE40uL42JSxdf4bptmaqr944F4zgfBjyaWnNZBFmBGglDpGgz3v7u6e0tYI9V4RQ0/132",
        "nick_name": "Jacky木",
        "remark_name": "B49皇家本森13229996296",
        "user_name": "",
        "wxid": "jackymu001"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/tmeWVamTmQ0S8ojPxicMWpUukd5FKQLGbKvBl3LyPTe26C6N0cpJjEPMMu4VMJ3mgtNfaCw7yWZcfxfNd16DoMw/132",
        "nick_name": "Aa",
        "remark_name": "A装死蒙德18518265115",
        "user_name": "qizhiaaaa",
        "wxid": "wxid_yzigroeah6ci31"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/qcnspFoXZQxkR5oZCYDyNUaWWChkZMga5jPlk7pC57vyXd8UbLibIGcBb6DXQqgTEPmQYXymFbhRCBsbGGk2mZibU6GPPEPCC1C8eiaunic3d8I/132",
        "nick_name": "指尖",
        "remark_name": "A80头很大18950998819",
        "user_name": "W-Jin-Heng",
        "wxid": "wxid_pwrdrtvga6ou21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/av9kC5j1yfnib5Y8ntsUrX3Zmp3Gaicag0aia8FUibctwekIpQAGACRbM5nadicBwdXZB7QdkGbg8cQ0IbCnbUxbTT9ShiaIcxY9Nos8X0gJcoJLY/132",
        "nick_name": "FP",
        "remark_name": "主141卡蓝18550412143",
        "user_name": "LAN_KONG_SE",
        "wxid": "wxid_lzsyn3idvxea22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/M9NtYtMNqajk9ibgIo9KpAT0ULfpicETOCk8ZkNc9VZelGlZnamKdTMq9FicbWN5sgRGuOAcic43ESQlicia32nxiaAFXpoicGKrdKrAIvTFh9hLEbw/132",
        "nick_name": "人语",
        "remark_name": "G20你的人语呀13855367776",
        "user_name": "liugang_0509",
        "wxid": "wxid_05c7pptjfomo12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/dpGGK23QA5DAwDvS0ZZ2vXcWNYRichKS1miary4JFg9ul1NQyHRF9bn9iaatfaYW0NcBjOZhopErMkyZUpONs6KGw/132",
        "nick_name": "四库闲人",
        "remark_name": "A78道公子 15179136424",
        "user_name": "ggy1414",
        "wxid": "wxid_g74rmtvrfohi21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/fszyTMPPkr7ZicgvGLs09QUrhuZrpOxXPscgvgSdtqNeWaboMbfE2MmauScIgAP2OwwYQzslVo4AicAmd7c6GuQQ/132",
        "nick_name": "DOM =͟͟͞͞ʕ•̫͡•ʔ",
        "remark_name": "A96毒蘑菇18566386626",
        "user_name": "dom-dingjiayong",
        "wxid": "djy_113323412"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/PiajxSqBRaEJAAWHJ0GHLribYufT06ESxkDlsqLRpS06R8v40NCujvicw/132",
        "nick_name": "庭亘",
        "remark_name": "A66边秋烽烟\n18565593689",
        "user_name": "dungeonhx",
        "wxid": "wxid_4puc8c81n74h22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/pgmJUqu1EXaNjoo5WWE8mvKLOvlL2ciaHD5FQdTbbBN8SEvJq8lHqXQnmTfryZEmib1AtU0LMAhNVwvvC8KdMwMkfEc9aI6MhEic4TSpibb5ibrs/132",
        "nick_name": "🐛🐛🐛",
        "remark_name": "A74太凶残13006914909",
        "user_name": "saberking_yang",
        "wxid": "wxid_l18x6pg0e8ox11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/K467syrVRetXcIYbiaibRUAjbnKPCLFfibqEH8tpEEiafkqNHbqfGyKO8cMfib2wh2yib410yUVP5AbQ2ibEAQicoTJVWQCypAyoqKHXXibaiancaKYyc/132",
        "nick_name": "Elv",
        "remark_name": "主121\n狮子Elv\n13922422221",
        "user_name": "",
        "wxid": "elv2221"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/7BlGPibEia45yZF5iaNY0M5p6eIHRicIicWjGzmibJ7Bxial5897tDNraFesKp6Hofe08YsFeDGzqD7bHz5PRqcV58GMNu9Vrlkf7UiactiaWeCM2DjQ/132",
        "nick_name": "MIKE·小陈",
        "remark_name": "G31MIKE小陈13482570734",
        "user_name": "chen1026_chen",
        "wxid": "wxid_nzhnaiqbkp8132"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/8t7CudcfXBW6Dd3rNgvBEsZjmXKuGVXcBfhJAEogYLhSuGsnlq2BnOuqicdIGt4JKtOAru8rAHSqDxhjL0KiaGOA/132",
        "nick_name": "花开、",
        "remark_name": "A55looper15640282520",
        "user_name": "qiqivip0808",
        "wxid": "xinxin8976"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/4B3GrvibC5ia6EoIlMw9DPYngM4gDgecYqcHxibeG7DevGZnbqKu6YhB84GY4LEG4Xu9ZkYUYfk0DqpjMbOyBxvOylZ3J0UPzJvn2UCiaQ93D78/132",
        "nick_name": "叁石",
        "remark_name": "主151施主莫装毙",
        "user_name": "xuelei3498008",
        "wxid": "a27716048"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Bneyl1Tgcdn1mLwXajEH3pph5UlJfls9wJicvmiabt7IibTnTQwtAjHK7O05ibSTzW4CLenlyyunu9q31m5BOo3h0nQFbiawgCMEoC9eKecKncsc/132",
        "nick_name": "晓晨sagoon",
        "remark_name": "A77Sagoon18754258820",
        "user_name": "",
        "wxid": "wangwqc1990"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/HtfxnHJnETcFUfEkmjkiaRAic836CZnhmCEDQX2woZL7Q4w8o5PoZ5QYvbyfiaKSP3WcEKOAM6fHwHmTBYCAzPtqQ/132",
        "nick_name": "我叫❌❌❌",
        "remark_name": "G14岚色梦想15510507305",
        "user_name": "ZXl521_999",
        "wxid": "wxid_kznexsups2bm11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/PEgXeo9oOVP4cYljW8BxV9ibibniag6c9mMWvk0JvNTcUQrCfic6PPHyQTYialAudaKBcMicic4t0MSDksdqPkKf7C2fKG9tNsDRQUYI9urPXaiaWgM/132",
        "nick_name": "大宝",
        "remark_name": "A55生气比塔13521924389",
        "user_name": "bao87117",
        "wxid": "wxid_353wn2i3o6vm11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/7p2E1f5ZItODKCurQoeR71Mj17qUFkVbnRpvLiaoEm2bqibnYVEPN8gNSiaA2zhwqLsmc3OKghD6AoSj79nYQ58ZQ/132",
        "nick_name": "Mr.卡卡",
        "remark_name": "主123\nMr卡卡\n18507000010",
        "user_name": "L330695208",
        "wxid": "lvping330695208"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Ww16gtJibic5yodnJaT1H0qpS3VdEhYKaGd9m4B4E4CtwrmicAt4WiaibhWxCuBpBmeO7sQWq6AA5zdDd21qJMmmeA98DOW5OaOxNCib76yU7fl1k/132",
        "nick_name": "🌲J",
        "remark_name": "A68傻东西18675664786",
        "user_name": "yyzam10",
        "wxid": "q115260149"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/CeszGiaB8wQat1eKBCUcKIwic0dXrOLG63fZibR1kiblB4bibHiaz3Xg8pmzutqT8mLadNVy38JNdjcibnKLEt3vVjEpxg5r2qgoTWNzoW5McWLiaLg/132",
        "nick_name": "A原色广告₁₅₁₂₇₂₀₉₄₁₆",
        "remark_name": "主114囉囉囉15127209416",
        "user_name": "yuanseguanggao1",
        "wxid": "wxid_ibnmvxr2juuj21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/t5wXw8RMFrlZNSmzokIWGrTm7UAhHlWalTZ64mQ3Uew0nl6hJxzAcmRKia0YaA64O3HzWgu0ZmP21oohpNRXoo5vketmMWoAKUqBAhrhGqAo/132",
        "nick_name": "ono",
        "remark_name": "B36专注詹理斯13916762814",
        "user_name": "qq405938012",
        "wxid": "wxid_pozgf2ziueka11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/V0mhkIwf3EEQjJIPJ8jjpKianWXDOtmGyIK4l2GZj1YNJuUlISc75Og/132",
        "nick_name": "丁满",
        "remark_name": "B23你baba18788104319",
        "user_name": "PHY02178",
        "wxid": "wxid_l8kn6npwraoh11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/kuCUFF2MsBMTQK20Vvm72tMUD3aPxaajFycD7wweZmlc22nPcxmWGuS8VHy3fHT9L0qLTAOTx8uvzHricJYqEQmzgmCst5gIVTpoKc9SNSHg/132",
        "nick_name": "丶﹏ 依旧执著ミ",
        "remark_name": "B39A雪J雪R18920599196",
        "user_name": "acm19990222",
        "wxid": "wxid_ck4or90qa3ib12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/aUqiaZg5ITY2yfGibMRdBqichibqtlibFQZsZSLRZylvf8tganV2Pd8QnMWWFt3x23yQvJ3RLjXpvA8AMcg5XJzp7ONNnibZ4PhWjvlHiaebpdmOjM/132",
        "nick_name": "王侠",
        "remark_name": "G26Vone13816331194",
        "user_name": "",
        "wxid": "wxid_vo65a2k3v52f22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/6CXiaKeALPfC1Ciaw8GJsujg4YwibtHxZybgKrdS7DRib4MPtKoaGIL1YLfOoiagLX8rsXsCwpJed5Hzd00m21y6Ob3VMLdH0EEEcoU2845f09Jk/132",
        "nick_name": "鑫",
        "remark_name": "G17飞舞黑格尔13805157091",
        "user_name": "",
        "wxid": "h158300675"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/0eC3qJl2EFc6FZdjibAKbWVGSicwatmV58U6JgpMnkbibIESgI8JZS5GaqsUkJSo6wveAcwu4zu6FXPg70b7BQOxp4H65vyqDBnGvJZSxxUt6Q/132",
        "nick_name": "malone",
        "remark_name": "G30心动警报18372036981",
        "user_name": "Cj1an9",
        "wxid": "wxid_vpzsh9j1a3o422"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/iaFeAn8bPlKPiaiaibBmuGt4TCfWSdZ9NBz8lopua7xmhC7xUKhuqzlGmaKJN4HrzLckrlyPLZMVc70bzEcN9f5uVQfPrEltcW0C0SW5GsCTkqc/132",
        "nick_name": "Andy",
        "remark_name": "主110守护者18250664744",
        "user_name": "junbin_0418",
        "wxid": "wxid_lltnkmrb5sm831"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/fPsdAhhlPFG68gaSib32NicHQVDFy9jKH1pRBqVmiaac2jz1iaoP8kchNfd9ibqguibVMuCA4jMgm7toUb6ic7dclkrmQ/132",
        "nick_name": "欧派志坚¹³⁷⁸⁰¹³⁷⁴⁶⁶",
        "remark_name": "主124玩到游戏倒闭13780137466",
        "user_name": "I_LaoHu_You",
        "wxid": "wxid_4xdbtioduo2v21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/2bq7UxeYxdJUXjstcQxJvb2sRW86G9Ml35AaRian5GNUge4hGFGCCEibv8jE1WrQpqsFxLicM1SRR5Wssyv5NlbGhMOAgtHApQ5mLibI56OzEl0/132",
        "nick_name": "Ty。。。",
        "remark_name": "主115-66大顺13797983903",
        "user_name": "ty---668",
        "wxid": "wxid_iay3g3mvqhud41"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/SPI9WpFHELCRfjzDbTlpdgSicTict5Vtvd9AUWdClb7ic3OicvT45nY9vT3vyu0El9eCpVfsRGpyQuCSeO5535RgkDEHNYqp0Rhgq4C8aq9LvFY/132",
        "nick_name": "陈k💤",
        "remark_name": "A67郁闷霍根18675759860",
        "user_name": "a584945251",
        "wxid": "wxid_w3kb5oj4ycm821"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Z6Gatm56dc3L3OnTSxnpiaklnGgkrATM4U7FLeHFHNiawtwXdQzsbics58vNZ4rJYjgsibuNJKngrBjLHY14qsrT8ESbOMxm7SaKMPub1FAdmIg/132",
        "nick_name": "邵某某",
        "remark_name": "主113\n邵掌柜\n13402312947",
        "user_name": "fank168168",
        "wxid": "wxid_iwdej16bvw5n11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/MRPmzpWpB1Hm4eGNVWkbEFTAI0nrvWbb3Zia0IoC3aql22p0N3hBxT7zIIyOnzh6ysYsSspT2CibbaPxm8dicMXxyx7qjQjUN1PXypkVzC2rYU/132",
        "nick_name": "©Ян",
        "remark_name": "A72坑钱游戏4k\n13711237277",
        "user_name": "",
        "wxid": "huazai116771"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/VpfibVWoXXCmFOcic36C6plmYlOoWhfIScy0ZQdWueRJOZPAF3ricvR76qyAduvX28C8NQHE6ZvK3hbHmbsvib2u2A/132",
        "nick_name": "不忘初心",
        "remark_name": "A78\n童年的回忆\n13685711552",
        "user_name": "yhh19870410",
        "wxid": "wxid_b7qjx6y4mx3e21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/s1WGAvXdnusRWpNmqKdMKIJyxiaWvUMibWLWWUmZZFdh32Es6Fia245nJRSSZNTHXY77GCGU9HHfmsF2M8kzUNxxCxnoSyprvg893K1evicy0DA/132",
        "nick_name": "、Jy",
        "remark_name": "A92丶Jy13616606565",
        "user_name": "_Jinyang",
        "wxid": "p13588985198"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/2V3ZK1oKXdbaR0ju2tic3L0b1I8qAKAvqcOTtx33jGic10osZibfM3Tt6pwtSWBvFwTe25f2wriaGOiaWialn6q3b4kkEoBBCM5DPqQbC4JnLh8y4/132",
        "nick_name": "💥爱芬",
        "remark_name": "A72端木梓潼",
        "user_name": "IvanGor-Gor",
        "wxid": "a25009074"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/mS0OrknS4lFeGlBbdlQpC4ia50Q43ofyh0485ib4dElU06luw3w0X6ZIkawv4aODcv1zYHObEibkpUz6yLib1hwIZA/132",
        "nick_name": "白开水不加糖",
        "remark_name": "A66丨林栖丶    15000180687",
        "user_name": "shumi0521",
        "wxid": "wxid_vfas921xs84721"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/oR2KibGicQnqrAuQrcgV7Stia3m22ShC7S8myX4Q124UzcOfr60NuPTSQJxf3Z08wQlckFriblAV1rQGVJx2Hs5W8Q/132",
        "nick_name": "ACE",
        "user_name": "",
        "wxid": "shenzhihuanghun"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/jrlP3VhhGb9wKIyzQlWXaXTUHjdFkr9648iauMWrbtDucw5m21xTnyc9o7KBWpUcfDWUo7UMtp5ynfzQ1WqxAMA/132",
        "nick_name": "Jerry璐淘淘",
        "remark_name": "G27璐淘淘13916375633",
        "user_name": "",
        "wxid": "assassinljj"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/U57SZc84lmoDGR2Yziagvia9xpVZfIibVPIQtxyX9qIW5ZTUNO0wzCCB5pJv04icdCkIjhibWvcAP0JvtCUYWVLia5tb41Pb0YSLvZ2Iefxd9rbYg/132",
        "nick_name": "谢路遥",
        "remark_name": "主146永生汉森",
        "user_name": "",
        "wxid": "xie67801528"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/q0wzic3SanIq8WpVhyKwdjYFtaL7x0MKgib4piaY2OeA6L3sf7PmX2AgibyMJkicc73al0Y4zBDlHrC8oxBtQ8VzrAA/132",
        "nick_name": "😶",
        "remark_name": "A91山前小伙15553867796",
        "user_name": "w164344872",
        "wxid": "wxid_qejbspgzr1ya22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/RHNqvcIPk50QFoHNKeXVX5GSIZ7gNZnGJpNMULZxUDH84zxzxibo6pCwM4llib0CIaoJo1TWRPsQcQqWTRyUG9VpzgQ2PBbVXlWKZFhfyMFlI/132",
        "nick_name": "温良恭俭",
        "remark_name": "主149温良恭俭张18438115678",
        "user_name": "",
        "wxid": "zhensacky"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/iaGQBo9o67CibnLx8Bv54Ba48q376QgjZ3GrmIIL7Dn3ASoO5iaLNHLek7bfyt0oicN1mUsBOCiarPT5fYBLQJhJ23Byqc2RggIiaOaTmkYphvacA/132",
        "nick_name": "蓝色格调",
        "remark_name": "主175飞舞博彻特13406812885",
        "user_name": "mhlsunshine",
        "wxid": "wxid_b8yxd6kelf0011"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/DrDdcjHhhmt0TycXayFTWOmNJwCYOAPiaKtZwJ7JQe6htVyNeEgleNkCCa0byg0hCq8NiaibyxDl4zuibyquJricBEB3noGw0Ru1OW3Mp189WHibk/132",
        "nick_name": "卖鞋大叔",
        "remark_name": "G31全服第一13567760916",
        "user_name": "ruan_jianwei",
        "wxid": "wxid_4qkvdht1zmt312"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ibH2kVmDh0hTLq2eZibYQBQHYJf5XnIibysaNXZfWdicd4Pic61icPuNtbEU5xAw3e4U7tVk5I1HfhKIx9NTaoqjTwbryjUCsSQzIOZj21eSK3biaA/132",
        "nick_name": "Kenny.You",
        "remark_name": "G5飞舞本兹15659770880 ",
        "user_name": "creadty",
        "wxid": "youyu170"
    },
    {
        "head_img": "",
        "nick_name": "🍥💋娇琪十足🍇🌙",
        "remark_name": "145.娇琪十足，13916261744",
        "user_name": "P13916261744",
        "wxid": "wxid_4t19ah3ug5ft21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/P1mc31vBZpDicuGjgYa9y0MGL8vxw6hDDR4ekibzpTyQFJIibD2gxgDFic5W7oHLLe84T6zWXHFWx3AayEUEnWs6MNFnqDjwP6hicMSbADbag3zo/132",
        "nick_name": "Smile",
        "remark_name": "B46独孤求败13420533553",
        "user_name": "",
        "wxid": "huangjian19920212"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/sdWmxgxCCl0RviaOYkb2nK3yLkY1oFjUGPMoIPFicCNBUzJ1XXPxXjdibNg2HE2e2E02FMib2rUT99w7uASWNwoGGw/132",
        "nick_name": "逸·飞冲天",
        "remark_name": "A93化形沃克利13052019069",
        "user_name": "",
        "wxid": "yifeichongtian136418"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/oXibGKno02vvQibyj85J8o5WUibrX8yuyxce3OrRSsnX7EXw4hB0psVOmYfWn2SHgLMicHc9H6oOeemaZVkbu18b1eUEs8wpxPqySkhyJFLtrs0/132",
        "nick_name": "不锈钢丝绳-小陈",
        "user_name": "",
        "wxid": "wxid_1we7tvogj7ie22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/fufcx0neibfwRXTGEtJu3fOnsrxxcSWKnJVCaA5hatRPR1Y6ebh6iabrTSbUM8ovfSSl4icKwLFxDHhOzic8icySszasyXibY4N4rE63YL97aLVvY/132",
        "nick_name": "一岁一枯荣",
        "remark_name": "B59哈哈哈13989766815",
        "user_name": "",
        "wxid": "guohaiyong1990223"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/h1Lhib9ZaCC6nbYTkdUGBBxps4vSFbfyx2jHqoTqGia2icQn8oAx6TR4I2EIWZ3aPP5tgweaTKtZT6cFVUymyMvNKs8eto2xXaZsQ6wohDib6bM/132",
        "nick_name": "Sky琪💤",
        "remark_name": "G23Sky小小琪15868141660",
        "user_name": "",
        "wxid": "xiewenqi1660"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/gUI6LTVyblGDU5oNb2ibb4ZjYRoibiaVfL3X0hhAkI7licECicFI9VFrtibg78ySgylQbTlYjNkLYbMh5ltXPWwceicibQ/132",
        "nick_name": "吴彦磊",
        "remark_name": "G22看我这头发帘",
        "user_name": "",
        "wxid": "wuyanlei123"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/SjweUR3Vrse2gJk4XXI2U0yuVyqaDNS9ZtEHcLQibp9qjowh8S1tEgkx0icJTHNbt24Ml5Jqx2kFZFuHjeeK3aYK4zicRLiaiaVGjVTazicPXoH1g/132",
        "nick_name": "笑！",
        "remark_name": "B88劳资贼飘13758984790",
        "user_name": "csc7065192",
        "wxid": "wxid_unc2zd9c4sry22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ESCNQlhwEXlV7XoBGKjZM4gkO5BDkjkSQbdlSRy1fsxMggBvkCa7sXEbw4YIJftW2GE7ASCnzaEpKPOw1puhtMpPw9ACuiboUq6fBtHGN0ZY/132",
        "nick_name": "陶森然",
        "remark_name": "G11Shunta15510033687",
        "user_name": "tsr961128",
        "wxid": "wxid_9hzsfg851j4a22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/jibXaGOwmKjzAUCJI61wlFH3mj1Vjhflp0grYvwpdTNt8ic87yR2XnMqLTadVb1Ra6rOQyFmIlkwS6FAIOcTA9hayZR1pGdb8taViabwrI4DWI/132",
        "nick_name": "🐵. Fa",
        "remark_name": "主106\n又呃钱\n13450214088",
        "user_name": "a14088",
        "wxid": "wxid_8rt73ruojgoa11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/T0a3ibNH9X3UKENic1QgAJQyAjiaxBkMK9Wzmbf2tiaAWqdKg0CamWaOzTN7mGcm7udrOXOZkQnwKPa6ia5mcmxXic34rTe72nuldxSzxF5E9bDl4/132",
        "nick_name": "～1028",
        "remark_name": "G29服\n余生与微度\n15167043138",
        "user_name": "songhuiqing_0528",
        "wxid": "wxid_1832458325112"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/UsZmxULBNCWZXQ81aAKibj7HUaKhPfUFWBEMMgv1SCQ3r04l7raYL9ddzI5Qj0icEnOX2egeiaT8dMdcobuehaiaw9G2Tq7TbffrO9IXciaRWa7U/132",
        "nick_name": "一番",
        "remark_name": "A90厦门一番13599536533",
        "user_name": "",
        "wxid": "hy33300043"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/qV6nZNwYrg6kcbyD0ibao4Cyh65GjUKFiah1qtWIm0sF6Hx51Pnwf9UX6auseg78Ajiaakmx9yrpsF93H9ia0XA0lFzdDLkvbOiaHMh533A1ecDA/132",
        "nick_name": "云雀 ",
        "remark_name": "主147宜宾燃面\n18507000071",
        "user_name": "yzgvsni",
        "wxid": "wxid_nxf90e0be5mm22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/iahNC2KrKo3AVL75OT8wSCzicybYOU2V7wmibvvgQlDG1wicTjHCEucC6NFTSB0QVqRPVa3w4La8cmeYsLF4EMOMnhwdBp8wiaD2hAoiafibYHRr8k/132",
        "nick_name": "风过了无痕",
        "user_name": "duwenjie19891029",
        "wxid": "a27917119"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/25pbZ9IWPhPcxX6w0tDRqEynw8j92PPFM8gxH7pLWTJTe7Doy6Fy9MTFeNeSzHpgeqfuJy5n7iaZUdDjz1PuzkiaemHibG2EwwHsBGvdbVlrRE/132",
        "nick_name": "爱诺堡宠物美容学院",
        "remark_name": "主114爱神丘比特\n18911520168",
        "user_name": "",
        "wxid": "wxid_74fcfk5cq9ek22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/vvYlsbWsxSL5TbiagBwia1SmNibI9gajTIHSzNMEhPDtaaTic1HBK4voSDzO6R43eBpxvrOKK0VCd6KdkjwgKjwSrQ/132",
        "nick_name": "I f",
        "remark_name": "G37小小怪16692808687",
        "user_name": "ZMY555321",
        "wxid": "wxid_lh60xjdjd2ax22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/VhHKQ3XlDfr9icJEicPe5dicqs2lmJ5Sg2Ua2OBlMzFu5DibEALjffoudsics7AXLmd0Vk0v565RHU75B3TibnlaQ89qfI0vP9nZsjHtQm8FNEJnQ/132",
        "nick_name": "Mr.xu",
        "remark_name": "主122镖局老兵18691710069",
        "user_name": "",
        "wxid": "xupeng_89"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/dsG86ribnjgGKSia2ViblkDEWcrpgYCcdDCAVOXnnX1MwQt7o7IxP58sluUpIJDIVmYYNyUKLsKJ9DabNVyzSwlwA/132",
        "nick_name": "闲云野鹤",
        "remark_name": "主121招展保罗18921131105",
        "user_name": "",
        "wxid": "mqy3224157"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/J1e0HdN8qmR1Ku93HJwTwylV8rpPqtpzQnoQRia3mr7GJ2dxumTHHxoLbb8nRBHG8d92PjmTUobXhicPuyf8Uibib9buiaBaGqzeXNaqibD6RK2Jk/132",
        "nick_name": "万能的蓝胖纸~",
        "remark_name": "G27-\n800匹飞度\n17600212127",
        "user_name": "Lambor_D_95",
        "wxid": "wxid_zr5t3e6tkpyq21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/syYppGYlhu4wWR4EvTK7cwAC6MCjzns4VzKy0FYnQJUgXV8hRBuibvh4G45WhjRBbwY6WynSU1EkGEK4miaZJsAg/132",
        "nick_name": "andy-liu",
        "remark_name": "A77俊闪雷鸣",
        "user_name": "eidsonomi",
        "wxid": "andy860928"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/7jAictXNrep6hjgaBe26TfM2a7qukkn1CjO6jfbURvX4DSskaR6tzcuBqicVGLcw5ib65IvQoOZgooePeYItdFpN41UpcrJZnLibXuQevbUOWVA/132",
        "nick_name": "Zec泽",
        "remark_name": "A86精神马乔里15016564043",
        "user_name": "",
        "wxid": "zjfank001"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/0mRk5GtObZNxsHPJBcx1xb4wFggTjicJxWf3fUIzbtvpuCibibL2HtSqErIyTpzmoYIeq2ibnNKFOqqdibV4AWqoI1A/132",
        "nick_name": "用心珍惜",
        "remark_name": "G19小帅YY17602161235",
        "user_name": "",
        "wxid": "ssh584690245"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/bofA1vl6EUZ60CVu9nSCVXWBlYFvWhJWM3GWJyaljXDPt0C9UicmF6g/132",
        "nick_name": "瀚",
        "remark_name": "B59張大俠13628351091",
        "user_name": "qaz750331plm",
        "wxid": "wxid_lx45m1bs68so12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/NE9L9xj4MUqm4XmDBn2YHIUwtzhrIBgmibCAL0KTKaUUQz1o2YpbrMn8qywfqvWZFvdoloD6tiaMlHSamyfSHmxPVx9QWU7PGnHaNZBLAEkCA/132",
        "nick_name": "体面",
        "remark_name": "主122区静静的爱静",
        "user_name": "SL2486348352",
        "wxid": "wxid_k5m4hv89dawl22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/6AQUiaN6Atlb25MQe7HS23GI5ic3cK0Pias5fW79Jiah03VBXOicAdHYIMNpjibiadic0j4fbaLy5EH9QdKMdabo2HG5xfDwR6Qorr3cPkZNjFTGicQI/132",
        "nick_name": "薛定谔的猫",
        "user_name": "zm123jhg",
        "wxid": "wxid_e2gqxrk3x69722"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/sSqY1Zdia9ia2y9VBh2o2HpG9znjxc3RbjnH79UhWjnW6RecHADLibk4MpcXwTcWwhps91tSGDIib5BlZ7BGgQdG6XnxMdUrNJ0KzTmMAibxRIOs/132",
        "nick_name": "pə\'dɛstrɪən",
        "remark_name": "主142服不说话的小鸟\n13798206641",
        "user_name": "FKF0717",
        "wxid": "wxid_9evgg8uyg6z322"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/9iayCu3TBWCcRRicDEYUbwy3jY3DXVpFwgsE6fgI4icibNWnmKNAXeOlh6icpQoChgFKR6x6EVO9ib74ngVbs1e4rZDrLK26Ex6D60udCgNRGkd2U/132",
        "nick_name": "Simba\'s mufasa",
        "remark_name": "主126玉米玉米玉米",
        "user_name": "",
        "wxid": "jason_baijiansong"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/OmwuHeVw0ypLiaibmeeje3PpTu4Re4qdp9kLosOq0cXNblC1dic9BD20n89dK0azaKy7Nnziav0guYY1jTibjwd0odm4mdSNEXtLqAmTqYPKGOP4/132",
        "nick_name": "🐈",
        "remark_name": "G42\nMega猫粮17758274959",
        "user_name": "ddddddy3",
        "wxid": "wxid_b3jbxeliql2212"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/rI17dnrbYDvUcQibr1lrkXWymUNiaBqjlmancpiamPcL2ibia9EYomurSBgpsFtt3qYibrq5YLFNYeuaFGrnd5aIUN3zlNG2RLOHq7icGlibM1QfCGQ/132",
        "nick_name": "Cloud",
        "remark_name": "主157Cloud",
        "user_name": "",
        "wxid": "wxid_a99hs18nxcbf21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/A87MzBx2VnPGTnnicXicbHUIuaE0JUWAob7wVnLgibialRABcicFBQrwfuNXQgAicD0Nqkiaicqp1gtfVY8tJx2dxmblpsQI7Ymgc9B8tuokxFDEAiaU/132",
        "nick_name": "C C",
        "remark_name": "G39苍白斯特朗17691134222",
        "user_name": "",
        "wxid": "guowenchaochao"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Swh578DdmFt0PICwGVviaG07hAd1pRA0Bm7uKUsEbwCM1iaB5bskv6nSMrJoclJIBibJF9eAQ3TibkjqV4DfUmQFfvyTJ2d19x5j6D3OV0arKx8/132",
        "nick_name": "麦冬",
        "remark_name": "A75章口就莱\n15521325834",
        "user_name": "HM_SYSU",
        "wxid": "wxid_7s3qh5ka5dpj22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/njgBSpMQIoSGRogg2WOQ0wQPib7q6mS3vEQ0PhJqibr0AM4FkeJud8eJWXria2cgP2icA8O4NbuJjMDeNkSx9RONfJGyQerbnO3cY5gqjHSibyGk/132",
        "nick_name": "张桦挺",
        "remark_name": "B41嘟嘟最可爱18321298264",
        "user_name": "",
        "wxid": "zht1991218"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/RPVMSo08N9DSuubJYOjPVUMJm28nh41et7MWeh6AHZTibNs3yibHAwYw8Ln690fKaZYOhRFu9QmNOFQ3R7xfBRhpdskdtIicufxlwoeNOuYNKU/132",
        "nick_name": "头大的不得了",
        "user_name": "zwj13738049975",
        "wxid": "wxid_k9necpoddf6922"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/RvRUs7WibXARDQz5sE9hrwiaBkEUs8WTtCcc6Uv75PxIiaNAGHbtlzeoFW10fLPZe8QQupOkjibx8BpCwvKImQyh8KISVicQUm9LBn45tWoJ3fEI/132",
        "nick_name": "joy",
        "remark_name": "主103joy18585037524",
        "user_name": "",
        "wxid": "joy890806"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/AX1gjibfC3unobiajicdHjEy911FjsOm2JwOSaIe6icp1qJIvCjCiaYx7C6b5UOSw52uLnVv1m4icfOfWbfGWXLFyMdh4jfUSGiaiaablZE9fbyU0Xg/132",
        "nick_name": "兔逼喃波腕",
        "remark_name": "主148唐僧爱蹦迪",
        "user_name": "q997036896",
        "wxid": "wxid_khgj8k56uw9321"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/2YwZ0D7eDqCbsdszxJ8SE2hiaGTwLFPS7HMTKBib0dW1Qic1YFd5BOhcwCuHX7LwnZDX26VyviaNyvSeVjAVWLNf4pibPrmyR0d1yguLCWAS9TW4/132",
        "nick_name": "鬼鬼",
        "user_name": "QQ22839911",
        "wxid": "wxid_kkrfsd6ih60722"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/7RiblwlwdqKIjPDKIy1ybFZEzAARroicMrFCTicVJjYq51fYD13rRzsaM8ygwMOq2NicvrWEDDNNFNAbAmZRAIPiciackM0ib859ibD1jG7ia9tY31Nc/132",
        "nick_name": "°旧非",
        "remark_name": "A99可爱的豆子15862954352",
        "user_name": "",
        "wxid": "wxid_qx65my1w1lgz12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/H6XxegI7MniaNUXXp70bGgibRIiblO9fulJLtB7DRIAytGhgnnWFpsYZcU3WWZhA6m55Hy0zXWHLDPnLDt71pGib4ZXiczImVZCGwnxkej7X7iapw/132",
        "nick_name": "加载中...",
        "remark_name": "G30丧气迈耶尔17326082906",
        "user_name": "",
        "wxid": "wxid_sm86440bny9022"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/g0NoGK6m4Vwh0zbGmptW6gfQ1hsUIkpFDY0fIjSDY7FhjWqDp0JXdrDu26cjy1GG3LquOkBr8gXDpSaoyfCQ40tmqrbCuCsfJmlibv5FW8d0/132",
        "nick_name": "那些.故事_",
        "remark_name": "主131火图腾13881583740",
        "user_name": "laok___80_MsMg",
        "wxid": "m1156621507"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Jv9xEaQh9r9YG52oQG8RbRQpPWiccowpCtF53RcicUrbkQYyaO8UyArYOEC5jAym7fw0WwKV40XwSvELOCUA5ibGZcha8pBTvicMqRcmiaSnPd84/132",
        "nick_name": "王志浩",
        "remark_name": "A65振奋托德15707519227",
        "user_name": "W_ZHao401",
        "wxid": "wxid_wr3qp5bgo8pt21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/WibBFiaZ1bzrNrf7By5oNAaqVaoc7NJskMw8hBibUvjlWvxpFcBCwIGwQdLrK8KRpNiaEF7DkZGqlGcL8KAmKgVw6DSoKTY9lkkvG4O7cicphJ0g/132",
        "nick_name": "帅",
        "remark_name": "B40小猪猪18210262290",
        "user_name": "liushuai890922",
        "wxid": "wxid_ybvkkboaq1qw12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/XHj7YlULc6p8xInoWmGANjSGS3fzylbn28pcMXhoXolYGHyXpIia0ZW0ZibEFOXQHIrSJeYfT5XbkiaveLmrub8GuMen0xBo1Ikic579C9NgdDA/132",
        "nick_name": "浮游",
        "remark_name": "A69千手修罗唐三",
        "user_name": "bbhaii1314520",
        "wxid": "wxid_6yzu9wvie8vc22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ZPMe0yS0lhyZMefRPSxuDeCdEj0aV7JOqzHiaLfPBqSwSHZtofwiaPhzGqE1J9gbsiaiaLCBzZmmgnYRFgLRRBD5M92NTQtjJTjMSoIUt18fImU/132",
        "nick_name": "钱广胜",
        "remark_name": "G36小胜神13701387578",
        "user_name": "s94424x",
        "wxid": "sheng1992112"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/0GMnbGqjxCibWVIzTG5a1ARdu8Xh2RfKcdStaHToArIRy9pPr2uwLnXCKPiaZ8ib21ppxMLJQ2VXc0WvvTK4UVwu4DgtpYpKwKI8uOzW99D5MQ/132",
        "nick_name": "依然丶不二",
        "remark_name": "B39依然不二",
        "user_name": "wsjqwhwzjrssxx_",
        "wxid": "jsy8088"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/XEGVOiaprrKpNSDQ1xqX6vyCEECszwC4hULAhngQqzRRicgaRNhF5fNMYqfTqmKsw1MuQ0aEOd6Zadl5VibCK8OYg/132",
        "nick_name": "赵先生",
        "remark_name": "B55赵先生18750909899",
        "user_name": "zhaoxiansheng985",
        "wxid": "wxid_puctr2e87t4411"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ZHGs6NiaXHNdyvcB1AF97p6ZJ1cTj8XjosoxJxGm8Xnf2EFB3F4DcRDJyDwxWRu9V8ebH2CaRZPwb3y0ldIIM8CPxbzA64FkwBicx8q7O60jM/132",
        "nick_name": "💜 VIP Rex 💜",
        "remark_name": "B56佐佐木小次郎18083777708",
        "user_name": "Rex_wang0624",
        "wxid": "wxid_mgwr09skqq1j22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/FYCAw71mkI5EdvgOvrpIjdvPQEeC2jdu1oocA7T1P27CkcLib9SAmLXossfgAzfZ9lrmibJ7RC9jia4JLpiapxF8V6bPPy0bNdKKiaDRSjC21gEA/132",
        "nick_name": "不高兴的没头脑",
        "remark_name": "主119神域之芒17710291190",
        "user_name": "wx344921951",
        "wxid": "wxid_ximc08pawvwo21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/lvLU3Am3UgbWheJnq1x5PI6CossNpSTP4XUxyOtyOb9WvuorJbWXtdZ2jvARMCUJZfXwItlEFocIPSeWX6yXd2V3UDCCWNHMIricwAn4nyUM/132",
        "nick_name": "叶振荣",
        "remark_name": "主157曾几何时18105035560",
        "user_name": "ye88134010",
        "wxid": "wxid_chffir76nrx721"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/jIIaeSNLQIumW6K1a41yNdtFL725RvYibuVKFNsJCmdZedw6fmxnokFnCrAibXa2z173MpJa44YRAYqRlIgat45w/132",
        "nick_name": "呵。buy",
        "remark_name": "A83是姽婳啊15006190844",
        "user_name": "sykdwx820",
        "wxid": "q250950001"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/OsJYryOnlMrnia9sNtnUm3MAhx1aFCdb08ESz5NwvsSk9tSEyhcCicS7LLh24c3BASH4e0Cx7QHENenGyOnlvD8A/132",
        "nick_name": "Leon蜀黍",
        "remark_name": "A99Leon蜀黍18610796910",
        "user_name": "",
        "wxid": "wlp34344883"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/gArIv9CicoBr1icqwYXwvm5O18H84FYNM84GtvJ6YAjGGiakD77EWhL16j7Be5oxGpu64ftjZ8saNds8KIfVzXXdg/132",
        "nick_name": "808bass",
        "remark_name": "G32腼腆季洛杜17872277393",
        "user_name": "zengqi1047853549",
        "wxid": "wxid_bhss0wc4vvih22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/wkzZYuhz91RDfiarYZNVI7UkN5S3tv3o3N0ckmYGtMv02JepvYlmuyOYs46PqXiaX0X0ibTQJLlk2sEfEHYwqy1LxMBtrOnnwGwEl4KgU34w64/132",
        "nick_name": "_",
        "remark_name": "主110大猪蹄子17643145889",
        "user_name": "",
        "wxid": "wxid_yp52184cf7c511"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Z8noUHiawPNXviarQibYoDvPbVibdmZU8guJQ0bc6yF1l2OAoc79sjcJ9Nr6JBiaYfCIwWJTqhNCenHzQG3NTEomAzl7yoYLdYdc8icQKPrqMsicNs/132",
        "nick_name": "ah1ng",
        "remark_name": "G29疾风剑豪18679754075",
        "user_name": "xjh18679754075",
        "wxid": "wxid_wclugkuxsonk12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/FCE4VVo0TN4S4h4vSCJiavg1Vib2Aia3JLxUvhicEuvhI34Dxo3d22ViaQibQe9HEKVQRx4V5vicoKDjBba7bJuAC2fHWB6Hia76rtwFHZdGdYkXXb0/132",
        "nick_name": "Aaron🐶",
        "remark_name": "G9服汪汪小怪兽1871777033",
        "user_name": "Aaron737321712",
        "wxid": "wxid_9riummbsc19422"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/xm9d38bM7LjTLWPHot8fh3tIwibniadT65I1jy1OLdcdvV3CS18V7zB9jTBkVuIrsGlNnUtadiclY5ekP8XtwRicjYUAEkpsO2pCicYkKNJvmia1E/132",
        "nick_name": "雨泽",
        "remark_name": "主157湘思风雨中",
        "user_name": "",
        "wxid": "q709475360"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/qaxPZLgdnnhvL6gBtgibbEiabUM7YpKg6Y09lFZzOc1lgiadqt6SEUI9qZ3pMVph4zH15SicUiajZfryGwa068iaKgibEM5uiaibTBQTTfNPtlNg9QIU/132",
        "nick_name": "Kk",
        "remark_name": "A103吕向圣18125138296",
        "user_name": "chenyukeng",
        "wxid": "wxid_4l9ho9pahw2l21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/RXcdQxp9rmTj9p0RDSSwbwUSQqPIQ0PLbmHoojKGsJ8BXqw7RzCOXQro24vkiaeIiaiaZeA7OID19QVicb5M9z6jcyAXEO2vWLicXibqpow1L2xXU/132",
        "nick_name": "素华流年",
        "remark_name": "G34素华流年1888866495",
        "user_name": "wu09068337",
        "wxid": "wxid_yhabcsww4joh12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/InlGaLwGmXZlB3snicKsmjfuFNoJpqUKbkYvzwgpb9bBz4aomxMibUHTGoRJazib6rSkp1bPtujJ2JcJDoqHicBia2g/132",
        "nick_name": "Je t\'aime",
        "remark_name": "B81末班破车15988837825",
        "user_name": "jiong_jiong_xia",
        "wxid": "wanghuojiong"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/uSkXDGBiaQqr6undxaY1bzLa07ZTDHiaYdkDjovIeV2FS2ia3HEmDceX0h7beSVZyKbgP4FicoVSvPNFtAQRgPWNxA/132",
        "nick_name": "卡普",
        "remark_name": "B90我超级有钱13617777388",
        "user_name": "qq394604460",
        "wxid": "qwe13960234978"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/N1PAaGJiakEtO0Aia0Z8RplfVb6YQ8TPLdWDZCqkKKuIB6DgsYWmeicIPs672LNsibnibX21WFlia6Q3nxc2Tbprsen0WOa1TGoawLhViahGYMU7OI/132",
        "nick_name": "。心无°",
        "remark_name": "A79平静鲁宾13651977346",
        "user_name": "Jabin_YC",
        "wxid": "wxid_nb12myjevjcs22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/pfGDtnoIQTwdmNiaB6Bd5Qdnv8k2sOJ1NDGGkx2jaDhjibwcgNhysUdibVfUFQOFXk9iaIRm1zmibK74ORXduNzgnWQ/132",
        "nick_name": "高炎",
        "remark_name": "G35大白兔13867475884",
        "user_name": "",
        "wxid": "gaoyan1993"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/WpB9icE2AGgqYdicdrTObibiafnibAO14G3LYicibibiaKiaCmDl9MHoJ97M2m8onwAjrVxuFBaxQGxAkiar84atMLADHa3lqqEHhTQfW6neMsDmdPnH5k/132",
        "nick_name": "♈晚风🐾",
        "remark_name": "G22\n行者晚风\n13916100341",
        "user_name": "jara0401",
        "wxid": "wxid_z7qf9wq8yluj22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/kSiaeFj92SMxs7bP5icic4obCCu9Ep5FM4Odbukfyf3pSIJr0eJibzc0xQ/132",
        "nick_name": "汤.m😐",
        "remark_name": "G30誓灬汤姆",
        "user_name": "tomxie0515",
        "wxid": "a39867689"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/lUd14W50bA0xee8bVGaLDDlWVOxemRz13kVMhaiaPSyYztJlkib24xicyFfraACqUtTiaGUNPQsicmPVcUib6FSPeXMvuv0zic130MDRF9vdPQWibcY/132",
        "nick_name": "💋三千繁华 不及一抹风华",
        "remark_name": "G24梁无极13290738800",
        "user_name": "xiaojun_882239",
        "wxid": "hehe_0914"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/DYn6Hb2BGeBoYMicKkOjYz05NSffZSxv1RTRibu22gOAeRR0qMOPdLkOPUgVUveHQkUIED5Wkof0gOL9sPGX7blcSj4SRBu3JiaNZBme4eX3aQ/132",
        "nick_name": "何",
        "remark_name": "B28蚂蚁吖嘿17689971671",
        "user_name": "haoxian225010",
        "wxid": "wxid_9416394163611"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/6FCaT4sEUA3ICzKkRxZA2rDB6icvdWiat0ZAib2AkOrwyAhE9OCzrhazUHKpeGkqGK3HIvBPAYIx6LrOtt27Kot4srSdlYgN0flbZ3kia8PBXEM/132",
        "nick_name": "⿻呱Gor°🍃",
        "remark_name": "A99\n閪閪閪18676527552",
        "user_name": "mjx19900302",
        "wxid": "wxid_0259892599022"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/qBpNY9POVogtfFFM1ziciaLlL2UPe88B66BHRhcmqIa9tjhPAqYMqLNCLzzAsOv5vp77HcUaSkV4ySian8rDiaELGKH9vQtwibv7Qge8KTPA5EZc/132",
        "nick_name": "恋炼健身工作室-王教",
        "remark_name": "B51训练师王教18898531704",
        "user_name": "wxf524309370",
        "wxid": "wxid_a26t0v29sh3521"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/hqJEx4u6IBgYpQ1WSsoDYjDKic3FQQichR1ictgGE9AV67VCLCe7f5xsEW3Gg3b7FurGXiaRiaIVCvfYhI4hTrV26buic5MWCUIsxGZJ89Kh6IeIk/132",
        "nick_name": "devil",
        "remark_name": "A72DevilQ15510005882",
        "user_name": "",
        "wxid": "wxid_828wp3eilwdo12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/gP4JM2W9x5PSIDYo9x7bXmlaQa2YOcbLbJPzxh8rSHnCgKicY5qI5SUFY4azt0UQyc7w4YibXuYuicjd1ELsAkmicRH8RUR4mIeSUvpiaFpGc7b0/132",
        "nick_name": "勿  念",
        "remark_name": "G23啵啵啵啵啵哔\n18967903727",
        "user_name": "Wyifan9077",
        "wxid": "wxid_jbc2awzh37z321"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/EOIopzib48prHiab9fKgVO8mN6VvlCib9aUhulaYo5pOhs3PAcGGFwkG0wiaiaoAIqiavWQEqfxPmnmYVLtK7H1ib22V781w0cyMYRPOjibLyoVVF4k/132",
        "nick_name": "咕叽咕叽",
        "remark_name": "A116L丶J\t13611437781",
        "user_name": "",
        "wxid": "wxid_u2rdlj6yuznb12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/9hZ7GukEg6shTiaE5ah2icS79TsjjGlywHQAOz6YNqgRsSZJvZL7eOLacCdDeWNic1STgjibDMV6RT64vI2iahJfSFQ/132",
        "nick_name": "離",
        "remark_name": "G28\n小離子\n15921235101",
        "user_name": "",
        "wxid": "wxid_7berl3m2u9il22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/RYAiaqbiajE59GCMbCc4vMzpv28nES1zhZVjNcTZbXicjBiax2fPlu2osCPWG3Fehx6t8ylBKhNUx2t3uBmvaFtxibicUgdoObR8M90CdkXncxPTo/132",
        "nick_name": "北冥有余",
        "remark_name": "A80字母君18850996613",
        "user_name": "",
        "wxid": "wxid_v93kzhto3yfj12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/XYkaSW58ziceh6L2Jvhg89pykFO4vib7Ilve5n5oESAgvibunS9KAe6jAYzHdzwGiboUd9lfkrJrhGYhb0o5ycyaYnk2iaibrtBPpeINoZHdhSDSc/132",
        "nick_name": "旧忆",
        "remark_name": "A76狗托斯玛17695655770",
        "user_name": "zhanshenzhaotiezhu",
        "wxid": "wxid_gp8qdu8n427s12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/0VXtKS9ib9GiaGYMCrhDpHdeGYzdk2GkcTkpJpKdgiaRO0Oj9m00LUVosU1bAf9foeMhyibv5yX2EC3SRunCTicuoKdsib4x26LQNsNribhAhsLVLc/132",
        "nick_name": "炎龙",
        "remark_name": "G30怪异利萨17317500098",
        "user_name": "gwh13818780820",
        "wxid": "wxid_07wxfaep2orl41"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/wic8I9XkiaLNuyfyHyIxeibTFibw9uXY2paWEIJRjzU89GDkicKjSOicN4jC1POhuJykg1GmEjlo69PsNiaIibuq3VAWmQ/132",
        "nick_name": "_Silver_Moon_",
        "remark_name": "A98Moon13683306592",
        "user_name": "_Silver_Moon_",
        "wxid": "q707731652"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/41PN7Y7nsxkebCmuZdA7ib8IguSvoViblPYfVPtEx1c4ic6X1HpD0P4dftgvPtwflQgq6oaG372HjIkbWrq7gS1icKST8xrLbqFgKPxaAUYx4GY/132",
        "nick_name": "😳",
        "user_name": "",
        "wxid": "wxid_4ehw28bbgb6w22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/TRd6tEJpfVcAzyAlsicnWWWCwz5WOqUyw8Vicn1dOOWQ50ODII2p5xVGLNdHHlTyFlPzCicictfEXaaabSH0bfoQxg/132",
        "nick_name": "L$$",
        "user_name": "",
        "wxid": "wxid_k6v8mdgtu7aq11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/5z0fhSN0iaJCSzhfNjwW76fekLZpu6X5icjeicUVBYS28qkV5QJeRnOJIzhxud0vgemVib5iaKQpt2QJia7D4pVSCxickQib7VFWiaVdfXuZT6icm9E4Q/132",
        "nick_name": "那个疯狂的人是我",
        "remark_name": "A97就决定是你了15250086450",
        "user_name": "QYFstcllll",
        "wxid": "wxid_6hlheq6z4kea12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/SIKyLt3uNLBjoVCoyRWakCf7vHcbHiarJbxYcgpDXHN8dLUWsuw7hBianANbOxPvLCyL9Y4eQ9sicsZdJJyZm88Dw/132",
        "nick_name": "乐子",
        "remark_name": "主147\n失神卡内基\n18553116886",
        "user_name": "Haoleforever",
        "wxid": "wxid_6034920341912"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/cngXYgYOy3FQkDe27sVjjN76g7k7POMMXA1yia4PdMvVjiaP2lRqR69pR1EclqeNDsRqaMXEPKAXWicppRicmEF1PyibIds0ZLtFEibT4sVGnkBYI/132",
        "nick_name": "尐",
        "remark_name": "G40皇灬爵\n18217746621",
        "user_name": "qq951012037",
        "wxid": "wxid_96edckowf02i22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/6YzUofUvUdnKXm27yXiawCdHxUbsks611XeyWvCYrUBrTLmPAEYY7SJsBtcFzaovJWcrGxAy5fLcO5UEdFiculPGtZMActAYybiaicjZN0M7Qic4/132",
        "nick_name": "李旭",
        "remark_name": "主144冒险本达",
        "user_name": "",
        "wxid": "zzjjlixu"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ibTBYAk5y0ZUUCeYUnODMKJOc196IibqsrdP1dXBtKibiaZq3alcichE4fKnAUITG9w2on64TBNdvTiaLflmEpYtK20e52QxLKm6uJZYhy5icCcvb0/132",
        "nick_name": "☆ぷ煜♪℡",
        "remark_name": "A92龍战于野15902097800",
        "user_name": "yu_10_10",
        "wxid": "hongyuyu1010"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/oQQYfKJSyEW2Cw9gY5km7Lm4aB2O1rBQrmTSNiaCabOsUHj5LkKibOVhHABkn9mG0tCWrbuD3T0WbNZJV2m3qFMQ/132",
        "nick_name": "向阳",
        "remark_name": "B50虎背爱德华13767105005",
        "user_name": "x_iangy_ang",
        "wxid": "xy26022033"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/pCTiboYFfFqRu4CTk3tJC40zmytXT9WIiaIX5PUAkzdaXxjfpHo48LwdDKrFBUMt8MznLmtfd1sia7Ts8nmIHBAicf0mHxVNn5SI6ByNUXCcRXs/132",
        "nick_name": "陈雅舟",
        "user_name": "",
        "wxid": "wxid_p96iw95oa0j121"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/bls6TrWy5ADYeYLoY1oBP8NOqUpgq3QDd9Pia78ibOHtv9jnnxPttXUvGppTuKUJ8iaAXNlOxGsJxXMMAF9d2GTEPQx2CickpQEeJPcUPxo7o9U/132",
        "nick_name": "喵星人",
        "remark_name": "A98又大又长13718059899",
        "user_name": "",
        "wxid": "ws563633764"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/9r8OUcicp6FK4UUGZ7S6AqEVxrSbXY7UPlcx4SbMCC1psrpvonmovCWA8YfX2pMhs75hOnPBPm81hpiagemEMf7w/132",
        "nick_name": "李冬",
        "remark_name": "G33Ss丶毒葯13911415223",
        "user_name": "",
        "wxid": "qq157402305"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/GibvHudxmlJYeTmet5KozdL22jW8h10YIdjZ6f2qxy6Td4ibGOpa81dQ/132",
        "nick_name": "Terence Wong",
        "user_name": "hjhob33806903",
        "wxid": "wxid_nkimmywzho9u22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/hrbgv7QYDJfbSPTthhtYT9xyVFUMXlFEibyHq8kKSeHADtKulfQQIUE23qqMlnJLqI381hCpajCjibbvibWBtqOlQ/132",
        "nick_name": "财迷",
        "remark_name": "G26魅力反派人物13818920237",
        "user_name": "",
        "wxid": "liuyuxiao459794"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/6CIiaxwe9ZCsQ5DMcH7HN8CetbfUSh04ypsmwZ0XHgWHaYKtQbIrPlvxkD2V67GwJNDlUfTA33yCGf90mSA8ibYqk7pVjRI4ns9q5o8zibib5sQ/132",
        "nick_name": "喜欢走走",
        "remark_name": "主132风发迪夫18533133863",
        "user_name": "zihao20121019",
        "wxid": "panfeng969995990"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/KzbR7j11Mb5xQyFjWTRgkpIkjL2FibIr0N8aM8sTiajWB7nIn4wnyt8k3NBJDfcybcZjGBbnXyiclFoLSdf6bcORQ/132",
        "nick_name": "miss",
        "user_name": "xv994004",
        "wxid": "oneboyintheword"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/JMk1lozclmAUT17iaXleMIm7EdZ3nHKia78iaOFYgmZgBZ7hyKd9nL3KoJBePtssiayVr5OQiaW2L5qNW4KgO0daHicCqTicwQWVg73Ug36IWHJCOk/132",
        "nick_name": "星夜听风",
        "remark_name": "B27服星夜听风1362255662",
        "user_name": "xuefeng13622556625",
        "wxid": "wxid_1280pppasg7821"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/Vy5GYLreItqjt0XLqibC3IoNlET4q7YaKKhnVvAtQkiaaO7zIxTEekoN1GIemQkRbF4EdDib47Iy507A1Grl4oCicQ/132",
        "nick_name": "小白🐾",
        "remark_name": "G24无极汰那\n15376175698",
        "user_name": "judy29thd03",
        "wxid": "wxid_vustovpkhl6t22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/gPO2hMgAIIDaibbicy5icQibOG3QXgeeNHYbjrsia68sRqYTlPN1l1iaOuEYoNbUsrR66KMmhKmW9gqxUIoszWng85xg/132",
        "nick_name": "&皮皮哥🇰🇷回国发货中🎁",
        "remark_name": "主179铛铛宝贝13336173510",
        "user_name": "",
        "wxid": "kuang070770"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/VFf11BibwJooHVyDib3WyXicDAsox2eibSTsCPZtDcepJibUaRmjVEIYuWnoPv3WMNlsc9Nk1NGNic4kVu647vkibKdymFicsib02euEAQJokMiaNd8X0/132",
        "nick_name": "money🦍",
        "remark_name": "A86傲慢的小七13764202293",
        "user_name": "maomaokaka1984",
        "wxid": "wxid_pffhxw1z4bat11"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/2ePlqKehUTjNU2AXMXticLYf0Q8TKAqGOjjKsb72PbXn2WqYR4icic0fdYG5c6JglSm6gzLSUtE9mGR2SNLN1xlaQtfh2xib8Sx1LyJj1khuzAU/132",
        "nick_name": "张斌",
        "remark_name": "主102戰、老张15901971729",
        "user_name": "zhangb21",
        "wxid": "jakeson56781"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/BB0aydwxKbX4F1v6qyP98ib5A8ZOaZmjPuGWfibMtUpA6nUnGsYiaMhMHPlCCYhhuibsq7iaic1hUKsPrSBpfnAa8R01SXFxib63FCHJev3aic4eUT4/132",
        "nick_name": "归属",
        "remark_name": "G18诸神黄昏18627151399",
        "user_name": "",
        "wxid": "belongto2010"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ib8FBoSL3PqWriaDDU5cN357KvMswOXicmcibJqVcVkiaoRRoKP6X7jO4axP3u1YoIBT9adicYwibrEsln1pQ0CKs38IUyuaKn2Uc9uicAhtqAxlYWY/132",
        "nick_name": "刘峰",
        "remark_name": "主140有神瑞利13331075900",
        "user_name": "",
        "wxid": "dinozzo904820"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/ywtMohAgGM0kMictr7F7IHPc8dEVQasVxtQVu41dz2wwQTNnNJhHFNsJ7zA4wGLVsrd7xpBNZTNqicnEKelAdFicZibgZzlvewzykP4VhmfqlbA/132",
        "nick_name": "白绍言",
        "remark_name": "A72夜白如雪15010091208",
        "user_name": "baiqiang19840517",
        "wxid": "wxid_mu1bsp2aazh121"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/BBVWicNfVQVWG27wVq3hpicAUeiaoWGnAOsdSMfc9t3jwG2zY4P5HRByM3QRZ552AxHCYNKcZ6MfQK3JmWK1cXNibOA1nbLVPD3FOE5LKwypf1g/132",
        "nick_name": "(๑•̀ㅂ•́)و✧ 锋",
        "remark_name": "主126随风听雨1581272463",
        "user_name": "",
        "wxid": "situ0020108585"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/cXBIg5cD54ic6PSO7j6KrwrvBrbrXyFnCTpTgtfTB4c3Rp9VH6pdx7qdOb7lKqj0RWKdIl2crvpBib4wSVkic14Z8iaMcesicyOA2iaJS57zbicT8o/132",
        "nick_name": "德延",
        "remark_name": "B13狗托全家祭天",
        "user_name": "haha3454673",
        "wxid": "wxid_o4l66g0yhuod12"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/DX8OVvIp50epxmpzI9xVufxHN4qlSGr6ic3O1sVxYyOFiaeTADicyhZ7yWicUscMYvmphA5ICx8TcTnBzVWzGjFkQCzKxHkFqtcJxduLBkSiac9g/132",
        "nick_name": "Best super",
        "remark_name": "A95周杰伦17694923425",
        "user_name": "bsp492555160",
        "wxid": "wxid_yb8btlhnv2pn21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/RnrRJeyPgAhfLbqjqJfic7YChAFmWbAAiauQhqzGgF4CexZFvNMgVnNce3aib57GzlKhIASIfydDicmJuBohOrKCWdibszhbt5xGfKCmKze1j4E4/132",
        "nick_name": "淼淼兮",
        "remark_name": "主106清新培迪15924178628",
        "user_name": "",
        "wxid": "wxid_rutj5uy81nf022"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/dElpzBhDccbVkaHoLtwSgRVM1O8maC9QaazsdT1wlKYdibtGpJuLwECqaKLKhX9ucYlicdwa6kpgoia3qEiagHibOPd91NmZLYicljo3YahMPIRFA/132",
        "nick_name": "H£Mr黄",
        "remark_name": "主135不死泰伦13602367727",
        "user_name": "",
        "wxid": "SAY-LIKE-"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/36fDmpey7BwUQJkj00NCvnCibZEXndFWZO7MSujrghKKWKIqjHiaXRib5yfiad1qFfI2qnKTHEG61xHJNT2Uchia8IPUmpQMVnmia0RbXcvqbKEQM/132",
        "nick_name": "安逸流云",
        "remark_name": "主128服安逸流云\n15878719862",
        "user_name": "",
        "wxid": "zhongyuming1990"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/OHZxuWvtUT1DxwsT3UlFc7bb1ibI9XotaibfiaP44E1KuKEPWqLVCI0Wkf3icO4bB9rmttwOAfSsxBvnx9HIW8mDicgq7DRnuKgNAV2OVLicn1PzA/132",
        "nick_name": "momo",
        "user_name": "OldWangxye",
        "wxid": "wxid_6auz8kmeynwi21"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/cWV1pJ4LibNdb2oOmVor1qRspF7sQ7Pj6pdSia0OUMLojayYsPFcRO8libIcZJCRJ76s9IT9Dq2REvP2ibnn7iceaicTTecLr1pGfAdQTe0V1MRDo/132",
        "nick_name": "朱开云",
        "remark_name": "主118慈溪奥拓13858348698",
        "user_name": "zhu13858348698",
        "wxid": "wxid_2kxxgcek8zlc22"
    },
    {
        "head_img": "http://wx.qlogo.cn/mmhead/ver_1/fQDmacSVGrsWJ94QwicIS2AFzAd7ehgPZUV0Q5bcd6X20yHdNWiavSWqepaALsY4P4xDYk7cwAFeI43Arpbr0bz5RicRxpDbArPICqp4dYQJUk/132",
        "nick_name": "-清风ゞ.",
        "remark_name": "B71丶清风ゞ13808720918",
        "user_name": "",
        "wxid": "xuji369"
    }
]';
        $n_data = json_decode($data,true);
        foreach ($n_data as $k=>$v){
            if($v['remark_name']){
                $content = strlen(str_replace(' ', '', $v['remark_name']));
                $get_iphone = substr($v['remark_name'],$content-11,$content);
                if(substr($get_iphone,0,1)=='1'){
                    $iphone = $get_iphone;
                }else{
                    $iphone = ' ';
                }
            }
            $remark_name = substr($v['remark_name'],0,$content-11);
            $arr[$k] = array(
                'iphone'=>$iphone,
                'name'=>$v['nick_name']?$v['nick_name']:' ',
                'remark_name'=>$remark_name?$remark_name:' ',
                'wxid'=>$v['wxid']
            );
        }
    self::test_excl($arr);
    }

    //导出用户充值金额Excel
    public function test_excl($data){
        // require_once  LIB_PATH . 'Org/Util/PHPExcel/PHPExcel.php';
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Worksheet.Drawing");
        import("Org.Util.PHPExcel.Writer.Excel2007");
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);//宽度

        $objActSheet->setCellValue('A1', '微信名');
        $objActSheet->setCellValue('B1', '备注名');
        $objActSheet->setCellValue('C1', '手机号');
        $objActSheet->setCellValue('D1', '微信号');
        foreach($data as $k=>$val){
            $k +=2;
            $objActSheet->setCellValue('A'.$k, $val['name']);
            $objActSheet->setCellValue('B'.$k, $val['remark_name']);
            $objActSheet->setCellValue('C'.$k, $val['iphone']);
            $objActSheet->setCellValue('D'.$k, $val['wxid']);
        }
        $fileName = '吔屎啦你';
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

    //导出用户充值金额Excel
    public function query_gameorder_excel1($data){
        // require_once  LIB_PATH . 'Org/Util/PHPExcel/PHPExcel.php';
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Worksheet.Drawing");
        import("Org.Util.PHPExcel.Writer.Excel2007");
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);//宽度

        $objActSheet->setCellValue('A1', '产品');
        $objActSheet->setCellValue('B1', '注册数');
        $objActSheet->setCellValue('C1', '创角数');
        foreach($data as $k=>$val){
            $k +=2;
            $objActSheet->setCellValue('A'.$k, '小精灵宝可萌新版');
            $objActSheet->setCellValue('B'.$k, $val['new_user_count']);
            $objActSheet->setCellValue('C'.$k, $val['new_user_role']);
        }
        $fileName = '蜜桃成熟时';
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

}