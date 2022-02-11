<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanModel extends Model
{
    protected $table = 'loan';
    protected $primaryKey = 'loan_id';

    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'item_code',
        'member_id',
        'loan_date',
        'due_date',
        'renewed',
        'loan_rules_id',
        'actual',
        'is_lent',
        'is_return',
        'return_date'
    ];
}