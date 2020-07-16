<?php 
class Stock_data_model extends CI_Model {
    protected $TB;
    protected $builder;
    
    
    public function __construct() {
        parent::__construct();

        $this->load->database();
        $this->TB = "stock_data";
    }

    public function get_backtest_stock_data($bt_dt_s, $bt_dt_e, $codes) {
        return $this->db->from($this->TB)->select("date, name, close")->where_in("code", $codes)->where([
            "date >=" => $bt_dt_s,
            "date <=" => $bt_dt_e
        ])->get()->result_array();
    }

    public function get_stock_data($r_date_s, $bt_date_e, $stock_sch_content)
    {
        $stock_sch_content_str = $this->db->escape($stock_sch_content);

        $stock_sch_content_int = intval($stock_sch_content);

        //print_r($stock_sch_content_int);

        $query = "SELECT a.name, a.code FROM (
            SELECT b.name, b.code, MIN(b.date) AS min_date, MAX(b.date) AS max_date FROM stock_data b GROUP BY b.name, b.code
        ) a WHERE a.min_date <= ? AND a.max_date > ? AND (a.name=$stock_sch_content_str or a.code=$stock_sch_content_int)";

        $result = $this->db->query($query, [$r_date_s, $bt_date_e])->result_array();

        //print_r($this->db->last_query());

        return $result;
    }

    
}