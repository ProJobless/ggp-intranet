<?php

class Wol extends CI_Controller {
	var $controller = 'wol';

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->library('session');
		$this->load->model('crud');
	}

	function insert($id) {
		$this->crud->insert($this->controller, $id);
	}

	function interim() {
		$this->crud->insert2($this->controller, $_POST);
	}

	function delete($idno=0, $state='no') {
		if(isset($_POST['id'])&& $_POST['id'] > 0) {
			$idno = $_POST['id']; }
		if(isset($_POST['submit'])) {
			$state = $_POST['submit']; }
		$this->crud->delete($this->controller, $idno, $state);
	}

	function showall($message='') {
		$this->crud->showall($this->controller, $message);
	}

	function read() {
		$this->crud->read($this->controller);
	}

	function test() {
		$this->crud->test();
	}
	
	function index($wakeid=0,$orderby='user') {
		$order=array('desc','user');
		if (isset($_POST['wakeid'])&& $_POST['wakeid'] > 0) {
			$wakeid = $_POST['wakeid'];
		}
		if (isset($_POST['orderby'])) {
			$orderby = $order[$_POST['orderby']];
		}
		if ($wakeid!=0) {
			$this->WakeOnLan($wakeid);
		}
		$this->load->helper(array('date','form','url'));
		$this->load->library(array('parser','form_validation'));
		$rules['service'] = 'required';
		$this->form_validation->set_rules($rules);
		$fields['service'] = 'Service';
		$this->form_validation->set_fields($fields);
		$format = 'DATE_ISO8601';
		$time = time();
		$atts = array(
			'class' => 'link-mailto',
			);
		$data = array(
			'intranet_title' => 'GGP Systems Ltd intranet',
			'intranet_module' => 'Wake-on-LAN',
			'intranet_user' => $_SERVER['INTRANET_USER'],
			'intranet_pass' => $_SERVER['INTRANET_PASS'],
			'author_name' => 'Murray Crane',
			'render_date' => standard_date($format, $time),
			'year' => mdate('%Y'),
		);
		$data['author_mailto'] = safe_mailto('murray@ggpsystems.co.uk',$data['author_name'],$atts);
		$atr = array(
		  'name' => 'form1',
		);
		$data['variable_pre'] = '<script type="text/javascript" src="/wz_tooltip.js"></script>';
		$data['variable_pre'] .= "\n";
		$data['variable_pre'] .= form_open('wol',$atr);
		$data['variable_post'] = form_close();
		$data['variable_post'] .= "\n".'<script type="text/javascript">
function submitOrderBy(order)
{
	var el = document.createElement("input");
	el.type = "hidden";
	el.name = "orderby";
	el.value = order;
	document.form1.appendChild(el);
	document.form1.submit();
}
</script>'."\n";
		$this->db->select('id,user,desc')->from('wol')->order_by($orderby);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$i = 1;
			foreach ($query->result_array() as $row) {
				$data['wol'][] = array(
					'class' => $i,
					'id' => $row['id'],
					'user' => $row['user'],
					'desc' => $row['desc'],
					'radio' => '<input type="radio" name="wakeid" value="'.$row['id'].'" onclick="javascript:document.getElementById(&apos;wake&apos;).disabled=false;" />',
					);
				($i==1?$i++:$i--);
			}
		}
		$btn = array(
			'id' => 'wake',
			'name' => 'btnSubmit',
			'value' => 'Wake Machine',
			'disabled' => 'disabled'
		);
		$data['variable'] = form_submit($btn);

		$this->parser->parse('page_head',$data);
		$this->parser->parse('wol_list',$data);
		$this->parser->parse('phone_list_form',$data);
		$this->parser->parse('page_foot',$data);
	}

	# Wake on LAN - (c) HotKey@spr.at, upgraded by Murzik
	# Modified by Allan Barizo http://www.hackernotcracker.com
	# CIed by Murray Crane
	function WakeOnLan($wakeid=0) {
		$bcast_addr = "10.0.0.255";
		$socket_number = "7";
		if ($wakeid > 0) {
			$this->db->select('name')->from('wol')->where('id',$wakeid);
			$query = $this->db->get();
			if ($query->num_rows()==1) {
				$row = $query->result_array();
				$mac = strtoupper($row[0]['name']);
				$addr_byte = explode(':', $mac);
				$hw_addr = '';
				for ($a=0; $a <6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));
				$msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
				for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;
				// send it to the broadcast address using UDP
				// SQL_BROADCAST option isn't help!!
				$s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
				if ($s == false) {
					echo "Error creating socket!\n";
					echo "Error code is '".socket_last_error($s)."' - " . socket_strerror(socket_last_error($s));
					return FALSE;
				} else {
					// setting a broadcast option to socket:
					$opt_ret = socket_set_option($s, SOL_SOCKET, SO_BROADCAST, TRUE);
					if($opt_ret <0) {
						// echo "setsockopt() failed, error: " . strerror($opt_ret) . "\n";
						return FALSE;
					}
					if(socket_sendto($s, $msg, strlen($msg), 0, $bcast_addr, $socket_number)) {
						// echo "Magic Packet sent successfully!"; */
						socket_close($s);
						return TRUE;
					} else {
						// echo "Magic packet failed!";
						return FALSE;
					}
				}
			}
		}
	}
}
?>
