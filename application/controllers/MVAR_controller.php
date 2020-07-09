<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mvar_controller extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	protected function _python_exec($src, $args) {
		$args = json_encode($args);
		if ( $_SERVER['HTTP_HOST'] == LOCAL_HTTP_HOST ) {
			// window
			$commend = "python ". APPPATH. DS. "pycode". DS. $src. " $args";
			$py_return = exec($commend);

		} else if ( $_SERVER['HTTP_HOST'] == LIVE_HTTP_HOST ) {
			// linux - centos
			// conda weather_analysis_with_tf env 
			$commend = DS . "root". DS."anaconda3" . DS . "envs". DS. "weather_analysis_with_tf" . DS . "bin". DS. "python " . APPPATH . "pycode" .DS . $src . " $args";
			$py_return = exec($commend);
		}

		
		return json_decode($py_return);
	}

	public function index()
	{
		$this->load->view('input');
	}
	

	public function result()
	{
		$post = $this->input->post();

		$py_return = $this->_python_exec("back_test.py", $post);

		$this->load->view("result", array(
			"DATA" => $py_return
		));
	}

	public function ajax_get_stocks()
	{

	}
}
