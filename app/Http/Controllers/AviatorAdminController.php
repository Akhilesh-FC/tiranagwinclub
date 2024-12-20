<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Storage;
class AviatorAdminController extends Controller
{
    // public function bet_history()
    // {
        
		
		  //$bets = DB::table('aviator_bet')
    //             ->leftJoin('users', 'aviator_bet.uid', '=', 'users.id')
    //             ->select('aviator_bet.*', 'users.username AS username')
    //             ->paginate(10);
    
    // return view('aviator.bet', compact('bets'));
		
		
		
    // }
	
// 	public function result_index(string $game_id)
//     {
//          $perPage = 10;
	   
// 		$results = DB::table('bet_results')
// 			->join('game_settings', 'bet_results.game_id', '=', 'game_settings.id')
// 			->where('bet_results.game_id', $game_id)
// 			->orderByDesc('bet_results.id')
// 			->first();

//         $aviator_res = DB::table('bet_results')->where('game_id',5)->orderByDesc('id')->paginate($perPage);

	   
//         return view('aviator.result')->with('results', $results)->with('game_id', $game_id)->with('aviator_res',$aviator_res);
        
//         /////////////
// // 		 $numbers= DB::select("SELECT `number` FROM `aviator_admin_result` ORDER BY id DESC LIMIT 1;");
         
// // 		    $perPage = 10;
// // 		    $results = DB::table('game_settings')->where('id', 5)->first();
// // 		    $aviator_res = DB::table('aviator_result')->paginate($perPage);
// // 		    return view('aviator.result')->with('results',$results)->with('aviator_res',$aviator_res)->with('numbers',$numbers);
		
		
		
//     }

   
    //  public function result_update(Request $request)
    //   {
		
    //     $percentage=$request->winning_percentage;
    //     $data= DB::update("UPDATE `game_settings` SET`	winning_percentage`='$percentage' WHERE `id`=5");
         
    //          return redirect()->route('result');
          
    //   }
// 	public function result_number_update(Request $request)
//       {
		
//         $numberss=$request->number;
//         $data= DB::update("UPDATE `aviator_admin_result` SET `number`='$numberss' ORDER by id DESC LIMIT 1;");
         
//              return redirect()->route('result');
          
//       }
	
      
///// Akash //////
  public function aviator_prediction_create(string $game_id)
    {
	    
	    $perPage = 10;
	   
		$results = DB::table('aviator_result')
			->join('game_settings', 'aviator_result.game_id', '=', 'game_settings.id')
			->where('aviator_result.game_id', $game_id)
			->orderByDesc('aviator_result.id')
			->first();

        $aviator_res = DB::table('aviator_result')->where('game_id',5)->orderByDesc('id')->paginate($perPage);

	   
        return view('aviator.result')->with('results', $results)->with('game_id', $game_id)->with('aviator_res',$aviator_res);
    }

    public function aviator_fetchDatacolor($game_id)
    {
		
        $bets = DB::select("SELECT bet_logs.*,game_settings.winning_percentage AS percentage ,game_settings.id AS id FROM `bet_logs` LEFT JOIN game_settings ON bet_logs.game_id=game_settings.id where bet_logs.game_id=$game_id Limit 10");

        return response()->json(['bets' => $bets, 'game_id' => $game_id]);
    }
	
	
	public function aviator_store(Request $request)
	{
		
		  	date_default_timezone_set('Asia/Kolkata');
           $datetime = date('Y-m-d H:i:s');
		
	  
      $game_id =$request->game_id;
		 $game_sr_num =$request->game_sr_num;
		 $number=$request->number;
       $multiplier =$request->multiplier;
    //   dd($multiplier);
		
		DB::table('aviator_admin_result')->insert([
		'game_sr_num'=>$game_sr_num,
        'game_id'=>$game_id,
			'number'=>$multiplier,
			'multiplier'=>$multiplier,
			'status'=>1,
			'datetime'=>$datetime
		]);
		
             return redirect()->back(); 
	}
  
   public function aviator_update(Request $request)
      {
	   
	     	date_default_timezone_set('Asia/Kolkata');
           $datetime = date('Y-m-d H:i:s');
	   
	   $game_id=$request->game_id;
        $percentage = $request->percentage;
	   
         //$data= DB::select("UPDATE `game_setting` SET `percentage` = '$percentage','datetime'='$datetime' WHERE `id` ='$gamid'");
	  $data =  DB::table('game_settings')->where('id',$game_id)->update(['winning_percentage'=>$percentage]);
         if($data){
        return redirect()->back();
		 }else{
			 return 'Can not update';
		 }
      }
   
      
	
	  public function aviator_bet_history(string $game_id)
    {
		  $perPage = 10;

			$bets = DB::table('aviator_bet')
				->select('aviator_bet.id as id','aviator_bet.game_sr_num as game_sr_num','aviator_bet.amount as amount', 'aviator_bet.win as win','aviator_bet.created_at as datetime','users.u_id as username', 'users.mobile as mobile')
				->where('aviator_bet.game_id', $game_id)
				->join('users', 'aviator_bet.uid', '=', 'users.id')
				->orderByDesc('aviator_bet.id')
				->paginate($perPage);
		  
	    return view('aviator.bet')->with('bets', $bets); 
	  }
	
	

}


