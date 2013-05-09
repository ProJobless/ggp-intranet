<?php
class Staff extends CI_Controller {
	var $controller = 'staff';

	function __construct() {
		parent::__construct();
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
}

/* End of file staff.php */
/* Location: ./system/application/controllers/staff.php */
?>
