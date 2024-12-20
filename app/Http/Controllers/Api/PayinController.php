<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;


class PayinController extends Controller
{
	
  public function usdt_payin(Request $request)
{
	
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'cash' => 'required|numeric',
        'type' => 'required|integer',
       
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ]);
    }

    $usdt = $request->cash;
    $image = $request->screenshot;
    $type = $request->type;
    $userid = $request->user_id;
    $inr = $usdt;
    $datetime = now();
    $orderid = date('YmdHis') . rand(11111, 99999);

    if (empty($image) || $image === '0' || $image === 'null' || $image === null || $image === '' || $image === 0) {
        return response()->json([
            'status' => 400,
            'message' => 'Please Select Image'
        ]);
    }

    $path = '';

    if (!empty($image)) {
        $imageData = base64_decode($image);
        if ($imageData === false) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid base64 encoded image'
            ]);
        }

        $newName = Str::random(6) . '.png';
        $path = 'usdt_images/' . $newName;
		
	

        if (!file_put_contents(public_path($path), $imageData)) {
            return response()->json([
                'status' => 400,
                'message' => 'Failed to save image'
            ]);
        }
    }

    if ($type == 2) {
        $insert_usdt = DB::table('payins')->insert([
            'user_id' => $userid,
            'cash' => $inr,
            'usdt_amount' => $usdt,
            'type' => $type,
            'screenshot' => $path,
            'order_id' => $orderid,
            'status' => 1,
            'created_at' => $datetime,
            'updated_at' => $datetime
        ]);

        if ($insert_usdt) {
            return response()->json([
                'status' => 200,
                'message' => 'UPI Payment Request sent successfully. Please wait for admin approval.'
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Failed to process payment'
            ]);
        }
    }
	  
	   elseif ($type == 3) {
        $insert_usdt = DB::table('payins')->insert([
            'user_id' => $userid,
            // 'cash' => $inr*90,
            // 'usdt_amount' => $usdt*90 ,
            'cash' => $usdt*90,
            'usdt_amount' => $inr,
            'type' => $type,
            'screenshot' => $path,
            'order_id' => $orderid,
            'status' => 1,
            'created_at' => $datetime,
            'updated_at' => $datetime
        ]);

        if ($insert_usdt) {
            return response()->json([
                'status' => 200,
                'message' => 'USDT Payment Request sent successfully. Please wait for admin approval.'
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Failed to process payment'
            ]);
        }
    }
	  else {
        return response()->json([
            'status' => 400,
            'message' => 'Indian pay is not supported.'
        ]);    }

}

// 	public function usdt_payin(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'user_id' => 'required|exists:users,id',
//         'cash' => 'required|numeric',
//         'type' => 'required|integer',
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ]);
//     }

//     $usdt = $request->cash;
//     $image = $request->screenshot;
//     $type = $request->type;
//     $userid = $request->user_id;
//     $inr = $usdt;
//     $datetime = now();
//     $orderid = date('YmdHis') . rand(11111, 99999);

//     if (empty($image) || $image === '0' || $image === 'null' || $image === null || $image === '' || $image === 0) {
//         return response()->json([
//             'status' => 400,
//             'message' => 'Please Select Image'
//         ]);
//     }

//     $path = '';

//     if (!empty($image)) {
//         $imageData = base64_decode($image);
//         if ($imageData === false) {
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'Invalid base64 encoded image'
//             ]);
//         }

//         $newName = Str::random(6) . '.png';
//         $path = 'usdt_images/' . $newName;

//         if (!file_put_contents(public_path($path), $imageData)) {
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'Failed to save image'
//             ]);
//         }
//     }

//     $insert_usdt = false;

//     if ($type == 2) {
//         $insert_usdt = DB::table('payins')->insert([
//             'user_id' => $userid,
//             'cash' => $inr,
//             'usdt_amount' => $usdt,
//             'type' => $type,
//             'screenshot' => $path,
//             'order_id' => $orderid,
//             'status' => 1,
//             'created_at' => $datetime,
//             'updated_at' => $datetime
//         ]);

//         $success_message = 'UPI Payment Request sent successfully. Please wait for admin approval.';
//     } elseif ($type == 3) {
//         $insert_usdt = DB::table('payins')->insert([
//             'user_id' => $userid,
//             'cash' => $usdt * 90,
//             'usdt_amount' => $inr,
//             'type' => $type,
//             'screenshot' => $path,
//             'order_id' => $orderid,
//             'status' => 1,
//             'created_at' => $datetime,
//             'updated_at' => $datetime
//         ]);

//         $success_message = 'USDT Payment Request sent successfully. Please wait for admin approval.';
//     } else {
//         return response()->json([
//             'status' => 400,
//             'message' => 'Indian pay is not supported.'
//         ]);
//     }

//     if ($insert_usdt) {
//         // Fetch the user and log their first_recharge value for debugging
//         $user = DB::table('users')->where('id', $userid)->first();
        
//         // Log the user details
//         if (!$user) {
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'User not found'
//             ]);
//         }

//         \Log::info('User first_recharge status before update: ' . $user->first_recharge);

//         // If first_recharge is 1, update it to 0
//         if ($user->first_recharge == 0) {
//             $update = DB::table('users')->where('id', $userid)->update(['first_recharge' => 1]);

//             // Check if the update was successful
//             if ($update) {
//                 \Log::info('First recharge status updated successfully for user ID: ' . $userid);
//             } else {
//                 \Log::error('Failed to update first recharge status for user ID: ' . $userid);
//             }
//         }

//         return response()->json([
//             'status' => 200,
//             'message' => $success_message
//         ]);
//     } else {
//         return response()->json([
//             'status' => 400,
//             'message' => 'Failed to process payment'
//         ]);
//     }
// }


	
	
 public function qr_view(Request $request)
       {
	 
	   $validator = Validator::make($request->all(), [
            'type' => 'required',
        ]);
        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];

            return response()->json($response);
        }
	 $type = $request->type;

       $show_qr = DB::select("SELECT * FROM `usdt_qr` where type=$type");

 

        if ($show_qr) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $show_qr
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }

  
      
		 public function payin(Request $request)
    {
       
         $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'cash' => 'required',
            'type' => 'required',
        ]);
        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];

            return response()->json($response);
        }

        
        
	$cash = $request->cash;
    // $extra_amt = $request->extra_cash;
     $type = $request->type;
    $userid = $request->user_id;
	   //	$total_amt=$cash+$extra_amt+$bonus;
		 
               $date = date('YmdHis');
        $rand = rand(11111, 99999);
        $orderid = $date . $rand;
        $datetime=now();
        $check_id = DB::table('users')->where('id',$userid)->first();
		// dd($check_id);
        if($type == 1){
        if ($check_id) {
            $redirect_url = env('APP_URL')."api/checkPayment?order_id=$orderid";
            //dd($redirect_url);
            $insert_payin = DB::table('payins')->insert([
                'user_id' => $request->user_id,
                'cash' => $request->cash,
                'type' => $request->type,
                'order_id' => $orderid,
                'redirect_url' => $redirect_url,
                'status' => 1, // Assuming initial status is 0
				'typeimage'=>"https://root.nandigame.live/uploads/fastpay_image.png",
                'created_at'=>$datetime,
                'updated_at'=>$datetime
            ]);
         // dd($redirect_url);
            if (!$insert_payin) {
                return response()->json(['status' => 400, 'message' => 'Failed to store record in payin history!']);
            }
 
            // $postParameter = [
            //     'merchantid' => "04",
            //     'orderid' => $orderid,
            //     'amount' => $request->cash,
            //     'name' => $check_id->u_id,
            //     'email' => "abc@gmail.com",
            //     'mobile' => $check_id->mobile,
            //     'remark' => 'payIn',
            //     'type'=>$request->cash,
            //     'redirect_url' => env('APP_URL')."api/checkPayment?order_id=$orderid"
            //   // 'redirect_url' => config('app.base_url') ."/api/checkPayment?order_id=$orderid"
            // ];

$array=[
                    "api_token"=>"9655467b43863ff6c3cdadc36cc2191d",
                    "customer_name"=>$check_id->u_id,
                    "order_id"=>$orderid,
                    "amount"=>$request->cash,
                    "customer_mobile"=>$check_id->mobile,
                    "redirect_url"=>env('APP_URL')."api/checkPayment?order_id=$orderid"
                    ];

$encodedata=json_encode($array);


                $curl = curl_init();
                
                curl_setopt_array($curl, array(
                  CURLOPT_URL => 'https://allupipay.in/order/create',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'POST',
                  CURLOPT_POSTFIELDS =>$encodedata,
                  CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/plain'
                  ),
                ));
                
                $response = curl_exec($curl);
                
                curl_close($curl);
                echo $response;


/*            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://indianpay.co.in/admin/paynow',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0, 
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postParameter),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Cookie: ci_session=1ef91dbbd8079592f9061d5df3107fd55bd7fb83'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
             
			echo $response;*/
		//	dd($response);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Internal error!'
            ]);
        }
            
        }else{
           return response()->json([
                'status' => 400,
                'message' => 'USDT is Not Supported ....!'
            ]); 
        }
    }
    public function checkPayment(Request $request)
    {
      	  $orderid = $request->input('order_id');
		$currentDateTime = Carbon::now()->format('Y-m-d H:i:s');

			$payment = DB::table('payins')->where('order_id',$orderid)->where('status',1)->update(["status"=>2]);
			
			  if($payment == '1'){
				
			  
			   $data1 = DB::table('payins')->where('order_id',$orderid)->where('status',2)->first();
			  
			    $userid = $data1->user_id; $amount = $data1->cash; 
			   
			  $userdata = DB::table('users')->where('id',$userid)->where('status',1)->first();
			     $first_recharge = $userdata->first_recharge;
			  
			  $referral_user = DB::table('users')->where('id', $userid)->value('referral_user_id');
			  
		  
			 if($first_recharge == '1'){
					 
				 $first_deposit_bonus = DB::table('first_deposit_bonus')
    					->where('recharge_min', '<=', $amount)
    					->where('recharge_max', '>=', $amount)
    					->first();
				 
				 $first_deposit_bonus->member;
				 $first_deposit_bonus->agent;
				
			 	 if($amount >= 200){ $first_recharge_status = 0; } else { $first_recharge_status = 1;}
				
				 $data2 = DB::table('users')
    				->where('id', $userid)
    				->update([
        					'wallet' => DB::raw("wallet + $amount + $first_deposit_bonus->member"),	
						    'recharge' => DB::raw("recharge + $amount + $first_deposit_bonus->member"),	
						    'first_recharge' => $first_recharge_status
						  
    					]);
				 
				   DB::table('users')
    				->where('id', $referral_user)
    				->update([
        					'wallet' => DB::raw("wallet + $first_deposit_bonus->agent"),
						    'recharge' => DB::raw("recharge + $first_deposit_bonus->agent"),
    					]);

					 
	  $insert= DB::table('wallet_history')->insert([
        'userid' => $userid,
        'amount' => $first_deposit_bonus->member,
        'subtypeid' => 10,
		'created_at'=> now(),
        'updated_at' => now()
		
    ]);
				 
	$insert= DB::table('wallet_history')->insert([
        'userid' => $referral_user,
        'amount' => $first_deposit_bonus->agent,
        'subtypeid' => 10,
		'created_at'=> now(),
        'updated_at' => now()
		
    ]);
			 
				 return redirect()->route('payin.successfully');
			}elseif($first_recharge == '0'){
				 
				 
				 
			   $data2 = DB::table('users')->where('id', $userid)
				   	->update([
        					'wallet' => DB::raw("wallet + $amount"),	
						    'recharge' => DB::raw("recharge + $amount")
    					]);
				
				return redirect()->route('payin.successfully');
		
				 
				 
			 }

		
			 }
		
		 return redirect()->route('payin.successfully');
		  }
	
		public function redirect_success(){
		
					return view ('success');	
	    }

  
	
	


public function withdraw_request(Request $request)
{

		  $date = date('Ymd');
        $rand = rand(1111111, 9999999);
        $transaction_id = $date . $rand;
	
		 $userid=$request->userid;
		 $amount=$request->amount;
		   $validator=validator ::make($request->all(),
        [
            'userid'=>'required',
			'amount'=>'required',
			
        ]);
        $date=date('Y-m-d h:i:s');
        if($validator ->fails()){
            $response=[
                'success'=>"400",
                'message'=>$validator ->errors()
            ];                                                   
            
            return response()->json($response,400);
        }
      
		 $datetime = date('Y-m-d H:i:s');
		 
         $user = DB::select("SELECT * FROM `users` where `id` =$userid");
		 $account_id=$user[0]->accountno_id;
		 $mobile=$user[0]->mobile;
		 $wallet=$user[0]->wallet;
	
		 $accountlist=DB::select("SELECT * FROM `bank_details` WHERE `id`=$account_id");
		 
		 $insert= DB::table('transaction_history')->insert([
        'userid' => $userid,
        'amount' => $amount,
        'mobile' => $mobile,
		'account_id'=>$account_id,
        'status' => 0,
		'type'=>1,
        'date' => $datetime,
		'transaction_id' => $transaction_id,
    ]);
		  DB::select("UPDATE `users` SET `wallet`=`wallet`-$amount,`winning_wallet`=`winning_wallet`-$amount  WHERE `id`=$userid");
          if($insert){
          $response =[ 'success'=>"200",'data'=>$insert,'message'=>'Successfully'];return response ()->json ($response,200);
      }
      else{
       $response =[ 'success'=>"400",'data'=>[],'message'=>'Not Found Data'];return response ()->json ($response,400); 
      } 
    }
	
		public function userpayin(Request $request)
	{
			
		 $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'cash' => 'required',
            'type' => 'required',
        ]);
        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];

            return response()->json($response);
        }	
			
         $date = date('YmdHis');
   $rand = rand(11111, 99999);
   $invno = "INV".$date . $rand;
			 $datetime=now();
        $check_id = DB::table('users')->where('id',$request->user_id)->first();
	if($request->type == 1){
		if($request->cash >= 10){
       if ($check_id) {
		    $insert_payin = DB::table('payins')->insert([
                'user_id' => $request->user_id,
                'cash' => $request->cash,
                'type' => $request->type,
                'order_id' => $invno,
              
                'status' => 1, // Assuming initial status is 0
				'typeimage'=>"https://root.winzy.app/uploads/favicon1.png",
                'created_at'=>$datetime,
                'updated_at'=>$datetime
            ]);
        
            if (!$insert_payin) {
                return response()->json(['status' => 400, 'message' => 'Failed to store record in payin history!']);
            }
		$postpara=[
			 "mId"=> "iDXVoIjm/4k=RkZGRk9FWFpLWlZQNkRCNQ==",
			  "amount"=> "$request->cash",
			  "invno"=> "$invno",
			  "fName"=> "$check_id->username",
			  "lName"=> "$check_id->username",
			  "mNo"=> "$check_id->mobile",
			  "currency"=> "INR",

			];
			$encodedparameter=json_encode($postpara);
		   
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://indiaonlinepay.com/api/iopregisterupiintent',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>$encodedparameter,
		  CURLOPT_HTTPHEADER => array(
			'opStatus: 0',
			'Content-Type: application/json',
			'Cookie: Path=/'
		  ),
		));

		echo $response = curl_exec($curl);
		  
     
		curl_close($curl);
		    die;
		$decoded=json_decode($response);
		   if(isset($decoded->responseCode)&&$decoded->responseCode==200)
		 {
		 		$transactionid=$decoded->orderId;
			   $intent=$decoded->intent;
			    $orderId=$decoded->orderId;
			   $status=$decoded->status;
			    $merchantIdentifier=$decoded->merchantIdentifier;
			    $amount=$decoded->amount;
			    $currency=$decoded->currency;
			    $expiryDate=$decoded->expiryDate;
			    $responseCode=$decoded->responseCode;
			    $responseMessage=$decoded->responseMessage; 
			   $transactionDate=$decoded->transactionDate;
			    $encodedIntent = urlencode($intent);

    // Construct the URL for generating QR code
    $qrCodeURL = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=$encodedIntent&ecc=M";

			 
			 DB::table('payins')->where('order_id', $invno)->update(['transaction_id' => $transactionid]);
			  
			   $response=[
			   
				"orderId" => $orderId,
				"status"=> $status,
				"merchantIdentifier"=> $merchantIdentifier,
				"amount"=> $amount,
				"currency"=> $currency,
				"expiryDate"=> $expiryDate,
				"responseCode"=> $responseCode,
				"responseMessage"=>$responseMessage,
				"intent"=> $intent,
				"transactionDate"=> $transactionDate,
				   "qrcode"=>$qrCodeURL    
			   ];
			   
			  $res= json_encode($response);
			   
			 echo $res;
		 }else
		 {
			 return response()->json([
                'status' => 400,
                'message' => $response
            ]);
		 }
		   
          } else {
            return response()->json([
                'status' => 400,
                'message' => 'Internal error!'
            ]);
        }
          }else{
           return response()->json([
                'status' => 400,
                'message' => ' Minimum deposit is 100 rupees'
            ]); 
        }   
        }else{
           return response()->json([
                'status' => 400,
                'message' => 'Indianpay is Not Supported ....!'
            ]); 
        }
		
	}
	
	
	 public function  callbackfunc()
    {
        $date=date('Y-m-s H:i:s');
            header("Access-Control-Allow-Methods: POST");
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: Origin, Content-Type");
            header("Content-Type: application/json");
            header("Expires: 0");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
                    
            
            $date=date('Y-m-d H:i:s');
          $data = json_decode(file_get_contents("php://input"), true);
         $d=json_encode($data);
         
		$transactionid= $data['iop_txn_id'];
		 $invoice= $data['invoiceNumber'];
		$amount= $data['amount'];
		$status= $data['status'];
          DB::table('payincallback')->insert([
               'data'=>$d,
			  'datetime'=>$date
            ]);
		 
		 if(isset($data['status'])&&$data['status']=='SUCCESS'&&isset($data['gatewayResponseStatus'])&&$data['gatewayResponseStatus']=='SUCCESS')
		 {



				$selecteddata = DB::table('payins')
					->select('cash as amount', 'user_id')
					->where('transaction_id', $transactionid)
					->where('order_id', $invoice)
					->first();
			     $uid = $selecteddata->user_id;
                $cash = $selecteddata->amount;
				if (!empty($selecteddata)) {
					DB::table('payins')
						->where('order_id', $invoice)
						->where('transaction_id', $transactionid)
						->update(['status' => '2']);

					$referid = DB::table('users')
						->select('referral_user_id', 'first_recharge')
						->where('id', $uid)
						->first();

					$first_recharge = $referid->first_recharge;
					$referuserid = $referid->referral_user_id;
					$cash = $selecteddata->amount;

					if ($first_recharge == 0) {
						DB::table('users')
							->where('id', $uid)
							->update([
								'wallet' => DB::raw("wallet + $cash"),
								'first_recharge' => DB::raw("first_recharge + $cash"),
								'first_recharge_amount' => DB::raw("first_recharge_amount + $cash"),
								'recharge' => DB::raw("recharge + $cash"),
								'total_payin' => DB::raw("total_payin + $cash"),
								'no_of_payin' => DB::raw("no_of_payin + 1"),
								'deposit_balance' => DB::raw("deposit_balance + $cash"),
							]);

						DB::table('users')
							->where('id', $referuserid)
							->update([
								'yesterday_payin' => DB::raw("yesterday_payin + $cash"),
								'yesterday_no_of_payin' => DB::raw("yesterday_no_of_payin + 1"),
								'yesterday_first_deposit' => DB::raw("yesterday_first_deposit + $cash"),
							]);

				
					} else {
						DB::table('users')
							->where('id', $uid)
							->update([
								'wallet' => DB::raw("wallet + $cash"),
								'recharge' => DB::raw("recharge + $cash"),
								'total_payin' => DB::raw("total_payin + $cash"),
								'no_of_payin' => DB::raw("no_of_payin + 1"),
								'deposit_balance' => DB::raw("deposit_balance + $cash"),
							]);

						DB::table('users')
							->where('id', $referuserid)
							->update([
								'yesterday_payin' => DB::raw("yesterday_payin + $cash"),
								'yesterday_no_of_payin' => DB::raw("yesterday_no_of_payin + 1"),
							]);
					}
				}


			 
			 
		 }
        
          
          
          
         
        }
	
	
		 public function  callbackfunc_payout()
    {
        $date=date('Y-m-s H:i:s');
            header("Access-Control-Allow-Methods: POST");
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: Origin, Content-Type");
            header("Content-Type: application/json");
            header("Expires: 0");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
                    
            
            $date=date('Y-m-d H:i:s');
          $data = json_decode(file_get_contents("php://input"), true);
         $d=json_encode($data);
         
	
          DB::table('withdrawal_calback')->insert([
               'data'=>$d,
			  'datetime'=>$date
            ]);
		 echo "hellow";
		 
		 if(isset($data['status'])&&$data['status']=='SUCCESS'&&isset($data['gatewayResponseStatus'])&&$data['gatewayResponseStatus']=='SUCCESS')
		 {



				 
		 }
        
          
          
          
         
        }
	
	
	
}
