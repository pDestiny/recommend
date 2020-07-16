<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MVAR_controller extends CI_Controller {
	private $stock_model;

	public function __construct()
	{
		parent::__construct();

		$this->load->model("Stock_data_model", "sp_model");
		$this->load->model("Analysis_params_model", "ap_model");
		$this->load->model("Analysis_result_model", "ar_model");
	}

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
	private function _python_exec($src, $args) {
		$args = json_encode($args);
		if ( $_SERVER['HTTP_HOST'] == LOCAL_HTTP_HOST ) {
			// window
			$commend = "python ". APPPATH. DS. "pycode". DS. $src. " $args";
			print_r($commend);
			$py_return = exec($commend);

		} else if ( $_SERVER['HTTP_HOST'] == LIVE_HTTP_HOST ) {
			// linux - centos
			// conda weather_analysis_with_tf env 
			$commend = DS . "root". DS."anaconda3" . DS . "envs". DS. "weather_analysis_with_tf" . DS . "bin". DS. "python " . APPPATH . "pycode" .DS . $src . " $args";
			$py_return = exec($commend);
		}


		return $py_return;
	}

	public function index()
	{
		//인덱스에서 시뮬레이션을 시작하면 로딩페이지로 이동
		$this->load->view('input');
	}

	public function loading()
	{
		$post = $this->input->post();

		$idata =[
            "ap_name" => $post["anlysis_name"],
			"ap_desc" => $post["desc"],
			"ap_asset" => $post["asset"],
            "ap_r_dt_s" => $post["r_dt_s"],
            "ap_r_dt_e" => $post["r_dt_e"],
            "ap_bt_dt_s" => $post["bt_dt_s"],
            "ap_bt_dt_e" => $post["bt_dt_e"],
            "ap_interval" => $post["interval"],
            "ap_eta" => $post["eta"],
            "ap_max_iter" => $post["max_iter"],
			"ap_is_finish" => 0
		];


		if($this->ap_model->is_analyzed($idata["ap_name"]) || $this->ap_model->is_saved($idata["ap_name"])) {
			print("<script>alert('이미 분석되었거나 분석중입니다..');location.href='/mvar/list';</script>");
			return;
		}

		$ap_id = $this->ap_model->save($idata, $post["stock_id"]);

		
		// $this->ap_model->save($idata, $post["stock_id"]);
		
		
		//loading page 에서 index에서 넘긴 파라미터를 db 저장

		//loading page 에서는 한 기업당 1 에포치의 시간을 11초로 잡고 progress bar 실행
		$n = count($post["stock_id"]);
		
		$epoches_n = $this->ap_model->get_epoch_n($ap_id, $idata["ap_interval"]);

		$time_exp = ($n * 24 + $n * $epoches_n * intval($idata["ap_max_iter"])  * 1 / 300) * 1000 + 14000; // millisec 단위

		$this->load->view("loading",[
			"time_exp" => $time_exp,
			"id" => $ap_id 
		]);
		//progress bar가 끝나면, ajax로 분석이 끝났는지를 3초 간격으로 확인
		
		//만일 끝났다면 result 페이지로 이동
		
	}

	public function ajax_is_analysis_finished()
	{
		$analysis_id = $this->input->get("analysis_id");

		$is_finish = $this->ap_model->is_finish($analysis_id);

		# 실제 분석이 끝났는지 확인
		print(json_encode([
			"is_finish" => $is_finish
		]));
		
	}

	public function ajax_analysis_start($id)
	{
		print_r($id);
		$py_return = $this->_python_exec("back_test.py", $id);

		print_r($py_return);
	}

	public function result($id)
	{
		
		$params = $this->ap_model->get_one($id);

		$n = $this->ap_model->get_n_stocks($id);

		$codes = $this->ap_model->get_codes($id);

		$codes_arr = [];

		foreach($codes as $val) {
			$codes_arr[] = $val["as_code"];
		}
		# code별 주식 데이터 가져오기 날짜는 $params에서 가져온다
		$bt_dt_s = $params["ap_bt_dt_s"];
		$bt_dt_e = $params["ap_bt_dt_e"];

		$bt_period_stock_data = $this->sp_model->get_backtest_stock_data($bt_dt_s, $bt_dt_e, $codes_arr);
		
		$result_data = $this->ar_model->get_analysis_result($id);

		$this->load->view("result", [
			"params"=> $params,
			"result_data" => $result_data,
			"n" => $n,
			"bt_period_stock_data" => $bt_period_stock_data
		]);

		$this->load->view("chartjs_script");
	}

	public function ajax_get_stocks()
	{
		$get = $this->input->get();

		$r_date_s = $get["r_date_s"];
		$bt_date_e = $get["bt_date_e"];
		$stock_sch_content = $get["stock_sch_content"];

		$stock_data = $this->sp_model->get_stock_data($r_date_s, $bt_date_e, $stock_sch_content);

		print(json_encode($stock_data));
	}

	public function ajax_dup_check()
	{
		$ap_name = $this->input->get("ap_name");

		$is_unique = $this->ap_model->is_unique($ap_name);

		print(json_encode(["is_unique" => $is_unique]));
	}

	public function list()
	{
		$all_analysis_list = $this->ap_model->all();
		$this->load->view("list", [
			"data" => $all_analysis_list
		]);
	}

	public function delete($id)
	{
		$this->ap_model->del($id);

		$this->ar_model->del($id);

		$this->list();
	}
}
