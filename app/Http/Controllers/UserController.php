<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Traits\ResponseAPI;

class UserController extends Controller
{
    use ResponseAPI;

    private $url = "https://api.paystack.co/transaction/initialize";

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
           'email' => 'required|email|unique:users',           
          'phone' => 'required|min:11|max:11|unique:users',
          'password'=>'required'

        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['message'=>$errors->first(),'status'=>false],422);
        }

        $user = new User;
        
        $user->email = $request->email;
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->save();
        $token = $user->createToken('loanapp')->accessToken;
        return $this->success("User Created!",['token'=>$token,'user'=>$user,]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'email' => 'required|email',           
          'password'=>'required'

        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['message'=>$errors->first(),'status'=>false],422);
        }

        $user = User::where('email',$request->email)->first();
        if($user)
        {
            if (Hash::check($request->password, $user->password)) 
            {
                $token = $user->createToken('loanapp')->accessToken;
                return $this->success("Login Successful!",['token'=>$token]);
            }
            return $this->error('email or password is incorrect!', '401');
        }
        return $this->error('email or password is incorrect!', '401');
        
    }

    protected function addCards(Request $request)
    {
        $user = auth('api')->user();
        // $url = "https://api.paystack.co/transaction/initialize";
        $headers = ['Authorization'=> 'Bearer '.config('services.paystack.secret_key')];

        $initialized_transaction = $this->initializeTransaction($headers,$request);
        //dd($initialized_transaction);
        return $this->verifyTransaction($initialized_transaction,$headers,$user);
        
       
        // $body = [
        //     'email' => $request->email,
        //     'amount' => $request->amount,
        // ];
        // $transaction_details = $this->callPostApi($url,$headers,$body);
        // if(!$transaction_details['status'])
        // {
        //     return $this->error('paystack error!', '400');
        // }
        
        // if($transaction_details['data']->status)
        // {
        //     //get refernce from paystack
        //     $reference = $transaction_details['data']->data->reference;
        //     //verify reference
        //     $verified_transaction = $this->callGetApi($url,$headers);
        //     if(!$verified_transaction['status'])
        //     {
        //         return $this->error('paystack connection failed', '400');
        //     }
        //     if($verified_transaction['data']->status)
        //     {
        //         $user->added_card = json_encode($verified_transaction['data']->data);
        //         $user->save();
        //         return $this->success('card added',200);
        //     }
        //      return $this->error('transaction could not be verified', '400');
        // }
        // return $this->error('transaction initialization failed!', '400');

    }

    // protected function verifyTransaction($transaction_details,$headers,$user)
    // {
    //     if(!$transaction_details['status'])
    //     {
    //         return $this->error('paystack error!', '400');
    //     }
    //     if($transaction_details['data']->status)
    //     {
    //         //get refernce from paystack
    //         $reference = $transaction_details['data']->data->reference;

    //         $url = "https://api.paystack.co/transaction/verify/$reference";

    //         //verify reference
    //         $verified_transaction = $this->callGetApi($url,$headers);
    //         if(!$verified_transaction['status'])
    //         {
    //             return $this->error('paystack connection failed', '400');
    //         }
    //         if(!$verified_transaction['data']->status)
    //         {
    //             return $this->error('invalid key', '400');
    //         }
    //         if($verified_transaction['data']->data->status == "success")
    //         {
    //             $user->card = json_encode($verified_transaction['data']->data);
    //             $user->save();
    //             return $this->success('card added',200);
    //         }
    //          return $this->error('transaction could not be verified', '400');
    //     }
    //     return $this->error('transaction initialization failed!', '400');
    // }

    // protected function initializeTransaction($headers,$request)
    // {
    //     $url = "https://api.paystack.co/transaction/initialize";
    //     $body = [
    //         'email' => $request->email,
    //         'amount' => $request->amount,
    //     ];
    //     $transaction_details = $this->callPostApi($url,$headers,$body);
    //     return $transaction_details;
       
    // }


    protected function addCard($reference)
    {
        $user = auth('api')->user();
        $url = "https://api.paystack.co/transaction/verify/$reference";

        //verify reference
        $verified_transaction = $this->callGetApi($url,$this->paystackHeader());
        if(!$verified_transaction['status'])
        {
            return $this->error('paystack connection failed', '400');
        }
        if(!$verified_transaction['data']->status)
        {
            return $this->error('invalid key', '400');
        }
        if($verified_transaction['data']->data->status == "success")
        {
            $user->card = json_encode($verified_transaction['data']->data);
            $user->save();
            return $this->success('card added',200);
        }
        return $this->error('transaction could not be verified', '400');
        
    }

    protected function chargeCard(Request $request)
    {
        $body = [
            'email' => $request->email,
            'amount' => $request->amount,
        ];
        $transaction_details = $this->callPostApi($this->url,$this->paystackHeader(),$body);
        return $transaction_details;
       
    }
}
