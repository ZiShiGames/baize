<?php

class Game_3_model extends CI_Model
{
    public function game($platform, $game, $openId)
    {
        $this->trun_to_game($game->game_id,$openId);
    }

    public function trun_to_game($game_id,$openId)
    {
        $game = $this->Game_model->get_by_game_id($game_id);
        if (!$game) {
            $this->Output_model->json_print(-2, '');

            return;
        }

        $this->cache->get('user_id');

        $openId = $openId;
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

        $noice = time();
        $nickname = urlencode($user->nickname);
        $avatar = urlencode($user->avatar);

        $sign = md5($openId.$noice.$game->app_key);
        $game_url = parse_url($game->game_login_url);
        $game_url=$game_url['query']?$game->game_login_url.'&':$game->game_login_url.'?';
        if($game->copy_game_id){
            $channel = $user->channel?$user->channel:'allu';
            if(substr($channel,0,4)=='WXMP'){
                $channel = 'WXMP';
            }else if(substr($channel,0,2)=='ZX'){
                $channel = 'ZX';
            }
            if($game->copy_game_id=='71'){
                $channel = 'C_'.$channel;
            }
            $url = $game_url."openId=$openId&openKey=$openKey&noice=$noice&appId=$game->copy_game_id&sign=$sign&nickname=$nickname&avatar=$avatar&channel=$channel";
        }else if($game_id=='74'){
            $channel = 'BZWD';
            $url = $game_url."openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&nickname=$nickname&avatar=$avatar&channel=$channel";
        }else{
            $url = $game_url."openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&nickname=$nickname&avatar=$avatar";
        }
        log_message('debug', "nineg login:$url");

        header("Location: $url");
    }
}
