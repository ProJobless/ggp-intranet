<?php

class Machines extends CI_Controller {

	function __construct() {
		parent::__construct();	
	}
	
	function index() {
		$this->load->helper(array('date','form','url'));
		$this->load->library(array('parser','form_validation'));
		$this->load->database();
		
		$rules = array();
		$rules['order'] = 'required';
		$this->form_validation->set_rules($rules);
		
		$format = 'DATE_ISO8601';
		$time = time();
		
		$this->db->select('staff.id, staff.name, staff.display_midname, staff.start_date, staff.end_date, staff.xmpp, extensions.name AS extn,  departments.name AS dept')->from('staff')->join('extensions','extensions.id = staff.extn_id')->join('departments','departments.id = staff.dept_id');
		switch (set_value('order')) {
		case 4:
			$this->db->order_by('departments.name')->order_by('staff.firstname')->order_by('staff.surname')->order_by('extensions.name');
			$order = "Department Order";
			break;
		case 3:
			$this->db->order_by('extensions.name');
			$order = "Extension Number Order";
			break;
		case 2:
			$this->db->order_by('staff.surname')->order_by('staff.firstname')->order_by('extensions.name');
			$order = "Surname Order";
			break;
		case 1:
		default:
			$this->db->order_by('staff.firstname')->order_by('staff.surname')->order_by('extensions.name');
			$order = "Firstname Order";
			break;
		}
		$atts = array(
			'class' => 'link-mailto',
			);
		$data = array(
			'intranet_title' => 'GGP Systems Ltd intranet',
			'intranet_module' => 'Internal Telephone Directory - '.$order,
			'intranet_user' => $_SERVER['INTRANET_USER'],
			'intranet_pass' => $_SERVER['INTRANET_PASS'],
			'author_name' => 'Murray Crane',
			'render_date' => standard_date($format, $time),
			'year' => mdate('%Y'),
			);
		$data['author_mailto'] = safe_mailto('murray@ggpsystems.co.uk',$data['author_name'],$atts);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$i=1;
			foreach ($query->result_array() as $row) {
				$dateArr = explode("-",$row['start_date']);
				$startdate = mktime(0,0,0,$dateArr[1],$dateArr[2],$dateArr[0]);
				$dateArr = explode("-",$row['end_date']);
				$enddate = mktime(23,59,59,$dateArr[1],$dateArr[2],$dateArr[0]);
				if (($row['start_date']=="0000-00-00" || $startdate<=time()) && ($row['end_date']=="0000-00-00" || $enddate>=time())) {
					$this->db->select('desc, name')->from('telephones')->where('staff_id',$row['id']);
					$_query = $this->db->get();
					$row['externals'] = "";
					if ($_query->num_rows() > 0) {
						foreach ($_query->result_array() as $_row) {
							if (!empty($row['externals'])) {
								$row['externals'] .= "<br />";
							}
							$row['externals'] .= $_row['desc'].": ".$_row['name'];
						}
					}
					$data['staff'][] = array(
						'class' => $i,
						'extn' => $row['extn'],
						'name' => $row['name'],
						'externals' => $row['externals'],
						'dept' => $row['dept'],
						'xmpp' => $row['xmpp'],
						);
					($i==1?$i++:$i--);
				}
			}
		}
		
		$this->db->select('name')->from('departments');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				$data['depts'][] = array('dept' => $row['name']);
			}
		}
		$js = 'onchange="this.form.submit();"';
		$data['variable'] = form_open('intranet');
		$data['variable'] .= form_fieldset('Internal telephone directory order');
		$data['variable'] .= "<br />" . $this->form_validation->error_string;
		$ddarray = array(
			'1' => 'firstname',
			'2' => 'surname',
			'3' => 'extension number',
//			'4' => 'department',
			);
		$data['variable'] .= form_label('Select ordering:','order');
		$data['variable'] .= form_dropdown('order', $ddarray, set_value('order'), $js);
		$data['variable'] .= form_fieldset_close();
		$data['variable'] .= form_close();
		$data['variable_post'] = form_close();
		$data['variable_post'] .= "\n";
		$this->parser->parse('page_head',$data);
		$this->parser->parse('phone_list',$data);
		$this->parser->parse('phone_list_form',$data);
		$this->parser->parse('page_foot',$data);
	}
}

/* End of file machines.php */
/* Location: ./system/application/controllers/machines.php */
