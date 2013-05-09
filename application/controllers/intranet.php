<?php

class Intranet extends CI_Controller {

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
		
		if (date('l')=='Tuesday') {
			$this->db->select('id')->from('tbl_backups')->where('notification_date',date('Y-m-d'))->where('notified',0);
			$query = $this->db->get();
			if ($query->num_rows()==1) {
				$this->backups(date('Y-m-d',mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"))));
				$row = $query->result_array();
				$myData=array('notified'=>1);
				$this->db->update('tbl_backups',$myData,array('id'=>$row[0]['id']));
			}
		}
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

	protected function backups($backupDate) {
		$this->load->library('email');

		$tapes = array(
			'January'=>'<strong>AIT000012</strong> and <strong>AIT000013</strong>',
			'February'=>'<strong>AIT000014</strong> and <strong>AIT000015</strong>',
			'March'=>'<strong>AIT000016</strong> and <strong>AIT000017</strong>',
			'April'=>'<strong>AIT000018</strong> and <strong>AIT000019</strong>',
			'May'=>'<strong>AIT000020</strong> and <strong>AIT000021</strong>',
			'June'=>'<strong>AIT000022</strong> and <strong>AIT000023</strong>',
			'July'=>'<strong>AIT000024</strong> and <strong>AIT000025</strong>',
			'August'=>'<strong>AIT000026</strong> and <strong>AIT000027</strong>',
			'September'=>'<strong>AIT000028</strong> and <strong>AIT000029</strong>',
			'October'=>'<strong>AIT000030</strong> and <strong>AIT000031</strong>',
			'November'=>'<strong>AIT000032</strong> and <strong>AIT000033</strong>',
			'December'=>'<strong>AIT000034</strong> and <strong>AIT000035</strong>',
			);
		$recipients = array(
			'murray.crane@ggpsystems.co.uk',
			'tim.maxwell@ggpsystems.co.uk',
			);
		$lineEnd = "\n";

		$subject = 'Backup resource(s) required';
		$thisMonth = date('F');
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta name="GENERATOR" content="CodeIgniter" />
<style type="text/css"> 
<!-- body { font-family : Calibri,sans-serif; font-size : 11pt; } --> 
</style>
</head>
<body>';
		$html .= '<p>Please provide the <strong>' . $thisMonth . '</strong> offsite backup tapes (labelled ' . $tapes[$thisMonth] . ') for the offsite backup tommorrow.</p>';
		$html .= '<p>Kind regards</p>';
		$html .= '<p><strong>Murray Crane</strong><br />';
		$html .= 'Network Administrator</p>';
		$html .= '<p><strong>GGP Systems Ltd</strong><br />';
		$html .= '<strong>T</strong>: 020 8686 9887 | <strong>E</strong>: <a href="mailto:murray.crane@ggpsystems.co.uk" title="murray DOT crane AT ggpsystems DOT co DOT uk">murray DOT crane AT ggpsystems DOT co DOT uk</a><br />';
		$html .= '<strong>F</strong>: 020 8662 8665 | <strong>W</strong>: <a href="http://www.ggpsystems.co.uk/" title="GGP Systems Ltd web site">www DOT ggpsystems DOT co DOT uk</a></p>';
		$html .= '<p style="color: seagreen;"><strong><em>*** AUTOMATED EMAIL - DO NOT REPLY ***</em></strong></p>';
		$html .= '</body>
</html>';

		$this->email->from('donotreply@ggpsystems.co.uk','GGP Systems Ltd');
		$this->email->to($recipients);
		$this->email->subject($subject);
		$this->email->message($html);

		if (!$this->email->send()) {
			echo $this->email->print_debugger();
		}
	}
}

/* End of file intranet.php */
/* Location: ./system/application/controllers/intranet.php */
