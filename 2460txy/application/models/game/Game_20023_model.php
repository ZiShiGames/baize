<?php

class Game_20023_model extends CI_Model
{
    public function game($platform, $game, $openId)
    {
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
