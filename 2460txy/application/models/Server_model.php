<?php

class Server_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('server_list');
        $this->load->driver('cache', array('adapter' => 'file'));
    }

    public function get_by_server_id($server_id, $platform)
    {
        $server_map_str = $this->cache->get('server_map');
        $server_map = array();
        if (!$server_map_str) {
            $server_map_str = $this->flush_cache();
        }

        $server_map = json_decode($server_map_str);

        if (isset($server_map->id)) {
            return $server_map->id;
        } else {
            $condition = array(
                'server_id' => $server_id,
                'platform'=>$platform
            );
            $server = $this->get_one_by_condition($condition);
            if ($server) {
                $this->flush_cache();

                return $server;
            }
        }
    }

    public function get_key($game_id, $key_name)
    {
        $server = $this->get_by_game_id($game_id);
        $keys = json_decode($server->id);
        if ($keys) {
            return $keys->id;
        } else {
            return false;
        }
    }

    public function flush_cache()
    {
        // $query = $this->db->get('server_list');
        $server = $this->get_by_condition();
        foreach ($server as $one) {
            $server_map[$one->server_list_id] = $one;
        }

            // make it string and decode it later, because the array and obj problem.
            $server_map_str = json_encode($server_map);
        $this->cache->save('server_map', $server_map_str, 60 * 60 * 24 * 7);

        return $server_map_str;

        // return;
    }
    public function insert_server($server_id, $status, $platform)
    {
        $data = array(
            'platform' =>$platform,
            'status' =>$status,
            'server_id' =>$server_id,
        );
        $server = $this->db->insert('server_list', $data);
        return $server;
    }
    public function update_server($server_id, $status, $platform)
    {
        $data = array(
            'status' =>$status,
        );
        $while=array(
            'platform' =>$platform,
            'server_id' =>$server_id,
        );
        $server = $this->update($data, $while);
        return $server;
    }
    public function delete_server($server_id, $status, $platform)
    {
        $data = array(
            'status' =>$status,
        );
        $while=array(
            'platform' =>$platform,
            'server_id' =>$server_id,
        );
        $server = $this->db->delete('server_list', $while);
        return $server;
    }
}
