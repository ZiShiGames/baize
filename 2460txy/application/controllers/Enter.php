<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Enter extends CI_Controller
{
    public $test = false;

    public function play($platform = null, $game_id = 0)
    {
        error_reporting(0);
        if (!$platform || !$game_id) {
            echo 'no game';

            return;
        }

        $data = array(
            'passId' => $platform,
            'appId' => $game_id,
            'wx_guid'=> $_POST['uid']?$_POST['uid']:'1'
        );

        $game = $this->Game_model->get_by_game_id($game_id);
        $data['game'] = $game;

        // $this->load->model('Platform_pkg_model');
        // if(in_array($platform, $this->Platform_pkg_model->MAILIANG_PLATFORM)){
        //     $this->load->view('mailiang_play_page',$data);
        //     return;
        // }
        if (isset($game->status) && $game->status != 1) {
            $this->load->library('console_log');
            $this->console_log->alert('游戏已下架');
            exit;
        }
        $qitianledi_platforms = array(
            'qitianledifour',
            'qitianledifive',
            'qitianledisix',
            'qitianlediseven',
            'qitianledieight',
            'qitianledinine',
            'qitianlediten',
        );

        // 宇宙起源
        $yuzhouqiyuan = array(
            1438, 1439, 1440, 1441, 1442, 1444, 1445, 1446, 1447, 1451,
        );
        if (in_array($game_id, $yuzhouqiyuan)) {
            $data['game']->game_name = '宇宙起源';
        };
        

        // 清泉找的cps
        $chaomeng_quange_cps = array(
            'bgm',
            'atc',
            'sgfivefive',
            'dongxin',
            'xingjiehuyu',
            'fuli',
            'hefangone',
            'hefangtwo',
            'yiyoujiahe',
            'baidujisu',
            'yuganyoucps',
            'baoge',
            
        );

        if ($platform == 'kemeng') {
            $this->load->view('kemeng_play_page', $data);
        }elseif($platform == 'azwdone'){
            $this->load->view('azwdone_play_page', $data);
        } elseif ($platform == 'login' || in_array($platform, $chaomeng_quange_cps)) {
            $this->load->view('login_play_page', $data);
        } elseif ($platform == 'lihuaqudao') {
            $this->load->view('lihuaqudao_play_page', $data);
        } elseif ($platform == 'egret') {
            $this->load->view('egret_play_page', $data);
        } elseif ($platform == 'qitianledi' || in_array($platform, $qitianledi_platforms)) {
            if( $platform == 'qitianledi' && $game_id == 1361){
                $this->load->view( 'qitianledi_chengetitle_play_page', $data);
            }else {
                $this->load->view('qitianledi_play_page', $data);
            }
        } elseif ($platform == 'qitianledione') {
            $this->load->view('qitianledione_play_page', $data);
        } elseif ($platform == 'qitianleditwo') {
            $this->load->view('qitianleditwo_play_page', $data);
        } elseif ($platform == 'qitianledithree') {
            $this->load->view('qitianledithree_play_page', $data);
        } elseif ($platform == 'jisu') {
            $this->load->view('jisu_play_page', $data);
        } elseif ($platform == 'jisufenbaoone') {
            $this->load->view('jisufenbaoone_play_page', $data);
        } elseif ($platform == 'jisuiostishentwo') {
            $this->load->view('jisuiostishentwo_play_page', $data);
        } elseif ($platform == 'jisuiostisheng') {
            $this->load->view('jisuiostisheng_play_page', $data);
        } elseif ($platform == 'xyouwang') {
            $this->load->view('xyouwang_play_page', $data);
        } elseif ($platform == 'kemengus') {
            $this->load->view('kemengus_play_page', $data);
        } elseif ($platform == 'leyou') {
            $this->load->view('leyou_play_page', $data);
        } elseif ($platform == 'qidian') {
            $this->load->view('qidian_play_page', $data);
        } elseif ($platform == 'iqiyi') {
            $this->load->view('iqiyi_play_page', $data);
        } elseif ($platform == 'dragoncity') {
            $this->load->view('dragoncity_play_page', $data);
        } elseif ($platform == 'shunwangyuzhou') {
            $this->load->view('shunwangyuzhou_play_page', $data);
        } elseif ($platform == 'fivegame') {
            $this->load->view('fivegame_play_page', $data);
        } elseif ($platform == 'dayu') {
            $this->load->view('dayu_play_page', $data);
        } elseif ($platform == 'kunyou') {
            $this->load->view('kunyou_play_page', $data);
        } elseif ($platform == 'tianyuyou') {
            $this->load->view('tianyuyou_play_page', $data);
        } elseif ($platform == 'sina') {
            $this->load->view('sina_play_page', $data);
        } elseif ($platform == 'zhuoyue') {
            $this->load->view('zhuoyue_play_page', $data);
        } elseif ($platform == 'fantastic') {
            $this->load->view('fantastic_play_page', $data);
        } elseif ($platform == 'yibabawan') {
            $this->load->view('yibabawan_play_page', $data);
        } elseif ($platform == 'yiniu') {
            $this->load->view('yiniu_play_page', $data);
        } elseif ($platform == 'four') {
            $this->load->view('four_play_page', $data);
        } elseif ($platform == 'ximalaya') {
            $this->load->view('ximalaya_play_page', $data);
        } elseif ($platform == 'xcyx') {
            $this->load->view('xcyx_play_page', $data);
        } elseif ($platform == 'huanlezm') {
            $this->load->view('huanlezm_play_page', $data);
        } elseif ($platform == 'baiduduoku') {
            $this->load->view('baiduduoku_play_page', $data);
        } elseif ($platform == 'maoer') {
            $this->load->view('maoer_play_page', $data);
        } elseif ($platform == 'yinli') {
            $this->load->view('yinli_play_page', $data);
        } elseif ($platform == 'maoeriostishen') {
            $this->load->view('maoeriostishen_play_page', $data);
        } elseif ($platform == 'maoeriostishentwo') {
            $this->load->view('maoeriostishentwo_play_page', $data);
        } elseif ($platform == 'maoeriostishenthree') {
            $this->load->view('maoeriostishenthree_play_page', $data);
        } elseif ($platform == 'huawei') {
            $this->load->view('huawei_play_page', $data);
        } elseif ($platform == 'xiangzhi') {
            $this->load->view('xiangzhi_play_page', $data);
        } elseif ($platform == 'duohaowan') {
            $this->load->view('duohaowan_play_page', $data);
        } elseif ($platform == 'vivo') {
            $this->load->view('vivo_play_page', $data);
        } elseif ($platform == 'langaohuyu') {
            $this->load->view('langaohuyu_play_page', $data);
        } elseif ($platform == 'yygamehf') {
            $this->load->view('yygamehf_play_page', $data);
        } elseif ($platform == 'baidu') {
            $this->load->view('baidu_play_page', $data);
        } elseif ($platform == 'tvmgame') {
            $this->load->view('tvmgame_play_page', $data);
        } elseif ($platform == 'xiaomi') {
            $this->load->view('xiaomi_play_page', $data);
        } elseif ($platform == 'dkmiostishen') { //dkm ios
            $this->load->view('dkmiostishen_play_page', $data);
        } elseif ($platform == 'qimiaoios') {
            $this->load->view('qimiaoios_play_page', $data); //qimiao ios
        } elseif ($platform == 'iosauditservicenovip') { //ios提审服 无充值
            $this->load->view('iosauditservicenovip_play_page', $data);
        } elseif ($platform == 'zhuoyueiosauditservice') { //ios提审服 无充值
            $this->load->view('zhuoyueiosauditservice_play_page', $data);
        } elseif ($platform == 'qidianyule') { // 登录需要判断是否有user_id参数
            $this->load->view('qidianyule_play_page', $data);
        } elseif ($platform == 'ybbw') { // 登录需要判断是否有user_id参数
            $this->load->view('ybbw_play_page', $data);
        } elseif ($platform == 'pinrong') {
            $this->load->view('pinrong_play_page', $data);
        } elseif ($platform == 'fanqie') {
            $this->load->view('fanqie_play_page', $data);
        } elseif ($platform == 'shanyaojl') {
            $this->load->view('shanyaojl_play_page', $data);
        } elseif ($platform == 'kunyoutishen') {
            $this->load->view('kunyoutishen_play_page', $data);
        } elseif ($platform == 'kaiersasi') {
            $this->load->view('kaiersasi_play_page', $data);
        } elseif ($platform == 'shanyaojulianghfive') {
            $this->load->view('shanyaojulianghfive_play_page', $data);
        } elseif ($platform == 'qitianledixiaomi') {
            $this->load->view( 'qitianledixiaomi_play_page', $data);
        } elseif ($platform == 'qitianledibaidu') {
            $this->load->view( 'qitianledibaidu_play_page', $data);
        }elseif ($platform == 'bzwd'||$platform == 'bzwdios'||$platform == 'bzwdzjzf'||$platform == 'bzwdzjzf') {
                $this->load->view( 'bzwd_play_page', $data);
        }elseif($platform == 'jklappcps'){
            $this->load->view( 'jklappcps_play_page', $data);
        }elseif($platform == 'kejin0001' || $platform == 'kejin0002' || $platform == 'kejin0003' || $platform == 'kejin0004'){
            $this->load->view( 'kejin_play_page', $data);
        }elseif($platform == 'xyxqy'){
            $this->load->view( 'xyxqy_play_page', $data);
        }else{
            $this->load->view('play_page', $data);
        }
    }

    public function login($platform = null, $game_id = 0)
    {
        error_reporting(0);
        if (!$platform || !$game_id) {
            echo 'no';

            return;
        }
        $platform_model = $platform . '_model';
        if ($this->load->model('platform/' . $platform_model)) {
            $user_id = $this->$platform_model->login($game_id);
            if ($user_id) {
                $openId = $user_id;
                $this->load->driver('cache', array('adapter' => 'redis'));

                if ($this->cache->redis->is_supported()) {
                    $illegal_user_map = $this->cache->redis->get("illegal_user_map");
                    if (empty($illegal_user_map)) {
                        // log_message('debug', 'enter login  ' . $illegal_user_map);
                        $illegal_user_map = json_decode($illegal_user_map);
                        foreach ($illegal_user_map as $one) {
                            if ($one->user_id == $user_id && $one->status == 1) {
                                if(strpos($one->game_id,$game_id) !== false){
                                    $data = array(
                                        'url' => "/gameerror.html",
                                        'orientation' => "portrait",
                                    );
                                    $this->Output_model->json_print(0, 'ok', $data);
                                    exit;
                                }
                            }
                        }
                    } else {
                        // log_message('debug', 'enter login no illegal cache ');
                        $this->load->model('db/Illegal_user_model');
                        $illegal_user_map = $this->Illegal_user_model->get_by_condition();
                        $requery = $this->cache->redis->save('illegal_user_map', json_encode($illegal_user_map), 86400 * 30);
                        foreach ($illegal_user_map as $one) {
                            if ($one->user_id == $user_id && $one->status == 1) {
                                if(strpos($one->game_id,$game_id) !== false){
                                    $data = array(
                                        'url' => "/gameerror.html",
                                        'orientation' => "portrait",
                                    );
                                    $this->Output_model->json_print(0, 'ok', $data);
                                    exit;
                                }
                            }
                        }
                    }
                } else {
                    log_message('debug', 'enter login get illegal cache error');
                }

                $game = $this->Game_model->get_by_game_id($game_id);
                if ($game->game_father_id == 20002 || $game->game_father_id == 20022) {
                    $orientation = 'landscape';
                } else {
                    $orientation = 'portrait';
                }

                $data = array(
                    'url' => "/index.php/enter/game/$platform/$game_id?openId=$openId",
                    'orientation' => "$orientation",
                );

                $this->Output_model->json_print(0, 'ok', $data);
            } else {
                $this->Output_model->json_print(-2, '');
            }
        }
    }

    public function game($platform = null, $game_id = 0)
    {
        // error_reporting(0);
        // if (!$platform || !$game_id) {
        //     echo 'no game';
        //
        //     return;
        // }
        //
        // $platform_model = $platform.'_model';
        //
        // if ($this->load->model('platform/'.$platform_model)) {
        //     $this->$platform_model->game($platform, $game_id);
        // }

        $openId = $this->input->get('openId');
        if (!$openId) {
            $this->Output_model->json_print(-4, 'id e');

            return;
        }

        // change login page test
        if ($platform != 'allutest') {
            header('Location: /index.php/enter/trun_to_game/' . $platform . '/' . $game_id . "?openId=$openId");
        }
        if ($platform == 'jklappcps' || $platform == 'allutest' || $platform == 'login' || $platform == 'jinbangtishen' || $platform == 'qimiaoios' || $platform == 'nineg' || $platform == 'allu' || $platform == 'allu' || $platform == 'qunhei' || $platform == 'hiwan' || $platform == 'five' || $platform == 'wanwei' || $platform == 'seven' || $platform == 'iqiyi' || $platform == 'sina' || $platform == 'lbw'
            || $platform == 'mtyx' || $platform == 'gzyx' || $platform == 'tn' || $platform == 'xcyx' || $platform == 'one' || $platform == 'zwfy' || $platform == 'dyyx' || $platform == 'ctyx' || $platform == 'four' || $platform == 'sixtwo' || $platform == 'shunw' || $platform == 'jiuw' || $platform == 'bear' || $platform == 'ftnn'
            || $platform == 'bais' || $platform == 'bb' || $platform == 'ntss' || $platform == 'wifi' || $platform == 'chengt' || $platform == 'viwan' || $platform == 'alluapp' || $platform == 'bbgame' || $platform == 'sougouhfive' || $platform == 'egret' || $platform == 'whale' || $platform == 'leyou' || $platform == 'minigame' || $platform == 'heke'
            || $platform == 'threefive' || $platform == 'dazhan' || $platform == 'qidian' || $platform == 'dayu' || $platform == 'sdwan' || $platform == 'gamemouse' || $platform == 'sevenk' || $platform == 'wanss' || $platform == 'taren' || $platform == 'youdao' || $platform == 'dragonball' || $platform == 'orange' || $platform == 'zhuoyue' || $platform == 'eighta'
            || $platform == 'zhongwenonline' || $platform == 'jiuxiangwan' || $platform == 'meishengyuan' || $platform == 'compote' || $platform == 'ifeng' || $platform == 'youwo' || $platform == 'banana' || $platform == 'fivegame' || $platform == 'baidu' || $platform == 'novembersixteen' || $platform == 'kugou' || $platform == 'allutest' || $platform == 'gametai' || $platform == 'aoyoupingtai'
            || $platform == 'decembertwelve' || $platform == 'qqbrowser' || $platform == 'Qqbrowser' || $platform == 'jinb' || $platform == 'tt' || $platform == 'yibabawan' || $platform == 'fantastic' || $platform == 'fiveone' || $platform == 'alluwd') {
            header('Location: /index.php/enter/trun_to_game/' . $platform . '/' . $game_id . "?openId=$openId");
        }
        // $frameHeight = $this->input->get('frameHeight');
        // $frameWidth = $this->input->get('frameWidth');
        if ($platform == 'kemeng') {
            $kemeng_pkg = $this->session->userdata('kemeng_pkg');
            if (in_array($kemeng_pkg, $this->Platform_pkg_model->YUEWAN_PKG_ARRAY)) {
                $game_id = 1089;
                header('Location: /index.php/enter/trun_to_game/' . $platform . '/' . $game_id . "?openId=$openId");
            } elseif (in_array($kemeng_pkg, $this->Platform_pkg_model->FOURNINEGAME_SERVER)) {
                $game_id = 1109;
            } else {
                header('Location: /index.php/enter/trun_to_game/' . $platform . '/' . $game_id . "?openId=$openId");
            }
        }

        $game = $this->Game_model->get_by_game_id($game_id);
        if (!$game) {
            $this->Output_model->json_print(-2, 'g n f');

            return;
        }

        if ($game->game_father_id != 10000) {
            $game_model_name = 'Game_' . $game->game_father_id . '_model';
            if ($this->load->model("game/$game_model_name")) {
                $this->$game_model_name->game($platform, $game, $openId);
            }

            return;
        }
        $this->load->model('Platform_pkg_model');
        $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/api/?m=player&fn=getserverlist&openId=' . $openId;
        if ($platform == 'yyb') {
            $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/yyb/api/?m=player&fn=getserverlist&openId=' . $openId;
        } elseif ($platform == 'nineg') {
            $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/9g/api/?m=player&fn=getserverlist&openId=' . $openId;
        } elseif ($platform == 'tt') {
            $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/api/?m=player&fn=getserverlist&openId=' . $openId;
            // $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/tt/api/?m=player&fn=getserverlist&openId='.$openId;  //天团专服
        } elseif ($platform == 'jinb') {
            $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/jinbang/api/?m=player&fn=getserverlist&openId=' . $openId;
        } elseif ($platform == 'dragoncity') {
            $kemeng_pkg = $this->session->userdata('kemeng_pkg');
            $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/dkmzf/api/?m=player&fn=getserverlist&openId=' . $openId;
        } elseif ($platform == 'kemeng') {
            $kemeng_pkg = $this->session->userdata('kemeng_pkg');
            if (in_array($kemeng_pkg, $this->Platform_pkg_model->YUEWAN_PKG_ARRAY)) {
                $game_id = 1089;
                $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/yuewan/api/?m=player&fn=getserverlist&openId=' . $openId;
            } elseif (in_array($kemeng_pkg, $this->Platform_pkg_model->FOURNINEGAME_SERVER)) {
                $game_id = 1109;
                $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/49you/api/?m=player&fn=getserverlist&openId=' . $openId;
            } else {
                $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/dkm/api/?m=player&fn=getserverlist&openId=' . $openId;
            }
        } elseif ($platform == 'kemengus') {
            $kemeng_pkg = $this->session->userdata('kemeng_pkg');
            $url = 'http://169.47.46.53/dkm/api/?m=player&fn=getserverlist&openId=' . $openId;
        } elseif ($platform == 'qimiaoios') {
            $kemeng_pkg = $this->session->userdata('9130_game_pkg');
            $url = 'http://169.47.46.53/dkm/api/?m=player&fn=getserverlist&openId=' . $openId;
        } elseif ($platform == 'zhuoyueiosauditservice') {
            $url = 'http://169.47.46.53/dkm/api/?m=player&fn=getserverlist&openId=' . $openId;
        } elseif ($platform == 'iosauditservicenovip') {
            // $kemeng_pkg = $this->session->userdata('kemeng_pkg');
            $url = 'http://169.47.46.53/dkm/api/?m=player&fn=getserverlist&openId&openId=' . $openId;
        } elseif ($platform == 'yybsdk') {
            $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/yybsdk/api/?m=player&fn=getserverlist&openId=' . $openId;
        } elseif ($platform == 'fantastic') {
            $pkg_name = $this->session->userdata('9130_game_pkg');
            $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/qimiao/api/?m=player&fn=getserverlist&openId=' . $openId;
        } elseif ($platform == 'fiveone') {
            $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/5151/api/?m=player&fn=getserverlist&openId=' . $openId;
        } elseif ($platform == 'yibabawan') {
            $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/188w/api/?m=player&fn=getserverlist&openId=' . $openId;
        } elseif ($platform == 'dkmiostishen') {
            $url = 'http://169.47.46.53/dkm/api/?m=player&fn=getserverlist&openId=' . $openId;
        }
        //dkmiostishen

        $content = $this->Curl_model->curl_get($url);
        if ($content) {
            $servers = json_decode($content);

            if ($platform == 'nineg' || $platform == 'jinb' || $platform == 'tt') {
                $content = $this->Curl_model->curl_get('http://lcby.gz.1251208707.clb.myqcloud.com/juhe/api/?m=player&fn=getserverlist&openId=' . $openId);
                if ($content) {
                    $old_ones = json_decode($content);

                    if (isset($old_ones->last_server) && count($old_ones->last_server) > 0) {
                        foreach ($old_ones->last_server as $one) {
                            if ($platform == 'nineg') {
                                $one->pfid = 207;
                            } elseif ($platform == 'jinb') {
                                $one->pfid = 214;
                            } elseif ($platform == 'tt') {
                                $one->pfid = 211;
                            }
                        }
                        $servers->last_server = array_merge($servers->last_server, $old_ones->last_server);
                    }
                }
            }
            $plat = $platform;
            if (!in_array($platform, $this->Platform_pkg_model->SPECIAL_SERVER)) {
                $plat = 'juhe';
            }
            $this->load->model('Server_model');
            $server_list = $this->Server_model->get_by_condition();
            foreach ($server_list as $one) {
                if ($game_id == 1089) {
                    $plat = 'yuewan';
                } elseif ($game_id == 1109) {
                    $plat = 'fourninegame';
                }
                foreach ($servers->server_list as $two) {
                    if ($one->platform == ' ' . $plat && $one->server_id == $two->id) {
                        $two->status = $one->status;
                    }
                }
            }

            if (count($servers->last_server) > 1) {
                $sort_column = array();

                foreach ($servers->last_server as $one) {
                    if (!isset($one->login_date)) {
                        $one->login_date = '0';
                    }
                    $sort_column[] = $one->login_date;
                }

                array_multisort($sort_column, SORT_DESC, $servers->last_server);
            }

            if ($servers) {
                if (!isset($servers->server_list)) {
                    $servers->server_list = array();
                }

                $game_name = $game->game_name;
                $url = "/index.php/enter/trun_to_game/$platform/$game_id?openId=$openId";

                $announce = $this->Curl_model->curl_get('http://lcby.gz.1251208707.clb.myqcloud.com/notice/?appId=' . $game_id);
                if ($platform == 'kemeng') {
                    $kemeng_channel_id = $this->session->userdata('partner_id');
                    $announce = $this->Curl_model->curl_get('http://lcby.gz.1251208707.clb.myqcloud.com/notice/?appId=' . $kemeng_channel_id);
                }
                if ($announce) {
                    $res_obj = json_decode($announce);
                    if (isset($res_obj->c) && $res_obj->c === 0) {
                        $announce = $res_obj->m;
                    } else {
                        $announce = '';
                    }
                }

                $direct = $this->input->get('direct');
                if ($direct) {
                    if (isset($servers->last_server) && count($servers->last_server) > 0) {
                        $latest_server = $servers->last_server[count($servers->last_server) - 1];
                    } else {
                        $latest_server = $servers->server_list[count($servers->server_list) - 1];
                    }

                    header('Location: ' . $url . '&serverId=' . $latest_server->id);

                    return;
                }
                $condition = array('user_id' => $openId);
                $user = $this->User_model->get_one_by_condition($condition);
                $this->load->model('Server_model');
                $loginstatus = $this->Server_model->get_by_server_id(1, 'all');
                if ((time() - $user->create_date) < 60 * 60) {
                    if (!isset($servers->last_server) || count($servers->last_server) == 0) {
                        $latest_server = $servers->server_list[count($servers->server_list) - 1];
                        if ($loginstatus->status == 0) {
                            if ($platform != 'kemeng' && $platform != 'kemengus') { //新用户导入服务器
                                header('Location: ' . $url . '&serverId=' . $latest_server->id);

                                return;
                            }
                        } else {
                            header('Location: ' . $url . '&serverId=' . $latest_server->id);

                            return;
                        }
                    }
                }

                $s_server = array();
                if ($platform == 'nineg') {
                    // $servers->server_list = array();

                    for ($index = 1; $index <= 11; ++$index) {
                        $id = $index;
                        if ($index == 1) {
                            $id = 8000;
                        }
                        $old_s1 = array(
                            'id' => $id,
                            'name' => 's' . $index,
                            'status' => '0',
                            'pfid' => 207,
                        );
                        $old_s1 = json_decode(json_encode($old_s1));
                        array_push($s_server, $old_s1);
                    }
                } elseif ($platform == 'jinb') {
                    for ($index = 1; $index <= 123; ++$index) {
                        $id = $index;
                        if ($index == 1) {
                            $id = 8000;
                        }
                        $old_s1 = array(
                            'id' => $id,
                            'name' => 's' . $index,
                            'status' => '0',
                            'pfid' => 214,
                        );
                        $old_s1 = json_decode(json_encode($old_s1));
                        array_push($s_server, $old_s1);
                    }
                    $s_server = array_reverse($s_server);
                } elseif ($platform == 'tt') {
                    for ($index = 1; $index <= 14; ++$index) {
                        $id = $index;
                        $old_s1 = array(
                            'id' => $id + 10000,
                            'name' => '天团 s' . $index,
                            'status' => '0',
                            'pfid' => 211,
                        );
                        $old_s1 = json_decode(json_encode($old_s1));
                        array_push($s_server, $old_s1);
                    }
                    $s_server = array_reverse($s_server);
                }

                // test for yyb
                if (count($servers->default_server->id) == null) {
                    $test_server = array(
                        'id' => '8003',
                        'name' => 's1',
                        'status' => '0',
                    );
                    $test_server = json_decode(json_encode($test_server));
                    $servers->default_server = $test_server;
                }
                $server_status = 0;
                $white_list = array(9, 20297475, 17379919, 13, 15, 23, 65, 52, 5, 19078669, 731754, 21337, 17562046, 210123, 752690, 104074, 16664680, 12912364, 16725175, 16826179, 14939831, 17083798);
                if (in_array($openId, $white_list)) {
                    $test_server = array(
                        'id' => '8003',
                        'name' => '测试服',
                        'status' => '0',
                    );
                    $test_server = json_decode(json_encode($test_server));
                    array_push($servers->server_list, $test_server);
                    $server_status = 2;
                    $test_server = array(
                        'id' => '8008',
                        'name' => '测试服8008',
                        'status' => '0',
                    );
                    $test_server = json_decode(json_encode($test_server));
                    array_push($servers->server_list, $test_server);
                    $server_status = 2;
                }
                if ($platform == 'jinb') {
                    foreach ($server_list as $one) {
                        foreach ($s_server as $two) {
                            if ($one->platform == ' juhe' && $one->server_id == $two->id) {
                                $two->status = $one->status;
                            }
                        }
                    }
                }
                $data = array(
                    'servers' => $servers,
                    'game_name' => $game_name,
                    'url' => $url,
                    'announce' => $announce,
                    's_server' => $s_server,
                    'game' => $game,
                    'platform' => $platform,
                    'server_status' => $server_status,
                );

                // if ($platform == 'tt') {
                //     $data['game_name'] = '天团';
                // }

                $condtion = array('game_id' => $game_id);
                $game = $this->Game_model->get_one_by_condition($condtion);
                if ($game->status == 0) {
                    if (in_array($openId, $white_list)) {
                        $this->load->view('game_login/allu_lc_login', $data);
                    } else {
                        $this->load->view('stop', $data);
                    }
                } else {
                    if ($platform == 'qidian') {
                        $this->load->view('game_login/qidian_lc_login.php', $data);
                        return;
                    }
                    // if ($platform == 'chengt') {
                    //     $this->load->view('game_login/chengt_lc_login.php', $data);
                    //     return;
                    // }
                    if ($platform == 'fantastic') {
                        $data['pkg_name'] = $pkg_name;
                        $this->load->view('game_login/fantastic_lc_login.php', $data);
                        return;
                    }
                    if ($platform == 'zhuoyue') {
                        $this->load->view('game_login/zhuoyue_lc_login.php', $data);
                        return;
                    }
                    // if ($platform == 'ifeng') {
                    //     $this->load->view('game_login/ifeng_lc_login.php', $data);
                    //     return;
                    // }
                    if ($platform == 'iosauditservicenovip') {
                        $this->load->view('game_login/iosauditservicenovip_lc_login.php', $data);
                        return;
                    }
                    if ($platform == 'dkmiostishen') {
                        $this->load->view('game_login/kemeng/dkmiostishen_lc_login.php', $data);
                        return;
                    } //
                    if ($platform == 'qimiaoios') {
                        $data['pkg_name'] = $kemeng_pkg;
                        $this->load->view('game_login/qimiaoios_lc_login.php', $data);
                        return;
                    }
                    if ($platform == 'dragoncity') {
                        $data['kemeng_pkg'] = $kemeng_pkg;
                        $this->load->view('game_login/dragoncity_lc_login.php', $data);
                        return;
                    }
                    if ($platform == 'baidu') {
                        $this->load->view('game_login/baidu_lc_login.php', $data);
                        return;
                    }
                    if ($platform == 'kemeng') {
                        $data['game_name'] = '专线';
                        if ($kemeng_pkg) {
                            $data['kemeng_pkg'] = $kemeng_pkg;
                            $this->load->view('game_login/kemeng/kemeng_lc_login', $data);
                        } else {
                            $data['kemeng_pkg'] = $kemeng_pkg;
                            $this->load->view('game_login/kemeng/kemeng_lc_login', $data);
                            // $this->load->view('game_login/allu_lc_login', $data);
                        }
                    } elseif ($platform == 'kemengus') {
                        $data['game_name'] = '专服';
                        $kemeng_pkg = $this->session->userdata('kemengus_pkg');
                        if ($kemeng_pkg) {
                            $data['kemeng_pkg'] = $kemeng_pkg;
                            $this->load->view('game_login/kemeng/kemengus_lc_login', $data);
                        } else {
                            $this->load->view('game_login/allu_lc_login', $data);
                        }
                    } else {
                        $this->load->view('game_login/allu_lc_login', $data);
                    }
                }

                return;
            }
        }

        $this->Output_model->json_print(-3, 's l e');
    }

    public function trun_to_game($platform = null, $game_id = 0)
    {

        if (!$platform || !$game_id) {
            echo 'no game';

            return;
        }

        $game = $this->Game_model->get_by_game_id($game_id);
        if (!$game) {
            $this->Output_model->json_print(-2, 'g n f');

            return;
        }

        if ($game->game_father_id != 20000) {
            $game_model_name = 'Game_' . $game->game_father_id . '_model';
            if ($this->load->model("game/$game_model_name")) {
                $this->$game_model_name->trun_to_game($game_id);
            }

            return;
        }

        $platform_model = $platform . '_model';

        if ($this->load->model('platform/' . $platform_model)) {
            $this->$platform_model->trun_to_game($game_id);
        }
    }
    public function wxlogin($platform = null, $game_id = 0)
    {
        error_reporting(0);
        if (!$platform || !$game_id) {
            echo 'no';
            
            return;
        }
        if($game_id=='76'){
            echo "11";die;
        }
        $platform_model = $platform . '_model';
        
        if ($this->load->model('platform/' . $platform_model)) {
            if($platform=='Wxminigame'||$platform=='wxminigame'){
                $jsonChannel = json_decode($this->input->get('channel'),true);
                if($game_id == '40'){
                    log_message('error',"wxChannel:".json_encode($jsonChannel));
                }
                if ($jsonChannel['query']['?channel']){ //微信广告
                    $channel = $jsonChannel['query']['?channel'];
                    $reserve['gdt_vid'] = $jsonChannel['query']['gdt_vid'];//获取
                    $weixinadinfo = explode('.', $jsonChannel['query']['weixinadinfo']);
                    $reserve['traceid'] = $weixinadinfo[0];//获取traceid参数
                    $reserve['weixinadinfo'] = $jsonChannel['query']['weixinadinfo'];
                    $json_reserve = json_encode($reserve); //微信广告全部参数转json格式存入备用字段
                }else if($jsonChannel['query']['channel']){ //分渠道 query参数获取
                    $channel = $jsonChannel['query']['channel'];
                }else if($jsonChannel['referrerInfo']['extraData']['channel']){ //分渠道 extraData参数获取
                    $channel = $jsonChannel['referrerInfo']['extraData']['channel'];
                }else{ //无分渠道则默认allu [自然量]
                    $channel = 'allu';
                }
                $data = $this->$platform_model->login($game_id,$channel,$json_reserve);
            }else{
                $data = $this->$platform_model->login($game_id);
            }
            $user_id = $data['user_id'];
            if ($user_id) {
                $this->load->driver('cache', array('adapter' => 'redis'));
//                if($user_id=='3677070'){
//                    log_message('debug', 'enter login no illegal cache '.$game_id);
//                }
                if ($this->cache->redis->is_supported()) {
                    $illegal_user_map = $this->cache->redis->get("illegal_user_map");

                    if (empty($illegal_user_map)) {
                        // log_message('debug', 'enter login  ' . $illegal_user_map);
                        $illegal_user_map = json_decode($illegal_user_map);
                        foreach ($illegal_user_map as $one) {
                            if ($one->user_id == $user_id && $one->status == 1) {
                                if(strpos($one->game_id,$game_id) !== false){
                                    header("Location:http://api01.baizegame.com/gameerror.html");
                                    exit;
                                }
                            }
                        }
                    }else{
                        $this->load->model('db/Illegal_user_model');
                        $illegal_user_map = $this->Illegal_user_model->get_by_condition();
                        $requery = $this->cache->redis->save('illegal_user_map', json_encode($illegal_user_map), 86400 * 30);
                        foreach ($illegal_user_map as $one) {
                            if ($one->user_id == $user_id && $one->status == 1) {
                                if(strpos($one->game_id,$game_id) !== false){
                                    header("Location:http://api01.baizegame.com/gameerror.html");
                                    exit;
                                }
                            }
                        }
                    }
                } else {
                    log_message('debug', 'enter login get illegal cache error');
                }
                $data['channel'] = $data['channel']?$data['channel']:'allu';
                $this->Output_model->json_print(0, 'ok', $data);
            } else {
                $this->Output_model->json_print(-2, '');
            }
        }
    }
}
