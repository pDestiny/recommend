<?php 
class Analysis_params_model extends CI_Model {
    protected $TB;
    protected $SUB;
    
    public function __construct() {
        parent::__construct();

        $this->load->database();
        $this->TB = "analysis_params";
        $this->SUB = "analysis_stocks";   
    }

    public function all()
    {
        return $this->db->select("*")->get($this->TB)->result_array();
    }

    public function is_unique($ap_name) {
        $this->db->select("count(ap_name) as count")->where(["ap_name" => $ap_name]);

        return !$this->db->get($this->TB)->result_array()[0]["count"];
    }

    public function save($idata, $stock_ids)
    {
        $this->db->insert($this->TB, $idata);

        $last_id = $this->db->insert_id();

        foreach($stock_ids as $val) {
            $this->db->insert($this->SUB, [
                "as_ap_id" => $last_id,
                "as_code" => $val
            ]);
        }

        return $last_id;
    }

    public function get_epoch_n($ap_id, $interval)
    {
        $sql = "SELECT (datediff(ap_bt_dt_e, ap_bt_dt_s) / ?) as diff FROM {$this->TB} WHERE ap_id = ?";

        $epoch = $this->db->query($sql, [intval($interval), intval($ap_id)]);

        return $epoch->result_array()[0]["diff"];
    }

    public function is_finish($ap_id)
    {
        $is_finish = $this->db->select("ap_is_finish")->where("ap_id", $ap_id)->get($this->TB);

        return $is_finish->result_array()[0]["ap_is_finish"] == 1;
    }

    public function del($id)
    {
        $this->db->delete($this->TB, [
            "ap_id" => $id
        ]);

        $this->db->delete($this->SUB, [
            "as_ap_id" => $id
        ]);
    }
}