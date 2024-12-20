<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Payin;
use App\Models\Withdraw_history;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Setting;
use App\Models\All_image;
use App\Models\Slider;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Account_detail;
use DateTime;
use App\Models\Wallet_history;
use Illuminate\Support\Facades\Http;
 

class PublicApiController extends Controller
{
	
	 public function deposit_reject_cron()
       {
    
      DB::select("UPDATE `payins` SET `status`='3' WHERE `status`=1");

    }

	
	
	 //// USDT Bank Details List by userid ///

 public function usdt_account_view(Request $request)
       {
       
         $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        $validator->stopOnFirstFailure();
        
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 

                return response()->json($response,400);

    }
    
    $userid=$request->user_id;
       $accountDetails = DB::select("SELECT * FROM `usdt_account_deatils` WHERE user_id=$userid
");
 if ($accountDetails) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $accountDetails
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }

	 ///// Add USDT Bank details ///


public function add_usdt_account(Request $request)
{
    
         $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'name' => 'required',
            'usdt_wallet_address' => 'required',
        ]);

        $validator->stopOnFirstFailure();

    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
                
                return response()->json($response,400);
                
    }
	  $datetime=now();
        $data = [
            'user_id' => $request->input('user_id'),
            'name' => $request->input('name'),
            'usdt_wallet_address' => $request->input('usdt_wallet_address'),
            'status' => 1,
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ];

        $inserted = DB::table('usdt_account_deatils')->insert($data);

        if ($inserted) {
             $response = [
        'status' =>200,
        'message' => 'Add USDT Account Successfully ..!',
    ];

    return response()->json($response,200);
            
        } else {
             $response = [
        'status' =>400,
        'message' => 'Internal error..!',
    ];
 return response()->json($response,400);
            
        }
    
}
	  protected function generateRandomUID() {
					$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
					$digits = '0123456789';

					$uid = '';

					// Generate first 4 alphabets
					for ($i = 0; $i < 4; $i++) {
						$uid .= $alphabet[rand(0, strlen($alphabet) - 1)];
					}

					// Generate next 4 digits
					for ($i = 0; $i < 4; $i++) {
						$uid .= $digits[rand(0, strlen($digits) - 1)];
					}

					return $this->check_exist_memid($uid);
					
				}

	  protected function check_exist_memid($uid){
					$check = DB::table('users')->where('u_id',$uid)->first();
					if($check){
						return $this->generateRandomUID(); // Call the function using $this->
					} else {
						return $uid;
					}
				}


public function login(Request $request) {
    // if ($request->isMethod('post')) {
        $identity = $request->input('identity'); // This can be either email or mobile
        $password = $request->input('password');

        if (!empty($identity) && !empty($password)) {
            // Check if the provided credentials are valid
            $user = $this->getUserByCredentials($identity, $password);

            if ($user) {
                $response['message'] = "Login successful";
                $response['status'] = "200";
                $response['id'] = $user->id;
                return response()->json($response,200);
            } else {
                $response['message'] = "Invalid credentials, Contact admin..!";
                $response['status'] = "401";
                return response()->json($response,401);
            }
        } else {
            $response['message'] = "Email or mobile and password are required";
            $response['status'] = "400";
            return response()->json($response,400);
        }
    // } else {
    //     return response()->json(['error' => 'Unsupported request method'], 405);
    // }
}

private function getUserByCredentials($identity, $password) {
    $user = User::where(function ($query) use ($identity) {
                $query->where('email', $identity)
                      ->orWhere('mobile', $identity);
            })
            ->where('password', $password)
            ->where('status', 1)
            ->first();

    return $user;
}
	
	
	public function otp_register(Request $request){
    try {    
        // Validate input
        $validator = Validator::make($request->all(), [
            'mobile' => ['required', 'string', 'regex:/^\d{10}$/'], // Ensure 10 digits
             'referral_code' => 'required',
            'otp' => 'required',
           
        ]);

        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ]; 

            return response()->json($response, 400);
        }

        $mobile = $request->mobile; // Define $mobile from the request
        $username = Str::random(6); // Generate random username
        $u_id = $this->generateRandomUID();
        $referral_code = Str::upper(Str::random(6)); // Generate referral code
        $rrand = rand(1, 20);
        $all_image = All_image::find($rrand);     
        $image = $all_image->image;
               
        $exist_user = User::where('mobile', $mobile)->where('type', 1)->first();
        
        // $refer_user = User::where('referral_code', $referral_code)->first();
               
        //       if (!empty($refer_user)) {
        //       }else{
        //          $response = [
        //             'status' => 400,
        //             'message' => 'Invalid referral code!'
        //         ];

        //         return response()->json($response, 400);  
        //       }
        if (!empty($exist_user)) {
            // Update existing user with new OTP
            $exist_user->otp = $request->otp;
            $exist_user->save();

            return response()->json([
                'status' => 200,
                'message' => 'OTP updated successfully for existing user.',
                'userid' => $exist_user->id,
                'mobile' => $exist_user->mobile,
            ]);   
        } else {
            // Insert new user into the database
            $userId = DB::table('users')->insertGetId([
                'mobile' => $mobile,
                'otp' => $request->otp,
                'username' => $username,
                'referral_code' => $referral_code,
                'u_id' => $u_id,
                'status' => 1,
                'type' => 1,
                'userimage' => $image,
                'created_at' => now()
            ]);

            if ($userId) {
                $user = DB::table('users')->where('id', $userId)->first();
                $response = [
                    'status' => 200,
                    'message' => 'User is created successfully.',
                    'userid' => $userId,
                    'mobile' => $user->mobile
                ];

                return response()->json($response);
            } else {
                $response = [
                    'status' => 400,
                    'message' => 'Failed to register!'
                ];

                return response()->json($response, 400);
            }
        }
              
               
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

      public function register(Request $request)
      {
     
   $validator = Validator::make($request->all(), [
    'mobile' => ['required', 'string'], // Ensure 10 digits
    'password' => 'required|string|min:6|confirmed', 
    'password_confirmation' =>'required|string|min:6', 
    'otp' => 'required',
	'userid' => 'required',
	'email' => 'required | unique:users,email',
]);

		 $userid  = $request->userid;
		 $mobile = $request->mobile;
		 $otp = $request->otp;
		
		$user = DB::table('users')->where('id',$userid)->first();
		  

if (!$user) {
    $response = ['status' => 400, 'message' => 'User not found.'];
    return response()->json($response, 400);
}

if ($user->mobile != $mobile) {
    $response = ['status' => 400, 'message' => 'The mobile provided does not match.'];
    return response()->json($response, 400);
}

if ($user->otp != $otp) {
    $response = ['status' => 400, 'message' => 'The otp provided does not match.'];
    return response()->json($response, 400);
}
 
// Continue with the rest of your logic here

		  
	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		     $response = ['status' => 400, 'message' => $validator->errors()->first()]; 
		
		    return response()->json($response,400);
		
    }
 $referral_code = $request->input('referral_code');
 if (!empty($referral_code)) {
        $refer = DB::table('users')->where('referral_code', $referral_code)->first();
            $referral_user_id=$refer->id;
		
        if ($refer) {
            $wallet = 0; 
        } else {
            return response()->json([
                'success' => "400",
                'message' => 'Invalid referral code',
            ], 400);
        }
    }
  
		  
	$userdata =  DB::table('users')->where('id', $userid)->where('mobile', $mobile)->where('otp', $otp)
    ->update([
        'email' => $request->email,
        'wallet' => 20,
        'password' => $request->password,
        'referral_user_id' => isset($referral_user_id) ? $referral_user_id : '1',
        'status' => 1,
		'type' => 0,
		'updated_at' => now()
    ]);

		  
		  
   $refid= isset($referral_user_id)? $referral_user_id : '1';
     DB::select("UPDATE `users` SET `yesterday_register`=yesterday_register+1 WHERE `id`=$refid");
	
	if($userdata){
       $user = DB::table('users')->where('id',$userid)->first();
    $response = [
        'status' =>200,
        'message' => 'User is created successfully.',
		'id' => $user->id,
		'mobile'=>$user->mobile
		
    ];

    return response()->json($response);
		
	}else{
		
		    $response = [
        'status' =>400,
        'message' => 'Failed to register!',
    ];

    return response()->json($response,400);
		
	}
		
}


//// Bank Details List by userid ///

 public function Account_view(Request $request)
       {
       
         $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
    
    $userid=$request->user_id;
       $accountDetails = DB::select("SELECT * FROM `account_details` WHERE user_id=$userid
");



        if ($accountDetails) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $accountDetails
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }


///// Add Bank details ///


public function add_account(Request $request)
{
    if ($request->isMethod('post')) {
         $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'name' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'bank_name' => 'required',
            'branch' => 'required',
        ]);

        $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }

        $data = [
            'user_id' => $request->input('user_id'),
            'name' => $request->input('name'),
            'account_number' => $request->input('account_number'),
            'ifsc_code' => $request->input('ifsc_code'),
            'bank_name' => $request->input('bank_name'),
            'branch' => $request->input('branch'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $inserted = DB::table('account_details')->insert($data);

        if ($inserted) {
             $response = [
        'status' =>200,
        'message' => 'Add Account Successfully ..!',
    ];

    return response()->json($response,200);
            
        } else {
             $response = [
        'status' =>400,
        'message' => 'Internal error..!',
    ];

    return response()->json($response,400);
            
        }
    } else {
        return response()->json(['error' => 'Unsupported request method'], 405);
    }
}

////// Withdraw /////


public function withdraw(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'account_id' => 'required',
        'type' => 'required',
        'amount' => 'required|numeric',
    ]);

    $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }

    $userid = $request->input('user_id');
    $accountid = $request->input('account_id');
    $amount = $request->input('amount');
    $type = $request->input('type');
    
     $date = date('YmdHis');
        $rand = rand(11111, 99999);
        $orderid = $date . $rand;
    if ($amount > 99) {
       //($amount >= 550) 
        $wallet=DB::select("SELECT `wallet`,`recharge`,`first_recharge` FROM `users` WHERE id=$userid");
       $user_wallet=$wallet[0]->wallet;
      // dd($user_wallet);
       $user_recharge=$wallet[0]->recharge;
		$user_first_recharge=$wallet[0]->first_recharge;
       if($user_recharge == 0){
		   
		  if($user_first_recharge == 1){
			   $response = [ 'status' =>400, 'message' => 'First recharge Rs. 200 mandatory for withdrawal', ];
				return response()->json($response,400);  
		  }
		   
        if($user_wallet >= $amount){
       $data= DB::table('withdraw_histories')->insert([
    'user_id' => $userid,
    'amount' => $amount,
    'account_id' => $accountid,
    'type' => $type,
    'order_id' => $orderid,
    'status' => 1,
	'typeimage'=>"https://root.nandigame.live/uploads/fastpay_image.png",
    'created_at' => now(),
    'updated_at' => now(),
]);
      DB::select("UPDATE `users` SET `wallet`=`wallet`-$amount WHERE id=$userid;");
 if ($data) {
             $response = [
        'status' =>200,
        'message' => 'Withdraw Request Successfully ..!',
    ];

    return response()->json($response,200);

        } else {
             $response = [
        'status' =>400,
        'message' => 'Internal error..!',
    ];

    return response()->json($response,400);
            
        }
        }else{
       $response = [
        'status' =>400,
        'message' => 'insufficient Balance..!',
    ];

    return response()->json($response,400);
 }  
           
       }else {
         $response = [
        'status' =>400,
        'message' => 'need to bet amount 0 to be able to Withdraw',
    ];

    return response()->json($response,400);   
       }
        
    }else{
        $response['message'] = "minimum Withdraw 100 And Maximum Withdraw 25000";
            $response['status'] = "400";
            return response()->json($response,400);
    }

    
    
}

///// Gift Claim List ////

 public function claim_list(Request $request)
       {
       
         $validator = Validator::make($request->all(), [
            'userid' => 'required',
        ]);

        $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
    
    $userid=$request->userid;
       $accountDetails = DB::select("SELECT * FROM `gift_claim` WHERE `userid`=$userid order by id DESC");



        if ($accountDetails) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $accountDetails
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }


//// Gift Card Apply ///

public function giftCartApply(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        'code' => 'required',
    ]);

     $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }

  
        $userid = $request->input('userid');
        $code = $request->input('code');

        $data = DB::table('gift_cart')->where('code', $code)->where('status', 1)->first();

        if ($data) {
            $fixPeople = $data->number_people;
            $availedPeople = $data->availed_num;

            if ($availedPeople < $fixPeople) {
                $claimUser = DB::table('gift_claim')->where('gift_code', $code)->where('userid', $userid)->first();

                if (!$claimUser) {
                    date_default_timezone_set('Asia/Kolkata');
                    $datetime = date('Y-m-d H:i:s');

                    $giftCartAmount = $data->amount;

                    if (!empty($giftCartAmount)) {
                        DB::table('gift_claim')->insert(['userid' => $userid, 'gift_code' => $code, 'amount' => $giftCartAmount]);
                        DB::table('users')->where('id', $userid)->update(['wallet' => DB::raw('wallet + ' . $giftCartAmount),
																		  'bonus' => DB::raw('bonus + ' . $giftCartAmount),
																		  'recharge' => DB::raw('recharge + ' . $giftCartAmount)]);
                        DB::table('gift_cart')->where('id', $data->id)->update(['availed_num' => DB::raw('availed_num + 1')]);

                        $data = [
                            'userid' => $userid,
                            'amount' => $giftCartAmount,
                            'subtypeid' => 20,
                            'created_at' => $datetime,
                            'updated_at' => $datetime
                        ];
                        DB::table('wallet_history')->insert($data);

                        $response['message'] = " Add $giftCartAmount Rs. Successfully";
                        $response['status'] = "200";
                        return response()->json($response,200);
                    } else {
                        $response['message'] = "No record found";
                        $response['status'] = "400";
                        return response()->json($response,400);
                    }
                } else {
                    $response['message'] = "You have already availed this offer!";
                    $response['status'] = "400";
                    return response()->json($response,400);
                }
            } else {
                $response['message'] = "No longer available this offer.";
                $response['status'] = "400";
                return response()->json($response,400);
            }
        } else {
            $response['message'] = "Invalid Gift Code!";
            $response['status'] = "400";
            return response()->json($response,400);
        }
    
}


///// FeedBack ////

 public function feedback(Request $request)
{
 
        $validator = Validator::make($request->all(), [
            'userid' => 'required',
            'description' => 'required',
        ]);

        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];

            return response()->json($response,400);
        }

        $data = array(
            'userid' => $request->input('userid'),
            'description' => $request->input('description'),
            'status' => 1,
            'created_at' => now(),
             'updated_at' => now(),
        );

        $data1 = DB::table('feedbacks')->insert($data);

        if ($data1) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $data1
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'Failed','status' => 400], 400);
        }
}



////Pay Modes ////


public function pay_modes(Request $request)
{
    if ($request->isMethod('get')) {
        $userid = $request->input('userid');
		$type = $request->input('type');
		if($type == ''){
        $check = DB::table('users')->where('first_recharge', '1')->where('id', $userid)->first();

        $pay_modes = DB::table('pay_modes')->where('status', '1')->get();

        if ($pay_modes->isNotEmpty()) {
            $response['msg'] = "Successfully";
            $response['data'] = $pay_modes->toArray();

            if ($check && $check->first_recharge == '1') {
                $response['minimum'] = 500;
                $response['status'] = "200";
            } else {
                $response['minimum'] = 100;
                $response['status'] = "400";
            }

            return response()->json($response);
        } else {
            // If no data is found, set an appropriate response
            $response['msg'] = "No record found";
            $response['status'] = "400";
            return response()->json($response);
        }
	 } else {
        $check = DB::table('users')->where('first_recharge', '1')->where('id', $userid)->first();

        $pay_modes = DB::table('pay_modes')->where('status', '1')->where('type', $type)->get();

        if ($pay_modes->isNotEmpty()) {
            $response['msg'] = "Successfully";
            $response['data'] = $pay_modes->toArray();

            if ($check && $check->first_recharge == '1') {
                $response['minimum'] = 500;
                $response['status'] = "200";
            } else {
                $response['minimum'] = 100;
                $response['status'] = "400";
            }

            return response()->json($response);
        } else {
            // If no data is found, set an appropriate response
            $response['msg'] = "No record found";
            $response['status'] = "400";
            return response()->json($response);
        }
    }
    } else {
        return response()->json(['error' => 'Unsupported request method'], 400);
    }
}





/// transaction_history_list Api ///
	
      public function transaction_history_list()
      {
      $subtype=DB::select("SELECT `id`,`name` FROM `subtype` WHERE `status_active`=1" );

        if ($subtype) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $subtype
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
    
   
   
////// Result Api ////
public function transaction_history(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }
    
    $userid = $request->userid;
    $subtype = $request->subtypeid;
     //$offset = $request->offset ?? 0;
    $from_date = $request->created_at;
    //$to_date = $request->created_at;
    //$status = $request->status;

// $status=DB::SELECT("SELECT `status` FROM `users` WHERE id=$userid"); 
// //dd($status);
// 	$ddd=$status[0]->status;
// 	//dd($ddd);
// if($ddd == 1){hea
    $where = [];

    if (!empty($userid)) {
        $where[] = "wallet_history.`userid` = '$userid'";
    }

    if (!empty($from_date)) {
		$newDateString = date("Y-m-d", strtotime($from_date));
		
        $where[] = "DATE(`wallet_history`.`created_at`) = '$newDateString'";
		
    }
    if (!empty($subtype)) {
        $where[] = "`wallet_history`.`subtypeid` = '$subtype'";
    }
    //
    //
    
    $query = "SELECT subtype.name as type , wallet_history.amount as amount, wallet_history.created_at as datetime FROM `wallet_history` LEFT JOIN `subtype` on wallet_history.subtypeid = subtype.id";

   if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where) . " AND wallet_history.subtypeid != '24'";
} else {
    $query .= "WHERE wallet_history.subtypeid != '24'";
}

    $query .= "ORDER BY wallet_history.id DESC";

    $results = DB::select($query);
    //dd($results);
if(!empty($results)){
    return response()->json([
        'status' => 200,
        'message' => 'Data found',
        'data' => $results
    ]);
}else{
     return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
}
// }else{
    
//  $response['message'] = "User block by admin..!";
//                 $response['status'] = "401";
//                 return response()->json($response,401);
    
// }
    
}
   

public function image_all(){
      
         $user = DB::select("SELECT `image` FROM `all_images`
");
          if($user){
          $response =[ 'success'=>"200",'data'=>$user,'message'=>'Successfully'];return response ()->json ($response,200);
      }
      else{
       $response =[ 'success'=>"400",'data'=>[],'message'=>'Not Found Data'];return response ()->json ($response,400); 
      } 
    }


      public function forget_pass(Request $request)
      {
		
		   $validator = Validator::make($request->all(), [
          'mobile' => ['required', 'string', 'regex:/^\d{10}$/','exists:users,mobile'], // Ensure 10 digits
	      'password' => 'required|string|min:6'
    ]);

	    $validator->stopOnFirstFailure();
	   
    if($validator->fails()){
		
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ]);
    }
	   
			
	  $user = DB::table('users')->where('mobile',$request->mobile)
		  ->update([
		   'password'=>$request->password
		  ]);
	  
	   return response()->json([
	      'status'=>200,
		  'message'=>'Password reset successfully.',
	   ]);
	   
	   
		
	}
	
      public function profile(Request $request) 
      {
          $ldate = new DateTime('now');
        
          
          //dd($login_time);
          $uid=$request->id;
    if (empty($uid)) {
        return response()->json([
            'status' => 400,
            'message' => 'UID Required'
        ]);
    }



$data = DB::table('users as u')
            ->select('u.*', 'a1.longtext as minimum_withdraw', 'a2.longtext as maximum_withdraw')
            ->leftJoin('admin_settings as a1', function ($join) {
                $join->on('a1.id', '=', DB::raw(15));
            })
            ->leftJoin('admin_settings as a2', function ($join) {
                $join->on('a2.id', '=', DB::raw(16));
            })
            ->where('u.id', $uid)
            ->limit(1)
            ->first();


    
    $thirdpartywallet = isset($data->third_party_wallet) ? $data->third_party_wallet : 0;
    $main_wallet = isset($data->wallet) ? $data->wallet : 0;

    $total_wallet = $thirdpartywallet + $main_wallet;

		$status=$data->status;
		if($status == 1)
    {
    if ($data !== null) {
        return response()->json([
            'status' => 200,
            'message' => 'Data found',
            'id' => $data->id,
			'mobile'=>$data->mobile,
			'email'=>$data->email,
			'username'=>$data->username,
			'userimage'=>$data->userimage,
			'recharge'=>$data->recharge,
			'u_id'=>$data->u_id,
			'main_wallet'=>$main_wallet,
			'third_party_wallet'=>$thirdpartywallet,
			'total_wallet'=>$total_wallet,
			'winning_amount'=>$data->winning_wallet,
			'minimum_withdraw'=>$data->minimum_withdraw,
			'maximum_withdraw'=>$data->maximum_withdraw,
			'last_login_time'=>$ldate->format('Y-m-d H:i:s'),
			'apk_link'=> env('APP_URL')."tirangawin.apk",
			'referral_code_url'=> env('APP_URL')."registerwithref/".$data->referral_code,
			'aviator_link'=>"https://goldwins.live/",
			'aviator_event_name'=>"tirnga_win",
			'usdt_value' => 90,
			'usdt_withdraw_amount' => 91,
			'inr_withdraw_amount' => 200
        ]);
    } else {    
        return response()->json([
            'status' => 400,
            'message' => 'No data found'
        ]);
    }
    }else{
         $response['message'] = "User block by admin..!";
                $response['status'] = "401";
                return response()->json($response,401);
    }
}



public function Status_list(){
      
      $status= array(
           array(
        'id' => '0',
        'name' => 'All'
    ),
    array(
        'id' => '1',
        'name' => 'Processing'
    ),
    array(
        'id' => '2',
        'name' => 'Completed'
    ),
    array(
        'id' => '3',
        'name' => 'Reject'
    )
);
      
        //  $status = DB::select("SELECT `id`,`name` FROM `status` WHERE 1");
          if($status){
          $response =[ 'success'=>"200",'data'=>$status,'message'=>'Successfully'];return response ()->json ($response,200);
      }
      else{
       $response =[ 'success'=>"400",'data'=>[],'message'=>'Not Found Data'];return response ()->json ($response,400); 
      } 
    }




      public function deposit_history(Request $request)
      {
        $validator = Validator::make($request->all(), [
        'user_id' => 'required',
		'type' => 'required',
			
    ]);

    $date = date('Y-m-d h:i:s');
     $validator->stopOnFirstFailure();
	   
    if($validator->fails()){
		
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ],400);
    }
   
     $user_id = $request->user_id;
     $status = $request->status;
        $type = $request->type; 
		  $date = $request->created_at; 
         $where = [];

    if (!empty($user_id)) {
        $where[] = "payins.`user_id` = '$user_id'";
    }

    if (!empty($status)) {
        $where[] = "`payins`.`status` = '$status'";
    }
	 if (!empty($type)) {
        $where[] = "`payins`.`type` = '$type'";
    }
	 if (!empty($date)) {
		  //$where[] = "payins.created_at ='%$date% '";
		$newDateString = date("Y-m-d", strtotime($date));
        $where[] = "DATE(`payins`.`created_at`)= '$newDateString'";
    }
  
    
    $query = "SELECT `cash`,`type`,`status`,`order_id`,`typeimage`,`created_at` FROM `payins`  ";

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " ORDER BY payins.id DESC";
	//echo $query;die;
    $payin = DB::select($query);
   
        if ($payin) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $payin
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 200,'data' => []], 400);
        }
    }

      public function withdraw_history(Request $request)
       {
         $validator = Validator::make($request->all(), [
        'user_id' => 'required',
		'type'=>'required',
    ]);

    $date = date('Y-m-d h:i:s');
     $validator->stopOnFirstFailure();
	   
    if($validator->fails()){
		
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ],400);
    }
   
     $user_id = $request->user_id;
     $status = $request->status;
      $type = $request->type;  
		$created_at = $request->created_at; 
         $where = [];

    if (!empty($user_id)) {
        $where[] = "withdraw_histories.`user_id` = '$user_id'";
    }

    if (!empty($status)) {
        $where[] = "`withdraw_histories`.`status` = '$status'";
    }
	if (!empty($type)) {
        $where[] = "`withdraw_histories`.`type` = '$type'";
    }
	if (!empty($created_at)) {
		$newDateString = date("Y-m-d", strtotime($created_at));
        $where[] = "DATE(`withdraw_histories`.`created_at`) = '$newDateString'";

    }
    
    $query = "SELECT `id`,`user_id`,`amount`,`type`,`status`,`typeimage`,`order_id`,`created_at` FROM withdraw_histories ";

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " ORDER BY withdraw_histories.id DESC";

    $payin = DB::select($query);

        if ($payin) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $payin
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 200,'data' => []], 400);
        }
    }

////notification ////

 public function notification()
       {
       

       $notification = DB::select("SELECT `name`,`disc` FROM `notifications` WHERE `status`=1
");



        if ($notification) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $notification
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }


      public function Privacy_Policy()
       {
       

       $privacyPolicy = Setting::where('id', 1)
          ->where('status', 1)
          ->select('name', 'description')
          ->first();



        if ($privacyPolicy) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $privacyPolicy
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }

	/// About us Api ///
	
      public function about_us(Request $request)
      {
		  $validator = Validator::make($request->all(), [
        'type' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $type = $request->type;
 
		  
		  
        $about_us = DB::select("SELECT `name`,`description` FROM `settings` WHERE `type`=$type;
");

        if ($about_us) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $about_us
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found', 'status' => 400,
                'data' => []], 400);
        }
    }
	
	
	////Customer Service /////
	  public function customer_service()
      {
        $customer_service = DB::select("SELECT `name`, `Image`, `link`
FROM `customer_services`
WHERE `status` = 1");


        if ($customer_service) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $customer_service
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found', 'status' => 400,
                'data' => []], 400);
        }
    }
	
	
	/// Contact us Api ///
	
      public function contact_us()
      {
        $contact = Setting::where('id', 4)
             ->where('status', 1)
             ->select('name', 'description', 'link')
             ->first();


        if ($contact) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $contact
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
	
/// Support Api ///
	
      public function support()
      {
        $support = Setting::where('id', 5)
                  ->where('status', 1)
                  ->select('name', 'link')
                  ->first();


        if ($support) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $support
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
	
	// profile Update Api //
public function update_profile(Request $request)
{
    $request->validate([
        'id' => 'required'
    ]);
    
    $id = $request->id;
    
    $value = User::findOrFail($id);
    $status=$value->status;
    
    	if($status == 1)
    {
    if (!empty($request->username)) {
        $value->username = $request->username;
    }
    
    if (!empty($request->userimage) && $request->userimage != "null") {
        $value->userimage = $request->userimage;
    }

    // Save the changes
    $value->save();

    $response = [
        'status' => 200,
        'message' => "Successfully updated"
    ];

    return response()->json($response, 200);
    }else{
         $response['message'] = "User block by admin..!";
                $response['status'] = "401";
                return response()->json($response,401);
    }
}


///// main wallet transfer //

// public function main_wallet_transfer(Request $request)
// {
//     $request->validate([
//         'id' => 'required'
//     ]);
    
//     $id = $request->id;
    
//     $value = User::findOrFail($id);
//     $status=$value->status;
//     $main_wallet=$value->wallet;
//     $thirdpartywallet=$value->third_party_wallet;
//     $add_main_wallet=$main_wallet+$thirdpartywallet;
//     	if($status == 1)
//     {
//   $add=DB::select("UPDATE `users` SET `wallet`='$add_main_wallet' WHERE id=$id");
//   $decriment=DB::select("UPDATE `users` SET `third_party_wallet`='third_party_wallet-$thirdpartywallet' WHERE id=$id");
//     $response = [
//         'status' => 200,
//         'message' => "Successfully updated"
//     ];

//     return response()->json($response, 200);
//     }else{
//          $response['message'] = "User block by admin..!";
//                 $response['status'] = "401";
//                 return response()->json($response,401);
//     }
// }

public function main_wallet_transfer(Request $request)
{
    $request->validate([
        'id' => 'required|exists:users,id'
    ]);
    
    $id = $request->id;
    
    $user = User::findOrFail($id);
    $status = $user->status;
    $main_wallet = $user->wallet;
    $thirdpartywallet = $user->third_party_wallet;
    $add_main_wallet = $main_wallet + $thirdpartywallet;
    
    if ($status == 1) {
        $user->wallet = $add_main_wallet;
        $user->third_party_wallet = 0;
        $user->save();

        $response = [
            'status' => 200,
            'message' => "Wallet transfer Successfully ....!"
        ];

        return response()->json($response, 200);
    } else {
        $response = [
            'status' => 401,
            'message' => "User blocked by admin..!"
        ];
        return response()->json($response, 401);
    }
}


///// Change Password ////


public function changePassword(Request $request)
{
        $validator = Validator::make($request->all(), [
            'userid' => 'required',
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password',
        ]);

        $validator->stopOnFirstFailure();
	   
    if($validator->fails()){
		
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ],400);
    }
        $userid = $request->input('userid');
        $oldPassword = $request->input('old_password');
        $newPassword = $request->input('new_password');

        $user = User::find($userid);

        if (!$user) {
            return response()->json([
                'msg' => 'User not found',
                'status' => 404
            ], 404);
        }

        if ($oldPassword != $user->password) {
            return response()->json([
                'msg' => 'Incorrect old password',
                'status' => 400
            ], 400);
        }

        $user->password = $newPassword;
        $user->save();

        return response()->json([
            'msg' => 'Password changed successfully!',
            'status' => 200
        ], 200);
    
}
	
	
	
// 	Slider Image Policy //
	
  public function slider_image_view()
    {
       

       $slider = DB::select("SELECT sliders.title as name,sliders.image as image,sliders.activity_image as activity_image FROM `sliders` WHERE `status`=1");
           
  //dd($slider);
        if ($slider) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $slider
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function attendance_List(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric'
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
       // $userid = $request->input('userid');
      $list = DB::select("SELECT COALESCE(COUNT(at_claim.`userid`),0) AS attendances_consecutively , COALESCE(SUM(attendances.attendance_bonus),0) AS accumulated FROM `at_claim` LEFT JOIN attendances ON at_claim.attendance_id =attendances.id WHERE at_claim.userid=$userid");

    $day = $list[0]->attendances_consecutively;
    $bonus_amt = $list[0]->accumulated;


        $attendanceList = DB::select("
   SELECT a.`id` AS `id`, a.`accumulated_amount` as accumulated_amount ,a.`attendance_bonus` as attendance_bonus, COALESCE(c.`status`, '1') AS `status`, COALESCE(a.`created_at`, 'Not Found') AS `created_at` FROM `attendances` a LEFT JOIN `at_claim` c ON a.`id` = c.`attendance_id` AND c.`userid` =$userid  ORDER BY a.`id` ASC LIMIT 7
");
  

        if (!empty($attendanceList)) {
            $response = [
                'message' => 'Attendance List',
                'status' => 200,
                'attendances_consecutively' => $day,
                'accumulated' =>$bonus_amt,
                'data' => $attendanceList,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function attendance_history(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric'
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
       // $userid = $request->input('userid');
      $list1 = DB::select("SELECT at_claim.id AS id,attendances.attendance_bonus AS attendance_bonus,at_claim.created_at FROM attendances LEFT JOIN at_claim ON at_claim.attendance_id=attendances.id WHERE at_claim.userid=$userid");

    
  

        if (!empty($list1)) {
            $response = [
                'message' => 'Attendance History',
                'status' => 200,
                'data' => $list1,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    //// Attendance Claim ////
    
//      public function attendance_claim(Request $request)
//     {
//           $validator = Validator::make($request->all(), [
//          'userid' => 'required|numeric'
//     ]);

	
// 	$validator->stopOnFirstFailure();
	
//     if($validator->fails()){
		
// 		        		     $response = [
//                         'status' => 400,
//                       'message' => $validator->errors()->first()
//                       ]; 
		
// 		return response()->json($response,400);
		
//     }
//      $userid = $request->userid;  
     
//       $results = DB::select("SELECT a.`id` AS `id`, a.`accumulated_amount` AS accumulated_amount, a.`attendance_bonus` AS attendance_bonus, COALESCE(c.`status`, '1') AS `status`, COALESCE(a.`created_at`, 'Not Found') AS `created_at`, u.`wallet` FROM `attendances` a LEFT JOIN `at_claim` c ON a.`id` = c.`attendance_id` AND c.`userid` = 2 JOIN `users` u ON u.id = $userid WHERE COALESCE(c.`status`, '1') = '1' AND u.wallet >= a.accumulated_amount ORDER BY a.`id` ASC LIMIT 7");
     
//  $bonus=$results[0]->attendance_bonus;
//  $id=$results[0]->id;
//  $status=$results[0]->status;
//  $count=DB::select("SELECT COALESCE(COUNT(userid), 0) AS userid FROM `at_claim` WHERE userid = $userid AND DATE(created_at) = CURDATE()");
 
//  if($count <= 0){
//      DB::table('at_claim')->insert([
//     'userid' => $userid,
//     'attendance_id' => $id,
//     'status' => $status
// ]);


//     DB::select("UPDATE `users` SET `wallet`=`wallet`+$bonus WHERE `id`=$userid");
//          DB::select("INSERT INTO `wallet_history`( `userid`, `amount`, `subtypeid`, `created_at`, `updated_at`) VALUES ('$userid','$bonus','11','$datetime','$datetime')");
         
//          $response = [
//                 'message' => 'Today Claimed Successfully ...!',
//                 'status' => 200,
                
//             ];
//             return response()->json($response,200);
    
    
//  }else{
     
//      return response()->json(['message' => 'Today You Are already Claimed..!','status' => 400], 400);
//  }
    
    
//     }

	public function attendance_claim(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric'
    ]);

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->userid;

    $results = DB::select("SELECT a.`id` AS `id`, a.`accumulated_amount` AS accumulated_amount, a.`attendance_bonus` AS attendance_bonus, COALESCE(c.`status`, '1') AS `status`, COALESCE(a.`created_at`, 'Not Found') AS `created_at`, u.`wallet` FROM `attendances` a LEFT JOIN `at_claim` c ON a.`id` = c.`attendance_id` AND c.`userid` = $userid JOIN `users` u ON u.id = $userid WHERE COALESCE(c.`status`, '1') = '1' AND u.wallet >= a.accumulated_amount ORDER BY a.`id` ASC LIMIT 7");
//dd($results);
    if (count($results) > 0) {
        $bonus = $results[0]->attendance_bonus;
        $id = $results[0]->id;
        $accumulated_amount =$results[0]->accumulated_amount;
        $wallet = $results[0]->wallet;
if($wallet >= $accumulated_amount){
        $count = DB::select("SELECT COALESCE(COUNT(userid), 0) AS userid FROM `at_claim` WHERE userid = $userid AND DATE(created_at) = CURDATE()");
    
   // dd($count);
        $datetime = now();
        if ($count[0]->userid == 0) {
            DB::table('at_claim')->insert([      
                'userid' => $userid,
                'attendance_id' => $id,   
                'status' => '0',
                'created_at' => $datetime,
                'updated_at' => $datetime    
            ]);

            // Assuming you have `$datetime` defined somewhere
            DB::table('users')->where('id', $userid)->increment('wallet', $bonus);
            DB::table('wallet_history')->insert([
                'userid' => $userid,
                'amount' => $bonus,
                'subtypeid' => 11,
                'created_at' => $datetime,
                'updated_at' => $datetime
            ]);

            $response = [
                'message' => 'Today Claimed Successfully ...!',
                'status' => 200,
            ];
            return response()->json($response, 200);
        } else {
            return response()->json(['message' => 'Today You Have Already Claimed', 'status' => 400], 400); 
        }
}else{
  return response()->json(['message' => 'You can not claim due to insufficient Balance...!', 'status' => 400], 400);  
}
        
    } else {
        return response()->json(['message' => 'Today You Have Already Claimed..!', 'status' => 400], 400);
    }
}

	
 
    //// activity rewards /////
    
     public function activity_rewards(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');
        $datetime = date('Y-m-d H:i:s');
        //  $date = date('Y-m-d');
        //  date_default_timezone_set('Asia/Kolkata');
        $date = now()->format('Y-m-d');
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric'
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
       // $userid = $request->input('userid');
      $bet_amount = DB::table('bets')
    ->where('userid', $userid)
    ->whereDate('created_at', '=', $date)
    ->sum('amount');


    $activity_reward=DB::select("SELECT * FROM `activity_rewards` ");
    //$range_amount=$activity_reward['range_amount'];
    $data=[];
foreach ($activity_reward as $item){
    
       $range_amount= $item->range_amount;
       $amount=$item->amount;
       
       $id=$item->id;
       $name=$item->name;
       $status=$item->status;
       $created_at=$item->created_at;
       $updated_at=$item->updated_at;
       
    
    //$amount=$activity_reward['amount'];
    
    if($bet_amount == $range_amount){
        DB::select("UPDATE `users` SET `wallet`=`wallet`+$amount WHERE `id`=$userid");
         DB::select("INSERT INTO `wallet_history`( `userid`, `amount`, `subtypeid`,`description`, `created_at`, `updated_at`) VALUES ('$userid','$amount','21','$range_amount','$datetime','$datetime')");
           DB::select("INSERT INTO `activity_rewards_claim`( `userid`, `acyivity_reward_id`, `status`) VALUES ('$userid','$id','1')");
    }
    
    
     $data[] = [
         'id'=>$id,
         'name'=>$name,
         'range_amount'=>$range_amount,
         'amount'=>$amount,
         'status'=>$status,
         'bet_amount'=>$bet_amount,
         'created_at'=>$created_at,    
         'updated_at'=>$updated_at       
         ];
          
    //dd($data);
}
    
    $activity_reward['bet_amount']=$bet_amount;
        if (!empty($activity_reward)) {
            $response = [
                'message' => 'activity rewards List',
                'status' => 200,
                //'bet_amount'=>$bet_amount,
                'data' => $data
                
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    ///// activity_rewards history ////
    
     public function activity_rewards_history(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric',
         'subtypeid'=>'required',
         
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
     $subtypeid = $request->subtypeid;  
       // $userid = $request->input('userid');

       $act_reward_hist=DB::select("SELECT wallet_history.*,subtype.name as name FROM `wallet_history` LEFT JOIN subtype ON wallet_history.subtypeid=subtype.id WHERE wallet_history.userid=$userid && wallet_history.subtypeid=$subtypeid");
       
  

        if (!empty($act_reward_hist)) {
            $response = [
                'message' => 'activity rewards List',
                'status' => 200,
                'data' => $act_reward_hist,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    /// invitation Bonus List/////
    
//      public function invitation_bonus_list(Request $request)
//     {
//           $validator = Validator::make($request->all(), [
//          'userid' => 'required|numeric'
//     ]);

	
// 	$validator->stopOnFirstFailure();
	
//     if($validator->fails()){
		
// 		        		     $response = [
//                         'status' => 400,
//                       'message' => $validator->errors()->first()
//                       ]; 
		
// 		return response()->json($response,400);
		
//     }
//      $userid = $request->userid;  
      

//     //   $userrefid=DB::select("SELECT `referral_user_id` FROM `users` WHERE `id`=$userid");
//     //   $refid=$userrefid[0]->referral_user_id;
      
//       $referid=DB::SELECT("SELECT (SELECT COALESCE( COUNT(first_recharge)) from `users` WHERE `referral_user_id`=$userid && first_recharge =0) AS refereler_only,(SELECT COALESCE( COUNT(first_recharge)) from `users` WHERE `referral_user_id`=$userid && first_recharge >0) AS first_recharge_count_success");
// $total_refer=$referid->refereler_only;
// $total_recharge=$referid->first_recharge_count_success;
//       $invite_people=DB::select("SELECT * FROM `invite_bonus`");
//       $id=$invite_people->id;
// $no_of_user=$invite_people->no_of_user;
// $amount=$invite_people->amount;
// $claim_amount=$invite_people->claim_amount;
// $status=$invite_people->status;
//       $row=[];
//       $row[]=[
//           'bonus_id'=>$id,
//          'no_of_user'=>$no_of_user,
//          'amount'=>$amount,
//          'claim_amount'=>$claim_amount,
//          'status'=>$status,
//          'no_of_invitees'=>$total_refer,
//          'refer_invitees'=>$total_recharge
           
//           ];

        
       
  

//         if (!empty($row)) {
//             $response = [
//                 'message' => 'invitation_bonus_list',
//                 'status' => 200,
//                 'data' => $row,
//             ];
//             return response()->json($response);
//         } else {
//             return response()->json(['message' => 'Not found..!','status' => 400,
//                 'data' => []], 400);
//         }
//     }
// public function invitation_bonus_list(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'userid' => 'required|numeric'
//     ]);

//     $validator->stopOnFirstFailure();

//     if ($validator->fails()) {
//         $response = [
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ];
//         return response()->json($response, 400);
//     }

//     $userid = $request->userid;

//     $referid = DB::selectOne("SELECT 
//                                     (SELECT COALESCE(COUNT(first_recharge), 0) FROM `users` WHERE `referral_user_id` = $userid AND first_recharge = 0) AS refereler_only,
//                                     (SELECT COALESCE(COUNT(first_recharge), 0) FROM `users` WHERE `referral_user_id` = $userid AND first_recharge > 0) AS first_recharge_count_success"
//                                 );

//     $total_refer = $referid->refereler_only;
//     $total_recharge = $referid->first_recharge_count_success;

//     $invite_bonus = DB::table('invite_bonus')->first();

//     if ($invite_bonus) {
//         $response = [
//             'message' => 'invitation_bonus_list',
//             'status' => 200,
//             'data' => [
//                 [
//                     'bonus_id' => $invite_bonus->id,
//                     'no_of_user' => $invite_bonus->no_of_user,
//                     'amount' => $invite_bonus->amount,
//                     'claim_amount' => $invite_bonus->claim_amount,
//                     'status' => $invite_bonus->status,
//                     'no_of_invitees' => $total_refer,
//                     'refer_invitees' => $total_recharge
//                 ]
//             ]
//         ];
//         return response()->json($response,200);
//     } else {
//         return response()->json([
//             'message' => 'Not found..!',
//             'status' => 400,
//             'data' => []
//         ], 400);
//     }
// }

public function invitation_bonus_list(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->userid;

    $referid = DB::selectOne("SELECT 
                                    (SELECT COALESCE(COUNT(first_recharge), 0) FROM `users` WHERE `referral_user_id` = $userid AND first_recharge = 0) AS refereler_only,
                                    (SELECT COALESCE(COUNT(first_recharge), 0) FROM `users` WHERE `referral_user_id` = $userid AND first_recharge > 0) AS first_recharge_count_success"
                                );

    $total_refer = $referid->refereler_only;
    $total_recharge = $referid->first_recharge_count_success;

    $invite_bonus = DB::table('invite_bonus')->get();

    if ($invite_bonus->isNotEmpty()) {
        $response = [
            'message' => 'invitation_bonus_list',
            'status' => 200,
            'data' => $invite_bonus->map(function ($bonus) use ($total_refer, $total_recharge) {
                return [
                    'bonus_id' => $bonus->id,
                    'no_of_user' => $bonus->no_of_user,
                    'amount' => $bonus->amount,
                    'claim_amount' => $bonus->claim_amount,
                    'status' => $bonus->status,
                    'no_of_invitees' => $total_refer,
                    'refer_invitees' => $total_recharge
                ];
            })
        ];
        return response()->json($response);
    } else {
        return response()->json([
            'message' => 'Not found..!',
            'status' => 400,
            'data' => []
        ], 400);
    }
}

///// Invitation_reward_rule ////
    
     public function Invitation_reward_rule(Request $request)
    {
          

       $rule=DB::select("SELECT * FROM `invite_bonus`");
       
  

        if (!empty($rule)) {
            $response = [
                'message' => 'Invitation rewards rule',
                'status' => 200,
                'data' => $rule,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
	
    //// Invitation record ////
    
    public function Invitation_records(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->userid;
 

       $records=DB::select("SELECT `username`,`u_id`,`first_recharge_amount`,`created_at` FROM `users` WHERE `referral_user_id`=$userid");
       
  

        if (!empty($records)) {
            $response = [
                'message' => 'Invitation rewards rule',
                'status' => 200,
                'data' => $records,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    //// Extra First Deposit bonus history ///
    
    //  public function extra_first_deposit_bonus(Request $request)
    // {
          

    //   $extra_first_bonus=DB::select("SELECT * FROM `extra_first_deposit_bonus`");
       
  

    //     if (!empty($extra_first_bonus)) {
    //         $response = [
    //             'message' => 'Invitation rewards rule',
    //             'status' => 200,
    //             'data' => $extra_first_bonus,
    //         ];
    //         return response()->json($response);
    //     } else {
    //         return response()->json(['message' => 'Not found..!','status' => 400,
    //             'data' => []], 400);
    //     }
    // }
    
     public function extra_first_payin(Request $request)
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

        $check_id = DB::table('users')->where('id',$userid)->first();
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
                'status' => 1 // Assuming initial status is 0
            ]);
         // dd($redirect_url);
            if (!$insert_payin) {
                return response()->json(['status' => 400, 'message' => 'Failed to store record in payin history!']);
            }
 
            $postParameter = [
                'merchantid' => "INDIANPAY00INDIANPAY0033",
                'orderid' => $orderid,
                'amount' => $request->cash,
                'name' => $check_id->u_id,
                'email' => "abc@gmail.com",
                'mobile' => $check_id->mobile,
                'remark' => 'payIn',
                'type'=>$request->cash,
                'redirect_url' => env('APP_URL')."api/checkPayment?order_id=$orderid"
               // 'redirect_url' => config('app.base_url') ."/api/checkPayment?order_id=$orderid"
            ];


            $curl = curl_init();

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
             
			echo $response;
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

    public function checkPayment1(Request $request)
    {
       // dd($request);
        $orderid = $request->input('order_id');
	//dd($orderid);
     //bonus = gift_cash
        if ($orderid == "") {
            return response()->json(['status' => 400, 'message' => 'Order Id is required']);
        } else {
            $match_order = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->first();
//dd($match_order);
            if ($match_order) {
                $uid = $match_order->user_id;
            
                $cash = $match_order->cash;
                $type = $match_order->type;
               
                $orderid = $match_order->order_id;
                
                $datetime=now();
               // dd("UPDATE payins SET status = 2 WHERE order_id = $orderid AND status = 1 AND user_id = $uid");

              $update_payin = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->where('user_id', $uid)->update(['status' => 2]);
    
                if ($update_payin) {
                    
                    // $wallet = $cash + $bonus + $extra_cash;
                    // $bonusToAdd = $bonus;
                    //dd($uid);
    $referid=DB::select("SELECT referral_user_id,first_recharge FROM `users` WHERE id=$uid");
    //dd($referid);
    $first_recharge=$referid[0]->first_recharge;
    $referuserid=$referid[0]->referral_user_id;
   // dd($first_recharge);
if($first_recharge == 0){
    
    $extra=DB::select("SELECT * FROM `extra_first_deposit_bonus` WHERE `first_deposit_ammount`=$cash"); 
    $id=$extra[0]->id;
    $first_deposit_ammount=$extra[0]->first_deposit_ammount;
    $bonus=$extra[0]->bonus;
    
    $amount=$bonus+$first_deposit_ammount;

    DB::INSERT("INSERT INTO `extra_first_deposit_bonus_claim`( `userid`, `extra_fdb_id`, `amount`, `bonus`, `status`, `created_at`, `updated_at`) VALUES ('$uid','$id','$first_deposit_ammount','$bonus','0','$datetime','$datetime')");
   
                    $updateUser =DB::update("UPDATE users 
    SET 
    wallet = wallet + $amount,
    first_recharge = first_recharge + $cash,
    first_recharge_amount = first_recharge_amount + $cash,
    recharge = recharge + $cash,
    total_payin = total_payin + $cash,
    no_of_payin = no_of_payin + 1,
    deposit_balance = deposit_balance + $cash
    WHERE id = $uid;
    ");
    //dd("hiii");
    // dd("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1,yesterday_first_deposit = yesterday_first_deposit + $cash WHERE id=$referuserid");
    //dd($referuserid);
    DB::UPDATE("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1,yesterday_first_deposit = yesterday_first_deposit + $cash WHERE id=$referuserid");
     return redirect()->away(env('APP_URL').'uploads/payment_success.php');
}else{
    
      $updateUser =DB::update("UPDATE users 
    SET 
    wallet = wallet + $cash,
    recharge = recharge + $cash,
    total_payin = total_payin + $cash,
    no_of_payin = no_of_payin + 1,
    deposit_balance = deposit_balance + $cash
    WHERE id = $uid;
    ");
    
    //dd("hello");
     //dd($referuserid);
    DB::select("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1 WHERE id=$referuserid");
     return redirect()->away(env('APP_URL').'uploads/payment_success.php');
}

     
    
                    if ($updateUser) {
                        // Redirect to success page
                        //dd("hello");
                        return redirect()->away(env('APP_URL').'uploads/payment_success.php');
                    } else {
                        return response()->json(['status' => 400, 'message' => 'User balance update failed!']);
                    }
                } else {
                    return response()->json(['status' => 400, 'message' => 'Failed to update payment status!']);
                }
            } else {
                return response()->json(['status' => 400, 'message' => 'Order id not found or already processed']);
            }
        }
    }
	
    
    
    public function extra_first_deposit_bonus(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->userid;
 

        
        $rowCount = DB::table('extra_first_deposit_bonus_claim')->where('userid', $userid)->count();
        
        if ($rowCount > 0) {
            $checkDate = DB::select("SELECT extra_first_deposit_bonus.first_deposit_ammount as first_deposit_ammount, extra_first_deposit_bonus.bonus as bonus, extra_first_deposit_bonus.bonus + extra_first_deposit_bonus.bonus as totalamount, COALESCE(extra_first_deposit_bonus_claim.status, 1) as status FROM extra_first_deposit_bonus LEFT JOIN extra_first_deposit_bonus_claim ON extra_first_deposit_bonus.first_deposit_ammount = extra_first_deposit_bonus_claim.amount AND extra_first_deposit_bonus_claim.userid = ? ORDER BY COALESCE(extra_first_deposit_bonus_claim.status, 1) DESC", [$userid]); 
           
            if (!empty($checkDate)) {
                return response()->json([
                    'msg' => 'Successfully...!',
                    'status' => '200',
                    'data' => $checkDate
                ]);
            } else {
                return response()->json([
                    'msg' => 'Internal error...',
                    'status' => '400'
                ]);
            }
        } else {
           
            $checkDate = DB::table('extra_first_deposit_bonus')->select('first_deposit_ammount', 'bonus', DB::raw('first_deposit_ammount + bonus as totalamount'), 'status','created_at')->get(); 
           
            
            if (!empty($checkDate)) {
                return response()->json([
                    'msg' => 'Successfully...!',
                    'status' => '200',
                    'data' => $checkDate
                ]);
            }
        }
    
}
  


//// Frontend MLM Data //


// public function frontend_mlm_data(Request $request)
// {
//     header("Access-Control-Allow-Origin: *");
    
        
//   $validator = Validator::make($request->all(), [
//         'id' => 'required|integer'
//     ]);

//     $validator->stopOnFirstFailure();

//     if ($validator->fails()) {
//         $response = [
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ];
//         return response()->json($response, 400);
//     }


   
//     date_default_timezone_set('Asia/Kolkata');
//     $datetime = date('Y-m-d H:i:s');

//     $userId = $request->input('id');

//     $user_bonus = User::select('bonus')->where('id', $userId)->first();
//     dd($user_bonus);
//     $bonus = $user_bonus->bonus;

//     $user_data = User::all()->toArray();
//     $mlm_level_data = DB::table('mlm_levels')
//         ->select('mlm_levels.*')
//         ->get()
//         ->toArray();
//         //dd($mlm_level_data);
//     $level_data = count($mlm_level_data);
//     $alldata = array();
//     for ($i = 0; $i < $level_data; $i++) {
//         $name = $mlm_level_data[$i]->name;
//         $commission = $mlm_level_data[$i]->commission;
//         $usermlm = array();
//         if ($i == 0) {
//             $usermlm[] = $userId;
//         } else {
//             $data = $mlm_level_data[$i - 1]->name;
//             foreach ($alldata[$data] as $itemss) {
//                 $usermlm[] = $itemss['user_id'];
//             }
//         }
//         $level = array();
//         for ($w = 0; $w < count($usermlm); $w++) {
//             foreach ($user_data as $item) {
//                 if ($item['referral_user_id'] === $usermlm[$w]) {
//                     $todays = $item['today_turnover'] * $commission * 0.01 ;
//                     $level[] = array(
//                         "user_id" => $item['id'],
//                         "username" => $item['username'],
//                         "turnover" => $item['today_turnover'],
//                         "commission" => number_format((float)$todays, 2, '.', '')

//                     );
//                 }
//             }
//         }
//         $alldata[$name] = $level;
//     }
//     $mlm_level_data = DB::table('mlm_levels')->get()->toArray();
//     $level_data = count($mlm_level_data);
//     $alldatacoount = array();
//     $totalcommission = 0;
//     $totaluser = 0;
//     $datalevelcome = [];
//     for ($i = 0; $i < $level_data; $i++) {
//         $co = 0;
//         $name = $mlm_level_data[$i]->name;
//         $levelcom = 0;
//         foreach ($alldata[$name] as $obj) {
//             $totalcommission = $totalcommission + $obj['commission'];
//             $totaluser = $totaluser + 1;
//             $levelcom = $levelcom + $obj['commission'];
//             $co = $co + 1;
//         }
//         $datalevelcome[] = array(
//             'count' => $co,
//             'name' => $name,
//             'commission' => number_format($levelcom, 2, '.', ''),
//         );
//     }

   
//     return response()->json([
//         'totalcommission' => number_format($totalcommission, 2, '.', ''),
//         'bonus' => number_format($bonus, 2, '.', ''),
//         'totaluser' => "$totaluser",
//         'levelwisecommission' => $datalevelcome,
//         'user_id' => $userId,
//         'userdata' => $alldata
//     ]);
// }


////// MLM Level/////

// public function level_getuserbyrefid(Request $request)
// {
//     header("Access-Control-Allow-Origin: *");
    
//     $validator = Validator::make($request->all(), [
//         'id' => 'required|integer'
//     ]);

//     $validator->stopOnFirstFailure();

//     if ($validator->fails()) {
//         $response = [
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ];
//         return response()->json($response, 400);
//     }

//     date_default_timezone_set('Asia/Kolkata');
//     $datetime = date('Y-m-d H:i:s');

//     $userId = $request->input('id');
//     $refer_code='';
// $user_data = User::select('id','username', 'today_turnover', 'total_payin', 'no_of_payin', 'referral_user_id', 'yesterday_payin','yesterday_register','referral_code','yesterday_first_deposit','yesterday_no_of_payin')->get()->toArray();
//     $mlm_level_data = DB::table('mlm_levels')->get()->toArray();


// foreach ($user_data as $user) {
//     if ($user['id'] == $userId) {
//         $refer_code = $user['referral_code'];
//     }
// }


//     $alldata = array();
// $lastlevelname='Level 6';
//     foreach ($mlm_level_data as $mlm_level) {
//         $name = $mlm_level->name;
//         $commission = $mlm_level->commission;
//         $usermlm = array();

//         if ($name == 'Level 1') {
//             $usermlm[] = $userId;
//         } else {
//             $data = $mlm_level_data[array_search($mlm_level, $mlm_level_data) - 1]->name;
//             foreach ($alldata[$data] as $itemss) {
//                 $usermlm[] = $itemss['user_id'];
//             }
//         }

//         $user_ids = array_column($user_data, 'id');
//         $filtered_users = array_filter($user_data, function($item) use ($usermlm) {
//             return in_array($item['referral_user_id'], $usermlm);
//         });

//         $level = array();

//         foreach ($filtered_users as $item) {
//             $todays = $item['today_turnover'] * $commission * 0.01 ;
//             $level[] = array(
//                 "user_id" => $item['id'],
//                 "username" => $item['username'],
//                 "turnover" => $item['today_turnover'],
//                 "commission" => number_format((float)$todays, 2, '.', ''),
//                 'total_payin'=> $item['total_payin'],
//                 'no_of_payin'=>$item['no_of_payin'],
//                  'yesterday_payin'=>$item['yesterday_payin'],
//                 'yesterday_register'=>$item['yesterday_register'],
//                 'yesterday_no_of_payin'=>$item['yesterday_no_of_payin'],
//                 'yesterday_first_deposit'=>$item['yesterday_first_deposit']
//             );
//         }

//         $alldata[$name] = $level;
//         $lastlevelname=$name;
//     }

//     $totalcommission = 0;
//     $totaluser = 0;
//     $datalevelcome = [];
//     $indirectTeam = 0;
//     $numofpayindirect = 0;
//     $numofpayteam = 0;
//     $payinAmountDirect = 0;
//     $payinAmountTeam = 0;
//     $noUserDirect = 0;
//     $noUserTeam = 0;
//     $noOfFristPayinDirect = 0;
//     $noOfFristPayinTeam = 0;
    
    
//     $yesterday_payin_direct=0;
//     $yesterday_register_direct=0;
//     $yesterday_no_of_payin_direct=0;
//     $yesterday_first_deposit_direct=0;


//   $yesterday_payin_team=0;
//     $yesterday_register_team=0;
//     $yesterday_no_of_payin_team=0;
//     $yesterday_first_deposit_team=0;


//     foreach ($mlm_level_data as $mlm_level) {
//         $name = $mlm_level->name;
//         $levelcom = 0;



//         foreach ($alldata[$name] as $obj) {
//             $totalcommission += $obj['commission'];
//             $totaluser++;
//             $levelcom += $obj['commission'];

//             if ($name == 'Level 1') {
//                 $payinAmountDirect += $obj['total_payin'];
//                 $noUserDirect++;
//                 if ($obj['total_payin'] != '0') {
//                     $noOfFristPayinDirect++;
//                 }
//                 if ($obj['no_of_payin'] != '0') {
//                     $numofpayindirect++;
//                 }
                
//             $yesterday_payin_direct=$yesterday_payin_direct+$obj['yesterday_payin'];
//             $yesterday_register_direct=$obj['yesterday_register'];
//             $yesterday_no_of_payin_direct=$yesterday_no_of_payin_direct+$item['yesterday_no_of_payin'];
//             $yesterday_first_deposit_direct=$item['yesterday_first_deposit'];



//             } else {
//                 $payinAmountTeam += $obj['total_payin'];
//                 $noUserTeam++;
//                 $indirectTeam++;
//                 if ($obj['total_payin'] != '0') {
//                     $noOfFristPayinTeam++;
//                 }
//                 if ($obj['no_of_payin'] != '0') {
//                     $numofpayteam++;
//                 }
//                 if($name != $lastlevelname){
//             $yesterday_payin_team=$yesterday_payin_team+$obj['yesterday_payin'];
//             $yesterday_register_team=$yesterday_register_team+$obj['yesterday_register'];
//             $yesterday_no_of_payin_team=$yesterday_no_of_payin_team+$item['yesterday_no_of_payin'];
//             $yesterday_first_deposit_team=$yesterday_first_deposit_team+$item['yesterday_first_deposit'];
//                 }
//             }
//         }

//         $datalevelcome[] = array(
//             'count' => count($alldata[$name]),
//             'name' => $name,
//             'commission' => number_format($levelcom, 2, '.', ''),
//         );
//     }
    
    

    
    

//     return response()->json([
//         'direct_user_count' => $yesterday_register_direct ?? 0,
        
        
//         'numofpayindirect' => $yesterday_no_of_payin_direct ?? 0,
//         'noUserDirect' => $yesterday_register_direct ?? 0,
//         'noOfFristPayinDirect' => $noOfFristPayinDirect ?? 0,
//         'payinAmountDirect' => $yesterday_payin_direct ?? 0,
        




//         'indirect_user_count' => $yesterday_register_team ?? 0,
        
//         'numofpayteam' => $yesterday_no_of_payin_team ?? 0,
//         'payinAmountTeam' => $yesterday_payin_team ?? 0,
//         'noUserTeam' => $yesterday_register_team ?? 0,
//         'noOfFristPayinTeam' => $yesterday_first_deposit_team ?? 0,
           
  
        
//         'total_payin_direct'=> $payinAmountDirect ?? 0,
//         'total_register_direct'=>$noUserDirect ?? 0,
//         'total_no_of_payin_direct'=>$numofpayindirect ?? 0,
//         'total_first_deposit_direct'=>$noOfFristPayinDirect ?? 0,
        
//             'total_payin_team'=>$payinAmountTeam ?? 0,
//         'total_register_team'=>$noUserTeam ?? 0,
//         'total_no_of_payin_team'=>$numofpayteam ?? 0,
//         'total_first_deposit_team'=>$noOfFristPayinTeam ?? 0,      
        
//         'totaluser' => "$totaluser" ?? 0,
//       'totalcommission' => number_format($totalcommission  , 2, '.', ''),
//       'user_refer_code'=>$refer_code,
//         'levelwisecommission' => $datalevelcome ?? 0,
//         'user_id' => $userId ?? 0,
//         'userdata' => $alldata ?? 0,
  
//     ]);
// }



public function  level_getuserbyrefid(Request $request)
{

    
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    date_default_timezone_set('Asia/Kolkata');
    $datetime = date('Y-m-d H:i:s');

    $userId = $request->input('id');
    $refer_code = User::where('id', $userId)->value('referral_code');
    $user_data = User::select('id','username', 'today_turnover', 'total_payin', 'no_of_payin', 'referral_user_id', 'yesterday_payin','yesterday_register','referral_code','yesterday_first_deposit','yesterday_no_of_payin','deposit_balance','yesterday_total_commission','u_id','totalbet','first_recharge','turnover')->get()->toArray();
    $mlm_level_data = DB::table('mlm_levels')->get()->toArray();

    $alldata = [];
    $lastlevelname = 'Tier 6';
    foreach ($mlm_level_data as $mlm_level) {
        $name = $mlm_level->name;
        $commission = $mlm_level->commission;
        $usermlm = [];

        if ($name == 'Tier 1') {
            $usermlm[] = $userId;
        } else {
            $data = $mlm_level_data[array_search($mlm_level, $mlm_level_data) - 1]->name;
            foreach ($alldata[$data] as $itemss) {
                $usermlm[] = $itemss['user_id'];
            }
        }

        $filtered_users = array_filter($user_data, function($item) use ($usermlm) {
            return in_array($item['referral_user_id'], $usermlm);
        });

        $level = [];
        foreach ($filtered_users as $item) {
            $todays = $item['today_turnover'] * $commission * 0.01 ;
            $level[] = [
                "user_id" => $item['id'],
                 "u_id" => $item['u_id'],
                 'totalbet'=> $item['totalbet'],
                "username" => $item['username'],
                "first_recharge"=>$item['first_recharge'],
                 "deposit_amount" => $item['deposit_balance'],
                "turnover" => $item['turnover'],
                'today_turnover'=>$item['today_turnover'],
                "commission" => number_format((float)$todays, 2, '.', ''),
                'total_payin'=> $item['total_payin'],
                'no_of_payin'=>$item['no_of_payin'],
                'yesterday_payin'=>$item['yesterday_payin'],
                'yesterday_register'=>$item['yesterday_register'],
                'yesterday_no_of_payin'=>$item['yesterday_no_of_payin'],
                'yesterday_first_deposit'=>$item['yesterday_first_deposit']
            ];
        }

        $alldata[$name] = $level;
        $lastlevelname = $name;
    }

    $totalcommission = 0;
    $totaluser = 0;
    $datalevelcome = [];
    $indirectTeam = 0;
    $numofpayindirect = 0;
    $numofpayteam = 0;
    $payinAmountDirect = 0;
    $payinAmountTeam = 0;
    $noUserDirect = 0;
    $noUserTeam = 0;
    $noOfFristPayinDirect = 0;
    $noOfFristPayinTeam = 0;
    
    $yesterday_total_commission = 0;
    
    $yesterday_payin_direct = 0;
    $yesterday_register_direct = 0;
    $yesterday_no_of_payin_direct = 0;
    $yesterday_first_deposit_direct = 0;

    $yesterday_payin_team = 0;
    $yesterday_register_team = 0;
    $yesterday_no_of_payin_team = 0;
    $yesterday_first_deposit_team = 0;

   
        $deposit_number_all=0;
        $deposit_amount_all=0;
        $first_recharge_all=0;
        $no_of_firstrechage_all=0;
        $total_bet_all=0;
        $total_bet_amount_all=0;   
   
   

    foreach ($mlm_level_data as $mlm_level) {
        $name = $mlm_level->name;
        $levelcom = 0;
        $deposit_number=0;
        $deposit_amount=0;
        $first_recharge=0;
        $no_of_firstrechage=0;
        $total_bet=0;
        $total_bet_amount=0;

        foreach ($alldata[$name] as $obj) {
            $totalcommission += $obj['commission'];
            $deposit_number_all+=$obj['total_payin'];
        $deposit_amount_all+=$obj['no_of_payin'];
        $first_recharge_all+=$obj['first_recharge'];
        $no_of_firstrechage_all+=$no_of_firstrechage;
        $total_bet_all+=$total_bet;
        $total_bet_amount_all+=$total_bet_amount; 
        
        
        
            $totaluser++;
            $levelcom += $obj['commission'];
            if ($name == 'Tier 1') {
                $payinAmountDirect += $obj['total_payin'];
                $noUserDirect++;
                if ($obj['yesterday_payin'] != '0') {
                     $numofpayindirect++;
                    $noOfFristPayinDirect++;
                }
                if ($obj['no_of_payin'] != '0') {
                  //  $numofpayindirect++;
                }
                
                $yesterday_payin_direct += $obj['yesterday_payin'];
                $yesterday_register_direct = $obj['yesterday_register'];
               // $yesterday_no_of_payin_direct += $obj['yesterday_no_of_payin'];
                $yesterday_first_deposit_direct += $obj['yesterday_first_deposit'];

            } else {
                $payinAmountTeam += $obj['total_payin'];
                $noUserTeam++;
                $indirectTeam++;
                if ($obj['total_payin'] != '0') {
                    $noOfFristPayinTeam++;
                }
                if ($obj['no_of_payin'] != '0') {
                    $numofpayteam++;
                }
                if ($name != $lastlevelname) {
                    if($obj['first_recharge'] > 0){
                        
                   $first_recharge += $obj['first_recharge'];

                       $no_of_firstrechage++;
                    }
                    $total_bet_amount += $obj['today_turnover']+$obj['turnover'];
                    $total_bet += $obj['totalbet'];
                    
                    
                    
                    $deposit_number += $obj['no_of_payin'];
                    $deposit_amount +=$obj['total_payin'];
                    $yesterday_payin_team += $obj['yesterday_payin'];
                    $yesterday_register_team += $obj['yesterday_register'];
                    $yesterday_no_of_payin_team += $obj['yesterday_no_of_payin'];
                    $yesterday_first_deposit_team += $obj['yesterday_first_deposit'];
                }
            }
        }

        $datalevelcome[] = [
            'count' => count($alldata[$name]),
            'name' => $name,
            'commission' => number_format($levelcom, 2, '.', ''),
            'total_payin'=>$deposit_amount,
            'no_of_payin' =>$deposit_number,
            'first_recharge' =>$first_recharge,
            'no_of_people'=>$no_of_firstrechage,
            'totalbet'=>$total_bet,
            'total_bet_amount'=>$total_bet_amount
            
        ];
      
    }
  $datalevelcome[]=[
        'count' => $totaluser,
        'name' => "all",
        'commission' => number_format($totalcommission, 2, '.', ''),
        'total_payin'=>$deposit_number_all,
        'no_of_payin' =>$deposit_amount_all,
        'first_recharge' =>$first_recharge_all,
        'no_of_people'=>$no_of_firstrechage_all,
        'totalbet'=>$total_bet_all,
        'total_bet_amount'=>$total_bet_amount_all
            ];
    return response()->json([
        'direct_user_count' => $yesterday_register_direct ?? 0,
        'numofpayindirect' => $yesterday_no_of_payin_direct ?? 0,
        'noUserDirect' => $yesterday_register_direct ?? 0,
        'noOfFristPayinDirect' => $numofpayindirect ?? 0,
        'payinAmountDirect' => $yesterday_payin_direct ?? 0,
        'indirect_user_count' => $yesterday_register_team ?? 0,
        'numofpayteam' => $yesterday_no_of_payin_team ?? 0,
        'payinAmountTeam' => $yesterday_payin_team ?? 0,
        'noUserTeam' => $yesterday_register_team ?? 0,
        'noOfFristPayinTeam' => $yesterday_first_deposit_team ?? 0,
        'total_payin_direct'=> $payinAmountDirect ?? 0,
        'total_register_direct'=>$noUserDirect ?? 0,
        'total_no_of_payin_direct'=>$numofpayindirect ?? 0,
        'total_first_deposit_direct'=>$noOfFristPayinDirect ?? 0,
        'total_payin_team'=>$payinAmountTeam ?? 0,
        'total_register_team'=>$noUserTeam ?? 0,
        'total_no_of_payin_team'=>$numofpayteam ?? 0,
        'total_first_deposit_team'=>$noOfFristPayinTeam ?? 0,      
        'totaluser' => "$totaluser" ?? 0,
        'totalcommission' => number_format($totalcommission, 2, '.', ''),
        'yesterday_totalcommission' => number_format($yesterday_total_commission, 2, '.', ''),
        'user_refer_code' => $refer_code,
        'levelwisecommission' => $datalevelcome ?? 0,
        'user_id' => $userId ?? 0,
        'userdata' => $alldata ?? 0,
        ///
        // 'all_total_payin'=>$deposit_number_all,
        // 'all_no_of_payin' =>$deposit_amount_all,
        // 'all_first_recharge' =>$first_recharge_all,
        // 'all_no_of_people'=>$no_of_firstrechage_all,
        // 'all_totalbet'=>$total_bet_all,
        // 'all_total_bet_amount'=>$total_bet_amount_all
    ]);
}
  

// commission details //////

 public function commission_details(Request $request)
    {
         $validator = Validator::make($request->all(), [
        'userid' => 'required|integer',
        'subtypeid'=>'required|integer',
        'date'=>'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }
         $userid = $request->userid;
         $subtypeid= $request->subtypeid;
         $date=$request->created_at;

       $commission=DB::select("SELECT * FROM `wallet_history` WHERE `userid`=$userid && `subtypeid`=$subtypeid &&`created_at` LIKE '%$date%'");
       
      $data=[];
foreach ($commission as $item){
    
       
       $amount=$item->amount;
       $description=$item->description;
       $description2=$item->description_2;
       $created_at=$item->created_at;
       $updated_at=$item->updated_at;
    }
    
    
     $data[] = [
         'number_of_bettors'=>$description2,
         'bet_amount'=>$description,
         'commission_payout'=>$amount,
         'date'=>$created_at,    
         'settlement_date'=>$updated_at       
         ];
          

        if (!empty($data)) {
            $response = [
                'message' => 'commission_details',
                'status' => 200,
                'data' => $data,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    
     //// All Rules ////
    
    public function all_rules(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'type' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $type = $request->type;
 

      $records=DB::select("SELECT name,list FROM `rules` WHERE `type`=$type");
       
 
        if (!empty($records)) {
            $response = [
                'message' => 'rules list',
                'status' => 200,
                'data' =>$records,
            ];
            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
     public function subordinate_userlist(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'id' => 'required|numeric',
        'type' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->id;
    $type = $request->type;
 
if($type == 1){
       $list=DB::select("SELECT `u_id` AS user_name, `mobile` AS mobile, `created_at` AS datetime 
FROM `users` 
WHERE referral_user_id =$userid  
AND DATE(`created_at`) = CURDATE();
");
}elseif ($type == 2) {
    $list=DB::select("SELECT `u_id` AS user_name, `mobile` AS mobile, `created_at` AS datetime 
FROM `users` 
WHERE referral_user_id = $userid 
AND DATE(`created_at`) = DATE_SUB(CURDATE(), INTERVAL 1 DAY);
");
}else{
    $list=DB::select("SELECT `u_id` AS user_name, `mobile` AS mobile, `created_at` AS datetime 
FROM `users` 
WHERE referral_user_id = $userid 
AND `created_at` BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE();
");
}
  

        if (!empty($list)) {
            $response = [
                'message' => 'Invitation rewards rule',
                'status' => 200,
                'data' => $list,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
   
  public function commission_distribution()
{
    $datetime = now();
    $user_data = User::select('id', 'today_turnover', 'referral_user_id', 'u_id', 'first_recharge', 'turnover')
        ->where('first_recharge', '!=', 0)
        ->get()
        ->toArray();
   
    $mlm_level_data = DB::table('mlm_levels')->get()->toArray();
    
    $userIds = [];
    $bonusWalletIncrement = 0;

    $inserts = [];

    foreach ($user_data as $item) {
        $user_id = $item['id'];

        $commission = $this->commission_distribute_mlm($mlm_level_data, $user_data, $user_id);
        $no_of_bet = $commission['no_of_bet'];
        $betamount = $commission['betamount'];
        $commissions = $commission['commission'];

        // Collect user IDs for bulk update
        $userIds[] = $user_id;

        // Build insert query
        if($commissions>0){
        $inserts[] = [
            'userid' => $user_id,
            'amount' => $commissions,
            'subtypeid'=>23,
            'description' => $betamount,
            'description_2' => $no_of_bet,
            'created_at' => $datetime
        ];
        }
        // Increment bonus wallet
        $bonusWalletIncrement += $commissions;
    }
//dd($userIds);
    // Bulk update
    if (!empty($userIds)) {
        $userIdsString = implode(',', $userIds);
        
        $updateQuery = "
            UPDATE users 
            SET 
                turnover = CASE 
                    " . implode(' ', array_map(function ($userId) {
                        return "WHEN $userId THEN turnover + today_turnover";
                    }, $userIds)) . "
                    ELSE today_turnover 
                END,
                today_turnover = 0,
                bonus_wallet = bonus_wallet + $bonusWalletIncrement,
                yesterday_payin = 0,
                today_turnover=0,
                yesterday_no_of_payin = 0,
                yesterday_first_deposit = 0,
                yesterday_total_commission = 0,
                yesterday_register = 0,
                recharge = recharge + $bonusWalletIncrement 
            WHERE 
                id IN ($userIdsString)
        ";
        //dd($updateQuery);

        // Execute update query
        DB::statement($updateQuery);
    }

    // Bulk insert
    if (!empty($inserts)) {
        DB::table('wallet_history')->insert($inserts);
    }
} 
   
//   public function commission_distribution()
// {
//     $datetime = now();
//     $user_data = User::select('id', 'today_turnover', 'referral_user_id', 'u_id', 'first_recharge', 'turnover')
//         ->where('first_recharge', '!=', 0)
//         ->get()
//         ->toArray();
   
//     $mlm_level_data = DB::table('mlm_levels')->get()->toArray();
    
//     $userIds = [];
//     $bonusWalletIncrement = 0;

//     $inserts = [];

//     foreach ($user_data as $item) {
//         $user_id = $item['id'];

//         $commission = $this->commission_distribute_mlm($mlm_level_data, $user_data, $user_id);
//         $no_of_bet = $commission['no_of_bet'];
//         $betamount = $commission['betamount'];
//         $commissions = $commission['commission'];

//         // Collect user IDs for bulk update
//         $userIds[] = $user_id;

//         // Build insert query
//         if($commissions>0){
//         $inserts[] = [
//             'userid' => $user_id,
//             'amount' => $commissions,
//             'subtypeid'=>23,
//             'description' => $betamount,
//             'description_2' => $no_of_bet,
//             'created_at' => $datetime
//         ];
//         }
//         // Increment bonus wallet
//         $bonusWalletIncrement += $commissions;
//     }
// //dd($userIds);
//     // Bulk update
//     if (!empty($userIds)) {
//         $userIdsString = implode(',', $userIds);
        
//         $updateQuery = "
//             UPDATE users 
//             SET 
//                 turnover = CASE 
//                     " . implode(' ', array_map(function ($userId) {
//                         return "WHEN $userId THEN turnover + today_turnover";
//                     }, $userIds)) . "
//                     ELSE today_turnover 
//                 END,
//                 today_turnover = 0,
//                 bonus_wallet = bonus_wallet + $bonusWalletIncrement,
//                 yesterday_payin = 0,
//                 today_turnover=0,
//                 yesterday_no_of_payin = 0,
//                 yesterday_first_deposit = 0,
//                 yesterday_total_commission = 0,
//                 yesterday_register = 0,
//                 recharge = recharge + $bonusWalletIncrement 
//             WHERE 
//                 id IN ($userIdsString)
//         ";
//         //dd($updateQuery);

//         // Execute update query
//         DB::statement($updateQuery);
//     }

//     // Bulk insert
//     if (!empty($inserts)) {
//         DB::table('wallet_history')->insert($inserts);
//     }
// }    //old
   
// public function commission_distribution()
// {
//     $datetime = now();
//     $user_data = User::select('id', 'today_turnover', 'referral_user_id', 'u_id', 'first_recharge', 'turnover')
//         ->where('first_recharge', '!=', 0)
//         ->get()
//         ->toArray();
    
//     $mlm_level_data = DB::table('mlm_levels')->get()->toArray();
    
//     $updates = [];
//     $inserts = [];
//     $bindings = [];

//     foreach ($user_data as $item) {
//         $user_id = $item['id'];

//         $commission = $this->commission_distribute_mlm($mlm_level_data, $user_data, $user_id);
//         $user_id = $commission['id'];
//         $no_of_bet = $commission['no_of_bet'];
//         $betamount = $commission['betamount'];
//         $commissions = $commission['commission'];

//         // Build update query
//         $updates[] = "WHEN $user_id THEN turnover + today_turnover ELSE today_turnover END";
//         $bindings[] = $commissions;
//         $bindings[] = $user_id;
// // SELECT `id`, `userid`, `amount`, `subtypeid`, `description`, `description_2`, `created_at`, `updated_at` FROM `wallet_history` WHERE 1
//         // Build insert query
//         $inserts[] = [
//             'user_id' => $user_id,
//             'amount' => $commissions,
//             'subtypeid'=>23,
//             'description' => $betamount,
//             'description_2' => $no_of_bet,
//             'created_at' => $datetime
//         ];
//     }

//     // Bulk update
//     if (!empty($updates)) {
//         $updateValues = implode(' ', $updates);
//         $updateQuery = "UPDATE users SET turnover = CASE id $updateValues END, today_turnover = 0, bonus_wallet = bonus_wallet + ?, yesterday_payin = 0, yesterday_no_of_payin = 0, yesterday_first_deposit = 0, yesterday_total_commission = 0, recharge = recharge + ? WHERE id IN (" . implode(',', array_column($inserts, 'user_id')) . ")";

//         // Execute update query
//         DB::statement($updateQuery, $bindings);
//     }

//     // Bulk insert
//     if (!empty($inserts)) {
//         DB::table('wallet_history')->insert($inserts);
//     }
// }   old/




		
// private function commission_distribute_mlm($mlm_level_data,$user_data,$user_id)
// {
//       $all_data = [];
//     $last_level_name = 'Tier 6';  
//      $total_commission = 0;
//     $user_id = $user_id;
//     $no_of_bet=0;
//     $betamount=0;
    

//     foreach ($mlm_level_data as $mlm_level) {
//         $name = $mlm_level->name;
//         $commission = $mlm_level->commission;
//         $user_mlm = [];

//         if ($name == 'Tier 1') {
//             $user_mlm[] = $user_id;
//         } else {
//             $data = $mlm_level_data[array_search($mlm_level, $mlm_level_data) - 1]->name;
//             foreach ($all_data[$data] as $item) {
//                 $user_mlm[] = $item['user_id'];
//             }
//         }

//         // Filter users based on MLM structure
//         $filtered_users = array_filter($user_data, function ($item) use ($user_mlm) {
//             return in_array($item['referral_user_id'], $user_mlm);
//         });

//         // Calculate commission for each user at this level
//         $level = [];
//         foreach ($filtered_users as $item) {
//             if($item['today_turnover']){
//                 $no_of_bet++;
//                 $betamount+=$item['today_turnover'];
//             }
//             $todays = $item['today_turnover'] * $commission * 0.01;
          
//             $level[] = [
//                 "user_id" => $item['id'],
//                 "turnover" => $item['turnover'],
//                 'today_turnover' => $item['today_turnover'],
//                 "commission" => number_format((float)$todays, 2, '.', ''),
//             ];
//         }

//         // Store commission data for this level
//         $all_data[$name] = $level;
//         $last_level_name = $name;
//     }

//     foreach ($mlm_level_data as $mlm_level) {
//         $name = $mlm_level->name;
//         foreach ($all_data[$name] as $obj) {
//             $total_commission += $obj['commission'];
          
//         }
//     }
//       $user_id = $user_id;
//     $no_of_bet=0;
//     $betamount=0;
//     $finaldatas=array(
//         'id'=>$user_id,
//         'no_of_bet'=>$no_of_bet,
//       'betamount'=> $betamount,
//       'commission'=>$total_commission
//         );
//     return $finaldatas;
// }  //old

private function commission_distribute_mlm($mlm_level_data,$user_data,$user_id)
{
      $all_data = [];
    $last_level_name = 'Tier 6';  
     $total_commission = 0;
    $user_id = $user_id;
    $no_of_bet=0;
    $betamount=0;
    

    foreach ($mlm_level_data as $mlm_level) {
        $name = $mlm_level->name;
        $commission = $mlm_level->commission;
        $user_mlm = [];

        if ($name == 'Tier 1') {
            $user_mlm[] = $user_id;
        } else {
            $data = $mlm_level_data[array_search($mlm_level, $mlm_level_data) - 1]->name;
            foreach ($all_data[$data] as $item) {
                $user_mlm[] = $item['user_id'];
            }
        }

        // Filter users based on MLM structure
        $filtered_users = array_filter($user_data, function ($item) use ($user_mlm) {
            return in_array($item['referral_user_id'], $user_mlm);
        });

        // Calculate commission for each user at this level
        $level = [];
        foreach ($filtered_users as $item) {
            if($item['today_turnover']){
                $no_of_bet++;
                $betamount+=$item['today_turnover'];
            }
            $todays = $item['today_turnover'] * $commission * 0.01;
          
            $level[] = [
                "user_id" => $item['id'],
                "turnover" => $item['turnover'],
                'today_turnover' => $item['today_turnover'],
                "commission" => number_format((float)$todays, 2, '.', ''),
            ];
        }

        // Store commission data for this level
        $all_data[$name] = $level;
        $last_level_name = $name;
    }

    foreach ($mlm_level_data as $mlm_level) {
        $name = $mlm_level->name;
        foreach ($all_data[$name] as $obj) {
            $total_commission += $obj['commission'];
          
        }
    }
      $user_id = $user_id;
    $no_of_bet=0;
    $betamount=0;
    $finaldatas=array(
        'id'=>$user_id,
        'no_of_bet'=>$no_of_bet,
       'betamount'=> $betamount,
       'commission'=>$total_commission
        );
    return $finaldatas;
}



public function betting_rebate(){
    
    $currentDate = date('Y-m-d');
		 
		 $a = DB::select("SELECT sum(amount) as betamount, userid FROM bets WHERE created_at like '$currentDate %' AND status= '2' GROUP BY userid;");

		
		//$a = DB::select("SELECT `today_turnover` FROM `users` WHERE `id`=$userid ");
		
		foreach($a as $item){
		
		   $betamount = $item->betamount;
		   $userid = $item->userid;
			
			DB::select("UPDATE users SET wallet = wallet + $betamount * 0.01 WHERE id = $userid");
		$rebate_rate=0.01;
		  $insert= DB::table('wallet_history')->insert([
        'userid' => $userid,
        'amount' => $betamount*$rebate_rate,
        'description'=>$betamount,
        'description_2'=>$rebate_rate,
        'subtypeid' => 25,
		'created_at'=> now(),
        'updated_at' => now()
		
        ]);
		
	   }
		
	}		
	
	
 public function betting_rebate_history(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric',
        'subtypeid' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->userid;
    $subtypeid = $request->subtypeid;
    
    $value=DB::select("SELECT 
    COALESCE(SUM(amount), 0) as total_rebet,
    COALESCE(SUM(description), 0) as total_amount,
    COALESCE(SUM(CASE WHEN DATE(CURDATE()) = CURDATE() THEN amount ELSE 0 END), 0) as today_rebet 
FROM 
    wallet_history 
WHERE 
    userid = $userid && subtypeid =$subtypeid");
    
    $records=DB::select("SELECT 
    `amount` as rebate_amount,description_2 as rebate_rate,created_at as datetime,
    COALESCE((SELECT SUM(description) FROM wallet_history WHERE `userid` = $userid AND subtypeid = $subtypeid), 0) as betting_rebate 
FROM 
    `wallet_history` 
WHERE 
    `userid` = $userid AND subtypeid = $subtypeid;");


       
 
        if (!empty($records)) {
            $response = [
                'message' => 'Betting Rebet List',
                'status' => 200,
                'data1' =>$records,
                'data' =>$value,
            ];
            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
 

    }	
	
	
	 public function versionApkLink(Request $request)
    {
        
            $data = DB::SELECT("SELECT * FROM `versions` WHERE `id`=1"); // Assuming you have a Version model with 'id' field

            if ($data) {
                
                $response = [
                 'msg' => 'Successfully',
                    'status' => 200,
                    'version' => $data[0]->version,
                    'link' => $data[0]->link
            ];
            return response()->json($response,200);
                
            } else {
                // If no data is found, set an appropriate response
                return response()->json([
                    'msg' => 'No record found',
                    'status' => 400
                ], 400);
            }
        
    }
	
public function sendSMS(Request $request)
{
    $validator = Validator::make($request->all(), [
        'mobile' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }
    $mobile = $request->mobile;

    $apikey = 'Vml2ZWtvdHA6OFljNEhDeHo=';
    $type = 'TEXT';
    $sender = 'NSLSMS';
    $entityId = '1201164562188563646';
    $templateId = '1207170323851947619';

    $otp = rand(1000, 9999);

    $message = "Dear user, the OTP for $otp for Money Makers NSL LIFE";

    $existingOTP = DB::table('otp_sms')->where('mobile', $mobile)->first();

    if ($existingOTP) {
       
        DB::table('otp_sms')
            ->where('mobile', $mobile)
            ->update([
                'otp' => $otp,
                'status' => 1, 
                'datetime' => now(),
            ]);
    } else {
        // Insert a new record into otp_sms table
        DB::table('otp_sms')->insert([
            'mobile' => $mobile,
            'otp' => $otp,
            'status' => 1, // Assuming 1 for successful status
            'datetime' => now(),
        ]);
    }

    // Make the API call
    $response = Http::get('http://login.swarajinfotech.com/domestic/sendsms/bulksms_v2.php', [
        'apikey' => $apikey,
        'type' => $type,
        'sender' => $sender,
        'entityId' => $entityId,
        'templateId' => $templateId,
        'mobile' => $mobile,
        'message' => $message,
    ]);

    // Validate the response
    if ($response->successful()) {
        return response()->json(['status' => 200,'message' => 'OTP sent successfully'], 200);
    } else {
        return response()->json(['status' => 400,'message' => 'Failed to send OTP'], 400);
    }
}

public function verifyOTP(Request $request)
{
    $validator = Validator::make($request->all(), [
        'mobile' => 'required',
        'otp' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $mobile = $request->mobile;
    $otp = $request->otp;

    $existingOTP = DB::table('otp_sms')->where('mobile', $mobile)->first();

    if ($existingOTP) {
        
        if ($existingOTP->otp == $otp) {
         
          

            return response()->json(['status' => 200, 'message' => 'OTP verified successfully'], 200);
        } else {
            return response()->json(['status' => 400, 'message' => 'Invalid OTP'], 400);
        }
    } else {
        return response()->json(['status' => 400, 'message' => 'No OTP found for the provided mobile number'], 400);
    }
}
	
public function updatePassword(Request $request)
{
    $validator = Validator::make($request->all(), [
     
        'mobile' => 'required',
        'password' => 'required',
        'confirm_password' => 'required|same:password',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $user = DB::table('users')
                ->where('mobile', $request->mobile)
                ->first();

    if (!$user) {
        return response()->json(['status' => 400,'message' => 'User not found'], 400);
    }

    $updated = DB::table('users')
                ->where('mobile', $request->mobile)
                ->update([
                    'password' => $request->password 
                ]);

        return response()->json(['status' => 200,'message' => 'Password updated successfully'], 200);
   
}

	
public function admin_salary(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'userid' => 'required|integer|exists:users,id', // Check if userid exists in users table
    ]);

    // Stop validation on first failure
    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    try {
        // Check if user exists
        $userExists = DB::table('users')->where('id', $request->userid)->exists();

        if (!$userExists) {
            return response()->json(['status' => 400, 'message' => 'User does not exist'], 400);
        }

        // Fetch wallet history
        $walletHistory = DB::table('wallet_history')
                           ->where('userid', $request->userid)
                           ->where('subtypeid', 13)
                           ->get();

        if ($walletHistory->isEmpty()) {
            return response()->json(['status' => 400, 'message' => 'No data found'], 400);
        }

        return response()->json(['status' => 200, 'message' => 'Successfully retrieved data', 'data' => $walletHistory], 200);

    } catch (\Exception $e) {
        // Handle any errors that may occur
        return response()->json(['status' => 500,  'message' => $e->getMessage()], 500);
    }
}

	
	
public function usersRegisterDeleted()
{
    try {
        // Delete user records where email and password are NULL and referral_user_id is 1
        DB::table('users')
            ->whereNull('email')
            ->whereNull('password')
            ->where('referral_user_id', '1')
            ->delete();

        // Return a success response
        return response()->json(['status' => 200, 'message' => 'Users deleted successfully'], 200);

    } catch (\Exception $e) {
        // Handle any errors that may occur
        return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
    }
}



	
	
	
		
}