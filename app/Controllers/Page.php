<?php

namespace App\Controllers;

use App\Models\LoanModel;

class Page extends BaseController
{
    public $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $member_id = '18383042121';

        $loan = new LoanModel();
        $data['loan'] = $loan->where('member_id', $member_id)->countAllResults();
        $data['return'] = $loan->where(array(
            'member_id' => $member_id,
            'is_return' => 1
        ))->countAllResults();
        $data['extend'] = $loan->where(array(
            'member_id' => $member_id,
            'renewed' => 1
        ))->countAllResults();
        $data['no_return'] = $data['loan'] - $data['return'];

        return view('home', $data);
    }

    public function peminjaman()
    {
        return view('peminjaman');
    }

    public function checkLoanRules()
    {
        $is_expired = true;

        $member_data = $this->db->query("SELECT m.*, mt.* FROM member AS m
            LEFT JOIN mst_member_type AS mt ON m.member_type_id = mt.member_type_id
            WHERE member_id='18383042121'")->getResult();

        $expire_date = $member_data[0]->expire_date;
        $is_pending = (bool)$member_data[0]->is_pending;
        $member_type_prop = array(
            'loan_limit' => $member_data[0]->loan_limit,
            'loan_periode' => $member_data[0]->loan_periode,
            'enable_reserve' => $member_data[0]->enable_reserve,
            'reserve_limit' => $member_data[0]->reserve_limit,
            'member_periode' => $member_data[0]->member_periode,
            'reborrow_limit' => $member_data[0]->reborrow_limit,
            'fine_each_day' => $member_data[0]->fine_each_day,
            'grace_periode' => $member_data[0]->grace_periode
        );

        // is membership expired ?
        // compare it with current date
        $_current_date = date('Y-m-d');
        if (strtotime($_current_date) <= strtotime($expire_date)) {
            $is_expired = false;
        }

        if ($is_expired) {
            return 'expired';
        }
    }

    public function proses_pinjam()
    {
        // Validasi
        $validation =  \Config\Services::validation();
        $validation->setRules(['barcode' => 'required']);
        $isDataValid = $validation->withRequest($this->request)->run();

        if ($isDataValid) {
            $loan_rules = $this->checkLoanRules();

            if ($loan_rules == 'expired') {
                header('Content-Type: application/json');
                echo json_encode(array(
                    'label' => 'error',
                    'title' => 'Gagal',
                    'pesan' => 'Masa Keanggotaan Sudah Berakhir'
                ));
            } else {
                $loan = new LoanModel();
                $loan->insert([
                    "item_code" => $this->request->getPost('barcode'),
                    "member_id" => '18383042121',
                    "loan_date" => date('Y-m-d'),
                    "due_date" => date('Y-m-d', strtotime('+7 days')),
                    "renewed" => 0,
                    "loan_rules_id" => 1,
                    "actual" => null,
                    "is_lent" => 1,
                    "is_return" => 0,
                    "return_date" => null
                ]);

                header('Content-Type: application/json');
                echo json_encode(array(
                    'label' => 'success',
                    'title' => 'Berhasil',
                    'pesan' => 'Data berhasil disimpan'
                ));
            }
        }
    }

    public function data_peminjaman()
    {
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];
        /* If we pass any extra data in request from ajax */
        //$value1 = isset($_REQUEST['key1'])?$_REQUEST['key1']:"";

        /* Value we will get from typing in search */
        $search_value = $_REQUEST['search']['value'];

        if (!empty($search_value)) {
            // If we have value in search, searching by id, name, email, mobile

            // count all data
            $total_count = $this->db->query(
                "SELECT i.item_code, b.title, l.loan_date, l.due_date FROM `loan` l
                JOIN member m ON l.member_id = m.member_id
                JOIN item i ON l.item_code = i.item_code
                JOIN biblio b ON i.biblio_id = b.biblio_id
                WHERE m.member_id = '18383042121'
                AND l.is_return = 0
                AND (b.title LIKE '%" . $search_value . "%' OR i.item_code LIKE '%" . $search_value . "%') ORDER BY l.loan_id DESC"
            )->getResult();

            $data = $this->db->query(
                "SELECT i.item_code, b.title, l.loan_date, l.due_date FROM `loan` l
                JOIN member m ON l.member_id = m.member_id
                JOIN item i ON l.item_code = i.item_code
                JOIN biblio b ON i.biblio_id = b.biblio_id
                WHERE (m.member_id = '18383042121' AND l.is_return = 0)
                AND (b.title LIKE '%" . $search_value . "%' OR i.item_code LIKE '%" . $search_value . "%') ORDER BY l.loan_id DESC limit $start, $length"
            )->getResult();
        } else {
            // count all data
            $total_count = $this->db->query("SELECT i.item_code, b.title, l.loan_date, l.due_date FROM `loan` l
            JOIN member m ON l.member_id = m.member_id
            JOIN item i ON l.item_code = i.item_code
            JOIN biblio b ON i.biblio_id = b.biblio_id
            WHERE m.member_id = '18383042121' AND l.is_return = 0
            ORDER BY l.loan_id DESC")->getResult();

            // get per page data
            $data = $this->db->query("SELECT i.item_code, b.title, l.loan_date, l.due_date FROM `loan` l
            JOIN member m ON l.member_id = m.member_id
            JOIN item i ON l.item_code = i.item_code
            JOIN biblio b ON i.biblio_id = b.biblio_id
            WHERE m.member_id = '18383042121' AND l.is_return = 0 ORDER BY l.loan_id DESC limit $start, $length")->getResult();
        }

        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => count($total_count),
            "recordsFiltered" => count($total_count),
            "data" => $data   // total data array
        );

        echo json_encode($json_data);
    }
}