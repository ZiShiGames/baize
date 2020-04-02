<?php

class Game_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('game');
        $this->load->driver('cache', array('adapter' => 'file'));
    }

    public function get_by_game_id($game_id)
    {
        $game_map_str = $this->cache->get('game_map');
        $game_map = array();
        if (!$game_map_str) {
            $game_map_str = $this->flush_cache();
        }

        $game_map = json_decode($game_map_str);

        if (isset($game_map->$game_id)) {
            return $game_map->$game_id;
        } else {
            $condition = array('game_id' => $game_id);
            $game = $this->get_one_by_condition($condition);
            if ($game) {
                $this->flush_cache();

                return $game;
            }
        }
    }

    public function get_key($game_id, $key_name)
    {
        $game = $this->get_by_game_id($game_id);
        $keys = json_decode($game->platform_key);
        if ($keys && isset($keys->$key_name)) {
            return $keys->$key_name;
        } else {
            return false;
        }
    }

    public function flush_cache()
    {
        $games = $this->get_by_condition();
        foreach ($games as $one) {
            $game_map[$one->game_id] = $one;
        }

        // make it string and decode it later, because the array and obj problem.
        $game_map_str = json_encode($game_map);
        $this->cache->save('game_map', $game_map_str, 60 * 60 * 24 * 7);

        return $game_map_str;
    }
}
