<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Test_report extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $role = $this->session->userdata('role')->admin_user_role;
        if ($role) {
            if ($role == 'admin'||$role=='customerService') {
            } else {
                $this->Output_model->json_print(-1, 'no authority');
                exit;
            }
        } else {
            exit;
        }
    }
    public function test()
    {
        $this->load->model('Game_order_model');
        $result = $this->Game_order_model->report();
        if ($result) {
            $content = "订单号,钱(元),用户ID,渠道,时间\n";
            foreach ($result as $order) {
                $content .= $order->u_order_id;
                $content .=  ",";
                $content .=  ($order->money/100);
                $content .=  ",";
                $content .=  $order->user_id;
                $content .=  ",";
                $content .=  $order->platform;
                $content .=  ",";
                $content .=  date("Y-m-d H:i:s", $order->create_date);
                $content .=  "\n";
            }
            file_put_contents('./debug/report.csv', $content);
            header("Location: /debug/report.csv");
        } else {
            echo 'no';
        }
    }

    //通过创角信息统计
    public function info_tongji()
    {
        //查询平台start
        $this->load->model('db/Platform_model');
        //$condition = array(1 => 1);
        $platform = $this->Platform_model->get_by_condition(null);



        if ($platform) {
            $data['platform_info'] = $platform;
        } else {
            $data['platform_info'] = array();
        }
        //查询平台end

        //接收数据开始查询start
        $start = $this->input->get('start');
        $end = $this->input->get('end');
        $platform = $this->input->get('platform');
        $server_id = $this->input->get('server_id');
        $data['start'] = $start;
        $data['end'] = $end;
        $data['platform'] = $platform;
        $data['server_id'] = $server_id;
        $flag = true;
        if (!$start) {
            $flag = false;
        }

        $start_stamp = strtotime($start);
        if (!$start_stamp) {
            $flag = false;
        }
        if ($end) {
            $end_stamp = strtotime($end);
            if (!$end_stamp) {
                $flag = false;
            }
        }
        //条件信息满足开始查找
        if ($flag) {
            if (!$end) {
                $temp_end = $start_stamp+3600*24-1;
                $this->load->model('Create_role_report_model');
                $condition = array(
                    'create_date >= '=>$start_stamp,
                    'create_date <= '=>$temp_end,
                );
                if ($platform) {
                    $condition['platform']=$platform;
                }
                if ($server_id) {
                    $condition['server_id']=$server_id;
                }
                $query_create_role = $this->Create_role_report_model->get_by_condition($condition, null, null, null, null, null, null);
                if ($query_create_role) {
                    $data['info_is_show'] = "show";
                    $create_res = $query_create_role;//创角信息汇总
                    $data['create_info'] = $create_res;
                    $data['create_num'] = count($create_res);


                    //查找该时间范围内支付信息 start
                    $this->load->model('Game_order_model');
                    $condition= array(
                        'create_date >=' => $start_stamp,
                        'create_date <=' => $temp_end,
                    );
                    if ($platform) {
                        $condition['platform']=$platform;
                    }
                    $query_game_order = $this->Game_order_model->get_by_condition($condition, null, null, null, null, null, null);
                    if ($query_game_order) {
                        $game_order_res =$query_game_order;//支付信息汇总
                        $data['game_order_res'] = $game_order_res;
                    } else {
                        $data['game_order_res'] = false;
                    }
                    //查找该时间范围内支付信息 end

                    //查找该时间范围内 注册时间 start
                    $create_ids_arr = "";
                    $index = 0;
                    foreach ($create_res as $one) {
                        if (!$index) {
                            $create_ids_arr.= $one->user_id;
                        }
                        $create_ids_arr.= ",".$one->user_id;
                        $index++;
                    }
                    $this->load->model('Sign_report_model');
                    $where_in = array(
                        'name'=>'user_id',
                        'values'=>$create_ids_arr,
                    );
                    $query_sign=$this->Sign_report_model->get_by_condition(null, null, null, null, null, null, $where_in);
                    if ($query_sign) {
                        $sign_res = $query_sign;//支付信息汇总
                        $data['sign_res'] = $sign_res;
                    }

                    //查找该时间范围内 注册时间 end

                    //查找时间范围内 登录信息 start
                    /* $sql_login = "select user_id,create_date from login_report where user_id in (".$create_ids_arr.") order by create_date desc";
                    $query_login = $this->db->query($sql_login);
                    if ($query_login->num_rows() > 0) {
                        $login_res = $query_login->result();//支付信息汇总
                        $data['login_res'] = $login_res;
                    } */
                    //查找时间范围内 登录信息 end
                } else {
                    $data['create_error_info'] = "无用户创角";
                }
            } else {
                $temp_end = $end_stamp+3600*24-1;
                $this->load->model('Create_role_report_model');
                $condition=array(
                    'create_date >= ' => $start_stamp,
                    'create_date <= ' => $temp_end,
                );
                if ($platform) {
                    $condition['platform'] = $platform;
                }
                if ($server_id) {
                    $condition['server_id'] = $server_id;
                }

                $query_create_role = $this->Create_role_report_model->get_by_condition($condition, null, null, null, null, null, null);
                if ($query_create_role) {
                    $data['info_is_show'] = "show";
                    $create_res = $query_create_role;//创角信息汇总
                    $data['create_info'] = $create_res;
                    $data['create_num'] = count($create_res);

                    //查找该时间范围内支付信息 start
                    $this->load->model('Game_order_model');
                    $query_game_order_condition=array(
                        'create_date >= '=>$start_stamp,
                        'create_date <=' => $temp_end,
                    );
                    if ($platform) {
                        $query_game_order_condition['platform'] =$platform;
                    }
                    $query_game_order = $this->Game_order_model->get_by_condition($query_game_order_condition, null, null, null, null, null, null);
                    if ($query_game_order) {
                        $game_order_res = $query_game_order;//支付信息汇总
                        $data['game_order_res'] = $game_order_res;
                    } else {
                        $data['game_order_res'] = false;
                    }
                    //查找该时间范围内支付信息 end

                    //查找该时间范围内 注册时间 start
                    $create_ids_arr = "";
                    $index = 0;
                    foreach ($create_res as $one) {
                        if (!$index) {
                            $create_ids_arr.= $one->user_id;
                        }
                        $create_ids_arr.= ",".$one->user_id;
                        $index++;
                    }
                    $this->load->model('Sign_report_model');
                    $where_in = array(
                        'name'=>'user_id',
                        'values'=>$create_ids_arr,
                    );
                    $query_sign=$this->Sign_report_model->get_by_condition(null, null, null, null, null, null, $where_in);
                    if ($query_sign) {
                        $sign_res = $query_sign;//支付信息汇总
                        $data['sign_res'] = $sign_res;
                    }
                    //查找该时间范围内 注册时间 end
                    //查找时间范围内 登录信息 start
                    /* $sql_login = "select user_id,create_date from login_report where user_id in (".$create_ids_arr.") order by create_date desc";
                    $query_login = $this->db->query($sql_login);
                    if ($query_login->num_rows() > 0) {
                        $login_res = $query_login->result();//支付信息汇总
                        $data['login_res'] = $login_res;
                    } */
                    //查找时间范围内 登录信息 end
                } else {
                    $data['create_error_info'] = "无用户创角";
                }
            }
        }

        //接收数据开始查询end

        $this->load->view("admin/info_tongji/info_tongji", $data);
    }

    public function info_tongji_new()
    {
        //查询平台start
        $this->load->model('db/Platform_model');
        //$condition = array(1 => 1);
        $platform = $this->Platform_model->get_by_condition(null);



        if ($platform) {
            $data['platform_info'] = $platform;
        } else {
            $data['platform_info'] = array();
        }
        //查询平台end

        //接收数据开始查询start
        $start = $this->input->get('start');
        $end = $this->input->get('end');
        $platform = $this->input->get('platform');
        $server_id = $this->input->get('server_id');
        $data['start'] = $start;
        $data['end'] = $end;
        $data['platform'] = $platform;
        $data['server_id'] = $server_id;
        $flag = true;
        if (!$start) {
            $flag = false;
        }

        $start_stamp = strtotime($start);
        if (!$start_stamp) {
            $flag = false;
        }
        if ($end) {
            $end_stamp = strtotime($end);
            if (!$end_stamp) {
                $flag = false;
            }
        }
        //条件信息满足开始查找
        if ($flag) {
            if (!$end) {
                $temp_end = $start_stamp+3600*24-1;
                $this->load->model('Create_role_report_model');
                $condition = array(
                    'create_date >= '=>$start_stamp,
                    'create_date <= '=>$temp_end,
                );
                if ($platform) {
                    $condition['platform']=$platform;
                }
                if ($server_id) {
                    $condition['server_id']=$server_id;
                }
                $query_create_role = $this->Create_role_report_model->get_by_condition($condition, null, null, null, null, null, null);
                if ($query_create_role) {
                    $data['info_is_show'] = "show";
                    $create_res = $query_create_role;//创角信息汇总
                    $data['create_info'] = $create_res;
                    $data['create_num'] = count($create_res);


                    //查找该时间范围内支付信息 start
                    $this->load->model('Game_order_model');
                    $condition= array(
                        'create_date >=' => $start_stamp,
                        'create_date <=' => $temp_end,
                    );
                    if ($platform) {
                        $condition['platform']=$platform;
                    }
                    $query_game_order = $this->Game_order_model->get_by_condition($condition, null, null, null, null, null, null);
                    if ($query_game_order) {
                        $game_order_res =$query_game_order;//支付信息汇总
                        $data['game_order_res'] = $game_order_res;
                    } else {
                        $data['game_order_res'] = false;
                    }
                    //查找该时间范围内支付信息 end

                    //查找该时间范围内 注册时间 start
                    $create_ids_arr = "";
                    $index = 0;
                    foreach ($create_res as $one) {
                        if (!$index) {
                            $create_ids_arr.= $one->user_id;
                        }
                        $create_ids_arr.= ",".$one->user_id;
                        $index++;
                    }
                    $this->load->model('Sign_report_model');
                    $where_in = array(
                        'name'=>'user_id',
                        'values'=>$create_ids_arr,
                    );
                    $query_sign=$this->Sign_report_model->get_by_condition(null, null, null, null, null, null, $where_in);
                    if ($query_sign) {
                        $sign_res = $query_sign;//支付信息汇总
                        $data['sign_res'] = $sign_res;
                    }

                    //查找该时间范围内 注册时间 end

                    //查找时间范围内 登录信息 start
                    /* $sql_login = "select user_id,create_date from login_report where user_id in (".$create_ids_arr.") order by create_date desc";
                    $query_login = $this->db->query($sql_login);
                    if ($query_login->num_rows() > 0) {
                        $login_res = $query_login->result();//支付信息汇总
                        $data['login_res'] = $login_res;
                    } */
                    //查找时间范围内 登录信息 end
                } else {
                    $data['create_error_info'] = "无用户创角";
                }
            } else {
                $temp_end = $end_stamp+3600*24-1;
                $this->load->model('Create_role_report_model');
                $condition=array(
                    'create_date >= ' => $start_stamp,
                    'create_date <= ' => $temp_end,
                );
                if ($platform) {
                    $condition['platform'] = $platform;
                }
                if ($server_id) {
                    $condition['server_id'] = $server_id;
                }

                $query_create_role = $this->Create_role_report_model->get_by_condition($condition, null, null, null, null, null, null);
                if ($query_create_role) {
                    $data['info_is_show'] = "show";
                    $create_res = $query_create_role;//创角信息汇总
                    $data['create_info'] = $create_res;
                    $data['create_num'] = count($create_res);

                    //查找该时间范围内支付信息 start
                    $this->load->model('Game_order_model');
                    $query_game_order_condition=array(
                        'create_date >= '=>$start_stamp,
                        'create_date <=' => $temp_end,
                    );
                    if ($platform) {
                        $query_game_order_condition['platform'] =$platform;
                    }
                    $query_game_order = $this->Game_order_model->get_by_condition($query_game_order_condition, null, null, null, null, null, null);
                    if ($query_game_order) {
                        $game_order_res = $query_game_order;//支付信息汇总
                        $data['game_order_res'] = $game_order_res;
                    } else {
                        $data['game_order_res'] = false;
                    }
                    //查找该时间范围内支付信息 end

                    //查找该时间范围内 注册时间 start
                    $create_ids_arr = "";
                    $index = 0;
                    foreach ($create_res as $one) {
                        if (!$index) {
                            $create_ids_arr.= $one->user_id;
                        }
                        $create_ids_arr.= ",".$one->user_id;
                        $index++;
                    }
                    $this->load->model('Sign_report_model');
                    $where_in = array(
                        'name'=>'user_id',
                        'values'=>$create_ids_arr,
                    );
                    $query_sign=$this->Sign_report_model->get_by_condition(null, null, null, null, null, null, $where_in);
                    if ($query_sign) {
                        $sign_res = $query_sign;//支付信息汇总
                        $data['sign_res'] = $sign_res;
                    }
                    //查找该时间范围内 注册时间 end
                    //查找时间范围内 登录信息 start
                    /* $sql_login = "select user_id,create_date from login_report where user_id in (".$create_ids_arr.") order by create_date desc";
                    $query_login = $this->db->query($sql_login);
                    if ($query_login->num_rows() > 0) {
                        $login_res = $query_login->result();//支付信息汇总
                        $data['login_res'] = $login_res;
                    } */
                    //查找时间范围内 登录信息 end
                } else {
                    $data['create_error_info'] = "无用户创角";
                }
            }
        }

        //接收数据开始查询end

        $this->load->view("admin/info_tongji/info_tongji_new", $data);
    }

    public function sign_tongji()
    {
        //查询平台start
        $this->load->model('db/Platform_model');
        //$condition = array(1 => 1);
        $platform = $this->Platform_model->get_by_condition(null);
        if ($platform) {
            $data['platform_info'] = $platform;
        } else {
            $data['platform_info'] = array();
        }
        //查询平台end

        $start = $this->input->get('start');
        $end = $this->input->get('end');
        $platform = $this->input->get('platform');
        $server_id = $this->input->get('server_id');
        $data['start'] = $start;
        $data['end'] = $end;
        $data['platform'] = $platform;
        $data['server_id'] = $server_id;
        $flag = true;
        if (!$start) {
            $flag = false;
        }

        $start_stamp = strtotime($start);
        if (!$start_stamp) {
            $flag = false;
        }
        if ($end) {
            $end_stamp = strtotime($end);
            if (!$end_stamp) {
                $flag = false;
            }
        }

        if ($flag) {
            if (!$end) {
                $temp_end = $start_stamp+3600*24-1;
                $this->load->model('Sign_report_model');
                $condition=array(
                    'create_date >= ' =>$start_stamp,
                    'create_date <= '=>$temp_end,
                );
                if ($platform) {
                    $condition['platform'] = $platform;
                }
                if ($server_id) {
                    $condition['server_id'] = $server_id;
                }
                $query_sign = $this->Sign_report_model->get_by_condition($condition, null, null, null, null, null, null);
                if ($query_sign) {
                    $data['info_is_show'] = "show";
                    $sign_res = $query_sign;//创角信息汇总
                    $data['sign_res'] = $sign_res;
                    $data['sign_res_num'] = count($sign_res);
                    $this->load->model('Create_role_report_model');
                    $condition=array(
                        'create_date >=' =>$start_stamp,
                        'create_date <='=>$temp_end,
                    );
                    if ($platform) {
                        $condition['platform'] = $platform;
                    }
                    if ($server_id) {
                        $condition['server_id'] = $server_id;
                    }
                    $query_create_role = $this->Create_role_report_model->get_by_condition($condition, null, null, null, null, null, null);
                    if ($query_create_role) {
                        $create_role_res = $query_create_role;//创角信息汇总
                        $data['create_role_res'] = $create_role_res;
                    } else {
                        $data['create_role_res'] = false;
                    }

                    //查找该时间范围内支付信息 start
                    $this->load->model('Game_order_model');
                    $condition=array(
                        'create_date >=' => $start_stamp,
                        'create_date <=' => $temp_end,
                    );
                    if ($platform) {
                        $condition['platform'] = $platform;
                    }
                    $query_game_order = $this->Game_order_model->get_by_condition($condition, null, null, null, null, null, null);
                    if ($query_game_order) {
                        $game_order_res = $query_game_order;//支付信息汇总
                        $data['game_order_res'] = $game_order_res;
                    } else {
                        $data['game_order_res'] = false;
                    }
                    //查找该时间范围内支付信息 end
                } else {
                    $data['create_error_info'] = "无用户";
                }
            } else {
                $temp_end = $end_stamp+3600*24-1;
                $this->load->model('Sign_report_model');
                $condition=array(
                    'create_date >=' => $start_stamp,
                    'create_date <=' => $temp_end,
                );
                if ($platform) {
                    $condition['platform'] = $platform;
                }
                if ($server_id) {
                    $condition['server_id'] = $server_id;
                }
                $query_sign = $this->Sign_report_model->get_by_condition($condition, null, null, null, null, null, null);
                if ($query_sign) {
                    $data['info_is_show'] = "show";
                    $sign_res = $query_sign;//创角信息汇总
                    $data['sign_res'] = $sign_res;
                    $data['sign_res_num'] = count($sign_res);
                    $this->load->model('Create_role_report_model');
                    $condition=array(
                        'create_date >= '=> $start_stamp,
                        'create_date <= '=> $temp_end,
                    );
                    if ($platform) {
                        $condition['platform']=$platform;
                    }
                    if ($server_id) {
                        $condition['server_id']=$server_id;
                    }
                    $query_create_role = $this->Create_role_report_model->get_by_condition($condition, null, null, null, null, null, null);
                    if ($query_create_role) {
                        $create_role_res = $query_create_role;//创角信息汇总
                        $data['create_role_res'] = $create_role_res;
                    } else {
                        $data['create_role_res'] = false;
                    }

                    //查找该时间范围内支付信息 start
                    $this->load->model('Game_order_model');
                    $condition=array(
                        'create_date >='=>$start_stamp,
                        'create_date <=' => $temp_end,
                    );
                    if ($platform) {
                        $condition['platform'] = $platform;
                    }
                    $query_game_order = $this->Game_order_model->get_by_condition($condition, null, null, null, null, null, null);
                    if ($query_game_order) {
                        $game_order_res = $query_game_order;//支付信息汇总
                        $data['game_order_res'] = $game_order_res;
                    } else {
                        $data['game_order_res'] = false;
                    }
                    //查找该时间范围内支付信息 end
                } else {
                    $data['create_error_info'] = "无用户";
                }
            }
        }

        $this->load->view("admin/info_tongji/info_tongji_sign", $data);
    }

    public function sign_tongji_new()
    {
        //查询平台start
        $this->load->model('db/Platform_model');
        //$condition = array(1 => 1);
        $platform = $this->Platform_model->get_by_condition(null);
        if ($platform) {
            $data['platform_info'] = $platform;
        } else {
            $data['platform_info'] = array();
        }
        //查询平台end

        $start = $this->input->get('start');
        $end = $this->input->get('end');
        $platform = $this->input->get('platform');
        $server_id = $this->input->get('server_id');
        $data['start'] = $start;
        $data['end'] = $end;
        $data['platform'] = $platform;
        $data['server_id'] = $server_id;
        $flag = true;
        if (!$start) {
            $flag = false;
        }

        $start_stamp = strtotime($start);
        if (!$start_stamp) {
            $flag = false;
        }
        if ($end) {
            $end_stamp = strtotime($end);
            if (!$end_stamp) {
                $flag = false;
            }
        }

        if ($flag) {
            if (!$end) {
                $temp_end = $start_stamp+3600*24-1;
                $this->load->model('Sign_report_model');
                $condition=array(
                    'create_date >= ' =>$start_stamp,
                    'create_date <= '=>$temp_end,
                );
                if ($platform) {
                    $condition['platform'] = $platform;
                }
                if ($server_id) {
                    $condition['server_id'] = $server_id;
                }
                $query_sign = $this->Sign_report_model->get_by_condition($condition, null, null, null, null, null, null);
                if ($query_sign) {
                    $data['info_is_show'] = "show";
                    $sign_res = $query_sign;//创角信息汇总
                    $data['sign_res'] = $sign_res;
                    $data['sign_res_num'] = count($sign_res);
                    $this->load->model('Create_role_report_model');
                    $condition=array(
                        'create_date >=' =>$start_stamp,
                        'create_date <='=>$temp_end,
                    );
                    if ($platform) {
                        $condition['platform'] = $platform;
                    }
                    if ($server_id) {
                        $condition['server_id'] = $server_id;
                    }
                    $query_create_role = $this->Create_role_report_model->get_by_condition($condition, null, null, null, null, null, null);
                    if ($query_create_role) {
                        $create_role_res = $query_create_role;//创角信息汇总
                        $data['create_role_res'] = $create_role_res;
                    } else {
                        $data['create_role_res'] = false;
                    }

                    //查找该时间范围内支付信息 start
                    $this->load->model('Game_order_model');
                    $condition=array(
                        'create_date >=' => $start_stamp,
                        'create_date <=' => $temp_end,
                    );
                    if ($platform) {
                        $condition['platform'] = $platform;
                    }
                    $query_game_order = $this->Game_order_model->get_by_condition($condition, null, null, null, null, null, null);
                    if ($query_game_order) {
                        $game_order_res = $query_game_order;//支付信息汇总
                        $data['game_order_res'] = $game_order_res;
                    } else {
                        $data['game_order_res'] = false;
                    }
                    //查找该时间范围内支付信息 end
                } else {
                    $data['create_error_info'] = "无用户";
                }
            } else {
                $temp_end = $end_stamp+3600*24-1;
                $this->load->model('Sign_report_model');
                $condition=array(
                    'create_date >=' => $start_stamp,
                    'create_date <=' => $temp_end,
                );
                if ($platform) {
                    $condition['platform'] = $platform;
                }
                if ($server_id) {
                    $condition['server_id'] = $server_id;
                }
                $query_sign = $this->Sign_report_model->get_by_condition($condition, null, null, null, null, null, null);
                if ($query_sign) {
                    $data['info_is_show'] = "show";
                    $sign_res = $query_sign;//创角信息汇总
                    $data['sign_res'] = $sign_res;
                    $data['sign_res_num'] = count($sign_res);
                    $this->load->model('Create_role_report_model');
                    $condition=array(
                        'create_date >= '=> $start_stamp,
                        'create_date <= '=> $temp_end,
                    );
                    if ($platform) {
                        $condition['platform']=$platform;
                    }
                    if ($server_id) {
                        $condition['server_id']=$server_id;
                    }
                    $query_create_role = $this->Create_role_report_model->get_by_condition($condition, null, null, null, null, null, null);
                    if ($query_create_role) {
                        $create_role_res = $query_create_role;//创角信息汇总
                        $data['create_role_res'] = $create_role_res;
                    } else {
                        $data['create_role_res'] = false;
                    }

                    //查找该时间范围内支付信息 start
                    $this->load->model('Game_order_model');
                    $condition=array(
                        'create_date >='=>$start_stamp,
                        'create_date <=' => $temp_end,
                    );
                    if ($platform) {
                        $condition['platform'] = $platform;
                    }
                    $query_game_order = $this->Game_order_model->get_by_condition($condition, null, null, null, null, null, null);
                    if ($query_game_order) {
                        $game_order_res = $query_game_order;//支付信息汇总
                        $data['game_order_res'] = $game_order_res;
                    } else {
                        $data['game_order_res'] = false;
                    }
                    //查找该时间范围内支付信息 end
                } else {
                    $data['create_error_info'] = "无用户";
                }
            }
        }

        $this->load->view("admin/info_tongji/info_tongji_sign_new", $data);
    }

    public function platform_tongji()
    {
        //查询平台start
        $this->load->model('db/Platform_model');
        //$condition = array(1 => 1);
        $platform = $this->Platform_model->get_by_condition(null);
        if ($platform) {
            $data['platform_info'] = $platform;
        } else {
            $data['platform_info'] = array();
        }
        //查询平台end
        $this->load->model('db/Game_father_model');
        $game_father_id = $this->Game_father_model->get_by_condition(null);
        if ($game_father_id) {
            $data['game_info'] = $game_father_id;
        } else {
            $data['game_info'] = array();
        }



        $start = $this->input->get('start');
        $end = $this->input->get('end');
        $platform = $this->input->get('platform');
        $server_id = $this->input->get('server_id');
        $game_father_id = $this->input->get('game_father_id');
        echo $game_father_id;



        $data['start'] = $start;
        $data['end'] = $end;
        $data['platform'] = $platform;
        $data['server_id'] = $server_id;
        $data['game_father_id'] = $game_father_id;

        $flag = true;
        if (!$start) {
            $flag = false;
        }

        $start_stamp = strtotime($start);
        if (!$start_stamp) {
            $flag = false;
        }
        if ($end) {
            $end_stamp = strtotime($end);
            if (!$end_stamp) {
                $flag = false;
            }
        }

        if ($flag) {
            if (!$end) {
                $temp_end = $start_stamp+3600*24-1;
                $sql_sign = 'select platform,user_id,p_uid,server_id,create_date,level,game_father_id from login_report where (create_date >= '.$start_stamp.') and (create_date <= '.$temp_end.')';
                if ($platform) {
                    $sql_sign.=" and (platform = '".$platform."')";
                }
                if ($server_id) {
                    $sql_sign.=" and (server_id = '".$server_id."')";
                }
                if ($game_father_id) {
                    $sql_sign.=" and (game_father_id = '".$game_father_id."')";
                }
                $query_sign = $this->db->query($sql_sign);
                if ($query_sign->num_rows() > 0) {
                    $data['info_is_show'] = "show";
                    $sign_res = $query_sign->result();//创角信息汇总
                    $data['sign_res'] = $sign_res;
                    $data['sign_res_num'] = count($sign_res);
                    $sql_create_role = 'select user_id,nickname,create_date from create_role_report where (create_date >= '.$start_stamp.') and (create_date <= '.$temp_end.')';
                    if ($platform) {
                        $sql_create_role.=" and (platform = '".$platform."')";
                    }
                    if ($server_id) {
                        $sql_create_role.=" and (server_id = '".$server_id."')";
                    }
                    $query_create_role = $this->db->query($sql_create_role);
                    if ($query_create_role->num_rows() > 0) {
                        $create_role_res = $query_create_role->result();//创角信息汇总
                        $data['create_role_res'] = $create_role_res;
                    } else {
                        $data['create_role_res'] = false;
                    }

                    //查找该时间范围内支付信息 start
                    $sql_game_order = "select user_id,money from game_order where create_date >= ".$start_stamp." and create_date <= ".$temp_end;
                    if ($platform) {
                        $sql_game_order.=" and (platform = '".$platform."')";
                    }
                    $query_game_order = $this->db->query($sql_game_order);
                    if ($query_game_order->num_rows() > 0) {
                        $game_order_res = $query_game_order->result();//支付信息汇总
                        $data['game_order_res'] = $game_order_res;
                    } else {
                        $data['game_order_res'] = false;
                    }
                    //查找该时间范围内支付信息 end
                } else {
                    $data['create_error_info'] = "无用户";
                }
            } else {
                $temp_end = $end_stamp+3600*24-1;
                $sql_sign = 'select platform,user_id,p_uid,server_id,create_date,level,game_father_id from login_report where (create_date >= '.$start_stamp.') and (create_date <= '.$temp_end.')';
                if ($platform) {
                    $sql_sign.=" and (platform = '".$platform."')";
                }
                if ($server_id) {
                    $sql_sign.=" and (server_id = '".$server_id."')";
                }
                if ($game_father_id) {
                    $sql_sign.=" and (game_father_id = '".$game_father_id."')";
                }
                $query_sign = $this->db->query($sql_sign);
                if ($query_sign->num_rows() > 0) {
                    $data['info_is_show'] = "show";
                    $sign_res = $query_sign->result();//创角信息汇总
                    $data['sign_res'] = $sign_res;
                    $data['sign_res_num'] = count($sign_res);
                    $sql_create_role = 'select user_id,nickname,create_date from create_role_report where (create_date >= '.$start_stamp.') and (create_date <= '.$temp_end.')';
                    if ($platform) {
                        $sql_create_role.=" and (platform = '".$platform."')";
                    }
                    if ($server_id) {
                        $sql_create_role.=" and (server_id = '".$server_id."')";
                    }
                    $query_create_role = $this->db->query($sql_create_role);
                    if ($query_create_role->num_rows() > 0) {
                        $create_role_res = $query_create_role->result();//创角信息汇总
                        $data['create_role_res'] = $create_role_res;
                    } else {
                        $data['create_role_res'] = false;
                    }

                    //查找该时间范围内支付信息 start
                    $sql_game_order = "select user_id,money from game_order where create_date >= ".$start_stamp." and create_date <= ".$temp_end;
                    if ($platform) {
                        $sql_game_order.=" and (platform = '".$platform."')";
                    }
                    $query_game_order = $this->db->query($sql_game_order);
                    if ($query_game_order->num_rows() > 0) {
                        $game_order_res = $query_game_order->result();//支付信息汇总
                        $data['game_order_res'] = $game_order_res;
                    } else {
                        $data['game_order_res'] = false;
                    }
                    //查找该时间范围内支付信息 end
                } else {
                    $data['create_error_info'] = "无用户";
                }
            }
        }
        $this->load->view("admin/info_tongji/platform_tongji", $data);
    }

}
