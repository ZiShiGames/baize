<?php
namespace Home\Controller;
use Think\Controller;
class H5DataController extends CommonController {

    public function base_data(){

        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
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


            $Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
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
            foreach ($page_where as $v){

                $parmas=array(
                    'start_time'=>$v['start_time'],
                    'end_time'=>$v['end_time'],
                    'product'=>$v['channel_name']?$_GET['channel'][0]:$_GET['product'],
                    'channel'=>$v['channel'],
                    'name'=>$v['channel_name']?$v['channel_name']:'game_id',
                    'father_name'=>$v['game_father_name']?$v['game_father_name']:'game_father_id',
                );

                $DATA=self::combination_data($parmas);
                $DATA['time']=$parmas['start_time'];

                $data[]=$DATA;
            }
            // print_r($Page->totalRows);die;
            // for ($i=0; $i < count($data)/2 ; $i++) {
            //     if($data[$i]['渠道']==$_GET['cpscode'][$i])
            // }

            $data_sum=self::base_data_sum($data);
            $data=array_slice($data,$Page->firstRow,$Page->listRows);
            array_unshift($data,$data_sum);

            self::assign('page',$page_show);
            self::assign('data',$data);
        }

        $product_list=M('game_father','','DB_CONFIG1')->cache(500)->getField('game_father_id,game_father_name');

        self::assign('product_list',$product_list);
        self::display();
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

                $DATA=self::combination_data($parmas);
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
        // require_once  LIB_PATH . 'Org/Util/PHPExcel/PHPExcel.php';
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
            $objActSheet->setCellValue('A'.$k, $val['start_time']);
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

    private function  base_data_sum($parma){
        $data= array(
            'time'=>'汇总',
            '渠道'=>'-',
            'product'=>'-',
            '新增注册'=>array_sum(array_column($parma, '新增注册')),
            '新增创角'=>array_sum(array_column($parma, '新增创角')),
            '总登陆用户'=>array_sum(array_column($parma, '总登陆用户')),
            '老用户登陆'=>array_sum(array_column($parma, '老用户登陆')),
            '迁移用户登陆'=>array_sum(array_column($parma, '迁移用户登陆')),
            '迁移用户总数'=>'-',
            '迁移用户付费金额'=>array_sum(array_column($parma, '迁移用户付费金额')),
            '迁移付费人数'=>array_sum(array_column($parma, '迁移付费人数')),
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
    private  function sum_combination_data($parmas){

    }

    private function  combination_data($parmas){
        // print_r($parmas);die;
        $new_user_count_where=array(
            $parmas['father_name']=>array('eq',$parmas['product']),
            $parmas['name']=>array('eq',$parmas['channel']),
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['end_time'].' +1 day')),'and')
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
                    // 'login_type'=>array('neq','1'),
                    'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['end_time'].' +1 day')),'and')
                ))
            //->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //活跃用户数


        $new_user_role=M('create_role_report','','DB_CONFIG1')
            ->where(array(
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['end_time'].' +1 day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
//             ->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //新增创角色数


        $old_user_count=M('login_report','','DB_CONFIG1')
            ->where(array(
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
//                'login_type'=>array('eq',2),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['end_time'].' +1 day')),'and'),
                'user_id'=>array('exp','not in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
//             ->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //老用户登录数

        $qianyi_user_login_count=M('login_report','','DB_CONFIG1')
            ->where(array(
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'login_type'=>array('eq','1'),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['end_time'].' +1 day')),'and'),
            ))
            ->cache(500)
            ->count('DISTINCT user_id'); //迁移用户登录数

        $qianyi_user_order= M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                'goto_game'=>array('eq','1'),
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['end_time'].' +1 day')),'and'),
//                'user_id'=>array('exp','not in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
            ->cache(500)
            ->field('sum(money)as new_user_pay,count(DISTINCT user_id) as new_user_pay_count')
            ->find(); //迁移用户付费金额，迁移用户付费人数

        $qianyi_user_new_count = M('user','','DB_CONFIG1')
            ->where(array(
                $parmas['name']=>array('eq',$parmas['channel']),
                'unionid'=>array('exp',"is not null"),
            ))
            ->cache(500)
            ->count('DISTINCT user_id');//迁移用户总数

        $new_user_order= M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
//                'goto_game'=>array('eq',2),
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['end_time'].' +1 day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->field('sum(money)as new_user_pay,count(DISTINCT user_id) as new_user_pay_count')
            ->find(); //新增付费金额，新增付费人数


        $old_user_order= M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
//                'goto_game'=>array('eq',2),
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['name']=>array('eq',$parmas['channel']),
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['end_time'].' +1 day')),'and'),
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
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['end_time'].' +1 day')),'and'),
            ))
            ->cache(500)
            ->field('sum(money)as user_pay,count(DISTINCT user_id) as user_pay_count')
            ->find(); //付费金额,付费人数$order['user_pay']=$order['user_pay']/100;


        // 上面方法到时候可模型化

        $new_user_order['new_user_pay']=$new_user_order['new_user_pay']/100;
        $qianyi_user_order['new_user_pay']=$qianyi_user_order['new_user_pay']/100;
        $old_user_order['old_user_pay']=$old_user_order['old_user_pay']/100;
        $order['user_pay']=$order['user_pay']/100;
        if ($parmas['name']=='channel') {
            $channel_name['channel_name'] = $parmas['channel'];
            // $data = array(
            //     'new_user_count'=>$new_user_count,//新增用户数
            //     'active_user_count'=>$active_user_count,//活跃用户数
            //     'new_user_role'=>$new_user_role,//新增创角
            //     'old_user_count'=>$old_user_count,//老用户登录数
            //     'new_user_order'=>$new_user_order,//新增付费金额，新增付费人数
            //     'old_user_order'=>$old_user_order,//老用户付费金额，老用户付费人数
            //     'order'=>$order,//付费金额,付费人数
            //     'channel_name'=>$channel_name['channel_name'],//渠道参数
            // );
        }else{
            $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$parmas['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();

        }

        $data=array(
            '渠道'=>$channel_name['channel_name'],
            '迁移用户总数'=>$qianyi_user_new_count?sprintf("%.0f",$qianyi_user_new_count):"-",
            '迁移用户付费金额'=>$qianyi_user_order['new_user_pay']?sprintf("%.2f",$qianyi_user_order['new_user_pay']):"-",
            '迁移付费人数'=>$qianyi_user_order['new_user_pay_count'],
            '迁移用户登录'=>$qianyi_user_login_count?sprintf("%.0f",$qianyi_user_login_count):"-",
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
        $channel_list=M('game','','DB_CONFIG1')->where(array('game_father_id'=>array('eq',$_POST['id'])))->cache(500)->getField('game_id,game_name,platform');
        // M()->getField($field)
        self::success($channel_list);
    }

    public function get_h5_cpscode(){

        // if($_POST['code'][0]){
        //     $cps_list=M('login_report','','DB_CONFIG1')->where(array('game_id'=>array('eq',$_POST['code'][0]),'platform'=>'wxminigame'))->group('channel')->cache(500)->getField('channel',true);
        //     foreach ($cps_list as $key => $value) {
        //         $channel = M('game_channel','','DB_CONFIG1')->where(array('game_id'=>$_POST['code'][0],'platform'=>'wxminigame','channel'=>$value))->find();
        //         if(!$channel){
        //             $data = array(
        //                 'game_id'=>$_POST['code'][0],
        //                 'platform'=>'wxminigame',
        //                 'channel'=>$value,
        //             );
        //             M('game_channel','','DB_CONFIG1')->add($data);
        //         }
        //     }
        // }
        $cps_list=M('game_channel','','DB_CONFIG1')->where(array('game_id'=>array('eq',$_POST['code'][0]),'platform'=>'wxminigame'))->group('channel')->cache(500)->getField('channel',true);

        self::success($cps_list);
    }

    public function get_h5_cpscode_new(){
        // if(S('cps_list_'.$_POST['code'])){
        //     $cps_list = S('cps_list_'.$_POST['code']);
        // }else{
        //     $cps_list=M('login_report','','DB_CONFIG1')->where(array('game_id'=>array('eq',$_POST['code']),'platform'=>'wxminigame'))->group('channel')->cache(500)->getField('channel',true);
        //     S('cps_list_'.$_POST['code'],$cps_list,86400);
        // }
        // M()->getField($field)
        $cps_list=M('game_channel','','DB_CONFIG1')->where(array('game_id'=>array('eq',$_POST['code']),'platform'=>'wxminigame'))->group('channel')->cache(500)->getField('channel',true);

        self::success($cps_list);
    }

    public function retain(){
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){

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
            // print_r($page_where);die;
            foreach ($page_where as $v){
                if($Page->totalRows<=$i){
                    break;
                }
                $parmas=array(
                    'start_time'=>$v['start_time'],
                    // 'end_time'=>$v['end_time'],
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
            // print_r($data_sum);die;
            array_unshift($data,$data_sum);

            self::assign('page',$page_show);
            self::assign('data',$data);
        }
        $product_list=M('game_father','','DB_CONFIG1')->cache(500)->getField('game_father_id,game_father_name');

        self::assign('product_list',$product_list);
        self::display();
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
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){

            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));

            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            // echo $date.'/';
            // print_r($_GET['cpscode']).'/';
            // print_r(array_fill(0,($date+1),$_GET['cpscode']));
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
                // $channel .= $vv.',';
                // $channel = rtrim($channel, ',');
                // $_GET['product'] = $_GET['channel'][0];
            }else{

                foreach (array_fill(0,($date+1),$_GET['channel']) as $k => $v) {
                    $page_where[]=array(
                        'channel'=>$_GET['channel'],
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                    );
                }
            }
            // print_r($page_where);die;
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
            foreach ($page_where as $v){

                $parmas=array(
                    'start_time'=>$v['start_time'],
                    // 'end_time'=>$v['end_time'],
                    'product'=>$v['channel_name']?$_GET['channel']:$_GET['product'],
                    'channel'=>$v['channel'],
                    'name'=>$v['channel_name']?$v['channel_name']:'game_id',
                    'father_name'=>$v['game_father_name']?$v['game_father_name']:'game_father_id',
                );
                // print_r($parmas);die;
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
            // print_r($data_sum);die;
            array_unshift($data,$data_sum);

            self::assign('page',$page_show);
            self::assign('data',$data);
        }
        $product_list=M('game_father','','DB_CONFIG1')->cache(500)->getField('game_father_id,game_father_name');

        self::assign('product_list',$product_list);
        self::display();
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
        // print_r($name_list);die;
        for($i=0;$i<$date;$i++){
            $data['ltv']['ltv'.($i)]=sprintf("%.2f",array_sum(array_column($name_list, 'ltv'.$i))/count(array_column($name_list, 'ltv'.$i)));
        }
        return $data;
    }

    private function ltv_combination_data($parmas){

        $new_user_count_where=array(
            // $parmas['name']=>array('eq',$parmas['product']),
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
        // print_r($parmas['channel']);die;
        for ($i=0; $i < count($parmas['channel']); $i++) {
            $parmas['into'] .= "`channel`='".$parmas['channel'][$i]."' OR ";
            // print_r( $parmas['channel'][$i] );
        }
        // print_r($parmas);die;
        $parmas['_string'] = rtrim($parmas['into'], " OR ");
        // print_r($parmas);die;
        $new_user_count_where=array(
            'game_id'=>array('eq',$parmas['product']),
            $parmas['_string'],
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
        );

        $new_user_count=M('user','','DB_CONFIG1')
            ->where($new_user_count_where)
            ->cache(500)
            ->count();//新增用户数
        // print_r($new_user_count_where);die;
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


    //查找玩家账号[仅安卓端在使用]
    public function get_number(){
        if ($_GET['roleid']&&$_GET['ext']) {
            //在用户表取得用户账号
            $number = M('create_role_report','','DB_CONFIG1')->where(array('cproleid'=>$_GET['roleid'],'server_id'=>$_GET['ext']))->find();
            if ($number['p_uid']) {
                $userInformation = M('allu_user','','DB_CONFIG1')->where(array('account'=>$number['p_uid']))->find();
                $illegal_user = M('illegal_user','','DB_CONFIG1')->where(array('user_id'=>$number['user_id']))->find();
                $userInformation['nickname'] = $number['nickname'];
                $userInformation['ext'] = $number['server_id'];
                $userInformation['platform'] = $number['platform'];
                $userInformation['id'] = '1';
                $userInformation['account'] = $number['p_uid'];
                $userInformation['status'] = $illegal_user['status']?$illegal_user['status']:'2';
                $userInformation['cproleid'] = $number['cproleid'];
                self::assign('data',$userInformation);
            }
        }
        self::display();
    }

    public function pay_section(){
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
            //查询时间
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            // //充值区间
            $pay_comma_separated = explode(" 到 ", urldecode($_GET['pay_start_time']));

            if ($_GET['cpscode']) {
                if(count($_GET['cpscode'])>1){
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
                            'pay_start_time'=>$pay_comma_separated[0]?$pay_comma_separated[0]:'',
                            'pay_end_time'=>$pay_comma_separated[1]?$pay_comma_separated[1]:'',
                            'channel_name'=>'channel',
                            'game_father_name'=>'game_id',
                        );
                    }
                }else{
                    foreach (array_fill(0,($date+1),$_GET['cpscode']) as $k=>$v){
                        foreach ($v as $vv){
                            $page_where[]=array(
                                'channel'=>$vv,
                                'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                                'pay_start_time'=>$pay_comma_separated[0]?$pay_comma_separated[0]:'',
                                'pay_end_time'=>$pay_comma_separated[1]?$pay_comma_separated[1]:'',
                                // 'end_time'=>date('Y-m-d',strtotime($comma_separated[1])),
                                'channel_name'=>'channel',
                                'game_father_name'=>'game_id',
                            );
                        }
                    }
                }
                // $_GET['product'] = $_GET['channel'][0];
            }else{
                foreach (array_fill(0,($date+1),$_GET['channel']) as $k=>$v){
                    foreach ($v as $vv){
                        $page_where[]=array(
                            'channel'=>$vv,
                            'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                            'pay_start_time'=>$pay_comma_separated[0]?$pay_comma_separated[0]:'',
                            'pay_end_time'=>$pay_comma_separated[1]?$pay_comma_separated[1]:'',
                            // 'start_time'=>date('Y-m-d',strtotime($comma_separated[0])),
                            'end_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                        );
                    }
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
            foreach ($page_where as $v){
                if($Page->totalRows<=$i){
                    break;
                }
                $parmas=array(
                    'start_time'=>$v['start_time'],
                    'pay_start_time'=>$v['pay_start_time'],
                    'pay_end_time'=>$v['pay_end_time'],
                    'product'=>$v['channel_name']?$_GET['channel'][0]:$_GET['product'],
                    'channel'=>$v['channel'],
                    'name'=>$v['channel_name']?$v['channel_name']:'game_id',
                    'father_name'=>$v['game_father_name']?$v['game_father_name']:'game_father_id',
                );


                if($parmas['name']=='channel' && count($_GET['cpscode']>1)){
                    $DATA=self::pay_array_combination_data($parmas);
                    $DATA['渠道']=$v['channel'];
                }else{
                    $DATA=self::pay_combination_data($parmas);
                    $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$parmas['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();
                    $DATA['渠道']=$parmas['name']=='channel'?$v['channel']:$channel_name['channel_name'];
                }

                // print_r($parmas);die;
                $DATA['start_time']=$parmas['start_time'];
                $data[]=$DATA;
            }

            $data_sum=self::pay_data_sum($data);
            $data=array_slice($data,$Page->firstRow,$Page->listRows);
            array_unshift($data,$data_sum);
            self::assign('page',$page_show);
            self::assign('data',$data);
        }

        $product_list=M('game_father','','DB_CONFIG1')->cache(500)->getField('game_father_id,game_father_name');

        self::assign('product_list',$product_list);
        self::display();
    }

    public function get_sectionExecl(){
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
            $_GET['channel'] = json_decode($_GET['channel'],ture);
            $_GET['cpscode'] = json_decode($_GET['cpscode'],ture);
            //查询时间
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            // //充值区间
            $pay_comma_separated = explode(" 到 ", urldecode($_GET['pay_start_time']));

            if ($_GET['cpscode']) {
                if(count($_GET['cpscode'])>1){
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
                            'pay_start_time'=>$pay_comma_separated[0]?$pay_comma_separated[0]:'',
                            'pay_end_time'=>$pay_comma_separated[1]?$pay_comma_separated[1]:'',
                            'channel_name'=>'channel',
                            'game_father_name'=>'game_id',
                        );
                    }
                }else{
                    foreach (array_fill(0,($date+1),$_GET['cpscode']) as $k=>$v){
                        foreach ($v as $vv){
                            $page_where[]=array(
                                'channel'=>$vv,
                                'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                                'pay_start_time'=>$pay_comma_separated[0]?$pay_comma_separated[0]:'',
                                'pay_end_time'=>$pay_comma_separated[1]?$pay_comma_separated[1]:'',
                                // 'end_time'=>date('Y-m-d',strtotime($comma_separated[1])),
                                'channel_name'=>'channel',
                                'game_father_name'=>'game_id',
                            );
                        }
                    }
                }
                // $_GET['product'] = $_GET['channel'][0];
            }else{
                foreach (array_fill(0,($date+1),$_GET['channel']) as $k=>$v){
                    foreach ($v as $vv){
                        $page_where[]=array(
                            'channel'=>$vv,
                            'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                            'pay_start_time'=>$pay_comma_separated[0]?$pay_comma_separated[0]:'',
                            'pay_end_time'=>$pay_comma_separated[1]?$pay_comma_separated[1]:'',
                            // 'start_time'=>date('Y-m-d',strtotime($comma_separated[0])),
                            'end_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                        );
                    }
                }
            }
            $count      = count($page_where);// 查询满足要求的总记录数


            // $Page       = new \Think\Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
            // $Page->lastSuffix = false;//最后一页不显示为总页数
            // $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
            // $Page->setConfig('prev','上一页');
            // $Page->setConfig('next','下一页');
            // $Page->setConfig('last','末页');
            // $Page->setConfig('first','首页');
            // $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            // $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里

            $data=array();
            //print_r($comma_separated);EXIT;
            for ($i=0;$i<=count($page_where);$i++){
                if(count($page_where)<=$i){
                    break;
                }
                $parmas=array(
                    'start_time'=>$page_where[$i]['start_time'],
                    'pay_start_time'=>$page_where[$i]['pay_start_time'],
                    'pay_end_time'=>$page_where[$i]['pay_end_time'],
                    'product'=>$page_where[$i]['channel_name']?$_GET['channel'][0]:$_GET['product'],
                    'channel'=>$page_where[$i]['channel'],
                    'name'=>$page_where[$i]['channel_name']?$page_where[$i]['channel_name']:'game_id',
                    'father_name'=>$page_where[$i]['game_father_name']?$page_where[$i]['game_father_name']:'game_father_id',
                );


                if($parmas['name']=='channel' && count($_GET['cpscode']>1)){
                    $DATA=self::pay_array_combination_data($parmas);
                    $DATA['渠道']=$page_where[$i]['channel'];
                }else{
                    $DATA=self::pay_combination_data($parmas);
                    $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$parmas['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();
                    $DATA['渠道']=$parmas['name']=='channel'?$page_where[$i]['channel']:$channel_name['channel_name'];
                }

                // print_r($parmas);die;
                $DATA['start_time']=$parmas['start_time'];
                $data[]=$DATA;
                unset($DATA);
            }
            $data_sum=self::pay_data_sum($data);
            array_unshift($data,$data_sum);
            self::section_execlData($data);
        }

    }

    //导出Excel方法
    public function section_execlData($data){
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(23);//宽度

        $objActSheet->setCellValue('A1', '时间');
        $objActSheet->setCellValue('B1', '产品');
        $objActSheet->setCellValue('C1', '渠道');
        $objActSheet->setCellValue('D1', '新增注册');
        $objActSheet->setCellValue('E1', '新增创角');
        $objActSheet->setCellValue('F1', '创角转化率');
        $objActSheet->setCellValue('G1', '新增付费率');
        $objActSheet->setCellValue('H1', '新增arppu');
        $objActSheet->setCellValue('I1', '新用户付费金额');
        $objActSheet->setCellValue('J1', '区间总额');
        foreach($data as $k=>$val){
            $k +=2;
            $objActSheet->setCellValue('A'.$k, $val['start_time']);
            $objActSheet->setCellValue('B'.$k, $game['game_father_name']);
            $objActSheet->setCellValue('C'.$k, $val['渠道']);
            $objActSheet->setCellValue('D'.$k, $val['新增注册']);
            $objActSheet->setCellValue('E'.$k, $val['新增创角']);
            $objActSheet->setCellValue('F'.$k, $val['创角转化率']);
            $objActSheet->setCellValue('G'.$k, $val['新增付费率']);
            $objActSheet->setCellValue('H'.$k, $val['新增arppu']);
            $objActSheet->setCellValue('I'.$k, $val['新用户付费金额']);
            $objActSheet->setCellValue('J'.$k, $val['新用户充值区间金额']);
        }
        $fileName = '充值区间数据表';
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


    private function  pay_data_sum($parma){
        $data= array(
            'time'=>'汇总',
            '渠道'=>'-',
            'product'=>'-',
            '新增注册'=>array_sum(array_column($parma, '新增注册')),
            '新增创角'=>array_sum(array_column($parma, '新增创角')),
            '新用户付费金额'=>array_sum(array_column($parma, '新用户付费金额')),
            '新增付费人数'=>array_sum(array_column($parma, '新增付费人数')),
            '新用户充值区间金额'=>array_sum(array_column($parma, '新用户充值区间金额')),
        );
        $data['创角转化率']=round(($data['新增创角']/$data['新增注册'])*100,2).'%';
        $data['新增付费率']=round(($data['新增付费人数']/$data['新增注册'])*100,2).'%';
        $data['新增arppu']=round(($data['新用户付费金额']/$data['新增付费人数']),2);
        return $data;
    }

    private function pay_array_combination_data($parmas){

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

        $new_user_role=M('create_role_report','','DB_CONFIG1')
            ->where(array(
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['_string'],
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
            ))
//             ->fetchSql()
            ->cache(500)
            ->count('DISTINCT user_id'); //新增创角色数

        $new_user_order= M('game_order','','DB_CONFIG1')
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

        if($parmas['pay_start_time']){
            $new_pay_user_order= M('game_order','','DB_CONFIG1')
                ->where(array(
                    'status'=>array('egt',1),
                    $parmas['father_name']=>array('eq',$parmas['product']),
                    $parmas['_string'],
                    'create_date'=>array(array('egt',strtotime($parmas['pay_start_time'])),array('lt',strtotime($parmas['pay_end_time'].' +1 day')),'and'),
                    'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
                ))
                //->fetchSql()
                ->cache(500)
                ->field('sum(money)as user_pay')
                ->find(); //新用户在充值区间付费总额
        }

        // 上面方法到时候可模型化

        $new_user_order['new_user_pay']=$new_user_order['new_user_pay']/100;
        $data=array(
            '新增注册'=>$new_user_count?sprintf("%.0f",$new_user_count):"-",
            '新增创角'=>$new_user_role?sprintf("%.0f",$new_user_role):"-",
            '创角转化率'=>$new_user_role/$new_user_count?round(($new_user_role/$new_user_count)*100,2).'%':"-",
            '新增付费人数'=>$new_user_order['new_user_pay_count'],
            '新增付费率'=>$new_user_order['new_user_pay_count']/$new_user_count?round(($new_user_order['new_user_pay_count']/$new_user_count)*100,2).'%':"-",
            '新增arppu'=>$new_user_order['new_user_pay']/$new_user_order['new_user_pay_count']?round($new_user_order['new_user_pay']/$new_user_order['new_user_pay_count'],2):"-",
            '新用户付费金额'=>$new_user_order['new_user_pay']?sprintf("%.2f",$new_user_order['new_user_pay']):"-",
            '新用户充值区间金额'=>$new_pay_user_order['user_pay']?sprintf("%.2f",$new_pay_user_order['user_pay']/100):"-",
        );
        return $data;
    }

    private function pay_combination_data($parmas){

        $new_user_count_where=array(
            $parmas['name']=>array('eq',$parmas['channel']),
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
        );

        $new_user_count=M('user','','DB_CONFIG1')
            ->where($new_user_count_where)
            ->cache(500)
            ->count();//新增用户数

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

        if($parmas['pay_start_time']){
            $new_pay_user_order= M('game_order','','DB_CONFIG1')
                ->where(array(
                    'status'=>array('egt',1),
                    $parmas['father_name']=>array('eq',$parmas['product']),
                    $parmas['name']=>array('eq',$parmas['channel']),
                    'create_date'=>array(array('egt',strtotime($parmas['pay_start_time'])),array('lt',strtotime($parmas['pay_end_time'].' +1 day')),'and'),
                    'user_id'=>array('exp','in '.M('user','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql())
                ))
                //->fetchSql()
                ->cache(500)
                ->field('sum(money)as user_pay')
                ->find(); //新用户在充值区间付费总额
        }

        // 上面方法到时候可模型化

        $new_user_order['new_user_pay']=$new_user_order['new_user_pay']/100;
        if ($parmas['name']=='channel') {
            $channel_name['channel_name'] = $parmas['channel'];
        }else{
            $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$parmas['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();
        }
        $data=array(
            '渠道'=>$channel_name['channel_name'],
            '新增注册'=>$new_user_count?sprintf("%.0f",$new_user_count):"-",
            '新增创角'=>$new_user_role?sprintf("%.0f",$new_user_role):"-",
            '创角转化率'=>$new_user_role/$new_user_count?round(($new_user_role/$new_user_count)*100,2).'%':"-",
            '新增付费人数'=>$new_user_order['new_user_pay_count'],
            '新增付费率'=>$new_user_order['new_user_pay_count']/$new_user_count?round(($new_user_order['new_user_pay_count']/$new_user_count)*100,2).'%':"-",
            '新增arppu'=>$new_user_order['new_user_pay']/$new_user_order['new_user_pay_count']?round($new_user_order['new_user_pay']/$new_user_order['new_user_pay_count'],2):"-",
            '新用户付费金额'=>$new_user_order['new_user_pay']?sprintf("%.2f",$new_user_order['new_user_pay']):"-",
            '新用户充值区间金额'=>$new_pay_user_order['user_pay']?sprintf("%.2f",$new_pay_user_order['user_pay']/100):"-",
        );
        return $data;
    }

    //订单查询角色信息
    public function get_role(){
        if($_GET['gameType'] && $_GET['gameOrder']){
            //小游戏、微端
            if ($_GET['gameType']=='1') {
                $order = M('game_order','','DB_CONFIG1')->where(array('u_order_id'=>$_GET['gameOrder']))->cache(500)->find(); //获取订单信息
                $role_name = M('create_role_report','','DB_CONFIG1')->where(array('uese_id'=>$order['uese_id'],'platform'=>$order['platform'],'game_id'=>$order['game_id']))->cache(500)->find();//获取角色信息
                $game_name = M('game','','DB_CONFIG1')->where(array('game_id'=>$role_name['game_id']))->cache(500)->find();//获取游戏名
                $data = array(
                    'gameName'=>$game_name['game_name'],//游戏名
                    'ext'=>$order['ext'],//角色区服
                    'roleName'=>$role_name['nickname'],//角色名
                    'roleUid'=>$order['user_id'],//我方uid
                    'roleId'=>$order['cproleid'],//研发方角色uid
                );
                self::assign('data',$data);
            }else if($_GET['gameType']=='2'){ //小程序
                $order = M('mini_game_order','','DB_CONFIG1')->where(array('u_order_id'=>$_GET['gameOrder']))->cache(500)->find();//获取订单信息
                $role_name = M('mini_role_report','','DB_CONFIG1')->where(array('user_id'=>$order['user_id'],'srvid'=>$order['server_id'],'cproleid'=>$order['cp_role_id'],'appid'=>$order['mini_appid']))->cache(500)->find();
                $data = array(
                    'gameName'=>$order['mini_name'],//游戏名
                    'ext'=>$order['server_id'],//角色区服
                    'roleName'=>$role_name['nickname'],//角色名
                    'roleUid'=>$order['user_id'],//我方玩家uid
                    'roleId'=>$order['cp_role_id'],//研发角色uid
                );
                self::assign('data',$data);
            }
        }
        self::display();
    }

    //充值总额查询
    public function pay_query(){
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
            //查询时间
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            // //充值区间
            $pay_comma_separated = explode(" 到 ", urldecode($_GET['pay_start_time']));

            if ($_GET['cpscode']) {
                if(count($_GET['cpscode'])>1){
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
                            'channel_name'=>'channel',
                            'game_father_name'=>'game_id',
                        );
                    }
                }else{
                    foreach (array_fill(0,($date+1),$_GET['cpscode']) as $k=>$v){
                        foreach ($v as $vv){
                            $page_where[]=array(
                                'channel'=>$vv,
                                'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                                // 'end_time'=>date('Y-m-d',strtotime($comma_separated[1])),
                                'channel_name'=>'channel',
                                'game_father_name'=>'game_id',
                            );
                        }
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
            foreach ($page_where as $v){
                if($Page->totalRows<=$i){
                    break;
                }
                $parmas=array(
                    'start_time'=>$v['start_time'],
                    'product'=>$v['channel_name']?$_GET['channel'][0]:$_GET['product'],
                    'channel'=>$v['channel'],
                    'name'=>$v['channel_name']?$v['channel_name']:'game_id',
                    'father_name'=>$v['game_father_name']?$v['game_father_name']:'game_father_id',
                );


                if($parmas['name']=='channel' && count($_GET['cpscode']>1)){
                    $DATA=self::pay_array_combination_query($parmas);
                    $DATA['渠道']=$v['channel'];
                }else{
                    $DATA=self::pay_combination_query($parmas);
                    $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$parmas['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();
                    $DATA['渠道']=$parmas['name']=='channel'?$v['channel']:$channel_name['channel_name'];
                }

                // print_r($parmas);die;
                $DATA['start_time']=$parmas['start_time'];
                $data[]=$DATA;
            }

            $data_sum=self::pay_query_sum($data);
            $data=array_slice($data,$Page->firstRow,$Page->listRows);
            array_unshift($data,$data_sum);
            self::assign('page',$page_show);
            self::assign('data',$data);
        }

        $product_list=M('game_father','','DB_CONFIG1')->cache(500)->getField('game_father_id,game_father_name');

        self::assign('product_list',$product_list);
        self::display();
    }

    public function query_sectionExecl(){
        if($_GET['start_time']&&$_GET['product']&&$_GET['channel']){
            $_GET['channel'] = json_decode($_GET['channel'],ture);
            $_GET['cpscode'] = json_decode($_GET['cpscode'],ture);
            //查询时间
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            // //充值区间
            $pay_comma_separated = explode(" 到 ", urldecode($_GET['pay_start_time']));

            if ($_GET['cpscode']) {
                if(count($_GET['cpscode'])>1){
                    foreach (array_fill(0,1,$_GET['cpscode']) as $k=>$v){
                        foreach ($v as $vv){
                            $channel .= $vv.',';
                        }
                    }
                    $channel = rtrim($channel, ',');
                    foreach (array_fill(0,($date+1),$_GET['cpscode']) as $key => $value) {
                        $page_where[]=array(
                            // 'channel'=>array('in',$channe),
                            'channel'=>$channel,
                            'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$key.'day')),
                            'pay_start_time'=>$pay_comma_separated[0]?$pay_comma_separated[0]:'',
                            'pay_end_time'=>$pay_comma_separated[1]?$pay_comma_separated[1]:'',
                            'channel_name'=>'channel',
                            'game_father_name'=>'game_id',
                        );
                    }
                }else{
                    foreach (array_fill(0,($date+1),$_GET['cpscode']) as $k=>$v){
                        foreach ($v as $vv){
                            $page_where[]=array(
                                'channel'=>$vv,
                                'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                                'pay_start_time'=>$pay_comma_separated[0]?$pay_comma_separated[0]:'',
                                'pay_end_time'=>$pay_comma_separated[1]?$pay_comma_separated[1]:'',
                                // 'end_time'=>date('Y-m-d',strtotime($comma_separated[1])),
                                'channel_name'=>'channel',
                                'game_father_name'=>'game_id',
                            );
                        }
                    }
                }
                // $_GET['product'] = $_GET['channel'][0];
            }else{
                foreach (array_fill(0,($date+1),$_GET['channel']) as $k=>$v){
                    foreach ($v as $vv){
                        $page_where[]=array(
                            'channel'=>$vv,
                            'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                            'pay_start_time'=>$pay_comma_separated[0]?$pay_comma_separated[0]:'',
                            'pay_end_time'=>$pay_comma_separated[1]?$pay_comma_separated[1]:'',
                            // 'start_time'=>date('Y-m-d',strtotime($comma_separated[0])),
                            'end_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                        );
                    }
                }
            }
            $count      = count($page_where);// 查询满足要求的总记录数

            $data=array();
            //print_r($comma_separated);EXIT;
            for ($i=0;$i<=count($page_where);$i++){
                if(count($page_where)<=$i){
                    break;
                }
                $parmas=array(
                    'start_time'=>$page_where[$i]['start_time'],
                    'pay_start_time'=>$page_where[$i]['pay_start_time'],
                    'pay_end_time'=>$page_where[$i]['pay_end_time'],
                    'product'=>$page_where[$i]['channel_name']?$_GET['channel'][0]:$_GET['product'],
                    'channel'=>$page_where[$i]['channel'],
                    'name'=>$page_where[$i]['channel_name']?$page_where[$i]['channel_name']:'game_id',
                    'father_name'=>$page_where[$i]['game_father_name']?$page_where[$i]['game_father_name']:'game_father_id',
                );


                if($parmas['name']=='channel' && count($_GET['cpscode']>1)){
                    $DATA=self::pay_array_combination_query($parmas);
                    $DATA['渠道']=$page_where[$i]['channel'];
                }else{
                    $DATA=self::pay_combination_query($parmas);
                    $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$parmas['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();
                    $DATA['渠道']=$parmas['name']=='channel'?$page_where[$i]['channel']:$channel_name['channel_name'];
                }

                // print_r($parmas);die;
                $DATA['start_time']=$parmas['start_time'];
                $data[]=$DATA;
                unset($DATA);
            }
            $data_sum=self::pay_query_sum($data);
            array_unshift($data,$data_sum);
            self::queryPay_execlData($data);
        }
    }


    //导出Excel方法
    public function queryPay_execlData($data){
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

        $objActSheet->setCellValue('A1', '时间');
        $objActSheet->setCellValue('B1', '产品');
        $objActSheet->setCellValue('C1', '渠道');
        $objActSheet->setCellValue('D1', '总付费金额');
        foreach($data as $k=>$val){
            $k +=2;
            $objActSheet->setCellValue('A'.$k, $val['start_time']);
            $objActSheet->setCellValue('B'.$k, $game['game_father_name']);
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


    private function  pay_query_sum($parma){
        $data= array(
            'time'=>'汇总',
            '渠道'=>'-',
            'product'=>'-',
            '总付费金额'=>array_sum(array_column($parma, '总付费金额')),
        );

        return $data;
    }

    private function pay_array_combination_query($parmas){

        $parmas['channel'] = explode(',', $parmas['channel']);
        for ($i=0; $i < count($parmas['channel']); $i++) {
            $parmas['into'] .= "`channel`='".$parmas['channel'][$i]."' OR ";
        }
        $parmas['_string'] = rtrim($parmas['into'], " OR ");

        $order=M('game_order','','DB_CONFIG1')
            ->where(array(
                'status'=>array('egt',1),
                $parmas['father_name']=>array('eq',$parmas['product']),
                $parmas['_string'],
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
            ))
            ->cache(500)
            ->field('sum(money)as user_pay,count(DISTINCT user_id) as user_pay_count')
            ->find(); //付费金额,付费人数

        // 上面方法到时候可模型化

        $order['user_pay']=$order['user_pay']/100;
        $data=array(
            '总付费金额'=>$order['user_pay']?sprintf("%.2f",$order['user_pay']):"-",
        );
        return $data;
    }

    private function pay_combination_query($parmas){

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

        $order['user_pay']=$order['user_pay']/100;
        if ($parmas['name']=='channel') {
            $channel_name['channel_name'] = $parmas['channel'];
        }else{
            $channel_name=M('game','','DB_CONFIG1')->where(array('game_id'=>array('eq',$parmas['channel'])))->cache(500)->field('concat(game_name,"-",platform) as channel_name')->find();
        }
        $data=array(
            '渠道'=>$channel_name['channel_name'],
            '总付费金额'=>$order['user_pay']?sprintf("%.2f",$order['user_pay']):"-",
        );
        return $data;
    }

    public function query_order(){
        if ($_GET['gameType'] && $_GET['role_id']) {
            $user = M('create_role_report','','DB_CONFIG1')->where(array('cproleid'=>$_GET['role_id']))->cache(500)->find();
            $order = M('game_order','','DB_CONFIG1')->where(array('user_id'=>$user['user_id'],'cproleid'=>$user['cproleid'],'platform'=>$user['platform'],'game_id'=>$user['game_id'],'game_father_id'=>$user['game_father_id'],'status'=>array('lt','2')))->select(); //获取订单信息
            $game = M('game','','DB_CONFIG1')->where(array('game_id'=>$user['game_id']))->cache(500)->find();
            foreach ($order as $key => $value) {
                $data[$key] = array(
                    'gameName'=>$game['game_name'],
                    'ext'=>$value['ext'],
                    'roleName'=>$user['nickname'],
                    'roleId'=>$value['cproleid'],
                    'orderSn'=>$value['u_order_id'],
                    'orderMoney'=>round($value['money']/100),
                    'orderState'=>$value['status'],
                    'orderTime'=>date('Y-m-d H:i:s',$value['create_date']),
                );
            }
            self::assign('data',$data);
        }else if($_GET['gameType'] && $_GET['order_no']){
            $order = M('game_order','','DB_CONFIG1')->where(array('u_order_id'=>$_GET['order_no'],'status'=>array('lt','2')))->find(); //获取订单信息
            $user = M('create_role_report','','DB_CONFIG1')->where(array('cproleid'=>$order['cproleid'],'user_id'=>$order['user_id'],'game_id'=>$order['game_id'],'game_father_id'=>$order['game_father_id'],'platform'=>$order['platform'],'server_id'=>$order['ext']))->find();
            $game = M('game','','DB_CONFIG1')->where(array('game_id'=>$user['game_id']))->cache(500)->find();
            $data[0] = array(
                'gameName'=>$game['game_name'],
                'ext'=>$order['ext'],
                'roleName'=>$user['nickname'],
                'roleId'=>$order['cproleid'],
                'orderSn'=>$order['u_order_id'],
                'orderMoney'=>round($order['money']/100),
                'orderState'=>$order['status'],
                'orderTime'=>date('Y-m-d H:i:s',$order['create_date']),
            );
            self::assign('data',$data);
        }
        self::display();
    }

    public function mipay(){
        $orderNo = $_POST['orderNo'];
        $order = M('game_order','','DB_CONFIG1')->where(array('u_order_id'=>$_POST['orderNo'],'status'=>array('lt','2')))->find(); //获取订单信息
        if ($order['status']=='0') {
            $notify_url = 'https://api.baizegame.com/Admin_game_switch/mipay?game_id='.$order['game_id'].'&user_id='.$order['user_id'].'&money='.$order['money'].'&orderNo='.$order['u_order_id'];
            $content = $this->curl_get($notify_url);
            if($content=='true'){
                // self::success('1');//补单成功
                $Notify = $this->gameNotify($orderNo);
                if($Notify){
                    self::success('1');//补单成功
                }else{
                    self::success('4');//研发补单失败，请联系研发处理
                }

            }else if($content=='no_login'){
                self::success('2');//session_key过期，请联系玩家登录游戏重新获取session_key，方可补单
            }else{
                self::success('3');//未知错误
            }
        }else{

            $Notify = $this->gameNotify($orderNo);
            if($Notify){
                self::success('1');//补单成功
            }else{
                self::success('4');//研发补单失败，请联系研发处理
            }
        }

    }

    public function test(){
        $condition = array('u_order_id' => '202_1730126_1573724891_888');
        $game_order = M('game_order','','DB_CONFIG1')->where($condition)->find();
        print_r($game_order);die;
    }

    public function kefupay(){
        $orderNo = $_POST['orderNo'];
        $Notify = $this->gameNotify($orderNo);
        if($Notify){
            self::success('1');//补单成功
        }else{
            self::success('4');//研发补单失败，请联系研发处理
        }
    }

    public function gameNotify($order_id)
    {
        $condition = array('u_order_id' => $order_id);
        $game_order = M('game_order','','DB_CONFIG1')->where($condition)->find();
        if (!$game_order) {
            return;
        }else if($game_order['status']!='1'){
            $data['status']='1';
            M('game_order','','DB_CONFIG1')->where($condition)->setField($data);
        }
        if ($game_order['status'] == '1') {
            $game_id = array('game_id'=>$game_order['game_id']);
            $game = M('game','','DB_CONFIG1')->where($game_id)->find();
            if (!$game) {
                return false;
            }
            $p = array(

                'actor_id' => $game_order['data'],
                'app_id' => $game_order['game_id'],
                'app_order_id' => $game_order['orderno'],
                'app_user_id' => $game_order['user_id'],
                'ext' => $game_order['ext'],
                'order_id' => $game_order['u_order_id'],
                'payment_time' => time(),
                'real_amount' => $game_order['money'],

            );
            $p_str = $this->sort_params($p);

            $p_str_sign = $p_str.'&key='.$game['app_key'];
            $sign = md5($p_str_sign);

            $game_pay_nofity = $game['game_pay_nofity'];


            $p = array(
                'actor_id' => urlencode($game_order['data']),
                'app_id' => $game_order['game_id'],
                'app_order_id' => $game_order['orderno'],
                'app_user_id' => $game_order['user_id'],
                'ext' => $game_order['ext'],
                'order_id' => $game_order['u_order_id'],
                'payment_time' => time(),
                'real_amount' => $game_order['money'],

            );

            $p_str = $this->sort_params($p);

            $notify_url = $game_pay_nofity.'?'.$p_str."&sign=$sign";

            $content = $this->curl_get($notify_url);

            if ($game_order['platform'] == 'tt') {
                if ($content != 'success') {
                    $game_pay_nofity = 'http://pay.gz.1251208707.clb.myqcloud.com/juhe/payment';
                    $notify_url = $game_pay_nofity.'?'.$p_str."&sign=$sign";
                    $content = $this->curl_get($notify_url);
                }
            }

            if ($content) {
                if ($content == 'success') {
                    $where = array('u_order_id' => $order_id,'cproleid' => $game_order['cproleid'],'game_id' => $game_order['game_id'],'platform' => $game_order['platform'],'orderno' => $game_order['orderno']);
                    $new_data = array('status' => '2');
                    M('game_order','','DB_CONFIG1')->where($where)->setField($new_data);
                    return 'true';
                } else {
                    return 'false';
                }
            }else{
                return 'false';
            }
        } else {
            return 'false';
        }
    }

    public function sort_params($params)
    {
        if (!$params || gettype($params) != 'array') {
            return false;
        }

        $keys = array_keys($params);
        sort($keys);
        $pair = '';
        $index = 0;
        foreach ($keys as $key) {
            if ($index != 0) {
                $pair .= '&';
            }
            $pair .= "$key=".$params["$key"];

            ++$index;
        }

        return $pair;
    }

    public function curl_get($url, $header = null)
    {
        $my_curl = curl_init();
        curl_setopt($my_curl, CURLOPT_URL, $url);
        curl_setopt($my_curl, CURLOPT_RETURNTRANSFER, 1);

        if ($header) {
            $header_list = array();
            foreach ($header as $key => $value) {
                $header_list[] = "$key: $value";
            }
            curl_setopt($my_curl, CURLOPT_HTTPHEADER, $header_list);
        }

        $str = curl_exec($my_curl);
        curl_close($my_curl);

        return $str;
    }
    //资讯包转换率
    public function game_change(){

        if($_GET['start_time'] && $_GET['product'] && $_GET['channel']){
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            $_GET['product'][0]=$_GET['product'];
            foreach (array_fill(0,($date+1),$_GET['product']) as $k=>$v){
                $page_where[]=array(
                    'channel'=>$v,
                    'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                    'product'=>$_GET['channel'],
                );
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

            $data=self::base_data_core($page_where);
            $data_sum=self::change_base_data_sum($data);
            $data=array_slice($data,$Page->firstRow,$Page->listRows);
            array_unshift($data,$data_sum);

            self::assign('page',$page_show);
            self::assign('data',$data);
        }

        $product_list = M('mini_programs','','DB_CONFIG1')->where(array('path'=>array('like','%pages%')))->select();
        foreach ($product_list as $key => $value) {
            $channelArr = explode('=', $value['path']);
            $channel = $channelArr[1];
            $product_list[$key] = array(
                $value['mini_name']=>$channel,
            );
        }
        self::assign('product_list',$product_list);
        self::display();
    }

    private  function base_data_core($parma){
        $data=array();
        foreach ($parma as $v){
            $parmas=array(
                'start_time'=>$v['start_time'],
                'product'=>$v['product'],
                'channel'=>$v['channel']
            );
            $DATA=self::change_combination_data($parmas);
            $DATA['time']=$parmas['start_time'];
            $data[]=$DATA;
        }
        return $data;
    }

    private function change_base_data_sum($params){
        $data = array(
            'time'=>'汇总',
            '渠道'=>'-',
            'product'=>'-',
            '新增自然量用户数'=>array_sum(array_column($params, '新增自然量用户数')),
            '新增自然量转换率'=>array_sum(array_column($params, '新增自然量转换率')),
            '新增渠道量用户数'=>array_sum(array_column($params, '新增渠道量用户数')),
            '新增渠道量转换率'=>array_sum(array_column($params, '新增渠道量转换率')),
            '总新增量'=>array_sum(array_column($params, '总新增量')),
            '资讯包转换率'=>array_sum(array_column($params, '资讯包转换率')),
        );
        return $data;
    }

    private function  change_combination_data($parmas){
        $mini_game = M('mini_programs','','DB_CONFIG1')->where(array('path'=>array('like','%'.$parmas['channel'].'%')))->find();
        //小程序渠道参数查询条件
        $channel_user_count_where=array(
            'appid'=>array('eq',$mini_game['mini_appid']),
            'channel'=>array('like','%JSCCC%'),
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
        );
        //小程序自然量参数查询条件
        $allu_user_count_where=array(
            'appid'=>array('eq',$mini_game['mini_appid']),
            'channel'=>array('eq','allu'),
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
        );

        $channel_user_count=M('mini_user','','DB_CONFIG1')
            ->where($channel_user_count_where)
            ->cache(500)
            ->count();//渠道量新增用户数

        $allu_user_count=M('mini_user','','DB_CONFIG1')
            ->where($allu_user_count_where)
            ->cache(500)
            ->count();//自然量新增用户数

        //小游戏渠道参数查询条件
        $game_channel_user_count_where=array(
            'platform'=>'wxminigame',
            'game_id'=>array('eq',$parmas['product']),
            'channel'=>array('eq',$parmas['channel']),
            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and')
        );

        $game_channel_user_count=M('user','','DB_CONFIG1')
            ->where($game_channel_user_count_where)
            ->cache(500)
            ->count();//渠道量新增用户数

        //点击跳转游戏用户数[渠道]
        $channel_new_user_click=M('mini_game_click','','DB_CONFIG1')
            ->where(array(
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'game_id'=>array('eq',$parmas['product']),
                'mini_appid'=>array('eq',$mini_game['mini_appid']),
                'channel'=>array('like','%JSCCC%'),
                'user_id'=>array('exp','in '.M('mini_user','','DB_CONFIG1')->where($channel_user_count_where)->field('mini_user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->count();

        //点击跳转游戏用户数[自然量]
        $allu_new_user_click=M('mini_game_click','','DB_CONFIG1')
            ->where(array(
                'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                'game_id'=>array('eq',$parmas['product']),
                'mini_appid'=>array('eq',$mini_game['mini_appid']),
                'channel'=>array('eq','allu'),
                'user_id'=>array('exp','in '.M('mini_user','','DB_CONFIG1')->where($allu_user_count_where)->field('mini_user_id')->buildSql())
            ))
            //->fetchSql()
            ->cache(500)
            ->count();
        // print_r($allu_new_user_click.'/');
        // print_r($channel_new_user_click);die;

        $zx_change_count = round($game_channel_user_count);//资讯包转换总数
        $zx_count = round($channel_user_count+$allu_user_count);//资讯包新增总数

        $data = array(
            '渠道'=>$parmas['channel'],
            '新增自然量用户数'=>$allu_user_count?$allu_user_count:"-",
            '新增渠道量用户数'=>$channel_user_count?$channel_user_count:"-",
            '新增渠道量转换率'=>$channel_new_user_click?round(($channel_new_user_click/$game_channel_user_count)*100,2).'%':"-",
            '新增自然量转换率'=>$allu_new_user_click?round(($allu_new_user_click/$game_channel_user_count)*100,2).'%':"-",
            '总新增量'=>$zx_count?$zx_count:"-",
            '小游戏新增数'=>$zx_change_count?$zx_change_count:"-",
            '资讯包转换率'=>$zx_change_count?round(($zx_change_count/$zx_count)*100,2)."%":"-",
        );
        // print_r($data);die;
        return $data;
    }

    public function upcdk_excel(){
        self::display();
    }

    public function excel_cdk(){
        if (!empty($_FILES['file']['name'])) {
            $fileName = $_FILES['file']['name'];
            $dotArray = explode('.', $fileName);
            $type = end($dotArray);
            if ($type != "xls" && $type != "xlsx") {
                exit(self::error('不是Excel文件，请重新上传!'));
            }

            $uploaddir = "../adminUploads/cdkExcel" . date("Y-m-d") . '/';
            if (!file_exists($uploaddir)) {
                mkdir($uploaddir, 0777, true);
                chmod($uploaddir,0777);
            }

            $path = $uploaddir . md5(uniqid(rand())) . '.' . $type;
            move_uploaded_file($_FILES['file']['tmp_name'], $path);

            $file_path = $path;
            if (!file_exists($path)) {
                exit(self::error("上传文件丢失!" . $_FILES['file']['error']));
            }
            vendor('PHPExcel.PHPExcel');
            $objPHPExcel = new \PHPExcel();
            import("Org.Util.PHPExcel");
            import("Org.Util.PHPExcel.Worksheet.Drawing");
            import("Org.Util.PHPExcel.Writer.Excel2007");
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($ext == 'xlsx') {
                $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
                $objPHPExcel = $objReader->load($file_path, 'utf-8');
            } elseif ($ext == 'xls') {
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                $objPHPExcel = $objReader->load($file_path, 'utf-8');
            }

            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 取得总行数
//            $highestColumn = $sheet->getHighestColumn(); // 取得总列数
            $ar = array();
            $i = 0;
            $importRows = 0;
            for ($j = 2; $j <= $highestRow; $j++) {
                $importRows++;
                $add['type'] = (string)$objPHPExcel->getActiveSheet()->getCell("A$j")->getValue();//需要导入的cdk归属类型 1：英雄训练师 2：口袋精灵王
                $add['cdk'] = (string)$objPHPExcel->getActiveSheet()->getCell("B$j")->getValue();   //需要导入的cdk
                // $ret['mdata'] = $this->addMemb($phone, $realName, $company, $job, $email);//这里就是我的数据库添加操作定义的一个方法啦,对应替换为自己的
                if($add['cdk']){
                    $ret['mdata'] = M('wxmp_cdk','','DB_CONFIG1')->add($add);
                    if ($ret['mdata'] && !is_Bool($ret['mdata'])) {
                        $ar[$i] = $ret['mdata'];
                        $i++;
                    }
                }
            }
            if ($i > 0) {
                exit(self::success("导入完毕!"));
            }
            $ret['res'] = "1";
            $ret['allNum'] = $importRows;
            $ret['errNum'] = 0;
            $ret['sucNum'] = $importRows;
            $ret['mdata'] = "导入成功!";
            return json_encode($ret);
        } else {
            exit(self::error("导入失败!"));
        }
    }

    public function test1(){
        M('user','','DB_CONFIG1')->where(array('game_id'=>'6'))->count();
        echo M('user')->getLastSql();die;
        //     foreach ($user as $key => $value) {
        //         $new_user = M('mini_user','','DB_CONFIG1')->where(array('mini_user_id'=>$value['user_id']))->find();
        //         if (!$value['channel']) {
        //             $save['channel']=$new_user['channel'];
        //             M('mini_game_click','','DB_CONFIG1')->where(array('user_id'=>$value['user_id']))->save($save);
        //             unset($new_user);
        //             unset($save);
        //         }
        //     }
    }

    //查询用户openid
    public function query_roleid(){
//        print_r($_GET);die;
        if ($_GET['user_id'] || $_GET['role_id']){
            if (!$_GET['product']){
                exit(self::error('请先选择要查询的游戏！'));
            }
            $game = M('game','','DB_CONFIG1')->where(array('game_father_id'=>$_GET['product']))->cache('500')->find();
            if($_GET['user_id'] && empty($_GET['role_id'])){
                $role_list = M('create_role_report','','DB_CONFIG1')->where(array('user_id'=>$_GET['user_id'],'game_father_id'=>$_GET['product']))->cache(500)->select();
            }else if(empty($_GET['user_id']) && $_GET['role_id']){
                $role_list = M('create_role_report','','DB_CONFIG1')->where(array('cproleid'=>$_GET['role_id'],'game_father_id'=>$_GET['product']))->cache(500)->select();
            }else{
                $role_list = M('create_role_report','','DB_CONFIG1')->where(array('user_id'=>$_GET['user_id'],'cproleid'=>$_GET['role_id'],'game_father_id'=>$_GET['product']))->cache(500)->select();
            }

            foreach ($role_list as $k => $v){
                $data[$k]['gameName'] = $game['game_name'];
                $data[$k]['gamePlatform'] = $v['platform'];
                $data[$k]['gameExt'] = $v['server_id'];
                $data[$k]['userId'] = $v['user_id'];
                $data[$k]['roleId'] = $v['cproleid'];
                $data[$k]['openId'] = $v['p_uid'];
            }
            self::assign('data',$data);
        }
        $product_list=M('game_father','','DB_CONFIG1')->cache(500)->getField('game_father_id,game_father_name');
        self::assign('product_list',$product_list);
        self::display();
    }

    //查询cdk库存页
    public function query_cdk(){
        if ($_GET['cdkType']){
            foreach ($_GET['cdkType'] as $k=>$v){
                $data = array(
                    'type'=>$v,
                );
                $list = self::cdk_base_query($data);
                $cdk_list[$k] = array(
                    'gameName'=>$list['name'][0],
                    'cdk_type'=>$v,
                    'cdk_use'=>$list['count_use'],
                    'cdk_nouse'=>$list['count_nouse'],
                );
            }
            self::assign('data',$cdk_list);
        }

        $product_list = M('wxmp_cdk_game','','DB_CONFIG1')->cache(500)->getField('type_id,name');
        self::assign('product_list',$product_list);
        self::display();
    }

    public function cdk_base_query($data){
        $list['count_nouse'] = M('wxmp_cdk','','DB_CONFIG1')->where(array('status'=>'0','type'=>$data['type']))->cache(500)->count();
        $list['count_use'] = M('wxmp_cdk','','DB_CONFIG1')->where(array('status'=>'1','type'=>$data['type']))->cache(500)->count();
        $list['name'] = M('wxmp_cdk_game','','DB_CONFIG1')->where(array('type_id'=>substr($data['type'],'0','1')))->group('name')->cache(500)->getField('name',true);
        return $list;
    }

    //查看cdk礼包类型list
    public function get_h5_cdk(){
        if ($_POST['id']){
            if (S('cdk_type'.$_POST['id'])){
                $list = self::cdk_type(S('cdk_type'.$_POST['id']));
            }else{
                $cdk_list = M('wxmp_cdk','','DB_CONFIG1')->where(array('type'=>array('like',$_POST['id'].'%')))->group('type')->cache(500)->getField('type',true);
                S('cdk_type'.$_POST['id'],$cdk_list,'7200');
                $list = self::cdk_type($cdk_list);
            }
        }
        self::success($list);
    }

    //转换cdk类型
    public function cdk_type($cdk_list){
        if ($cdk_list){
            foreach ($cdk_list as $k=>$v){
                if (substr($v,2)=='01'){
                    if(substr($v,0,1)=='8'){
                        $list[$k][name] = '1月礼包';
                        $list[$k]['code'] = $v;
                    }else{
                        $list[$k][name] = '关注礼包';
                        $list[$k]['code'] = $v;
                    }
                }else if(substr($v,2)=='02'){
                    if(substr($v,0,1)=='5'){
                        $list[$k][name] = '新年礼包';
                        $list[$k]['code'] = $v;
                    }else if(substr($v,0,1)=='8'){
                        $list[$k][name] = '新春礼包';
                        $list[$k]['code'] = $v;
                    }else if(substr($v,0,1)=='7'){
                        $list[$k][name] = '1月礼包';
                        $list[$k]['code'] = $v;
                    }else{
                        $list[$k][name] = '补偿礼包';
                        $list[$k]['code'] = $v;
                    }
                }else if(substr($v,2)=='03'){
                    if(substr($v,0,1)=='5'){
                        $list[$k][name] = '元宵礼包';
                        $list[$k]['code'] = $v;
                    }else if(substr($v,0,1)=='7'){
                        $list[$k][name] = '新春礼包';
                        $list[$k]['code'] = $v;
                    }else {
                        $list[$k][name] = 'VIP礼包';
                        $list[$k]['code'] = $v;
                    }
                }else if(substr($v,2)=='04'){
                    $list[$k][name] = '新年礼包';
                    $list[$k]['code'] = $v;
                }else if(substr($v,2)=='05'){
                    $list[$k][name] = '元宵礼包';
                    $list[$k]['code'] = $v;
                }else if(substr($v,2)=='07'){
                    $list[$k][name] = '圣诞礼包';
                    $list[$k]['code'] = $v;
                }else if(substr($v,2)=='08'){
                    if(substr($v,0,1)=='1'){
                        $list[$k][name] = '公众号礼包';
                        $list[$k]['code'] = $v;
                    }else if(substr($v,0,1)=='2' || substr($v,0,1)=='3'){
                        $list[$k][name] = '新年礼包';
                        $list[$k]['code'] = $v;
                    }
                }else if(substr($v,2)=='09'){
                    if(substr($v,0,1)=='1'){
                        $list[$k][name] = '新年礼包';
                        $list[$k]['code'] = $v;
                    }else if(substr($v,0,1)=='2' || substr($v,0,1)=='3'){
                        $list[$k][name] = '元宵礼包';
                        $list[$k]['code'] = $v;
                    }
                }else if(substr($v,2)=='10'){
                    $list[$k][name] = '元宵礼包';
                    $list[$k]['code'] = $v;
                }
            }
        }
        return $list;
    }
    //查询用户&游戏包 充值总额
    public function query_role_order(){
        $_GET['roleid'] = str_replace(' ','',$_GET['roleid']);
        if($_GET['product'] && $_GET['channel']){
            if (!$_GET['start_time'] && !$_GET['roleid']){
                exit(self::error('查询时间和角色roleid必填其中一项！'));
            }
            if ($_GET['excel']){
                $_GET['channel'] = json_decode($_GET['channel']);
            }
            $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
            $date=floor((strtotime($comma_separated[1])-strtotime($comma_separated[0]))/86400);
            $list['roleid'] = $_GET['roleid'];
            foreach (array_fill(0,($date+1),$_GET['channel']) as $k=>$v){
                foreach ($v as $vv){
                    $page_where[]=array(
//                        'platform'=>'wxminigame',
                        'game_id'=>$vv,
                        'game_father_id'=>$_GET['product'],
                        'start_time'=>date('Y-m-d',strtotime($comma_separated[0].' +'.$k.'day')),
                    );
                }
            }
            $data=array();
            for ($i=0;$i<count($page_where);$i++){
                if(count($page_where)<=$i){
                    break;
                }
                $parmas=array(
                    'start_time'=>$page_where[$i]['start_time'],
//                    'platform'=>$page_where[$i]['platform'],
                    'game_id'=>$page_where[$i]['game_id'],
                    'game_father_id'=>$page_where[$i]['game_father_id'],
                );
                $DATA=self::get_order_list($parmas,$list);
                if ($list['roleid']){
                    $data[]=$DATA;
                }else{
                    foreach ($DATA as $vv){
                        $data[] = $vv;
                    }
                }
            }
            //排序金额
            foreach ($data as $k=>$v){
                $money[$k] = $v['money'];
                $create_data[$k] = $v['create_date'];
            }
            array_multisort($create_data , SORT_ASC , $money , SORT_DESC , $data);
            if($_GET['excel']){
                self::query_gameorder_excel($data,$_GET['product']);
            }
            $count      = count($data);// 查询满足要求的总记录数

            $Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->lastSuffix = false;//最后一页不显示为总页数
            $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('last','末页');
            $Page->setConfig('first','首页');
            $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里

            $data=array_slice($data,$Page->firstRow,$Page->listRows);


            self::assign('page',$page_show);
            self::assign('data',$data);

        }
        $product_list=M('game_father','','DB_CONFIG1')->cache(500)->getField('game_father_id,game_father_name');
        self::assign('product_list',$product_list);
        self::display();
    }

//    public function game_order_sum($parma){
//        $data = array(
//            'create_date'=>
//        );
//    }

    public function get_order_list($parmas,$data){
        $product = M('game','','DB_CONFIG1')->where(array('game_id'=>$parmas['game_id']))->cache(500)->find();

        if ($data['roleid']){
            $new_user_pay = M('game_order','','DB_CONFIG1')
                ->where(
                    array(
//                        'platform'=>'wxminigame',
                        'game_id'=>$parmas['game_id'],
                        'game_father_id'=>$parmas['game_father_id'],
                        'status'=>array('egt','1'),
                        'cproleid'=>$data['roleid'],
                        'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                    )
                )
                ->cache(500)
                ->field('sum(money)as new_user_pay')
                ->find();
            $order = M('game_order','','DB_CONFIG1')
                ->where(
                    array(
                        'cproleid'=>$data['roleid'],
                        'game_id'=>$parmas['game_id'],
                        'game_father_id'=>$parmas['game_father_id'],
                        'status'=>array('egt','1'),
                        'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                    )
                )
                ->cache(500)
                ->find();

            $res = array(
                'ext'=>$order['ext']?$order['ext']:'-',
                'cproleid'=>$data['roleid']?$data['roleid']:'-',
                'create_date'=>$parmas['start_time'],
                'money'=>round(($new_user_pay['new_user_pay']/100),2),
            );
        }else{
            $get_roleid = M('game_order','','DB_CONFIG1')
                ->where(
                    array(
                        'game_id'=>$parmas['game_id'],
                        'game_father_id'=>$parmas['game_father_id'],
                        'status'=>array('egt','1'),
                        'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                    )
                )
                ->field('DISTINCT cproleid,ext')
                ->select();
//            print_r($get_roleid);die;
            foreach ($get_roleid as $k=>$v){
                $new_user_pay = M('game_order','','DB_CONFIG1')
                    ->where(
                        array(
                            'cproleid'=>$v['cproleid'],
                            'game_id'=>$parmas['game_id'],
                            'game_father_id'=>$parmas['game_father_id'],
                            'status'=>array('egt','1'),
                            'create_date'=>array(array('egt',strtotime($parmas['start_time'])),array('lt',strtotime($parmas['start_time'].' +1 day')),'and'),
                        )
                    )
                    ->cache(500)
                    ->field('sum(money)as new_user_pay')
                    ->find();

                $res[] = array(
                    'ext'=>$v['ext']?$v['ext']:'-',
                    'cproleid'=>$v['cproleid']?$v['cproleid']:'-',
                    'create_date'=>$parmas['start_time'],
                    'money'=>round(($new_user_pay['new_user_pay']/100),2),
                );
            }
        }
        return $res;
    }
    //导出用户充值金额Excel
    public function query_gameorder_excel($data,$product){
        // require_once  LIB_PATH . 'Org/Util/PHPExcel/PHPExcel.php';
        $game = M('game_father','','DB_CONFIG1')->where(array('game_father_id'=>$product))->find();
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Worksheet.Drawing");
        import("Org.Util.PHPExcel.Writer.Excel2007");
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(8);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);//宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);//宽度

        $objActSheet->setCellValue('A1', '产品');
        $objActSheet->setCellValue('B1', '服务器');
        $objActSheet->setCellValue('C1', '角色ID');
        $objActSheet->setCellValue('D1', '查询时间');
        $objActSheet->setCellValue('E1', '当日累充金额');
        foreach($data as $k=>$val){
            $k +=2;
            $objActSheet->setCellValue('A'.$k, $game['game_father_name']);
            $objActSheet->setCellValue('B'.$k, $val['ext']);
            $objActSheet->setCellValue('C'.$k, ' '.$val['cproleid']);
            $objActSheet->setCellValue('D'.$k, $val['create_date']);
            $objActSheet->setCellValue('E'.$k, $val['money']);
        }
        $fileName = '用户充值表';
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
        $ymdTime = date('Y-m-d',$time);//转换当前时间戳为yyyy-mm-dd
        $gameId = '6';//获取游戏id
        $game_father_id = '3';//游戏大类id

        //24h内登录过的玩家
        $new_user_count_where = array(
            'game_id'=>$gameId,
            'game_father_id'=>$game_father_id,
            'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -1 day')),'and'),
        );
//        $test = M('login_report','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql();
//        print_r($test);die;
        //查询48h内登录过的玩家
//        $user['p_uid'] = M('login_report','','DB_CONFIG1')
//            ->where(array(
//                'game_id'=>$gameId,
//                'game_father_id'=>$game_father_id,
//                'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -2 day')),'and'),
//                'user_id'=>array('exp','not in '.M('login_report','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql()),//排除24h内登录过的玩家
//            ))
//            ->cache(500)
//            ->distinct(true)
//            ->field('p_uid')
//            ->select();
//
//        $user['game_id'] = $gameId;
        $user['p_uid'][0]= 'o0nhp5A9FjGECqaKs00o0xIa-1IM';
        $user['game_id'] = '6';
        $user = json_encode($user);
        $test = self::curl_post('http://api.baizegame.com/Wx_minigame/weixinMsg',$user);
        print_r($test);die;

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

    public function kefu(){
        if($_GET){
            if($_GET['start_time']){
                $comma_separated = explode(" 到 ", urldecode($_GET['start_time']));
                if(self::isDiffDays(getdate($comma_separated[0]),getdate($comma_separated[1]))){
                    $condition['create_date'] = array(array('egt',strtotime($comma_separated['0'])),array('lt',strtotime($comma_separated['1'])),'and');
                }else{
                    $condition['create_date'] = array(array('egt',strtotime($comma_separated['0'])),array('lt',strtotime($comma_separated['1'].' +1 day')),'and');
                }
            }
            if($_GET['user_id']){
                $condition['user_id'] = $_GET['user_id'];
            }
            if($_GET['role_id']){
                $condition['role_id'] = $_GET['role_id'];
            }
            if($_GET['status']){
                if($_GET['status']=='1'){
                    $condition['status'] = array('exp',"is null");
                }else{
                    $condition['status'] = array('eq',1);
                }
            }
            $data = M('kefu_feedback','','DB_CONFIG1')
                ->where($condition)
                ->select();
        }else{
            $data = M('kefu_feedback','','DB_CONFIG1')
                ->select();
        }
        foreach (array_reverse($data) as $k=>$v){
            $game = M('game','','DB_CONFIG1')
                ->where(array(
                    'game_id'=>$v['game_id']
                ))
                ->cache(500)
                ->find();
            $arr[$k] = array(
                'game_name'=>$game['game_name'],
                'create_date'=>date('Y-m-d H:i:s',$v['create_date']),
                'category_name'=>self::getErrorname($v['category_id']),
                'content'=> str_replace(PHP_EOL, '', json_decode($v['content'],true)),
                'image_url'=>$v['image_url']?json_decode($v['image_url'],true):'',
                'contact'=>$v['contact']?$v['contact']:'-',
                'user_id'=>$v['user_id'],
                'role_id'=>$v['role_id']?$v['role_id']:'-',
                'status'=>$v['status'],
                'kefu_reply'=>$v['kefu_reply']? str_replace(array("\r\n", "\r", "\n"), "", json_decode($v['kefu_reply'],true)):'-',
                'role_name'=>$v['role_name']?$v['role_name']:'-',
                'ext'=>$v['ext']?$v['ext']:'-',
                'id'=>$v['id'],
            );
        }
        $count      = count($arr);// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->lastSuffix = false;//最后一页不显示为总页数
        $Page->setConfig('header','<li class="disabled"><a>共<em>%TOTAL_ROW%</em>条  <em>%NOW_PAGE%</em>/%TOTAL_PAGE%页</a></li>');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('last','末页');
        $Page->setConfig('first','首页');
        $Page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $page_show = $Page->bootstrap_page_style($Page->show());//重点在这里
        $arr=array_slice($arr,$Page->firstRow,$Page->listRows);
        self::assign('data',$arr);
        self::assign('page',$page_show);
        self::display();
    }
    public function getErrorname($value){
        $arr = array(
            '1'=>'登录异常',
            '2'=>'活动异常',
            '3'=>'充值异常',
            '4'=>'BUG反馈',
            '5'=>'游戏币/物品丢失',
            '6'=>'闪退/黑屏/白屏/卡顿',
            '7'=>'提示游戏内存不足',
            '8'=>'游戏建议',
            '9'=>'其他'
        );
        return $arr[$value];
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
            'title'=>'反馈图片',
            'id'=>time(),
            'start'=>'0'
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
        $returnArr = array(
            'status'=>'1',
            'msg'=>'success',
            'data'=>$json,
        );
        header('Content-Type:application/json');
        $arrJson = json_encode($returnArr,JSON_UNESCAPED_UNICODE);
        echo $arrJson;

    }
    public function getReply(){
        $id = $_POST['id'];
        $content = str_replace(array("\r\n", "\r", "\n"), "", $_POST['content']);;
        header('Content-Type:application/json');
        if($id){
            $condition = array('id'=>$id);
            $update = array('kefu_reply'=>json_encode($content,true),'kefu_create_date'=>time(),'status'=>'1');
            $res = M('kefu_feedback','','DB_CONFIG1')->where($condition)->save($update);
            if($res=='1'){
                echo json_encode(array('info'=>'1'),JSON_UNESCAPED_UNICODE);
            }else{
                echo json_encode(array('info'=>'2'),JSON_UNESCAPED_UNICODE);

            }
        }
    }

    //判断两天是否是同一天
    public function isDiffDays($last_date,$this_date){

        if(($last_date['year']===$this_date['year'])&&($this_date['yday']===$last_date['yday'])){
            return FALSE;
        }else{
            return TRUE;
        }
    }

    public function illegal_user(){
        $cpList = M('create_role_report','','DB_CONFIG1')->where(array('cproleid'=>$_POST['cproleid']))->cache(500)->find();
        $game = M('game','','DB_CONFIG1')->where(array('game_id'=>$cpList['game_id']))->cache(500)->find();
        //存储封号信息
        $data = array(
            'user_id'=>$cpList['user_id'],
            'p_uid'=>$cpList['p_uid'],
            'platform'=>$cpList['platform'],
            'game_id'=>$game['pay_game_id']?$game['pay_game_id'].','.$cpList['game_id']:$cpList['game_id'],
            'create_date'=>time(),
            'game_father_id'=>$cpList['game_father_id'],
            'performer_admin'=>$_SESSION['admin_info']['admin_user_id'],
            'cproleid'=>$cpList['cproleid'],
            'nickname'=>$cpList['nickname'],
            'status'=>1,
        );
        //查询封号信息
        $condition = array(
            'user_id'=>$cpList['user_id'],
            'p_uid'=>$cpList['p_uid'],
            'platform'=>$cpList['platform'],
            'game_id'=>$game['pay_game_id']?$game['pay_game_id'].','.$cpList['game_id']:$cpList['game_id'],
            'game_father_id'=>$cpList['game_father_id'],
            'status'=>1,
        );
        $requery=M('illegal_user','','DB_CONFIG1')->where($condition)->find();
        header('Content-Type:application/json');
        if (!$requery) {
            M('illegal_user','','DB_CONFIG1')->add($data);
            echo json_encode(array('info'=>'1'),JSON_UNESCAPED_UNICODE);

        } else {
            M('illegal_user','','DB_CONFIG1')->where(array('cproleid'=>$_POST['cproleid']))->delete();
            echo json_encode(array('info'=>'2'),JSON_UNESCAPED_UNICODE);
        }

    }

}