<?php

class Game_20003_model extends CI_Model
{
    public function game($platform, $game, $openId)
    {
        // $openId = $this->input->get('openId');
        // if (!$openId) {
        //     $this->Output_model->json_print(-4, 'id e');
        //
        //     return;
        // }
        // // $frameHeight = $this->input->get('frameHeight');
        // // $frameWidth = $this->input->get('frameWidth');
        //
        // if (!$game) {
        //     $this->Output_model->json_print(-2, 'g n f');
        //
        //     return;
        // }
        //
        // $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/api/?m=player&fn=getserverlist&openId='.$openId;
        // $content = $this->Curl_model->curl_get($url);
        // $plat = $platform;
        // if ($content) {
        //     $servers = json_decode($content);
        //     // $this->load->model('Server_model');
        //     // $server_list = $this->Server_model->get_by_condition();
        //     // foreach ($server_list as $one) {
        //     //     foreach ($servers->server_list as $two) {
        //     //         if ($one->platform == ' '.$plat && $one->server_id == $two->id) {
        //     //             $two->status = $one->status;
        //     //         }
        //     //     }
        //     // }
        //
        //     if (count($servers->last_server) > 1) {
        //         $sort_column = array();
        //
        //         foreach ($servers->last_server as $one) {
        //             if (!isset($one->login_date)) {
        //                 $one->login_date = '0';
        //             }
        //             $sort_column[] = $one->login_date;
        //         }
        //
        //         array_multisort($sort_column, SORT_DESC, $servers->last_server);
        //     }
        //
        //     if ($servers) {
        //         if (!isset($servers->server_list)) {
        //             $servers->server_list = array();
        //         }
        //
        //         $game_name = $game->game_name;
        //         $game_id = $game->game_id;
        //         $url = "/index.php/enter/trun_to_game/$platform/$game_id?openId=$openId";
        //         $announce = '';
        //         // $announce = $this->Curl_model->curl_get('http://lcby.gz.1251208707.clb.myqcloud.com/notice/?appId='.$game_id);
        //         // if ($announce) {
        //         //     $res_obj = json_decode($announce);
        //         //     if (isset($res_obj->c) && $res_obj->c === 0) {
        //         //         $announce = $res_obj->m;
        //         //     } else {
        //         //         $announce = '';
        //         //     }
        //         // }
        //
        //         $direct = $this->input->get('direct');
        //         if ($direct) {
        //             if (isset($servers->last_server) && count($servers->last_server) > 0) {
        //                 $latest_server = $servers->last_server[count($servers->last_server) - 1];
        //             } else {
        //                 $latest_server = $servers->server_list[count($servers->server_list) - 1];
        //             }
        //
        //             header('Location: '.$url.'&serverId='.$latest_server->id);
        //
        //             return;
        //         }
        //
        //         $condition = array('user_id' => $openId);
        //         $user = $this->User_model->get_one_by_condition($condition);
        //         $this->load->model('Server_model');
        //         $loginstatus = $this->Server_model->get_by_server_id(1, 'all');
        //         if ((time() - $user->create_date) < 60 * 60) {
        //             if (!isset($servers->last_server) || count($servers->last_server) == 0) {
        //                 $latest_server = $servers->server_list[count($servers->server_list) - 1];
        //                 if ($loginstatus->status == 0) {
        //                     if ($platform != 'kemeng' && $platform != 'kemengus') {  //新用户导入服务器
        //                     header('Location: '.$url.'&serverId='.$latest_server->id);
        //
        //                         return;
        //                     }
        //                 } else {
        //                     header('Location: '.$url.'&serverId='.$latest_server->id);
        //
        //                     return;
        //                 }
        //             }
        //         }
        //
        //         $s_server = array();
        //
        //         // test for yyb
        //         if (count($servers->default_server->id) == null) {
        //             $test_server = array(
        //                                 'id' => '8003',
        //                                 'name' => 's1',
        //                                 'status' => '0',
        //                             );
        //             $test_server = json_decode(json_encode($test_server));
        //             $servers->default_server = $test_server;
        //         }
        //         $server_status = 0;
        //         $white_list = array(9,13,15,23,65,52,5,19078669,731754,21337,17562046,210123,752690,104074,16664680,12912364,16725175,16826179,14939831,17083798);
        //         if (in_array($openId, $white_list)) {
        //             $test_server = array(
        //                                 'id' => '8003',
        //                                 'name' => '测试服',
        //                                 'status' => '0',
        //                             );
        //             $test_server = json_decode(json_encode($test_server));
        //             array_push($servers->server_list, $test_server);
        //             $server_status = 2;
        //         }
        //         $data = array(
        //                     'servers' => $servers,
        //                     'game_name' => $game_name,
        //                     'url' => $url,
        //                     'announce' => $announce,
        //                     's_server' => $s_server,
        //                     'game' => $game,
        //                     'platform' => $platform,
        //                     'server_status' => $server_status,
        //                 );
        //
        //         // if ($platform == 'tt') {
        //         //     $data['game_name'] = '天团';
        //         // }
        //
        //         $condtion = array('game_id' => $game_id);
        //         $game = $this->Game_model->get_one_by_condition($condtion);
        //         if ($game->status == 0) {
        //             if (in_array($openId, $white_list)) {
        //                 $this->load->view('game_login/szww/allu_szww_login', $data);
        //             } else {
        //                 $this->load->view('stop', $data);
        //             }
        //         } else {
        //             $this->load->view('game_login/szww/allu_szww_login', $data);
        //         }
        //
        //         return;
        //     }
        // }
        //
        // $this->Output_model->json_print(-3, 's l e');
        header('Location: /index.php/enter/trun_to_game/'.$platform.'/'.$game->game_id."?openId=$openId");
    }

    public function trun_to_game($game_id)
    {
        $game = $this->Game_model->get_by_game_id($game_id);
        if (!$game) {
            $this->Output_model->json_print(-2, '');

            return;
        }

        $this->cache->get('user_id');

        $openId = $this->input->get('openId');
        if (!$openId) {
            echo 'error';

            return;
        }
        $condition = array('user_id' => $openId);

        $user = $this->User_model->get_one_by_condition($condition);
        if (!$user) {
            echo 'user not found';

            return;
        }

        $openKey = $this->cache->get($openId.'_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $server_name = $this->input->get('server_name');
        $pfid = $this->input->get('pfid');
        $noice = time();
        $nickname = urlencode($user->nickname);
        $avatar = urlencode($user->avatar);

        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;

        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId&server_name=$server_name&nickname=$nickname&avatar=$avatar";
        log_message('debug', "nineg login:$url");

        header("Location: $url");
    }
}
