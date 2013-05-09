<?php

class Welcome extends CI_Controller {

	function Welcome()
	{
		parent::__construct();	
	}
	
	function index()
	{
		$this->load->library('email');

                $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta name="GENERATOR" content="CodeIgniter" />
<style type="text/css"> 
<!-- body { font-family : Calibri,sans-serif; font-size : 12pt; } --> 
</style>
</head>
<body>';
                $html .= '<p>Please provide the <strong>July</strong> offsite backup tapes (labelled <strong>AIT00000-AIT00000</strong>) at your earliest convenience for the upcoming offsite backup.</p>';
                $html .= '<p>Kind regards</p>';
                $html .= '<p><strong>Murray Crane</strong><br />';
                $html .= 'Network Administrator</p>';
                $html .= '<p><strong>GGP Systems Ltd</strong><br />';
                $html .= '<strong>T</strong>: 020 8686 9887 | <strong>E</strong>: <a href="#">murray.crane AT ggpsystems DOT co DOT uk</a><br />';
                $html .= '<strong>F</strong>: 020 8662 8665 | <strong>W</strong>: <a href="#">www DOT ggpsystems DOT co DOT uk</a></p>';
                $html .= '<p style="color: seagreen;"><strong><em>*** AUTOMATED EMAIL - DO NOT REPLY ***</em></strong></p>';
                $html .= '</body>
</html>';

                $this->email->from('donotreply@ggpsystems.co.uk','GGP Systems Ltd');
                $this->email->to('murray.crane@ggpsystems.co.uk');

                $this->email->subject('[CI] HTML email test');
                $this->email->message($html);
                $this->email->set_alt_message('See the little goblin!');

#		echo $html;
                $this->email->send();
                echo $this->email->print_debugger();
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */
