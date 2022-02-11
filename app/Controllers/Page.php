<?php

namespace App\Controllers;

use App\Models\LoanModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Page extends BaseController
{
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

    public function contact()
    {
        echo "Contact";
    }

    public function faqs()
    {
        echo "FAQ";
    }

    public function tos()
    {
        echo "Term Of the Service";
    }
}