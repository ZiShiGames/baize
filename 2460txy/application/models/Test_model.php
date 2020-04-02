<?php
	class Test_model extends CI_model{
		public function __construct(){
			parent::__construct();
		}
		
		public function deal_data(){
			//$this->load->database();
			$seller_id = $this->input->get("seller_id");
			$start = $this->input->get("start");
			$end = $this->input->get("end");
			$platform = 'allu';
			
			if(!$start || !$end){
				return;
			}
			
			$start_date = strtotime($start);
			$end_date = strtotime($end)+60*60*24;
			
			if (!$start_date || !$end_date) {
				return;
			}
			
			$sql = "select * from sign_report where create_date>=".$start_date." and create_date<=".$end_date." and platform='allu' group by p_uid";
			$query = $this->db->query($sql);
			
			 if ($query->num_rows() > 0) {
				$res = $query->result();
				return $res;
			} else {
				return;
			}
			
		}
		
		public function deal_create_role_data(){
			$start = $this->input->get("start");
			$end = $this->input->get("end");
			$platform = $this->input->get("platform");
			if(!$start || !$end){
				return;
			}
			
			$start_date = strtotime($start);
			$end_date = strtotime($end)+60*60*24;
			
			if (!$start_date || !$end_date) {
				return;
			}
			
			$sql = "select * from create_role_report where create_date>=".$start_date." and create_date<=".$end_date." and platform='".$platform."'";
			$query = $this->db->query($sql);
			
			 if ($query->num_rows() > 0) {
				$res = $query->result();
				return $res;
			} else {
				return;
			}
			
		}
		
		
		
		
	}
?>