<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_public_api extends CI_Controller
{
  //每小时插入静态数据
  public function every_hour_date($begin_hour, $end_hour)
  {
    $date=$this->input->get('data');
    ($date)?$date:$date = date('Y-m-d', time());

    $_begin_hour = strtotime($date." $begin_hour:0:0");
    $_end_hour = strtotime($date." $end_hour:0:0");
    if ($_begin_hour==$_end_hour) {
      $_end_hour += 86400;
    }
    $this->load->model('Game_order_model');
    $this->load->model('Create_role_report_model');
    $this->load->model('Login_report_model');
    $this->load->model('db/Stage_hour_data_model');
    $this->load->model('db/Game_father_model');
    $this->load->model('db/Platform_model');
    $this->load->model('db/Sign_report_model');
    $games = $this->Game_model->get_by_condition();
    $game_fathers = $this->Game_father_model->get_by_condition();

    foreach ($games as $one) {
      $hour_insert_date = array(
        'platform' => '0',
        'platform_name' => '0',
        'cishu' => '0',
        'renshu' => '0',
        'login' => '0',
        'createrole' => '0',
        'fufeilv' => '0',
        'arpu'=>'0',
        'arpuu'=>'0',
        'sign_report' => '0',
        'create_date' => '0',
        'money' => '0',
        'begin_time' => '0',
        'end_time' => '0',
        'new_pay_users' => '0',
        'new_user_create_role' => '0',
        'new_pay_money' => '0',
        'game_father_id'=>'0',
      );
      $platform_condition = array(
        'platform'=>$one->platform,
      );
      $_req = $this->Platform_model->get_one_by_condition($platform_condition);
      $hour_insert_date['platform']=$one->platform;
      $hour_insert_date['platform_name']=$_req->platform_chinese;
      $hour_insert_date['game_father_id']=$one->game_father_id;
      $hour_insert_date['begin_time'] = $date." $begin_hour:0:0";
      $hour_insert_date['end_time'] = $date." $end_hour:0:0";
      $hour_insert_date['create_date']=$_begin_hour;
      $login_info_condition = array(
        'create_date >= ' => $_begin_hour,
        'create_date <= ' => $_end_hour,
        'platform '=>$one->platform,
        'game_father_id'=>$one->game_father_id
      );
      //统计登录
      $this->db->select('count(DISTINCT(user_id)) as login_count ,  platform ,game_father_id');
      $login_reqery = $this->Login_report_model->get_one_by_condition($login_info_condition);
      if ($login_reqery->login_count) {
        $hour_insert_date['login']=$login_reqery->login_count;
      }
      //统计创角
      $this->db->select('count(DISTINCT(user_id)) as create_role_count ,  platform ,game_father_id');
      $create_role_requery = $this->Create_role_report_model->get_one_by_condition($login_info_condition);
      if ($create_role_requery->create_role_count) {
        $hour_insert_date['createrole']=$create_role_requery->create_role_count;
      }

       //统计新增
       $this->db->select('count((user_id)) as sign_report ,  platform ,game_father_id');
       $sign_role_requery = $this->Sign_report_model->get_one_by_condition($login_info_condition);
       if ($sign_role_requery->sign_report) {
           $hour_insert_date['sign_report']=$sign_role_requery->sign_report;
       }
      $order_info_condition = array(
        'create_date >= ' => $_begin_hour,
        'create_date <= ' => $_end_hour,
        'platform '=>$one->platform,
        'game_father_id'=>$one->game_father_id,
        'status'=>2
      );
      $this->db->select('platform,count(DISTINCT(`user_id`)) as renshu ,sum(`money`) as money ,count(`order_id`) as cishu ');
      $game_order_requery = $this->Game_order_model->get_one_by_condition($order_info_condition);


      // 新增用户付费金额、人数
      $this->db->select(' sum(`money`) as money ,count(DISTINCT(`game_order`.`user_id`)) as xinfufeirenshu');
      $_join_on_str = "sign_report.user_id = game_order.user_id  and game_order.status = 2 and game_order.create_date >= '$_begin_hour' and game_order.create_date <= '$_end_hour' and game_order.game_father_id = '$one->game_father_id' and game_order.platform = '$one->platform' ";
      //将 signreport userid 去重
      $_join_table_str = "( select DISTINCT(`user_id`) as `user_id` from sign_report where sign_report.create_date >= '$_begin_hour' and sign_report.create_date <= '$_end_hour' and game_father_id = '$one->game_father_id' and platform = '$one->platform' ) as sign_report";
      $this->db->join($_join_table_str, $_join_on_str, "INNER");
      $new_user_pay_sum = $this->Game_order_model->get_one_by_condition("");


      // 新增用户创角
      $this->db->select('COUNT(DISTINCT(`cproleid`)) as xinchuangjue ');
      $__join_on_str = "sign_report.user_id = create_role_report.user_id and create_role_report.create_date >= '$_begin_hour' and create_role_report.create_date <= '$_end_hour' and create_role_report.game_father_id = '$one->game_father_id' and create_role_report.platform = '$one->platform' ";
      $__join_table_str = "( select DISTINCT(`user_id`) as `user_id` from sign_report where sign_report.create_date >= '$_begin_hour' and sign_report.create_date <= '$_end_hour' and game_father_id = '$one->game_father_id' and platform = '$one->platform' ) as sign_report";
      $this->db->join($__join_table_str, $__join_on_str, 'INNER');
      $new_user_create_role_sum = $this->Create_role_report_model->get_one_by_condition("");



      //数据整合
      if($new_user_pay_sum->money&&$new_user_pay_sum->xinfufeirenshu){
          $hour_insert_date['new_pay_money']=$new_user_pay_sum->money;
          $hour_insert_date['new_pay_users']=$new_user_pay_sum->xinfufeirenshu;
      }

      ($new_user_create_role_sum->xinchuangjue)?$hour_insert_date['new_user_create_role']=$new_user_create_role_sum->xinchuangjue:'';

      if ($game_order_requery->cishu &&$game_order_requery->renshu &&$game_order_requery->money) {
        $hour_insert_date['cishu']=$game_order_requery->cishu;
        $hour_insert_date['renshu']=$game_order_requery->renshu;
        $hour_insert_date['money']=$game_order_requery->money;
      }
      if ($hour_insert_date['login']&&$hour_insert_date['renshu']&&$hour_insert_date['money']) {
        $hour_insert_date['fufeilv'] = floatval(number_format(floatval($hour_insert_date['renshu'])/floatval($hour_insert_date['login']), 2));
        $hour_insert_date['arpu'] = floatval(number_format(floatval($hour_insert_date['money'])/floatval($hour_insert_date['login'])/100, 2));
        $hour_insert_date['arpuu'] = floatval($hour_insert_date['money']/$hour_insert_date['renshu']/100);
      }

      //check info
      $check_info_condition = array(
        'platform'=>$hour_insert_date['platform'],
        'begin_time'=>$hour_insert_date['begin_time'],
        'game_father_id'=>$hour_insert_date['game_father_id'],
      );

      $check_requery = $this->Stage_hour_data_model->get_one_by_condition($check_info_condition);
      if ($check_requery) {
        $response = $this->Stage_hour_data_model->update($hour_insert_date, $check_info_condition);
        echo 'update '.$response;
      } else {
        $response = $this->Stage_hour_data_model->add($hour_insert_date);
        echo 'add '.$response;
      }

      // echo $login_reqery->login_count.'   '.$login_reqery->platform.' '.$login_reqery->game_father_id;
      // $response = $this->Stage_hour_data_model->add($hour_insert_date);
      // if ($response) {
      //     echo $hour_insert_date['platform'].'  '.$hour_insert_date['game_father_id'].' ok';
      // } else {
      //     echo json_encode($hour_insert_date);
      // }

      echo '<br>';
    }
  }
  //每月首日获取所有渠道月总计数据接口
  public function month_data_insert()
  {
    $platforms = $this->Game_model->get_by_condition();
    foreach ($platforms as $one) {
      $request = "http://".$_SERVER['HTTP_HOST']."/index.php/Admin_public_api/insert_info_by_mounth/$one->platform?game_father_id=$one->game_father_id";
      if($this->input->get('month')){
        $request = $request."&month=".$this->input->get('month');
        echo $request.PHP_EOL;
      }else{
        echo $request.PHP_EOL;
      }
    }
    // $this->insert_info_by_mounth();
  }

  //every month 1th insert info
  public function insert_info_by_mounth($platform_en_name=null)
  {
    $this->load->model('Fake_data_model');
    $game_father_id =$this->input->get('game_father_id');
    if (!$game_father_id) {
      exit("no game father id ");
    }
    $condition = array(
      'status' => 2,
    );
    $where_in = array();
    $this->load->model('Create_role_report_model');
    $this->load->model('Game_order_model');
    $this->load->model('Sign_report_model');
    $this->load->model('Login_report_model');
    $this->load->model('db/Platform_model');
    $this->load->model('db/Game_father_model');
    $this->load->model('Fake_model');
    $this->load->model('Test_month_data_model');
    $this->load->model('Month_data_model');
    $scale=1;
    $platforms = $this->Platform_model->get_by_condition();
    $game_faters = $this->Game_father_model->get_by_condition();
    $platform_info_by_day=array();
    //查询订单详情
    // $done_time = $current_date;
    $index=1;
    $to = $this->input->get('to');
    $year = date('y', time());
    $mounth = date('m', time())-1;
    if ($mounth==0) {
      $mounth=12;
      $year-=1;
    }
    if($this->input->get('month')){
      $mounth = $this->input->get('month');
    }
    $date = $year.'-'.$mounth.'-'.'1';
    $current_date = $this->str_to_zero_time($date);
    $big_mounth = array(1,3,5,7,8,10,12);
    $small_mounth=array(4,6,9,11);
    if (in_array($mounth, $big_mounth)) {
      $next_date = $current_date + 2678400;
    } elseif (in_array($mounth, $small_mounth)) {
      $next_date = $current_date + 2592000;
    } elseif ($mounth==2) {
      $next_date = $current_date + 2419200;
    } else {
      $this->Output_model->json_print(1, 'not much mounth');
    };

    // foreach ($platforms as $one) {
    // $mounth = date('m', $current_date);
    // $platform=$one->platform;
    // $platform=$this->input->get('platform');
    if ($platform_en_name) {
      $platform=$platform_en_name;
    }

    $check_info_conditions=array(
      'platform_name'=>$platform,
      'create_date'=>$next_date
    );
    // if ($this->Test_month_data_model->get_one_by_condition($check_info_conditions)) {
    //     continue;
    // }

    $done_time=$next_date;
    $select = 'platform,count(DISTINCT(`user_id`)) as renshu ,sum(`money`) as money ,count(`order_id`) as cishu ';
    $loginselect = 'count(DISTINCT(`user_id`)) as login ';
    $createroleselect = 'count(DISTINCT(`user_id`)) as createrole ';
    // $done_time +=86400;
    $login_create_condition=array(
      'create_date >= '=>$current_date,
      'create_date <= '=>$done_time,
      'game_father_id'=>$game_father_id,
      'platform' => $platform,
    );
    $condition['create_date >= ']=$current_date;
    $condition['create_date <= ']=$done_time;
    $condition['status ']=2;
    $condition['platform']=$platform;
    $condition['game_father_id']=$game_father_id;
    // $this->Create_role_report_model->set_table='create_role_report';
    $create_role = $this->Create_role_report_model->get_createrole_info($createroleselect, $login_create_condition, null, null, null, null, null, null);
    // echo $this->db->last_query();
    // echo '<br>';
    $login = $this->Login_report_model->get_loginreport_info($loginselect, $login_create_condition, null, null, null, null, null, null);
    $order = $this->Game_order_model->get_order_info($select, $condition, null, null, null, null, null, null);
    //统计充值次数数组声明
    $platform_order_count_array=array();
    //统计充值人数数组声明
    if (!$order) {
      $platform_order_count_array[$platform]['cishu'] =0;
      $platform_order_count_array[$platform]['renshu'] =0;
      $platform_order_count_array[$platform]['total'] =0;
      $platform_order_count_array[$platform]['platform_name'] =$platform;
      $platform_order_count_array[$platform]['begin'] =date('Y-m-d', $current_date);
      $platform_order_count_array[$platform]['to'] =date('Y-m-d', $done_time);
      $platform_order_count_array[$platform]['login']=$login['0']->login;
      $platform_order_count_array[$platform]['createrole']=$create_role['0']->createrole;
      $platform_order_count_array[$platform]['arppu'] =0;
      $platform_order_count_array[$platform]['fufeilv'] =0;
    } else {
      //统计充值次数
      $platform_order_count_array[$platform]['cishu']=$order[0]->cishu;

      //统计充值人数
      $platform_order_count_array[$platform]['renshu'] =$order[0]->renshu;

      //统计充值总额
      $platform_order_count_array[$platform]['total']=$order[0]->money/100 * $scale;
      //添加渠道名
      if (!isset($platform_order_count_array[$platform]['platform_name'])) {
        $platform_order_count_array[$platform]['platform_name']=$platform;
      }
      //添加开始时间
      if (!isset($platform_order_count_array[$platform]['begin'])) {
        $platform_order_count_array[$platform]['begin']=date('Y-m-d', $current_date);
      }
      //添加结束时间
      // if (!isset($platform_order_count_array[$one->platform]['to'])&&isset($done_time)) {
      //     $platform_order_count_array[$one->platform]['to']=date('Y-m-d', $done_time);
      // }
      $platform_order_count_array[$platform]['login']=$login['0']->login * $scale;
      $platform_order_count_array[$platform]['createrole']=$create_role['0']->createrole * $scale;

      if ($login['0']->login==0) {
        $login['0']->login=1;
      }
      $platform_order_count_array[$platform]['fufeilv']=($platform_order_count_array[$platform]['renshu']/$login['0']->login)*100;
    }

    $platform_order_count_array[$platform]['cishu']=$platform_order_count_array[$platform]['cishu']*$scale;
    $platform_order_count_array[$platform]['renshu']=$platform_order_count_array[$platform]['renshu']*$scale;
    $platform_info_by_day[$index]=$platform_order_count_array[$platform];
    // $current_date = $done_time;
    $insert_info = array(
      'platform_name' =>$platform_info_by_day[$index]['platform_name'],
      'cishu' =>$platform_info_by_day[$index]['cishu'],
      'renshu' =>$platform_info_by_day[$index]['renshu'],
      'login' =>$platform_info_by_day[$index]['login'],
      'createrole' =>$platform_info_by_day[$index]['createrole'],
      'fufeilv' =>$platform_info_by_day[$index]['fufeilv'],
      'money' =>$platform_info_by_day[$index]['total'],
      'create_date' =>$this->str_to_zero_time($platform_info_by_day[$index]['begin']),
      'date_time' =>$platform_info_by_day[$index]['begin'],
      'game_father_id' =>$game_father_id,
    );
    // echo json_encode($insert_info);
    $check_info_condition = array(
      'platform_name'=>$platform_info_by_day[$index]['platform_name'],
      'create_date' =>$this->str_to_zero_time($platform_info_by_day[$index]['begin']),
      'game_father_id' =>$game_father_id,
    );
    $check_exist = $this->Month_data_model->get_one_by_condition($check_info_condition);
    if ($check_exist) {
      $_where=array(
        "mouth_data_id"=>$check_exist->mouth_data_id,
      );
      echo 'update: '.$this->Month_data_model->update($insert_info,$_where).PHP_EOL;
      // continue;
    } else {
      echo 'add: '.$this->Month_data_model->add($insert_info).PHP_EOL;
    }
    // }


    ++$index;
  }
  // date string to 00:00:00 of the give day
  public function str_to_zero_time($str)
  {
    $current_date = strtotime($str);
    if (!$current_date) {
      return false;
    }

    $current_date_str = date('Y-m-d', $current_date);
    $current_date = strtotime($current_date_str);
    // return $current_date-21600;
    return $current_date;
  }

  public function baqi_check_zhanli($user_id, $sid)
  {
    $key = '087yx_lc68952685';
    $this->load->model('User_model');
    $condition = array(
      'p_uid'=>$user_id,
      'platform'=>'baqi',
    );
    $user_data = $this->User_model->get_by_condition($condition)[0];
    echo $this->db->last_query();
    echo json_encode($user_data);
    $uid = $user_data->user_id;
    $sign = md5("$uid$sid$key");

    $url = "http://lcby.gz.1251208707.clb.myqcloud.com/juhe/api?m=player&fn=getjuheuserinfo&uid=$uid&sid=$sid&sign=$sign";

    echo $url;
    //header("Location:$url");
  }
  public function get_xiyou_zhuce_api($server_id){
    $this->load->model("Create_role_report_model");
    $this->load->model('Game_order_model');
    $condition = array(
        'game_father_id' => 20017,
        "server_id" => $server_id
    );
    $this->db->select(' count(cproleid) as c ');
    $response = $this->Create_role_report_model->get_one_by_condition($condition);;
    $this->db->select(' sum(money) as m ');
    $_condition = array(
        'game_father_id'=>20017,
        'status'=>2,
        'ext'=>$server_id
    );

    $money = $this->Game_order_model->get_one_by_condition($_condition);
    if($response && $money){
        echo '创角： '.$response->c.'   总付费额: '.$money->m/100;
    }else{
        echo 0;
    }
  }
}
