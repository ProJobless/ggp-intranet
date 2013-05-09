<?php

class Emails extends CI_Controller {
	function __construct() {
		parent::__construct();
	}
	function index() {
		$config['hostname'] = "localhost";
		$config['username'] = "sugarcrm";
		$config['password'] = "RUwEvathaca4";
		$config['database'] = "sugarcrm";
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";

		$this->load->database($config);
		
		$this->db->select('email_addresses.email_address,email_addr_bean_rel.bean_id');
		$this->db->from('email_addresses');
		$this->db->join('email_addr_bean_rel','email_addr_bean_rel.email_address_id=email_addresses.id');
		$this->db->where('email_addresses.invalid_email','1');
		$this->db->where('email_addresses.deleted','0');
		$this->db->where('email_addr_bean_rel.deleted','0');
		$this->db->order_by('email_addresses.email_address');

		$query = $this->db->get();
		$i = 0;
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$this->db->select('*');
				$this->db->where('id_c', $row->bean_id);
				$_query = $this->db->get('contacts_cstm');
				$data = array(
					'id_c' => $row->bean_id,
					'do_not_email_c' => 0,
					'invalid_email_c' => 1,
					);
				if ($_query->num_rows() > 0) {
					// ID already in the table, update
					$_row = $_query->row();
					$data['do_not_email_c'] = $_row->do_not_email_c;
					$this->db->where('id_c', $row->bean_id);
					$this->db->update('contacts_cstm', $data);
				} else {
					// ID not in the table, insert
					$this->db->insert('contacts_cstm',$data);
				}
				if($this->db->affected_rows()== 1) {
					$i++;
					print $row->email_address;
					print " updated.<br />";
				}
			}
			print $i." records changed.";
		}
	}
}
/* End of file emails.php */
/* Location: ./system/application/controllers/emails.php */
?>
