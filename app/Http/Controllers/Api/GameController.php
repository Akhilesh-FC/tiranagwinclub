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



class GameController extends Controller
{
    
    //// Bet ////
    
public function dragon_bet(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
    
        'game_id' => 'required',
      
        'json'=>'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
    }
    
    $datetime=date('Y-m-d H:i:s');
    
     $testData = $request->json;
    $userid = $request->userid;
    $gameid = $request->game_id;
  // $gameno = $request->game_no;
 
  $orderid = date('YmdHis') . rand(11111, 99999);
    
    $gamesrno=DB::select("SELECT gamesno FROM `bet_logs` WHERE `game_id`=$gameid  LIMIT 1");
    $gamesno=$gamesrno[0]->gamesno;
 
   
    
    foreach ($testData as $item) {
        $user_wallet = DB::table('users')->select('wallet')->where('id', $userid)->first();
            $userwallet = $user_wallet->wallet;
   
        $number = $item['number'];
        $amount = $item['amount'];
        if($userwallet > $amount){
      if ($amount>=1) {
        DB::insert("INSERT INTO `bets`(`amount`,`trade_amount`, `number`, `gamesno`, `game_id`, `userid`, `status`,`order_id`,`created_at`,`updated_at`) 
            VALUES ('$amount','$amount', '$number', '$gamesno', '$gameid', '$userid', '0','$orderid','$datetime','$datetime')");

 $data1 = DB::select("SELECT * FROM virtual_games WHERE virtual_games.number=$number");
             foreach($data1 as $row){
             $multiplier = $row->multiplier;
             $num=$row->actual_number;
            $multiply_amt = $amount * $multiplier;
            
            
          $bet_amt= DB::update("UPDATE `bet_logs` SET `amount`=amount+'$multiply_amt' where game_id= $gameid && number=$num");
             }
            DB::table('users')->where('id', $userid)->update(['wallet' => DB::raw('wallet - ' . $amount)]);
		   DB::table('users')->where('id', $userid)->where('recharge', '>', 0)->decrement('recharge', $amount);
        //DB::table('users')->where('id', $userid)->update([
           // 'recharge' => DB::raw('CASE 
                                   // WHEN recharge >= ' . $amount . ' THEN recharge - ' . $amount . '
                                  //  ELSE 0
                                  //  END')
        //]);
      
        
      }
       
      }
      
      else {
                $response['msg'] = "Insufficient balance";
                $response['status'] = "400";
                return response()->json($response);
            }

    }

     return response()->json([
        'status' => 200,
        'message' => 'Bet Successfully',
    ]);   
    
}




// public function bet(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'userid' => 'required',
//         'game_id' => 'required',
//         'number' => 'required',
//         'amount' => 'required|numeric|min:1',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
//     }

//     $userid = $request->userid;
//     $gameid = $request->game_id;
//     $number = $request->number;
//     $amount = $request->amount;
//     date_default_timezone_set('Asia/Kolkata');
//     $datetime = date('Y-m-d H:i:s');
//     $orderid = date('YmdHis') . rand(11111, 99999);

//     $gamesno = DB::table('bet_logs')->where('game_id', $gameid)->value('gamesno');

//     $userWallet = DB::table('users')->where('id', $userid)->value('wallet');

//     if ($userWallet < $amount) {
//         return response()->json(['status' => 400, 'message' => 'Insufficient balance']);
//     }

//     $commission = $amount * 2.00 * 0.01; // Calculate commission
//     $betAmount = $amount - $commission; // Bet amount after commission deduction

//     $data1 = DB::table('virtual_games')
//         ->where('number', $number)
//         ->where('game_id', $gameid)
//         ->get(['multiplier', 'actual_number']);

//     $totalAmount = 0;
//     foreach ($data1 as $row) {
//         $totalAmount += $betAmount * $row->multiplier;
//     }

//     DB::beginTransaction();

//     try {
//         DB::table('bets')->insert([
//             'amount' => $amount,
//             'trade_amount' => $betAmount,
//             'commission' => $commission,
//             'number' => $number,
//             'gamesno' => $gamesno,
//             'game_id' => $gameid,
//             'userid' => $userid,
//             'order_id' => $orderid,
//             'created_at' => $datetime,
//             'updated_at' => $datetime,
//             'status' => 0
//         ]);

//         foreach ($data1 as $row) {
//             DB::table('bet_logs')
//                 ->where('game_id', $gameid)
//                 ->where('number', $row->actual_number)
//                 ->increment('amount', $betAmount * $row->multiplier);
//         }

//         DB::table('users')->where('id', $userid)->decrement('wallet', $amount);
        
//         DB::table('users')->where('id', $userid)->where('recharge', '>', 0)->decrement('recharge', $amount);
//     //   $rec= DB::table('users')->where('id', $userid)->where('recharge', '>', 0)->decrement('recharge', $amount);
//       //dd();
//         // DB::table('users')->where('id', $userid)->update([
//         //     'recharge' => DB::raw('CASE 
//         //                             WHEN recharge >= ' . $amount . ' THEN recharge - ' . $amount . '
//         //                             ELSE 0
//         //                             END')
//         // ]);

//         DB::table('users')->where('id', $userid)->increment('today_turnover', $amount);

//         DB::commit();

//         return response()->json(['status' => 200, 'message' => 'Bet Successfully']);
//     } catch (\Exception $e) {
//         DB::rollback();
//         // Log the exception for debugging
//         \Log::error('Bet error: ' . $e->getMessage());
//         return response()->json(['status' => 500, 'message' => 'Something went wrong: ' . $e->getMessage()]);
//     }
// }

public function bet(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        'game_id' => 'required',
        'number' => 'required',
        'amount' => 'required|numeric|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }

    $userid = $request->userid;
    $gameid = $request->game_id;
    $number = $request->number;
    $amount = $request->amount;
    date_default_timezone_set('Asia/Kolkata');
    $datetime = date('Y-m-d H:i:s');
    $orderid = date('YmdHis') . rand(11111, 99999);

    $gamesno = DB::table('bet_logs')->where('game_id', $gameid)->value('gamesno');
    $userWallet = DB::table('users')->where('id', $userid)->value('wallet');

    if ($userWallet < $amount) {
        return response()->json(['status' => 400, 'message' => 'Insufficient balance']);
    }

    $commission = $amount * 2.00 * 0.01; // Calculate commission
    $betAmount = $amount - $commission; // Bet amount after commission deduction

    $data1 = DB::table('virtual_games')
        ->where('number', $number)
        ->where('game_id', $gameid)
        ->get(['multiplier', 'actual_number']);

    $totalAmount = 0;
    foreach ($data1 as $row) {
        $totalAmount += $betAmount * $row->multiplier;
    }

    DB::beginTransaction();

    try {
        DB::table('bets')->insert([
            'amount' => $amount,
            'trade_amount' => $betAmount,
            'commission' => $commission,
            'number' => $number,
            'gamesno' => $gamesno,
            'game_id' => $gameid,
            'userid' => $userid,
            'order_id' => $orderid,
            'created_at' => $datetime,
            'updated_at' => $datetime,
            'status' => 0
        ]);

        foreach ($data1 as $row) {
            DB::table('bet_logs')
                ->where('game_id', $gameid)
                ->where('number', $row->actual_number)
                ->increment('amount', $betAmount * $row->multiplier);
        }

        // Decrement user's wallet
        DB::table('users')->where('id', $userid)->decrement('wallet', $amount);

        // Manage recharge: Only decrement if current recharge is greater than 0
        $userRecharge = DB::table('users')->where('id', $userid)->value('recharge');

        if ($userRecharge > 0) {
            // Calculate new recharge amount
            $newRecharge = $userRecharge - $amount;

            // Ensure recharge doesn't go below zero
            if ($newRecharge < 0) {
                $newRecharge = 0;
            }

            // Update recharge
            DB::table('users')->where('id', $userid)->update(['recharge' => $newRecharge]);
        }

        // Increment today's turnover
        DB::table('users')->where('id', $userid)->increment('today_turnover', $amount);

        DB::commit();

        return response()->json(['status' => 200, 'message' => 'Bet Successfully']);
    } catch (\Exception $e) {
        DB::rollback();
        // Log the exception for debugging
        \Log::error('Bet error: ' . $e->getMessage());
        return response()->json(['status' => 500, 'message' => 'Something went wrong: ' . $e->getMessage()]);
    }
}

public function win_amount(Request $request)
	{
	    
	    	$validator = Validator::make($request->all(), [ 
				'userid' => 'required',
		       'game_id' => 'required',
		       'gamesno'=>'required'
		
			]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }
	
	
    $game_id = $request->game_id;
    $userid = $request->userid;
	$game_no = $request->gamesno;
 
	   
	     $win_amount = DB::Select("SELECT 
    SUM(`win_amount`) AS total_amount,
    `gamesno`,
    `game_id` AS gameid,
    `win_number` AS number,
    CASE WHEN SUM(`win_amount`) = 0 THEN 'lose' ELSE 'win' END AS result 
FROM 
    `bets` 
WHERE 
    `gamesno` =  $game_no
    AND `game_id` = $game_id 
    AND `userid` = $userid 
GROUP BY 
    `gamesno`,
    `game_id`,
    `win_number`
");

 

 if ($win_amount) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'win' => $win_amount[0]->total_amount,
                'gamesno' => $win_amount[0]->gamesno,
                'result' => $win_amount[0]->result,
                'gameid' => $win_amount[0]->gameid,
                'number' => $win_amount[0]->number,
            ];
            return response()->json($response,200);
        } else {
            return response()->json(['msg' => 'No record found','status' => 400,], 400);
        }
	    
	}
	

////Win Amount api /////
	
// 	public function win_amount(Request $request)
// 	{
	    
// 	    	$validator = Validator::make($request->all(), [ 
// 				'userid' => 'required',
// 		       'game_id' => 'required',
// 		       'gamesno'=>'required'
		
// 			]);

//     $validator->stopOnFirstFailure();

//     if ($validator->fails()) {
//         return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
//     }
	
	
//     $game_id = $request->game_id;
//     $userid = $request->userid;
// 	$game_no = $request->gamesno;
 
	   
// 	     $win_amount = DB::Select("SELECT 
//     COALESCE((SELECT SUM(`win_amount`) FROM `bets` WHERE `game_id`=$game_id && `gamesno`=$game_no && `userid`=$userid
// ), 0) AS total_amount,
//     bets.gamesno AS gamesno,bets.`game_id` AS gameid,bets.number AS number,
//     virtual_games.name AS name,
//     CASE 
    
//      WHEN COUNT(*) = 1 THEN
//             CASE 
//                 WHEN COALESCE(SUM(bets.win_amount), 0) > 0 THEN 'win' 
//                 ELSE 'lose'
//             END
//         ELSE 'win' 
//     END AS result 
// FROM 
//     bets
//     LEFT JOIN virtual_games ON bets.game_id = virtual_games.game_id AND bets.number = virtual_games.number
// WHERE 
//     bets.userid = $userid
//     AND bets.game_id = $game_id
//     AND bets.gamesno = $game_no 
// GROUP BY 
//     bets.gamesno,
//     bets.game_id,
//     bets.number,
//     virtual_games.name -- Include virtual_games.name in the GROUP BY clause
// ORDER BY 
//     gamesno DESC -- Order by the alias instead of the column directly
// LIMIT 1;
// ");
// //         $win_amount= DB::Select("SELECT 
// //     COALESCE(SUM(bets.win_amount), 0) AS total_amount,
// //     bets.gamesno AS gamesno,
// //     bets.game_id AS gameid,
// //     bets.number AS number,
// //     virtual_games.name AS name,
// //     CASE 
// //         WHEN COUNT(*) = 1 THEN
// //             CASE 
// //                 WHEN COALESCE(SUM(bets.win_amount), 0) > 0 THEN 'win' 
// //                 ELSE 'lose'
// //             END
// //         ELSE 'win' 
// //     END AS result 
// // FROM 
// //     bets
// //     LEFT JOIN virtual_games ON bets.game_id = virtual_games.game_id AND bets.number = virtual_games.number
// // WHERE 
// //     bets.userid = $userid
// //     AND bets.game_id = $game_id
// //     AND bets.gamesno = $game_no 
// // GROUP BY 
// //     bets.gamesno,
// //     bets.game_id,
// //     bets.number,
// //     virtual_games.name -- Include virtual_games.name in the GROUP BY clause
// // ORDER BY 
// //     gamesno DESC -- Order by the alias instead of the column directly
// // LIMIT 1
// // ");

//  //dd($win_amount);
// //$amount=$win_amount[0]->total_amount;
// //$gameno=$win_amount[0]->gamesno;
//  if ($win_amount) {
//             $response = [
//                 'message' => 'Successfully',
//                 'status' => 200,
//                 'win' => $win_amount[0]->total_amount,
//                 'gamesno' => $win_amount[0]->gamesno,
//                 'result' => $win_amount[0]->result,
//                 'gameid' => $win_amount[0]->gameid,
//                 'number' => $win_amount[0]->number,
//             ];
//             return response()->json($response,200);
//         } else {
//             return response()->json(['msg' => 'No record found','status' => 400,], 400);
//         }
	    
// 	}
	

////// Result Api ////
    public function results(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'game_id' => 'required',
        'limit' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }
    
    $game_id = $request->game_id;
    $limit = $request->limit;
     $offset = $request->offset ?? 0;
    $from_date = $request->created_at;
    $to_date = $request->created_at;
    $status = $request->status;

    $where = [];

    if (!empty($game_id)) {
        $where[] = "bet_results.game_id = '$game_id'";
    }

    if (!empty($from_date) && !empty($to_date)) {
        $where[] = "bet_results.created_at BETWEEN '$from_date' AND '$to_date'";
    }
    //
    //
    
    $query = "
        SELECT 
    bet_results.*, 
    virtual_games.name AS game_name,
    virtual_games.number AS game_number, 
    virtual_games.game_id AS game_gameid,
    game_settings.name AS game_setting_name 
FROM 
    bet_results
LEFT JOIN 
    virtual_games ON bet_results.game_id = virtual_games.game_id && bet_results.number=virtual_games.number
JOIN 
    game_settings ON bet_results.game_id = game_settings.id 
    ";

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " ORDER BY bet_results.id DESC LIMIT $offset,$limit";

    $results = DB::select($query);
     
   // $daata=json_encode($results);

    return response()->json([
        'status' => 200,
        'message' => 'Data found',
        'data' => $results
    ]);
}

  	//// Bet History ////

    public function bet_history(Request $request)
	{
	$validator = Validator::make($request->all(), [
	            'userid'=>'required',
				'game_id' => 'required',
		       //'limit' => 'required'
		       	]);
		
    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }
	
	$userid = $request->userid;
    $game_id = $request->game_id;
    $limit = $request->limit ?? 10000;
    $offset = $request->offset ?? 0;
	$from_date = $request->created_at;
	$to_date = $request->created_at;
	//////
	

if (!empty($game_id)) {
    $where['bets.game_id'] = "$game_id";
    $where['bets.userid'] = "$userid";
}


if (!empty($from_date)) {
    
       $where['bets.created_at']="$from_date%";
  $where['bets.created_at']="$to_date%";
}

$query = " SELECT DISTINCT bets.*, game_settings.name AS game_name, virtual_games.name AS name 
FROM bets
LEFT JOIN game_settings ON game_settings.id = bets.game_id 
LEFT JOIN virtual_games ON virtual_games.game_id = bets.game_id AND virtual_games.number = bets.number" ;

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", array_map(function ($key, $value) {
        return "$key = '$value'";
    }, array_keys($where), $where));
}

 $query .= " ORDER BY  bets.id DESC  LIMIT $offset , $limit";
//////
$results = DB::select($query);
$bets=DB::select("SELECT userid, COUNT(*) AS total_bets FROM bets WHERE `userid`=$userid GROUP BY userid
");
$total_bet=$bets[0]->total_bets;
if(!empty($results)){
    ///
		//
		 return response()->json([
            'status' => 200,
            'message' => 'Data found',
            'total_bets' => $total_bet,
            'data' => $results
            
        ]);
         return response()->json($response,200);
}else{
    
     //return response()->json(['msg' => 'No Data found'], 400);
    $response = [
    'status' => 400,
    'message' => 'No Data found',
    'data' => $results
];

//
return response()->json($response, $response['status']);
         
    
}
		
	}
	
	/// Cron ////
    
    public function cron($game_id)
    {
              $per=DB::select("SELECT game_settings.winning_percentage as winning_percentage FROM game_settings WHERE game_settings.id=$game_id");
        $percentage = $per[0]->winning_percentage;  

            $gameno=DB::select("SELECT * FROM bet_logs WHERE game_id=$game_id LIMIT 1");
            //
            
            ///
            $game_no=$gameno[0]->gamesno;
             $period=$game_no;
            
          
				
				
            $sumamt=DB::select("SELECT SUM(amount) AS amount FROM bets WHERE game_id = '$game_id' && gamesno='$game_no'");


				
            $totalamount=$sumamt[0]->amount;
		
            $percentageamount = $totalamount*$percentage*0.01; 
			
            $lessamount=DB::select(" SELECT * FROM bet_logs WHERE game_id = '$game_id'  && gamesno='$game_no' && amount <= $percentageamount ORDER BY amount asc LIMIT 1 ");
				if(count($lessamount)==0){
				$lessamount=DB::select(" SELECT * FROM bet_logs WHERE game_id = '$game_id'  && gamesno='$game_no' && amount >= '$percentageamount' ORDER BY amount asc LIMIT 1 ");
				}
            $zeroamount=DB::select(" SELECT * FROM bet_logs WHERE game_id =  '$game_id'  && gamesno='$game_no' && amount=0 ORDER BY RAND() LIMIT 1 ");
            $admin_winner=DB::select("SELECT * FROM admin_winner_result WHERE game_no = '$game_no' AND game_id = '$game_id' LIMIT 1");
            //  dd($admin_winner);
            $min_max=DB::select("SELECT min(number) as mins,max(number) as maxs FROM bet_logs WHERE game_id=$game_id;");
        if(!empty($admin_winner)){
            echo 'a ';
            $number=$admin_winner[0]->number;
        }
      
        if (!empty($admin_winner)) {
            echo 'b ';
            $res=$number;
        } 
         elseif ( $totalamount< 150) {
             echo 'c ';
            $res= rand($min_max[0]->mins, $min_max[0]->maxs);
        }elseif($totalamount > 150){
            echo 'd ';
            $res=$lessamount[0]->number;
        }
        //$result=$number;
        $result=$res;
    
     
      //  $this->resultannounce($game_id,$period,$result);
				
				//$this->colour_prediction_and_bingo($game_id,$period,$result);
				// $this->trx($game_id,$period,$result);
				if ($game_id == 1 || $game_id == 2 || $game_id == 3 || $game_id == 4) {
    $this->colour_prediction_and_bingo($game_id, $period, $result);
					
} elseif ($game_id == 10 ) {
    $this->dragon_tiger($game_id, $period, $result);
} elseif ($game_id == 6 || $game_id == 7 || $game_id == 8 || $game_id == 9) {
    $this->trx($game_id, $period, $result);
}

            
                
    }

//  public function cron($game_id)
//     {
//               $per=DB::select("SELECT game_settings.winning_percentage as winning_percentage FROM game_settings WHERE game_settings.id=$game_id");
//         $percentage = $per[0]->winning_percentage;  

//             $gameno=DB::select("SELECT * FROM bet_logs WHERE game_id=$game_id LIMIT 1");
//             //
            
//             ///
//             $game_no=$gameno[0]->gamesno;
//              $period=$game_no;
            
          
				
				
//             $sumamt=DB::select("SELECT SUM(amount) AS amount FROM bets WHERE game_id = '$game_id' && gamesno='$game_no'");


				
//             $totalamount=$sumamt[0]->amount;
		
//             $percentageamount = $totalamount*$percentage*0.01; 
			
//             $lessamount=DB::select(" SELECT * FROM bet_logs WHERE game_id = '$game_id'  && gamesno='$game_no' && amount <= $percentageamount ORDER BY amount asc LIMIT 1 ");
// 				if(count($lessamount)==0){
// 				$lessamount=DB::select(" SELECT * FROM bet_logs WHERE game_id = '$game_id'  && gamesno='$game_no' && amount >= '$percentageamount' ORDER BY amount asc LIMIT 1 ");
// 				}
//             $zeroamount=DB::select(" SELECT * FROM bet_logs WHERE game_id =  '$game_id'  && gamesno='$game_no' && amount=0 ORDER BY RAND() LIMIT 1 ");
//             $admin_winner=DB::select("SELECT * FROM admin_winner_result WHERE game_no = '$game_no' AND game_id = '$game_id' ORDER BY id DESC LIMIT 1");
//             //  dd($admin_winner);
//             $min_max=DB::select("SELECT min(number) as mins,max(number) as maxs FROM bet_logs WHERE game_id=$game_id;");
//         if(!empty($admin_winner)){
//             echo 'a ';
//             $number=$admin_winner[0]->number;
//         }
      
//         if (!empty($admin_winner)) {
//             echo 'b ';
//             $res=$number;
//         } 
//          elseif ( $totalamount< 450) {
//              echo 'c ';
//             $res= rand($min_max[0]->mins, $min_max[0]->maxs);
//         }elseif($totalamount > 450){
//             echo 'd ';
//             $res=$lessamount[0]->number;
//         }
//         //$result=$number;
//         $result=$res;
    
     
//       //  $this->resultannounce($game_id,$period,$result);
				
// 				//$this->colour_prediction_and_bingo($game_id,$period,$result);
// 				// $this->trx($game_id,$period,$result);
// 				if ($game_id == 1 || $game_id == 2 || $game_id == 3 || $game_id == 4) {
//     $this->colour_prediction_and_bingo($game_id, $period, $result);
					
// } elseif ($game_id == 10 ) {
//     $this->dragon_tiger($game_id, $period, $result);
// } elseif ($game_id == 6 || $game_id == 7 || $game_id == 8 || $game_id == 9) {
//     $this->trx($game_id, $period, $result);
// }elseif ($game_id == 13 ) {
//     $this->andarbaharpatta($game_id, $period, $result);
// }
// elseif ($game_id == 14 ) {
//     $this-> head_tail($game_id, $period, $result);
// }

            
                
//     }

//   private function colour_prediction_and_bingo($game_id,$period,$result)
//   {
   
// // 	  echo "SELECT name FROM virtual_games WHERE actual_number=$result && game_id=$game_id && multiplier !='1.5'";
	   
//       $colour=DB::select("SELECT name FROM virtual_games WHERE actual_number=$result && game_id=$game_id && multiplier !='1.5'");
//       $json=[];
//       foreach ($colour as $item){
//           $json[]=$item->name;
//       } 
//       $pdata=json_encode($json);

//       DB::select("INSERT INTO bet_results( number, gamesno, game_id, status,json,random_card) VALUES ('$result','$period','$game_id','1','$pdata','$result');");
// 	   $this->amountdistributioncolors($game_id,$period,$result);
//         // DB::select("UPDATE bet SET status=2 WHERE game_no='$period' && game_id=  '$game_id' && number ='$result' && status=0;");
//         DB::select("UPDATE bet_logs SET amount=0,gamesno=gamesno+1 where game_id =  '$game_id';");
//      return true;
//   }

private function colour_prediction_and_bingo($game_id, $period, $result)
{
    // Ensure $result is an integer (or handle empty or invalid values)
    if (empty($result) || !is_numeric($result)) {
        $result = 0; // Set a default value (e.g., 0) if the result is invalid
    } else {
        $result = (int) $result; // Explicitly cast to an integer
    }

    // Fetch the color information from the virtual_games table
    $colour = DB::select("SELECT name FROM virtual_games WHERE actual_number = ? AND game_id = ? AND multiplier != '1.5'", [$result, $game_id]);

    $json = [];
    foreach ($colour as $item) {
        $json[] = $item->name;
    }
    $pdata = json_encode($json);

    // Insert data into bet_results table
    DB::insert("INSERT INTO bet_results (number, gamesno, game_id, status, json, random_card) 
           VALUES (?, ?, ?, ?, ?, ?)", 
           [$result, $period, $game_id, '1', $pdata, $result]);

    // Perform the amount distribution logic
    $this->amountdistributioncolors($game_id, $period, $result);

    // Update bet_logs table to reset the amount
    DB::select("UPDATE bet_logs SET amount = 0, gamesno = gamesno + 1 WHERE game_id = ?", [$game_id]);

    return true;
}

  
  private function dragon_tiger($game_id, $period, $result)
  
  {
       $data=[]; 
            if($result==1){
            $rand=    rand(2,13);
            $cards1=DB::select("SELECT `card`, `colour`, `image`  FROM `cards` where card >$rand order by rand(id) LIMIT 1")[0]->card;
            $rand2=    rand(2,$rand-2);
            $cards2=DB::select("SELECT `card`, `colour`, `image`  FROM `cards` where card >$rand2 order by rand(id) LIMIT 1")[0]->card;
            $data=[$cards1,$cards2];
            }elseif($game_id==2){
                 $rand=    rand(2,13);
            $cards2=DB::select("SELECT `card`, `colour`, `image`  FROM `cards` where card >$rand order by rand(id) LIMIT 1")[0]->card;
            $rand2=    rand(2,$rand-2);
            $cards1=DB::select("SELECT `card`, `colour`, `image`  FROM `cards` where card >$rand2 order by rand(id) LIMIT 1")[0]->card;
                        $data=[$cards1,$cards2];
            }else{
                   $rand=    rand(2,13);
            $cards2=DB::select("SELECT `card`, `colour`, `image`  FROM `cards` where card =$rand order by id asc LIMIT 1")[0]->card;
            $cards1=DB::select("SELECT `card`, `colour`, `image`  FROM `cards` where card =$rand order by id desc LIMIT 1")[0]->card;
                        $data=[$cards1,$cards2];  
            }
            $resjson=json_encode($data);
       // dd($resjson);
        DB::select("INSERT INTO `bet_results`( `number`, `gamesno`, `game_id`, `status`,`json`) VALUES ('$result','$period','$game_id','1','$resjson')"); 
         $this->amountdistributioncolors($game_id,$period,$result);
         DB::select("UPDATE `bet_logs` SET amount=0,gamesno=gamesno+1 where game_id =  '$game_id'"); 
  }
  
  
   	 private function trx($game_id,$period,$result)
   {
       $colour=DB::select("SELECT `name` FROM `virtual_games` WHERE actual_number=$result && game_id=$game_id && `multiplier` !='1.5'");
      
       $tokens=$this->generateRandomString().$result;
		 
       $json=[];
       foreach ($colour as $item){
           $json[]=$item->name;
       }
       $pdata=json_encode($json);
		 $blockk = DB::table('bet_results')
            ->selectRaw('`block` + CASE 
                            WHEN ? = 6 THEN 20 
                            WHEN ? = 7 THEN 60 
                            WHEN ? = 8 THEN 100 
                            ELSE 200 
                          END AS adjusted_block', [$game_id, $game_id, $game_id])
            ->where('game_id', $game_id)
            ->orderByDesc('id')
            ->limit(1)
            ->first();
		 //$block=$blockk[0]->adjusted_block;
	$block=$blockk->adjusted_block;
       DB::select("
     INSERT INTO `bet_results` (`number`, `gamesno`, `game_id`, `status`, `json`, `random_card`, `token`,`block`)VALUES ('$result', '$period', '$game_id', '1', '$pdata','$result', '$tokens','$block')");
          $this->amountdistributioncolors($game_id,$period,$result);
         DB::select("UPDATE `bets` SET `status`=2 WHERE `gamesno`='$period' && `game_id`=  '$game_id' && number ='$result' && `status`=0;");
         DB::select("UPDATE `bet_logs` SET amount=0,gamesno=gamesno+1 where game_id =  '$game_id';");
      return true;
			
      
   }
	
	
	 private function amountdistributioncolors($game_id,$period,$result)
    {
       
        $virtual=DB::select("SELECT name, number, actual_number, game_id, multiplier FROM virtual_games WHERE actual_number='$result' && game_id= '$game_id' AND ((type != 1 AND multiplier != '1.5') OR (type = 1 AND multiplier = '1.5'));");
     //print_r($virtual);

        foreach ($virtual as $winamount) {
            
            $multiple = $winamount->multiplier;

            $number=$winamount->number;
            if(!empty($number)){
				
				if($result == '0'){
					 DB::select("UPDATE bets SET win_amount =(trade_amount*9),win_number= '0',status=1 WHERE gamesno='$period' && game_id=  '$game_id' && number =$result");
				}
            
          DB::select("UPDATE bets SET win_amount =(trade_amount*$multiple),win_number= '$result',status=1 WHERE gamesno='$period' && game_id=  '$game_id' && number =$number");
        
            }
            
		}
                $uid = DB::select("SELECT  win_amount,  userid FROM bets where win_number>=0 && gamesno='$period' && game_id=  '$game_id' ");
        foreach ($uid as $row) {
             $amount = $row->win_amount;
            $userid = $row->userid;
      DB::update("UPDATE users SET wallet = wallet + $amount, winning_wallet = winning_wallet + $amount WHERE id = $userid");
        
        }
 
          DB::select("UPDATE bets SET status=2 ,win_number= '$result' WHERE gamesno='$period' && game_id=  '$game_id' &&  status=0 && win_amount=0");

            
    }
    
   private function generateRandomString($length = 4) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    $maxIndex = strlen($characters) - 1;

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $maxIndex)];
    }

    return $randomString;
}
    
public function plinko_bet(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        'game_id' => 'required',
        'amount' => 'required|numeric|min:0',
        'type' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }

    $userid = $request->userid;
    $gameid = $request->game_id;
    $amount = $request->amount; 
    $type = $request->type; 
	date_default_timezone_set('Asia/Kolkata');
    $datetime = date('Y-m-d H:i:s');
    $orderid = date('YmdHis') . rand(11111, 99999);
    $tax = 0.00;
    $commission = $amount * $tax; // Calculate commission
    $betAmount = $amount - $commission;
    $userWallet = DB::table('users')->where('id', $userid)->value('wallet');
   if($amount >= 10){
   $alreadyBet = DB::table('plinko_bet')->where('userid', $userid)->where('status', 0)->orderBy('id', 'DESC')->first();

    if (empty($alreadyBet)) {
        if ($userWallet >= $amount) {
            DB::table('plinko_bet')->insert([
                'amount' => $amount,
                'game_id' => $gameid,
                'type' => $type,
                'userid' => $userid,
                'status' => 0,
                'datetime' => $datetime,
                'tax' => $tax,
                'after_tax' => $betAmount,
                'orderid' => $orderid
            ]);

            DB::update("UPDATE users SET wallet = wallet - $amount WHERE id = $userid");
            return response()->json(['status' => 200, 'message' => 'Bet placed successfully'], 200);
        } else {
            return response()->json(['status' => 400, 'message' => 'Insufficient balance'], 400);
        }
    } else {
        return response()->json(['status' => 400, 'message' => 'Already Bet placed'], 400);
    }
} else {
    return response()->json(['status' => 400, 'message' => 'Bet placed minimum 10 rupees'], 400);
}

}

	
	
public function plinko_index_list(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'type' => 'required',
    ]);

    $validator->stopOnFirstFailure();
    
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }
    
    $type = $request->type;
    
    $data = DB::table('pinko_index_list')
        ->where('type', $type)
        ->get();

    if (!$data->isEmpty()) {  
        return response()->json([
            'status' => 200,
            'message' => 'Success',
            'data' => $data
        ], 200);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'No data found'
        ], 400);
    }
}

	public function plinko_multiplier(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'userid' => 'required|integer',
        'index' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
    }

    $userid = $request->userid;
    $index = $request->index;
	date_default_timezone_set('Asia/Kolkata');	
    $datetime = date('Y-m-d H:i:s');

    $plinko_bet = DB::table('plinko_bet')
        ->where('userid', $userid)
        ->where('Status', 0)
        ->orderBy('id', 'asc')
        ->first();

    if (!$plinko_bet) {
        return response()->json(['status' => 400, 'message' => 'No active plinko bet found for the user'], 400);
    }

    $bet_amount = $plinko_bet->amount;
    $type = $plinko_bet->type;


    $index_multiplier = DB::table('pinko_index_list')
        ->where('type', $type)
        ->where('indexs', $index)
        ->first();


    if (empty($index_multiplier)) {
        DB::table('plinko_bet')
            ->where('id', $plinko_bet->id)
            ->update(['Status' => 1, 'indexs' => $index, 'multipler' => 'out', 'win_amount' => 0]);

        return response()->json([
            'status' => 200,
            'message' => 'Plinko result calculated successfully',
            'win_amount' => '0'
        ], 200);
    }
    $multipler=$index_multiplier->multiplier;
  
    $win_amount = $bet_amount * $multipler;

 
    DB::table('plinko_bet')
        ->where('id', $plinko_bet->id)
        ->update(['Status' => 1, 'indexs' => $index, 'multipler' => $multipler,'win_amount' => $win_amount]);

     DB::update("UPDATE users SET wallet = wallet + $win_amount  WHERE id = $userid");
    return response()->json([
        'status' => 200,
        'message' => 'Plinko result calculated successfully',
        'win_amount' => $win_amount
    ],200);
} 

public function plinko_result(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
    ]);

    
    $validator->stopOnFirstFailure();
    
    
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }
    
   
    $userid = $request->userid;
    $limit = $request->limit??0;
	$offset = $request->offset ?? 0;


   if (empty($limit)) {
        $data = DB::table('plinko_bet')->where('userid', $userid)->where('status', 1)->orderBy('id', 'DESC')->get();
    } else {
        $data = DB::table('plinko_bet')->where('userid', $userid)->where('status', 1)->orderBy('id', 'DESC')->skip($offset)->take($limit)->get();
    }   
  
    if (!$data->isEmpty()) {  
        return response()->json([
            'status' => 200,
            'message' => 'Success',
            'data' => $data
        ], 200);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'No data found'
        ], 400);
    }
}
public function mine_bet(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        'game_id' => 'required',
        'amount' => 'required|numeric|min:0',
       
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }

    $userid = $request->userid;
    $gameid = $request->game_id;
    $amount = $request->amount; 
   date_default_timezone_set('Asia/Kolkata');
    $datetime = date('Y-m-d H:i:s');
    $orderid = date('YmdHis') . rand(11111, 99999);
    $tax = 0.00;
    $commission = $amount * $tax; 
    $betAmount = $amount - $commission;
    $userWallet = DB::table('users')->where('id', $userid)->value('wallet');
   if($amount >= 10){

        if ($userWallet >= $amount) {
			
            DB::table('minegame_bet')->insert([
                'amount' => $amount,
                'game_id' => $gameid,
                'userid' => $userid,
                'status' => 0,
                'datetime' => $datetime,
                'tax' => $tax,
                'after_tax' => $betAmount,
                'orderid' => $orderid
            ]);

            DB::update("UPDATE users SET wallet = wallet - $amount WHERE id = $userid");
            return response()->json(['status' => 200, 'message' => 'Bet placed successfully'], 200);
        } else {
            return response()->json(['status' => 400, 'message' => 'Insufficient balance'], 400);
        }

} else {
    return response()->json(['status' => 400, 'message' => 'Bet placed minimum 10 rupees'], 400);
}

}
	
	
	
public function mine_cashout(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required|integer',
        'win_amount' => 'required|numeric',
        'multipler' => 'required|numeric',
        'status' => 'required|integer'
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
    }

    $userid = $request->userid;
    $win_amount = $request->win_amount;
    $status = $request->status;
    $multipler = $request->multipler;
	date_default_timezone_set('Asia/Kolkata');
    $datetime = date('Y-m-d H:i:s');

    
    $user = DB::table('users')->where('id', $userid)->first();
    if (!$user) {
        return response()->json(['status' => 400, 'message' => 'User does not exist'], 400);
    }

    $minegame_bet = DB::table('minegame_bet')
        ->where('userid', $userid)
        ->where('Status', 0)
        ->orderBy('id', 'asc')
        ->first();

    if (!$minegame_bet) {
        return response()->json(['status' => 400, 'message' => 'No active minegame bet found for the user'], 400);
    }

    DB::table('minegame_bet')
        ->where('id', $minegame_bet->id)
        ->update([
            'Status' => $status,  
            'multipler' => $multipler, 
            'win_amount' => $win_amount
        ]);

    DB::table('users')
        ->where('id', $userid)
        ->update(['wallet' => DB::raw("wallet + $win_amount")]);

    return response()->json([
        'status' => 200,
        'message' => 'CashOut successfully',
        'win_amount' => $win_amount
    ], 200);
}
public function mine_result(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
    ]);

    
    $validator->stopOnFirstFailure();
    
    
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }
    
   
    $userid = $request->userid;
    $limit = $request->limit??0;
	$offset = $request->offset ?? 0;


   if (empty($limit)) {
        $data = DB::table('minegame_bet')->where('userid', $userid)->where('status', 1)->orWhere('status', 2)->orderBy('id', 'DESC')->get();
	  
    } else {
        $data = DB::table('minegame_bet')->where('userid', $userid)->where('status', 1)->orWhere('status', 2)->orderBy('id', 'DESC')->skip($offset)->take($limit)->get();
	    
    }  
	 $datas = DB::table('minegame_bet')->where('userid', $userid)->where('status', 1)->orWhere('status', 2)->orderBy('id', 'DESC')->get();
	$count=$datas->count();
  
    if (!$data->isEmpty()) {  
        return response()->json([
            'status' => 200,
            'message' => 'Success',
			'count'=>"$count",
            'data' => $data
        ], 200);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'No data found'
        ], 400);
    }
}

	
	
// public function plinko_cron()
// {
//     date_default_timezone_set('Asia/Kolkata');
//     $current_time = date('H:i:s');
//     $one_minute_before = date('H:i:s', strtotime('-1 minute'));

//     $plinko_bet = DB::table('plinko_bet')
//             ->where('Status', 0)
// 		    ->whereRaw("TIME(datetime) < '$one_minute_before'", )
//             ->update(['Status' => 1, 'indexs' => 9, 'multipler' => 'out', 'win_amount' => 0]);
	
     
// }

public function plinko_cron()
{
    date_default_timezone_set('Asia/Kolkata');
    $current_time = date('H:i:s');
    $one_minute_before = date('H:i:s', strtotime('-1 minute'));

    // Fix: Removed extra comma after $one_minute_before
    $plinko_bet = DB::table('plinko_bet')
        ->where('Status', 0)
        ->whereRaw("TIME(datetime) < ?", [$one_minute_before])
        ->update([
            'Status' => 1, 
            'indexs' => 9, 
            'multipler' => 'out', 
            'win_amount' => 0
        ]);
}



}