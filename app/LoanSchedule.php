<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanSchedule extends Model
{
    public function loan_request()
    {
        return $this->belongsTo('App\LoanRequest');
    }
}
