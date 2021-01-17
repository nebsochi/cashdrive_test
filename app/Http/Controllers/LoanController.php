<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LoanRequest;
use App\LoanSchedule;
use Carbon\Carbon;
use App\Traits\ResponseAPI;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Support\Facades\Auth;


class LoanController extends Controller
{
    use ResponseAPI;

    public function createLoan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'principal' => 'required',
            'monthly_tenure' => 'required|int',           
            'monthly_interest' => 'required',

        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['message'=>$errors->first(),'status'=>false],422);
        }
        $user = auth('api')->user();
        //return $user;
        $loan_request = new LoanRequest;
        $loan_request->user_id = $user->id;
        $loan_request->principal = $request->principal;
        $loan_request->monthly_tenure = $request->monthly_tenure;
        $loan_request->monthly_interest = $request->monthly_interest;

        $total_repayment = (100 + ($loan_request->monthly_interest * $loan_request->monthly_tenure))/100 * $loan_request->principal;

        $loan_request->monthly_repayment_amount = $total_repayment/$loan_request->monthly_tenure;


        $loan_request->save();

        return $this->success("Loan Request Created",['loan_request'=>$loan_request]);
    }


    public function activateLoan(Request $request, $loan_request)
    {
        $loan_request = LoanRequest::where('id',$loan_request)->first();
        
        if($loan_request)
        {
            $user = $loan_request->user;
            if(!$loan_request->user->card)
            {
                return $this->error('card not added!', '403');
            }
            $any_active_loans = $user->loan_requests()->where('active',1)->first();
            if($any_active_loans)
            {
                return $this->error('user already has an active loan!', '403');
            }

            $loan_request->active = 1;
            $loan_request->save();
            for($i=1;$i<=$loan_request->monthly_tenure;$i++)
            {
                $loan_schedule = new LoanSchedule;
               
                $loan_schedule->loan_request_id = $loan_request->id;

                $loan_schedule->amount = $loan_request->monthly_repayment_amount;

                $loan_schedule->due_date = Carbon::today()->addDays(30 * $i);
                $schedules[] = $loan_schedule;
                $loan_schedule->save();
                
            }
            
            return $this->success("Loan activated!",['schedule'=>$schedules]);
        }
        return $this->error('loan request is invalid!', '400');

        
    }

    

}
