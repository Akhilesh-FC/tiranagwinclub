<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use DateTime;

use Illuminate\Support\Facades\Http;


 
class AgencyPromotionController extends Controller
{
	
	public function promotion_data($id) {
    try {
       
        $users = User::findOrFail($id);
        $user_id = $users->id;
		
		$currentDate = Carbon::now()->subDay()->format('Y-m-d');

       
        $directSubordinateCount = User::where('referral_user_id', $user_id)->count();

       
        $totalCommission = User::where('id', $user_id)->value('commission');
		
		$referral_code = User::where('id', $user_id)->value('referral_code');
		
		$yesterday_total_commission = User::where('id', $user_id)->value('yesterday_total_commission');
		
		
		 $teamSubordinateCount = \DB::select("
            WITH RECURSIVE subordinates AS (
                SELECT id, referral_user_id, 1 AS level
                FROM users
                WHERE referral_user_id = ?

                UNION ALL

                SELECT u.id, u.referral_user_id, s.level + 1
                FROM users u
                INNER JOIN subordinates s ON s.id = u.referral_user_id
                WHERE s.level <= 7
            )
            SELECT COUNT(*) as count
            FROM subordinates
        ", [$user_id]);
		
		
		$register = \DB::select("
    SELECT count(`id`) as register
    FROM `users`
    WHERE `referral_user_id` = ? 
    AND `created_at` LIKE '$currentDate %'
", [$user_id]);
		
	
		
		$deposit_number = \DB::select("SELECT count(`p`.`id`) as deposit_number, 
       		sum(`p`.`cash`) as deposit_amount 
			FROM `payins` p
			JOIN `users` u ON p.`user_id` = u.`id`
			WHERE u.`referral_user_id` = ?
			AND  p.status = 2
  			AND p.`created_at` like '$currentDate %'", [$user_id]);
		
		
		
		
	  $first_deposit = \DB::select("
    SELECT count(`p`.`id`) as first_deposit
    FROM `payins` p
    JOIN `users` u ON p.`user_id` = u.`id`
    WHERE u.`referral_user_id` = $user_id
    AND p.`created_at` LIKE '$currentDate %' 
    AND u.`first_recharge` = '0'
");


$subordinates_register = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id
        FROM users
        WHERE referral_user_id = ?

        UNION ALL

        SELECT u.id, u.referral_user_id
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
    )
    SELECT COUNT(*) as register
    FROM subordinates
    INNER JOIN users u ON u.id = subordinates.id
    WHERE u.created_at LIKE '$currentDate %'
", [$user_id]);


			$subordinates_deposit = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level <= 7
    )
    SELECT count(p.id) as deposit_number, 
           sum(p.cash) as deposit_amount 
    FROM payins p
    JOIN subordinates s ON p.user_id = s.id
    WHERE p.created_at LIKE ?
	AND  p.status = 2
", [$user_id, $currentDate . '%']);

	
		
  $subordinates_first_deposit = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level < 7
    )
    SELECT count(p.id) as first_deposit
    FROM payins p
    JOIN subordinates s ON p.user_id = s.id
    JOIN users u ON u.id = s.id
    WHERE s.referral_user_id = ?
    AND p.created_at LIKE ? 
    AND u.first_recharge = '0'
", [$user_id, $user_id, "$currentDate%"]);



		$datetime = Carbon::now()->format('Y-m-d');

     //$today_withdraw= DB::select("SELECT SUM(`amount`) as amount FROM `withdraw_histories` WHERE status = 2 AND user_id=$user_id AND DATE(`created_at`) =$datetime");
     $today_withdraw = DB::select("SELECT SUM(`amount`) as amount FROM `withdraw_histories` WHERE status = 2 AND user_id = ? AND DATE(`created_at`) = CURDATE()", [$user_id]);

		 $total_withdraw= DB::select("SELECT SUM(`amount`) as total_withdraw FROM `withdraw_histories` WHERE `user_id`=$user_id && `status`=2");
		$today_salary= DB::select("SELECT SUM(`amount`) as today_salary FROM wallet_history WHERE `userid`=$user_id AND `subtypeid`=13 AND DATE(`created_at`) =CURDATE()");
		$total_salary= DB::select("SELECT SUM(`amount`) AS total_salary FROM `wallet_history` WHERE `userid`=$user_id AND `subtypeid`=13;
");
		
		
      
        $result = [
			'yesterday_total_commission' => $yesterday_total_commission ?? 0,
			'register' => $register[0]->register ?? 0,
			'deposit_number' => $deposit_number[0]->deposit_number ?? 0,
			'deposit_amount' => $deposit_number[0]->deposit_amount ?? 0,
			'first_deposit' => $first_deposit[0]->first_deposit ?? 0,
			
			'subordinates_register' => $subordinates_register[0]->register ?? 0,
			'subordinates_deposit_number' => $subordinates_deposit[0]->deposit_number ?? 0,
			'subordinates_deposit_amount' => $subordinates_deposit[0]->deposit_amount ?? 0,
			'subordinates_first_deposit' => $subordinates_first_deposit[0]->first_deposit ?? 0,
			
            'direct_subordinate' => $directSubordinateCount ?? 0,
            'total_commission' => $totalCommission ?? 0,
            'team_subordinate' => $teamSubordinateCount[0]->count ?? 0,
			
			'referral_code' => $referral_code,
			'today_withdraw' => $today_withdraw[0]->amount ?? 0,
			'total_withdraw' => $total_withdraw[0]->total_withdraw ?? 0,
			'today_salary' =>$today_salary[0]->today_salary ?? 0,
			'total_salary' => $total_salary[0]->total_salary ?? 0
        ];

	
                return response()->json($result,200);
		
     
    } catch (\Exception $e) {
       
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

	
	
	public function new_subordinate(Request $request){
	try {
		
		 $validator = Validator::make($request->all(), [
            'id' => 'required',
			'type' => 'required',
        ]);

        $validator->stopOnFirstFailure();
	
        if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
       
        $users = User::findOrFail($request->id);
        $user_id = $users->id;
		
		$currentDate = Carbon::now()->format('Y-m-d');
		$yesterdayDate  = Carbon::yesterday()->format('Y-m-d');
		$startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
		
		if($request->type == 1){
		$subordinate_data = DB::table('users')->select('mobile', 'u_id', 'created_at')
    ->where('referral_user_id', $user_id)
    ->where('created_at', 'like', $currentDate . '%')
    ->get();
			
			if($subordinate_data->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $subordinate_data]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
			
		}elseif($request->type == 2){
			
				$subordinate_data = DB::table('users')->select('mobile', 'u_id', 'created_at')
    ->where('referral_user_id', $user_id)
    ->where('created_at', 'like', $yesterdayDate . '%')
    ->get();
			
			if($subordinate_data->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $subordinate_data]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
			
		}elseif($request->type == 3){
				$subordinate_data = DB::table('users')->select('mobile', 'u_id', 'created_at')
    ->where('referral_user_id', $user_id)
    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
    ->get();
			
			if($subordinate_data->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $subordinate_data]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
		}
		
		
		 } catch (\Exception $e) {
       
        return response()->json(['error' => $e->getMessage()], 500);
    }
 }
	
	
	
	
	
	public function tier(){
		try {
			
		// $tier =	DB::table('mlm_levels')->select('name')->get();
			
			$tier =	DB::table('mlm_levels')->select('id','name')->get();
			
			if($tier->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $tier]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
			
			} catch (\Exception $e) {
       
        	 return response()->json(['error' => $e->getMessage()], 500);
      }
		
		
	}
	
	
public function subordinate_data(Request $request) {
    try {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'tier' => 'required|integer|min:0',
        ]);

        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ]; 
            return response()->json($response, 400);
        }

        $user_id = $request->id; 
        $tier = $request->tier; 
		$search_uid = $request->u_id;
		$CurrentDate = $request->created_at;
	 if (!empty($CurrentDate)) {	 
		 $currentDate = $CurrentDate;
	 }else{
         $currentDate = Carbon::now()->subDay()->format('Y-m-d');
	 }
		  if (!empty($search_uid)) {
            $subordinates_deposit = \DB::select("WITH RECURSIVE subordinates AS (
    SELECT id, referral_user_id, 1 AS level
    FROM users
    WHERE referral_user_id = ?
    UNION ALL
    SELECT u.id, u.referral_user_id, s.level + 1
    FROM users u
    INNER JOIN subordinates s ON s.id = u.referral_user_id
    WHERE s.level + 1 <= ?
)
SELECT 
    users.id, 
    users.u_id, 
    COALESCE(SUM(bets.amount), 0) AS bet_amount, 
    COALESCE(SUM(payins.cash), 0) AS total_cash, 
    COALESCE(SUM(bets.amount), 0) * COALESCE(mlm_levels.commission, 0) / 100 AS commission, 
    ? AS yesterday_date 
FROM users
LEFT JOIN subordinates ON users.id = subordinates.id
LEFT JOIN mlm_levels ON subordinates.level = mlm_levels.id
LEFT JOIN bets ON users.id = bets.userid AND bets.created_at LIKE ?
LEFT JOIN payins ON users.id = payins.user_id AND payins.created_at LIKE ?
WHERE users.u_id LIKE ?
GROUP BY users.id, users.u_id, mlm_levels.commission;
;
", [$user_id, $tier, $currentDate ,$currentDate . ' %', $currentDate . ' %', $search_uid . '%']);
			  
			  
			  $subordinates_data = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        users.u_id, 
        COALESCE(payin_summary1.total_payins, 0) AS payin_count,
        COALESCE(bettor_count.total_bettors, 0) AS bettor_count,
        COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount,
        COALESCE(payin_summary2.total_payin_cash, 0) AS payin_amount
    FROM users
    LEFT JOIN (
        SELECT userid, SUM(amount) AS total_bet_amount 
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bet_summary ON users.id = bet_summary.userid
    
    LEFT JOIN (
        SELECT user_id, SUM(cash) AS total_payin_cash
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary2 ON users.id = payin_summary2.user_id
    
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS total_payins
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary1 ON users.id = payin_summary1.user_id

    LEFT JOIN (
        SELECT userid, COUNT(DISTINCT userid) AS total_bettors
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bettor_count ON users.id = bettor_count.userid
	
    WHERE users.id IN (
        SELECT id FROM subordinates WHERE level = ?
    )
    GROUP BY 
        users.id, 
        users.u_id, 
        payin_summary1.total_payins,
        bettor_count.total_bettors,
        bet_summary.total_bet_amount,
        payin_summary2.total_payin_cash
		
", [$user_id, $tier, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%', $tier]);

        } else {
		
      $subordinates_deposit = \DB::select("
  WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        users.u_id, 
        COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount, 
        COALESCE(payin_summary.total_cash, 0) AS total_cash,  
        COALESCE(bet_summary.total_bet_amount, 0) * COALESCE(mlm_levels.commission, 0) / 100 AS commission,
        ? AS yesterday_date 
		

    FROM users
    LEFT JOIN (
        SELECT userid, SUM(amount) AS total_bet_amount 
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bet_summary ON users.id = bet_summary.userid 
    LEFT JOIN (
        SELECT user_id, SUM(cash) AS total_cash 
        FROM payins  
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary ON users.id = payin_summary.user_id
	LEFT JOIN subordinates ON users.id = subordinates.id
    LEFT JOIN mlm_levels ON subordinates.level = mlm_levels.id
    WHERE users.id IN (
        SELECT id FROM subordinates WHERE level = ?
    )
    GROUP BY users.id, users.u_id, mlm_levels.commission, bet_summary.total_bet_amount, payin_summary.total_cash;

",[$user_id, $tier, $currentDate ,$currentDate . ' %', $currentDate . ' %', $tier]);
			  
	$subordinates_data = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        users.u_id, 
        COALESCE(SUM(payin_summary1.total_payins), 0) AS payin_count,
        COALESCE(SUM(bettor_count.total_bettors), 0) AS bettor_count,
        COALESCE(SUM(bet_summary.total_bet_amount), 0) AS bet_amount,
        COALESCE(SUM(payin_summary2.total_payin_cash), 0) AS payin_amount
    FROM users
    LEFT JOIN (
        SELECT userid, SUM(amount) AS total_bet_amount 
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bet_summary ON users.id = bet_summary.userid
    
    LEFT JOIN (
        SELECT user_id, SUM(cash) AS total_payin_cash
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary2 ON users.id = payin_summary2.user_id
    
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS total_payins
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary1 ON users.id = payin_summary1.user_id

    LEFT JOIN (
        SELECT userid, COUNT(DISTINCT userid) AS total_bettors
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bettor_count ON users.id = bettor_count.userid
    WHERE users.id IN (
        SELECT id FROM subordinates 
        WHERE ? = 0 OR level = ?
    )
    GROUP BY 
        users.id, 
        users.u_id
", [$user_id, $tier > 0 ? $tier : PHP_INT_MAX, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%', $tier, $tier]);




		 }
		
$betAmountTotal = 0;
$numberOfBettors = 0;
$number_of_deposit = 0;
$payin_amount = 0;
$first_deposit = 0;
$first_deposit_amount = 0;
		
foreach ($subordinates_data as $data) {
	$number_of_deposit += $data->payin_count ?? 0;
    $payin_amount += $data->payin_amount ?? 0;
	
    $betAmountTotal += $data->bet_amount ?? 0;
    $numberOfBettors += $data->bettor_count ?? 0;
	
	$first_deposit += $data->total_first_recharge ?? 0;
    $first_deposit_amount += $data->total_first_deposit_amount ?? 0;
}

$result = [
    'number_of_deposit' => $number_of_deposit,
    'payin_amount' => $payin_amount,
    'number_of_bettor' => $numberOfBettors,
    'bet_amount' => $betAmountTotal,
    'first_deposit' => $first_deposit,
    'first_deposit_amount' => $first_deposit_amount,
    'subordinates_data' => $subordinates_deposit,
];

		

        return response()->json($result, 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
		
    }
}




public function turnover_new()
{
    // Get the current datetime and the date for the previous day
    $datetime = Carbon::now();
    $currentDate = Carbon::now()->subDay()->format('Y-m-d');
	
	
	    DB::table('users')->update(['yesterday_total_commission' => 0]);


    // Fetch users who have a referral_user_id
    $referralUsers = DB::table('users')->whereNotNull('users.referral_user_id')->get();


    // Check if there are any users retrieved
    if ($referralUsers->count() > 0) {
      

        foreach ($referralUsers as $referralUser) {
            $user_id = $referralUser->id;
	
            $maxTier = 7;

   $subordinatesData = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        subordinates.level,
        COALESCE(SUM(bet_summary.total_bet_amount), 0) AS bet_amount,
        COALESCE(SUM(bet_summary.total_bet_amount), 0) * COALESCE(level_commissions.commission, 0) / 100 AS commission
    FROM users
    LEFT JOIN (
        SELECT userid, SUM(amount) AS total_bet_amount 
        FROM bets 
        WHERE created_at LIKE ?
        GROUP BY userid
    ) AS bet_summary ON users.id = bet_summary.userid 
    LEFT JOIN subordinates ON users.id = subordinates.id
    LEFT JOIN (
        SELECT id, commission
        FROM mlm_levels
    ) AS level_commissions ON subordinates.level = level_commissions.id
    WHERE subordinates.level <= ?
    GROUP BY users.id, subordinates.level, level_commissions.commission;
", [$user_id, $maxTier, $currentDate . '%', $maxTier]);

$totalCommission = 0;

foreach ($subordinatesData as $data) {
    $totalCommission += $data->commission;
}

			
	// echo "<pre>"; print_r($subordinatesData); echo "<pre>";  die;

                DB::table('users')->where('id', $user_id)->update([
        'wallet' => DB::raw('wallet + ' . $totalCommission),
        'commission' => DB::raw('commission + ' . $totalCommission),
		'yesterday_total_commission' => $totalCommission,
        'updated_at' => $datetime,
    ]);
		
			  DB::table('wallet_history')->insert([
    'userid' => $user_id,
    'amount' => $totalCommission,
    'subtypeid' => 26,
    'created_at' => $datetime,
    'updated_at' => $datetime,
]);
			

           
        }

    
    }

    return response()->json(['message' => 'No referral users found.'], 200);
}


	  
	
}