<?php
//推送微信小游戏客服会话登录文案
//public function weixinGameMsg(){
    $time = time();//获取当前时间
    $ymdTime = date('Y-m-d H:i:s',$time);//转换当前时间戳为yyyy-mm-dd hh:ii:ss
    $gameId = '62';//获取游戏id
    $game_father_id = '25';//游戏大类id
    $game = M('game','','DB_CONFIG1')->where(array('game_id'=>$gameId,'game_father_id'=>$game_father_id))->cache(500)->find();
    //15h内登录过的玩家
    $new_user_count_where = array(
        'game_id'=>$gameId,
        'game_father_id'=>$game_father_id,
        'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -15 hour')),'and'),
    );
//        $test = M('login_report','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql();
//        print_r($test);die;
    //查询48h内登录过的玩家
    $user['p_uid'] = M('login_report','','DB_CONFIG1')
        ->where(array(
            'game_id'=>$gameId,
            'game_father_id'=>$game_father_id,
            'create_date'=>array(array('elt',$time),array('egt',strtotime($ymdTime.' -2 day')),'and'),
            'user_id'=>array('exp','not in '.M('login_report','','DB_CONFIG1')->where($new_user_count_where)->field('user_id')->buildSql()),//排除24h内登录过的玩家
        ))
        ->cache(500)
        ->distinct(true)
        ->field('p_uid')
        ->select();

    $user['game_id'] = $gameId;
    $user['game_name'] = $game['game_name'];
//        $user['p_uid'][0]['p_uid']= 'o0nhp5A9FjGECqaKs00o0xIa-1IM';
//        $user['game_id'] = '6';
//        print_r($user);die;
    $data = json_encode($user);
    $url = 'http://api.baizegame.com/Wx_minigame/weixinMsg';
//    self::curl_post('http://api.baizegame.com/Wx_minigame/weixinMsg',$user);

//}
//
//function curl_post($url, $data = null)
//{
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
//    return $output;
//}
?>