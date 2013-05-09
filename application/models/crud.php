<?php

class Crud extends CI_Model {

	var $data = array();
	var $form = array();
	var $status = '';
	var $controller;

	function __construct() {
		parent::__construct();
		$this->load->helper(array('form','url'));
		$this->load->library('form_validation');
		$this->load->model('display');
		$this->form = array(
			'departments' => array(
				'id' => array('Department ID','readonly','numeric'),
				'name' => array('Department name','input','required|alpha_space'),
				'submit' => array('Enter details','submit','numeric'),
				),
			'doorcards' => array(
				'id' => array('Doorcard ID','readonly','numeric'),
				'name' => array('Doorcard Number','input','required|numeric'),
				'submit' => array('Enter details','submit','numeric'),
				),
			'extensions' => array(
				'id' => array('Telephone extension ID','readonly','numeric'),
				'name' => array('Extension number','input','required|numeric'),
				'desc' => array('Extension description','input','alpha_space|xss_clean'),
				'submit' => array('Enter details','submit','numeric'),
				),
			'staff' => array(
				'id' => array('Staff member ID','readonly','numeric'),
				'name' => array('Staff member full name','readonly','alpha_space|xss_clean'),
				'firstname' => array('Staff member first name','input','required|alpha|xss_clean'),
				'midname' => array('Staff member middle name(s)','input','alpha_space|xss_clean'),
				'surname' => array('Staff member surname','input','required|alpha_space|xss_clean'),
				'extn_id' => array('Telephone extension','dropdown','required|numeric','extensions'),
				'dept_id' => array('Department','dropdown','required|numeric','departments'),
				'start_date' => array('Start date','input','date'),
				'end_date' => array('End date','input','date'),
				'display_midname' => array('Display middle name(s)','checkbox','numeric'),
				'door_card_id' => array('Door entry card number','dropdown','required|numeric','doorcards'),
				'submit' => array('Enter details','submit','numeric'),
				),
			'telephones' => array(
				'id' => array('External telephone ID','readonly','numeric'),
				'name' => array('External telephone number','input','required|telephone'),
				'desc' => array('Number description','input','alpha_space'),
				'staff_id' => array('Staff member','dropdown','required|numeric','staff'),
				'submit' => array('Enter details','submit','numeric'),
				),
			'services' => array(
				'id' => array('Service ID','readonly','numeric'),
				'name' => array('Service short name','input','required|alpha_space|numeric|xss_clean'),
				'description' => array('Service description','input','alpha_space|numeric|xss_clean'),
				'machine' => array('NetBIOS name','input','required|alpha_space|numeric|xss_clean'),
				'submit' => array('Enter details','submit','numeric'),
				),
			'wol' => array(
				'id' => array('Machine ID','readonly','numeric'),
				'name' => array('Machine MAC Address','input','required|xss_clean'),
				'desc' => array('Machine description','input','required|xss_clean'),
				'user' => array('Machine owner','input','required|alpha_space|xss_clean'),
				'submit' => array('Enter details','submit','numeric'),
				),
			);
	}

	function showall($controller='',$message='',$test='no') {
		$result = '';
		$mysess = $this->session->userdata('session_id');
		$mystat = $this->session->userdata('status');
		if(!$this->db->table_exists($controller)) {
			$place = __FILE__.__LINE__;
			$outcome = "exception: $place: looking for table $controller: it doesn't exist ";
			if($test == 'yes') {
				$place = __FILE__.__LINE__;
				return $outcome;
			} else {
				show_error($outcome);
			}
		}
		$this->db->select('id, name');
		$query = $this->db->get($controller);
		if($query->num_rows() >0) {
			$result .= "<table class='table'>";
			$result .= "<tr><td colspan='3'><h3>$controller table management</h3></td></tr>";
			$result .= "<tr><td colspan='3' class='message'>$message</td></tr>";
			$result .= "<tr><td colspan='3'>";
			$result .= anchor("$controller/insert/0", 'New entry');
			$result .= "</td></tr>";
			$result .= "<tr><td colspan='3'>";
			$result .= anchor("$controller/read",'Show all entries in the table');
			$result .= "</td></tr>";
			foreach($query->result() as $row) {
				$result .= "<tr><td>";
				$result .= $row->id;
				$result .= " ";
				$result .= $row->name;
				$result .= "</td><td>";
				$result .= anchor("$controller/insert/$row->id",'Update this entry');
				$result .= "</td><td>";
				$result .= anchor("$controller/delete/$row->id",'Delete');
				$result .= "</td></tr>";
			}
			$result.="</table>";
			$data['text']=$result;
			$this->display->mainpage($data,$this->status);
		} else {
			$place = __FILE__.__LINE__;
			$outcome = "exception: $place: no results from table $controller ";
			if($test == 'yes') {
				$place = __FILE__.__LINE__;
				return $outcome;
			} else {
				$message = "No data in the $controller table";
				show_error($message);
			}
		}
	}

	function read($controller) {
		$this->load->library('table');
		$tmpl = array(
			'table_open' => "<table border='1' cellpadding='4' cellspacing='0' width='100%'>",
			'row_alt_start' => "<tr bgcolor='silver'>",
			);
		$this->table->set_template($tmpl);
		$this->load->database();
		$this->load->library('table');
		$query = $this->db->get($controller);
		$result = $this->table->generate($query);
		$data['text'] = $result;
		$this->display->mainpage($data);
	}

	function delete($controller,$idno=0,$state='no',$test='no') {
		if(!isset($state) || $state != 'yes') {
			if($test == 'yes') {
				$place = __FILE__.__LINE__;
				$outcome = "exception: $place: sent state value $state to trydelete function ";
				return $outcome;
			} else {
				$this->trydelete($controller,$idno,'no');
			}
		} else {
			if(isset($idno) && $idno > 0 && is_numeric($idno)) {
				if($test == 'yes') {
					$place = __FILE__.__LINE__;
					$outcome = "OK at $place: doing delete on id of $idno ";
					return $outcome;
				} else {
					$this->db->where('id',$idno);
					$this->db->delete($controller);
					$changes = $this->db->affected_rows();
				}
				if($changes != 1) {
					$place = __FILE__.__LINE__;
					$outcome = "exception: $place: cdnt delete on $controller with id of $idno ";
					if($test == 'yes') {
						return $outcome;
					} else {
						show_error($outcome);
					}
				} else {
					if($test == 'yes') {
						return 'OK';
					} else {
						$this->showall($controller,"Entry no. $idno deleted.");
					}
				}
			} else {
				$place = __FILE__.__LINE__;
				$outcome = "exception: $place: id of $idno set for delete in $controller, expecting integer ";
				if($test == 'yes') {
					return $outcome;
				} else {
					show_error($outcome);
				}
			}
		}
	}

	function trydelete($controller,$idno,$submit='no') {
		if($submit == 'yes') {
			$this->delete($controller,$idno,'yes');
		} else {
			$result = "<table><tr><td>Are you sure you want to delete this entry?</td></tr>";
			$result .= form_open("$controller/delete");
			$result .= form_hidden('id',$idno);
			$result .= "<tr><td>";
			$result .= form_submit('submit','yes');
			$result .= "</td></tr>";
			$result .= form_close();
			$result .= "</table>";
			$result .= anchor("$controller/showall","No, don't delete");
			$data['text'] = $result;
			$this->display->mainpage($data);
		}
	}

	function insert($controller='',$id=0,$test='no') {
		$myform = '';
		$myid = 0;
		$currentvalue=array();
		if(!$this->db->table_exists($controller)) {
			$place = __FILE__.__LINE__;
			$outcome = "exception: $place: looking for table $controller: it doesn't exist ";
			if($test == 'yes') {
				return $outcome;
			} else {
				show_error($outcome);
			}
		} else {
			if($test == 'yes') {
				return 'OK';
			}
		}
		if(isset($id) && $id > 0) {
			$myid = $id;
			$this->db->where('id',$id);
			$query = $this->db->get($controller);
			if($query->num_rows() > 0) {
				$row = $query->row();
				foreach($row as $key => $value) {
					if(isset($this->form_validation->$key)) {
						$_POST[$key] = $this->form_validation->$key;
					}
					if(isset($_POST[$key])) {
						$currentvalue[$key] = $_POST[$key];
					} else {
						$currentvalue[$key] = $value;
					}
				}
				if($test == 'yes') {
					$place = __FILE__.__LINE__;
					$outcome = "OK at $place: id $id return results from $controller so updating ";
					return $outcome;
				}
				$myform .= "<tr><td colspan='2'>Update existing entry number $id</td></tr>";
			} else {
				$place = __FILE__.__LINE__;
				$outcome = "exception: $place: despite id $id, cant get results from $controller table ";
				if($test == 'yes') {
					return $outcome;
				} else {
					show_error($outcome);
				}
			}
		} else {
			if(isset($_POST)) {
				foreach($_POST as $key => $value) {
					if(isset($_POST[$key])) {
						$currentvalue[$key] = $_POST[$key];
					} 
				}
			}
			if(!array_key_exists('id',$currentvalue)) {
				foreach($this->form[$controller] as $key => $value) {
					$currentvalue[$key]='';
				}
			}
			$myform .= "<p>New entry</p>";
			if($test == 'yes') {
				$place = __FILE__.__LINE__;
				$outcome = "exception: $place: id $id treated as no id, new entry ";
				return $outcome;
			}
		}
		$myform .= "<table class='table'>";
		$myform .= form_open("$controller/interim");
		$myform .= '<p>This entry could not be made because...</p>';
		$myform .=$this->form_validation->error_string;
		foreach($this->form[$controller] as $key => $value) {
			$fieldtype = $value[1];
			if (isset($id) && $id > 0) {
				if(isset($this->form_validation->$key)) {
					$val_string = $this->form_validation->$key;
				}
			}
//$myform .= "<code>DEBUG value:".print_r($value)."</code>";
			switch($value[1]) {
				case 'input':
					$data = array(
						'name' => $key,
						'id' => $key,
						'value' => $currentvalue[$key],
						'maxlength' => '100',
						'size' => '50',
						'style' => 'width:50%',
						);
					$myform .= "<tr><td>$value[0]</td><td>";
					$myform .= form_input($data);
					$myform .= "</td></tr>";
					if($test == 'second') {
						return 'input';
					}
					break;
				case 'textarea':
					$data = array(
						'name' => $key,
						'id' => $key,
						'value' => $currentvalue[$key],
						'rows' => '6',
						'cols' => '70',
						'style' => 'width:50%',
						);
					$myform .= "<tr><td valign='top'>$value[0]</td><td>";
					$myform .= form_textarea($data);
					$myform .= "</td></tr>";
					break;
				case 'dropdown':
					if(isset($value[3])) {
						$dropbox = array();
						$this->db->select('id,name');
						$query = $this->db->get($value[3]);
						if($query->num_rows() > 0) {
							foreach($query->result() as $row) {
								$dropbox[$row->id] = $row->name;
							}
						}
					}
					$myform .= "<tr><td valign='top'>$value[0]</td><td>";
					$myform .= form_dropdown($key,$dropbox,$currentvalue[$key]);
					$myform .= "</td></tr>";
					break;
				case 'submit':
					$myform .= "<tr><td>$value[0]</td><td>";
					$time = time();
					$data = array(
						'id' => 'submit',
						'name' => 'submit',
						'value' => 'Submit',
						);
					$myform .= form_submit($data);
					$myform .= "</td></tr>";
					break;
				case 'hidden':
					$myform .= form_hidden($key,$currentvalue[$key]);
					break;
				case 'readonly':
					$myform .= "<tr><td>$value[0]</td><td>$currentvalue[$key]";
					$myform .= form_hidden($key,$currentvalue[$key]);
					$myform .= "</td></tr>";
					break;
				case 'timestamp':
					$myform .= "<tr><td>$value[0]</td><td>now()";
					if($currentvalue[$key] == ''||$currentvalue[$key] == 0) {
						$timenow = time();
					} else {
						$timenow = $currentvalue[$key];
					}
					$myform .= form_hidden($key,$timenow);
					$myform .= "</td></tr>";
					break;
				case 'updatestamp':
					$myform .= "<tr><td>$value[0]</td><td>now()";
					$timenow = time();
					$myform .= form_hidden($key,$timenow);
					$myform .= "</td></tr>";
					break;
				case 'checkbox':
					$myform .= "<tr><td>$value[0]</td><td>";
					$myform .= form_checkbox($key,'1',$currentvalue[$key]);
					$myform .= "</td></tr>";
					break;
				default:
					$place = __FILE__.__LINE__;
					$outcome = "exception: $place: cannot handle $fieldtype ";
					if($test == 'second') {
						return $outcome;
					} else {
						show_error($outcome);
					}
					break;
			}
		}
		$myform .= form_hidden('submit',$time);
		$myform .= form_close();
		$myform .= "</table>";
		$data['text'] = $myform;
		$this->display->mainpage($data);
	}

	function insert2($controller,$newpost,$test='no') {
		$myform = '';
		if(!$this->db->table_exists($controller)) {
//Test here!
		}
		$this->load->library('form_validation');
		$_POST = $newpost;
		$errorform = '';
		$newtemparray = $this->form[$controller];
		foreach($newtemparray as $key => $value) {
			$fields[$key] = $value[0];
			$rules[$key] = $value[2];
		}
		$this->form_validation->set_fields($fields);
		$this->form_validation->set_rules($rules);
		if($this->form_validation->run() == FALSE) {
			$id = $_POST['id'];
			$this->insert($controller,$id,'no',$_POST);
		} else {
			if(isset($_POST['id']) && $_POST['id'] > 0) {
				$tempid = $_POST['id'];
				unset($_POST['id']);
				$this->db->where('id',$tempid);
				$this->db->update($controller,$_POST);
				if($this->db->affected_rows() == 1) {
					$this->showall($controller, "Entry number $tempid updated.");
				} else {
					show_error("Failed to update $controller for id no $tempid");
				}
			} else {
				$this->db->insert($controller,$_POST);
				if($this->db->affected_rows() == 1) {
					$this->showall($controller,"New entry added.");
				} else {
					show_error("Failed to make new entry in $controller");
				}
			}
		}
	}

/*	function test() {
		$return = "<h3>Test results</h3>";
		$this->extendarray();
		$return .= $this->testarray();
		$this->reducearray();
		$return .= $this->testarray();
		$this->testbuild();
		$return .= $this->testdelete();
		$this->testdestroy();
		$return .= $this->testinsert();
		$return .= $this->testinsert2();
		$return .= $this->testshowall();
		$data['text'] = $return;
		$this->display->mainpage($data);
	} */
}

/* End of file crud.php */
/* Location: ./system/application/models/crud.php */
?>
