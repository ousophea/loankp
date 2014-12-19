<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('BASEPATH'))
    exit('Permission Denied!');

class m_report extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
    }

    /**
     * 
     */
//    function select_count_trn() {
//        $data['total_debit'] = $this->db->select('SUM(tra_debit) as debit_total');
//        $data['total_credit'] = $this->db->select('SUM(tra_credit) as credit_total');
//        return $data;
//    }
    public function select_count_trn($arr_total_case = array()) {
        $this->db->select_sum('tra_credit', 'total_credit');
        $this->db->select_sum('tra_debit', 'total_debit');
        foreach ($arr_total_case as $field => $value) {
            $this->db->where($field, $value);
        }

        // $this->db->where($arr_total_case);
        $query = $this->db->get('transaction');
        //echo $this->db->last_query();
        return $query;
    }

    public function sum_balance($arr_item_where = NULL) {
        if ($arr_item_where != NULL) {
            foreach ($arr_item_where as $field => $value) {
                $data['balance'] = $this->db->where($field, $value);
            }
        }
        $data['balance'] = $this->db->select('SUM(tra_debit) as debit_total,SUM(tra_credit) as credit_total');
        return $data;
    }


    public function get_contact_info($sum_query=NULL) {
        $this->db->where('loa_status', 0);
        $this->db->select('*');
        $this->db->join('contacts_detail', 'con_id=con_det_con_id');
        $this->db->join('contacts_type', 'con_con_typ_id=con_typ_id');
        $this->db->join('loan_account', 'loan_account.loa_acc_con_id=contacts.con_id', 'left');
//        ============ More detail about contact ============
        $this->db->join('provinces', 'contacts_detail.con_det_pro_id=provinces.pro_id', 'left');
        $this->db->join('districts', 'contacts_detail.con_det_dis_id=districts.dis_id', 'left');
        $this->db->join('communes', 'contacts_detail.con_det_com_id=communes.com_id', 'left');
        $this->db->join('villages', 'contacts_detail.con_det_vil_id=villages.vil_id', 'left');
        $this->db->join('repayment_schedule', 'loan_account.loa_acc_id=repayment_schedule.rep_sch_loa_acc_id', 'inner');

       $this->db->select_sum('rep_sch_rate_repayment', 'total_rate');
        $this->db->group_by('rep_sch_loa_acc_id');
        
        $query = $this->db->get('contacts');
        return $query;
    }

}

?>
