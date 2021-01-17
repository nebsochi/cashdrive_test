<?php

namespace App\Traits;
use App\LoanSchedule;
use Carbon\Carbon;
use App\LoanRequest;

trait CronJobs
{
    
    use ResponseAPI;
    
    private $url = "https://api.paystack.co/transaction/charge_authorization";

    public function getAllDueLoans()
    {
        $due_loans = LoanSchedule::where('due_date','<=',Carbon::today())->where('paid',0)->get();
        return $due_loans;
    }
 
    private function chargeDueLoans($due_loans)
    {
        foreach($due_loans as $due_loan)
        {
            $due_loan = LoanSchedule::where('id',$due_loan->id)->first();
            $full_repayment = $due_loan->amount + $due_loan->charge;
            $this->chargeCard(json_decode($due_loan->loan_request->user->card),$full_repayment,$due_loan);
        }
        //return $this->success('loans due charged',200);

    }

    private function chargeCard($card,$amount,$loan_schedule)
    {      
        $body  = [
            'email' => $card->customer->email,
            'amount' => $amount,
            "authorization_code"=> $card->authorization->authorization_code
          ];
        $charged_card = $this->callPostApi($this->url,$this->paystackHeader(),$body);
        if(!$charged_card['status'])
        {
            return $this->error('error occured!', '400');
        }

        if(!$charged_card['data']->status)
        {
            return $this->error('invalid key!', '400');
        }

        if($charged_card['data']->data->status == "success")
        {
            $loan_schedule->due_date = Carbon::parse($loan_schedule->due_date)->addDays(30);
            $loan_schedule->paid = 1;
            $loan_schedule->save();
        }
        else
        {
            $this->addLoanPenalty($loan_schedule->loan_request);
        }
    }

    private function addLoanPenalty($loan_request)
    {
        $loan_request->monthly_interest += 0.5;
        $penalty_interest = $loan_request->monthly_interest;
        $loan_request->save();

        $penalty_total_repayment = (100 + ($penalty_interest * $loan_request->monthly_tenure))/100 * $loan_request->principal;

        $original_total_repayment = $loan_request->monthly_repayment_amount * $loan_request->monthly_tenure;

        $extra_repayment_charges = $penalty_total_repayment - $original_total_repayment;

        //add charges on due loans that have not been paid
        $unpaid_due_loans_count = $loan_request->loan_schedules()->where('due_date','<=',Carbon::today())->where('paid',0)->count();

        $due_loans_charges = $unpaid_due_loans_count == 0 ? NULL : $extra_repayment_charges / $unpaid_due_loans_count;

        $loan_request->loan_schedules()->where('due_date','<=',Carbon::today())->where('paid',0)->update(['charge'=>$due_loans_charges]);
    }
    
   
}