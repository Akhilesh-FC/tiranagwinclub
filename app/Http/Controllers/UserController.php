<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use DB;
use Illuminate\Support\Str;
use App\Models\All_image;

class UserController extends Controller
{
    public function user_create(Request $request)
    {
		$u_id = $request->u_id;
		$mobile = $request->mobile;

		$perPage = 10;
		
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {

			// $users = DB::select("SELECT e.*, m.username AS sname FROM users e LEFT JOIN users m ON e.referral_user_id = m.id; ");
			
			$query = DB::table('users')
				->leftJoin('users as m', 'users.referral_user_id', '=', 'm.id')
				->select('users.*', 'm.username as sname');
		
			// Apply filters if provided
			if (!empty($u_id)) {
				$query->where('users.u_id', 'LIKE', '%' . $u_id . '%');
			}
			if (!empty($mobile)) {
				$query->where('users.mobile', 'LIKE', '%' . $mobile . '%');
			}

			// Execute the query and paginate results
			$users = $query->paginate($perPage);
        
        return view ('user.index', compact('users'));
        }
        else
        {
           return redirect()->route('login');  
        }
        
    }
	
    public function user_details(Request $request,$id)
    {
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {
        $users = DB::select("SELECT * FROM `bets` WHERE `userid`='$id' ");
        $withdrawal = DB::select("SELECT * FROM `withdraw_histories` WHERE `user_id`='$id' ");
        $dipositess = DB::select("SELECT * FROM `payins` WHERE `user_id`='$id' ");
       return view ('user.user_detail',compact('dipositess','users','withdrawal')); 
			  }
        else
        {
           return redirect()->route('login');  
        }
    }

    public function user_active(Request $request,$id)
    {
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {
    //   Order::where("id",$id)->update(['status'=>0]);
    DB::update("UPDATE `users` SET `status`='1' WHERE id=$id;");
        
        return redirect()->route('users');
			  }
        else
        {
           return redirect()->route('login');  
        }
    }
	
    public function user_inactive(Request $request,$id)
    {
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {
    //   Order::where("id",$id)->update(['status'=>1]);
      DB::update("UPDATE `users` SET `status`='0' WHERE id=$id;");
        return redirect()->route('users');
			  }
        else
        {
           return redirect()->route('login');  
        }
  }

	public function password_update(Request $request, $id)
    {
		 $value = $request->session()->has('id');
	
        if(!empty($value))
        {
        $password=$request->password;
               $data= DB::update("UPDATE `users` SET `password`='$password' WHERE id=$id");
         
             return redirect()->route('users');
			  }
        else
        {
           return redirect()->route('login');  
        }
          
      }
	
	public function wallet_store(Request $request ,$id)
    {
		date_default_timezone_set('Asia/Kolkata');
		$date=date('Y-m-d H:i:s');
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {
      $wallet=$request->wallet;
     //dd($wallet);
         $data = DB::update("UPDATE `users` SET `wallet` = `wallet` + $wallet,`deposit_balance`=`deposit_balance`+$wallet,`total_payin`=`total_payin`+$wallet, `recharge`=`recharge`+$wallet  WHERE id = $id;");
			$insert=DB::insert("INSERT INTO `payins`(`user_id`, `cash`, `order_id`, `type`, `status`,`created_at`) VALUES ('$id','$wallet','via Admin','2','2','$date')");
		
             return redirect()->route('users');
			  }
        else
        {
           return redirect()->route('login');  
        }
      }
	
	public function wallet_subtract(Request $request, $id)
    {
		//dd($request);
    date_default_timezone_set('Asia/Kolkata');
    $date = date('Y-m-d H:i:s');
    $value = $request->session()->has('id');

    if (!empty($value)) {
        $ammount = $request->wallet;
        
        // Check if the wallet amount is sufficient
        $user = DB::table('users')->where('id', $id)->first();
        if ($user->wallet < $ammount) {
            return redirect()->back()->with('error', 'Insufficient wallet balance.');
        }
        
        $data = DB::update("UPDATE `users` SET `wallet` = `wallet`-$ammount WHERE id = $id");


        
        
        return redirect()->route('users')->with('success', 'Amount subtracted successfully!');
    } else {
        return redirect()->route('login');
    }
}

	public function password_store(Request $request ,$id)
    {
		date_default_timezone_set('Asia/Kolkata');
		$date=date('Y-m-d H:i:s');
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {
      $password=$request->password;
			
			$sponser_mobile =$request->sponser_mobile;
			
     //dd($wallet);
         $data = DB::update("UPDATE `users` SET `password` = $password  WHERE id = $id;");
			
			if($sponser_mobile){

    $sponser_data = DB::table('users')->where('mobile', $sponser_mobile)->first();
    
    if ($sponser_data) {
     
        $sponser_id = $sponser_data->id;
  
        DB::table('users')->where('id', $id)->update(['referral_user_id' => $sponser_id]);
    }
}

			
             return redirect()->route('users');
			  }
        else
        {
           return redirect()->route('login');  
        }
      }
	
	public function user_mlm(Request $request,$id)
    {
			
$value = $request->session()->has('id');
	
        if(!empty($value))
        {

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://mahajong.club/admin/index.php/Mahajongapi/level_getuserbyrefid?id=$id",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Cookie: ci_session=itqv6s6aqactjb49n7ui88vf7o00ccrf'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$data= json_decode($response);

			
      
		
             return view ('user.mlm_user_view')->with('data', $data);
			
			  }
        else
        {
           return redirect()->route('login');  
        }
      }
      
    public function registerwithref($id){
         
        $ref_id = User::where('referral_code',$id)->first();
       
        return view('user.newregister')->with('ref_id',$ref_id);
         
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
				
 
      
    public function register_store(Request $request,$referral_code)
    {
       
        $validatedData = $request->validate([
                'mobile' => 'required',
                'password' => 'required|string|min:6|confirmed', 
                'password_confirmation' =>'required|string|min:6', 
                'email' => 'required | unique:users,email',
                
    			//'otp' => 'required',
            ]);
              //dd($ref_id);
    
           $refer = DB::table('users')->where('referral_code', $referral_code)->first();
           
    	 	if ($refer !== null) {
    			$referral_user_id = $refer->id;
    			//dd($referral_user_id);
    
    		$username = Str::random(6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
	    $u_id = $this->generateRandomUID();
	     
	     $referral_code = Str::upper(Str::random(6, 'alpha'));
	     
	      $rrand = rand(1,20);
          $all_image = All_image::find($rrand);
          
    $image = $all_image->image;	
   $userId = DB::table('users')->insertGetId([
        'mobile' => $request->mobile,
        'email' => $request->email,
        'username' => $username,
        'password' =>$request->password,
        'referral_user_id' =>$referral_user_id,
        'referral_code' => $referral_code,
		'u_id' => $u_id,
		'status' => 1,
		'userimage' => $image,
    ]);
        
    //dd($userdata);
    	if($userId){
    		//	dd("hiii");
         DB::select("UPDATE `users` SET `yesterday_register`=yesterday_register+1 WHERE `id`=$referral_user_id");
    	
         return redirect(str_replace('https://admin.', 'http://', "https://tirangawin.club"));
    
    	}else{
    		
    		 return redirect()->back()->with('error', 'Mobile or Otp not match, Contact to admin..!');
    		
    	    }
        }
    }
      
}
      
      
	
      

     
