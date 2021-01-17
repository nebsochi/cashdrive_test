<?php

namespace App\Traits;

trait ResponseAPI
{
    private function paystackHeader()
    {
        return ['Authorization'=> 'Bearer '.config('services.paystack.secret_key')];
    }
    /**
     * Core of response
     * 
     * @param   string          $message
     * @param   array|object    $data
     * @param   integer         $statusCode  
     * @param   boolean         $isSuccess
     */
    public function coreResponse($message, $data = null, $statusCode, $isSuccess = true)
    {
        // Check the params
        if(!$message) return response()->json(['message' => 'Message is required'], 500);

        // Send the response
        if($isSuccess) {
            return response()->json([
                'message' => $message,
                'error' => false,
                'code' => $statusCode,
                'results' => $data
            ], $statusCode);
        } else {
            return response()->json([
                'message' => $message,
                'error' => true,
                'code' => $statusCode,
            ], $statusCode);
        }
    }

    /**
     * Send any success response
     * 
     * @param   string          $message
     * @param   array|object    $data
     * @param   integer         $statusCode
     */
    public function success($message, $data, $statusCode = 200)
    {
        return $this->coreResponse($message, $data, $statusCode);
    }

    /**
     * Send any error response
     * 
     * @param   string          $message
     * @param   integer         $statusCode    
     */
    public function error($message, $statusCode = 500)
    {
        return $this->coreResponse($message, null, $statusCode, false);
    }

    public function callPostApi($url,$header,$body)
    {
        try{
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->request('POST', $url,['headers' => $header,'json' => $body]);
            return ['status'=>true,'data'=>json_decode($response->getBody())];
        }catch(\Exception $e){
            return ['status'=>false,'message'=>$e->getMessage()];
        }
        
        //return $response->getBody();
    }

    public function callGetApi($url,$headers=[])
    {
        try{
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->request('GET', $url, [ 'headers' => $headers]);
            //$response = json_decode($response->getBody());
            return ['status'=>true,'data'=>json_decode($response->getBody())];
        }catch(\Exception $e){
            return ['status'=>false,'message'=>$e->getMessage()];
        }
        
        //return $response->getBody();
    }

}