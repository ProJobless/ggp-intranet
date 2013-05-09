<?php

class Display extends CI_Model {
	var $data = array();
	var $base;
	var $status = '';

	function __construct() {
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('user_agent');
		$this->data['css'] = $this->config->item('css');
		$this->data['base'] = $this->config->item('base_url');
		$this->base = $this->config->item('base_url');
		$this->data['myrobots'] = '<meta name="robots" content="noindex,nofollow">';
/*		$sessionid = $this->session->userdata('session_id');
		$this->db->select('status');
		$this->db->where('session_id',$sessionid);
		$query = $this->db->get('ci_sessions');
		if($query->num_rows() > 0) {
			$row = $query->row();
			$this->status = $row->status;
		} */
		$this->status = '0';
	}

	function mainpage($mydata) {
		$this->data['mytitle'] = 'GGP Systems Ltd Intranet';
		// $this->data['diagnostic'] = $diagnostic;
		foreach($mydata as $key => $variable) {
			$this->data[$key] = $variable;
		}
		$mysess = $this->session->userdata('session_id');
		if(isset($this->status) && $this->status > 0) {
			$this->data['menu'] = $fred->show_menu($this->status);
		}
		$this->load->view('basic_view', $this->data);
	}
}

/* End of file display.php */
/* Location: ./system/application/models/display.php */
?>
