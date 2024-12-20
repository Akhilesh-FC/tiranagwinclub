<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
// use App\Models\Project_maintenance;

class WidthdrawlController extends Controller
{
    public function widthdrawl_index($id)
    {
		
        // Fetch all records from the Project_maintenance model
        // $widthdrawls = DB::select("SELECT withdraw_histories.*, users.username AS uname, users.mobile AS mobile, account_details.account_number AS acno, account_details.bank_name AS bname, account_details.ifsc_code AS ifsc FROM withdraw_histories LEFT JOIN users ON withdraw_histories.user_id = users.id LEFT JOIN account_details ON account_details.id = withdraw_histories.account_id WHERE withdraw_histories.`status`=$id order by withdraw_histories.id desc ;");
        
        $widthdrawls = DB::select("
    SELECT 
        withdraw_histories.*, 
        users.username AS uname, 
        users.mobile AS mobile, 
        account_details.account_number AS acno, 
        account_details.bank_name AS bname, 
        account_details.ifsc_code AS ifsc,
        account_details.user_id AS account_user_id,
        account_details.name AS name
    FROM 
        withdraw_histories 
    LEFT JOIN 
        users ON withdraw_histories.user_id = users.id 
    LEFT JOIN 
        account_details ON account_details.id = withdraw_histories.account_id 
    WHERE 
       withdraw_histories.status = ? AND 
            withdraw_histories.type = ? 
        ORDER BY 
            withdraw_histories.id DESC
    ", [$id, '2']); // Replace 'desired_type' with either 'usdt' or 'inr' as needed

    // Pass the data to the view and load the 'widthdrawl.index' Blade file
    return view('widthdrawl.index', compact('widthdrawls'))->with('id', $id);
    }


//     public function success(Request $request,$id)
//     {
// 		$value = $request->session()->has('id');
		
//      if(!empty($value))
//         {
        
//          $data=DB::select("SELECT account_details.*, users.email AS email, users.mobile AS mobile, withdraw_histories.amount AS amount, admin_settings.longtext AS mid, (SELECT admin_settings.longtext FROM admin_settings WHERE id = 13) AS token, (SELECT admin_settings.longtext FROM admin_settings WHERE id = 14 ) AS orderid FROM account_details LEFT JOIN users ON account_details.user_id = users.id LEFT JOIN withdraw_histories ON withdraw_histories.user_id = users.id && withdraw_histories.account_id=account_details.id LEFT JOIN admin_settings ON admin_settings.id = 12 WHERE withdraw_histories.id=$id;");
//       //dd($data);
//          foreach ($data as $object) {
            
//             // $object->amount
//             $name= $object->name;
//             $ac_no= $object->account_number;
//             $ifsc=$object->ifsc_code;
//             $bankname= $object->bank_name;
//             $email= $object->email;
//             $mobile=$object->mobile;
//             $amount=$object->amount;
//             $mid=$object->mid;
//             $token=$object->token;
//             $orderid=$object->orderid;
//         }
// 		//echo $mid;
//         $rand=rand(11111111,99999999);
//       $randid="$rand";
//       //$amount
//       $payoutdata=  json_encode(array(    
//          "merchant_id"=>$mid,
//          "merchant_token"=>$token,
//          "account_no"=>$ac_no,
//          "ifsccode"=>$ifsc,
//          "amount"=>$amount,
//          "bankname"=>$bankname,
//          "remark"=>"payout",
//          "orderid"=>$randid,
//          "name"=>$name,
//          "contact"=>$mobile,
//          "email"=>$email
//       ));
//       //dd($payoutdata);
//     // Encode the payout data using base64
//     $salt = base64_encode($payoutdata);
    
//     // Prepare the JSON data
//     $json = [
//         "salt" => $salt
//     ];
    
//     // Initialize cURL session
//     $curl = curl_init();
    
//     // Set cURL options
//     curl_setopt_array($curl, array(
//         CURLOPT_URL => 'https://indianpay.co.in/admin/single_transaction',
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_ENCODING => '',
//         CURLOPT_MAXREDIRS => 10,
//         CURLOPT_TIMEOUT => 0,
//         CURLOPT_FOLLOWLOCATION => true,
//         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//         CURLOPT_CUSTOMREQUEST => 'POST',
//         CURLOPT_POSTFIELDS => json_encode($json), // Encode JSON data
//         CURLOPT_HTTPHEADER => array(
//             'Content-Type: application/json' // Set Content-Type header
//         ),
//     ));
    
//     // Execute cURL request and get the response
//     $response = curl_exec($curl);
//     //dd($response);
//     // Check for errors
//     if (curl_errno($curl)) {
//         echo 'Error: ' . curl_error($curl);
//     } else {
//         // Print the response
//         echo $response;
//     }
    
//     // Close cURL session
//     curl_close($curl);

    

		 
		
//     DB::select("UPDATE `withdraw_histories` SET `status`='2',`response`='$response' WHERE id=$id;");
// 		 return redirect()->route('widthdrawl', '1')->with('key', 'value');

//     }
// 		else
//         {
//           return redirect()->route('login');  
//         }
			
			
//     }




    public function success(Request $request,$id)
    {
		$value = $request->session()->has('id');
		
     if(!empty($value))
        {
        
    DB::select("UPDATE `withdraw_histories` SET `status`='2' WHERE id=$id");
		 return redirect()->route('widthdrawl', '1')->with('key', 'value');
    }
		else
        {
           return redirect()->route('login'); 
        }
    }
		
		
		
    public function reject(Request $request,$id)
  {
		
		
  $rejectionReason = $request->input('msg');
		
		$data=DB::select("SELECT * FROM `withdraw_histories` WHERE id=$id;");
	
		$amt=$data[0]->amount;
		$useid=$data[0]->user_id;
         $value = $request->session()->has('id');
			
     if(!empty($value))
        {
            // dd("UPDATE `withdraw_histories` SET `status`='3' WHERE id=$id;");
     $ss= DB::select("UPDATE `withdraw_histories` SET `status`='3',`rejectmsg`='$rejectionReason' WHERE id=$id;");
    //dd("UPDATE `users` SET `wallet`=`wallet`+'$amt' WHERE id=$useid;");
	DB::select("UPDATE `users` SET `wallet`=`wallet`+'$amt' WHERE id=$useid;");
	//DB::select("UPDATE `users` SET `wallet`=`wallet`+'$amt',`winning_wallet`=`winning_wallet`+'$amt' WHERE id=$useid;");
		         //return view('widthdrawl.index', compact('widthdrawls'))->with($id,'0');
return redirect()->route('widthdrawl', '1')->with('key', 'value');
		  }
		 else
        {
          return redirect()->route('login');  
        }
			

      // return redirect()->route('widthdrawl/0');
  }
    
    public function all_success()    
    {           
		$value = $request->session()->has('id');
		
     if(!empty($value))
        {
      DB::select("UPDATE `withdraw_histories` SET `status`='2' WHERE `status`='1';");
		         return view('widthdrawl.index', compact('widthdrawls'))->with($id,'1');
	 }
else
        {
           return redirect()->route('login');  
        }
			
      //return redirect()->route('widthdrawl/0');
    }
	
	public function indiaonlin_payout(Request $request,$id)
    {
		$value = $request->session()->has('id');
		
     if(!empty($value))
        {
        
         $data=DB::select("SELECT account_details.*, users.email AS email, users.mobile AS mobile, withdraw_histories.amount AS amount, admin_settings.longtext AS mid, (SELECT admin_settings.longtext FROM admin_settings WHERE id = 13) AS token, (SELECT admin_settings.longtext FROM admin_settings WHERE id = 14 ) AS orderid FROM account_details LEFT JOIN users ON account_details.user_id = users.id LEFT JOIN withdraw_histories ON withdraw_histories.user_id = users.id && withdraw_histories.account_id=account_details.id LEFT JOIN admin_settings ON admin_settings.id = 12 WHERE withdraw_histories.id=$id;");
       
         foreach ($data as $object) {
            
            $name= $object->name;
            $ac_no= $object->account_number;
            $ifsc=$object->ifsc_code;
            $bankname= $object->bank_name;
            $email= $object->email;
            $mobile=$object->mobile;
            $amount=$object->amount;
           
            $token=$object->token;
            $orderid=$object->orderid;
        }
$rand = rand(11111111, 99999999);
$date = date('YmdHis');
$invoiceNumber = $date . $rand;
		 
		$data = [
    "merchantId" => 204,
    "secretKey" => "1a89da05-0607-4f7b-b3fe-6311ce14cb1c",
    "apiKey" => "5692d831-decd-450c-8ff5-d1d11943dc82",
    "invoiceNumber" => $invoiceNumber,
    "customerName" => $name,
    "phoneNumber" => $mobile,
    "payoutMode" => "IMPS",
    "payoutAmount" => 1,
    "accountNo" => $ac_no,
    "ifscBankCode" => $ifsc,
    "ipAddress" => "35.154.155.190"
];

		 
         $encodeddata=json_encode($data);
		
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://indiaonlinepay.com/api/iop/payout',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS =>$encodeddata,
			  CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Cookie: Path=/'
			  ),
			));

			$response = curl_exec($curl);

			curl_close($curl);
		 
			echo  $response; 
		 $dataArray = json_decode($response, true);

         $referenceId=$dataArray['Data']['ReferenceId'];
		 $Status=$dataArray['Data']['Status'];
		 if($Status == "Received"){
		 
   
         DB::select("UPDATE `withdraw_histories` SET `referenceId`='$referenceId',`response`='$response',status='2' WHERE id=$id;");
		 return redirect()->route('widthdrawl', '1')->with('key', 'value');
		 }
       return redirect()->route('widthdrawl', '1')->with('key', 'value');
    }
		else
        {
           return redirect()->route('login');  
        }
			
			
    }
	
	
	
	



}
