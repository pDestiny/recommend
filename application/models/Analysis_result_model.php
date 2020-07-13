<?php 
class Analysis_result_model extends CI_Model {
    protected $TB;
    
    
    public function __construct() {
        parent::__construct();

        $this->load->database();
        $this->TB = "analysis_result";
    }

    public function get_analysis_result($id)
    {
        # 일반 포트폴리오, MVAR 포트폴리오 에포치별 수익률 데이터

        ## MVAR
        $sql = "SELECT 
                    ar_kind, 
                    ar_epoch,
                    round(((SUM(ar_remain_assets) - SUM(ar_start_assets)) / SUM(ar_start_assets) * 100), 2) AS income_rate, 
                    round(SUM(ar_remain_assets), 2) as ar_remain_assets
                FROM analysis_result 
                    WHERE ar_ap_id = ? and ar_kind = 'MVAR' 
                    GROUP BY ar_kind, ar_epoch";
        $mvar_income = $this->db->query($sql, $id)->result_array();

        ## 일반
        $sql = "SELECT 
                    ar_kind, 
                    ar_epoch,
                    round(((SUM(ar_remain_assets) - SUM(ar_start_assets)) / SUM(ar_start_assets) * 100), 2) AS income_rate, 
                    round(SUM(ar_remain_assets),2) as ar_remain_assets 
                FROM analysis_result 
                    WHERE ar_ap_id = ? and ar_kind = 'ant' 
                    GROUP BY ar_kind, ar_epoch";
        $ant_income = $this->db->query($sql, $id)->result_array();

        ## 수익률 표준 편차 

        $sql = "SELECT a.ar_kind, stddev(a.income_rate) as std
                FROM (
                    SELECT 
                    ar_kind, 
                    ar_epoch,
                    round(((SUM(ar_remain_assets) - SUM(ar_start_assets)) / SUM(ar_start_assets) * 100), 2) AS income_rate, 
                    round(SUM(ar_remain_assets), 2) as ar_remain_assets 
                FROM analysis_result 
                    WHERE ar_ap_id = ? 
                    GROUP BY ar_kind, ar_epoch
                ) AS a group by a.ar_kind
        ";

        $std = $this->db->query($sql, $id)->result_array();

        # epoch별 비중

        # MVAR데이터
        $sql = "SELECT ar_epoch, ar_name, round(ar_weight * 100, 2) as ar_weight FROM analysis_result  WHERE ar_ap_id = ? AND ar_kind='MVAR' ORDER BY ar_epoch ASC, ar_name ASC";

        $mvar_weight = $this->db->query($sql, $id)->result_array();

        # 일반 데이터
        $sql = "SELECT ar_epoch, ar_name, round(ar_weight * 100, 2) as ar_weight FROM analysis_result  WHERE ar_ap_id = ? AND ar_kind='ant' ORDER BY ar_epoch ASC, ar_name ASC";

        $ant_weight = $this->db->query($sql, $id)->result_array();

        # 데이터 정리
        $data = [
            "income" => [
                "mvar" => $mvar_income,
                "ant" => $ant_income,
                "std" => $std
            ],
            "weight" => [
                "mvar" => $mvar_weight,
                "ant" => $ant_weight
            ]
        ];

        return $data;
    }
    
    public function del($id)
    {
        $this->db->delete($this->TB, [
            "ar_ap_id" => $id
        ]);
    }

}