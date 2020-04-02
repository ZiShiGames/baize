<?php

class Mini_programs_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('mini_programs');
        $this->load->driver('cache', array('adapter' => 'file'));
    }
    public function get_by_mini_id($mini_id)
    {
        $mini_map_str = $this->cache->get('mini_programs_map');
        $mini_map = array();
        if (!$mini_map_str) {
            $mini_map_str = $this->flush_cache();
        }

        $mini_map = json_decode($mini_map_str);

        if (isset($mini_map->$mini_id)) {
            return $mini_map->$mini_id;
        } else {
            $condition = array('mini_id' => $mini_id);
            $mini = $this->get_one_by_condition($condition);
            if ($mini) {
                $this->flush_cache();

                return $mini;
            }
        }
    }

    // public function get_key($mini_id, $key_name)
    // {
    //     $game = $this->get_by_game_id($mini_id);
    //     $keys = json_decode($game->platform_key);
    //     if ($keys && isset($keys->$key_name)) {
    //         return $keys->$key_name;
    //     } else {
    //         return false;
    //     }
    // }

    public function flush_cache()
    {
        $mini_programs = $this->get_by_condition();
        foreach ($mini_programs as $one) {
            $game_map[$one->mini_id] = $one;
        }

        // make it string and decode it later, because the array and obj problem.
        $mini_map_str = json_encode($game_map);
        $this->cache->save('mini_programs_map', $mini_map_str, 60 * 60 * 24 * 7);

        return $mini_map_str;
    }
}
