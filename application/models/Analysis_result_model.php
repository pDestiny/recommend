<?php 
class Analysis_result_model extends CI_Model {
    protected $TB;
    
    
    public function __construct() {
        parent::__construct();

        $this->load->database();
        $this->TB = "analysis_result";
    }

}