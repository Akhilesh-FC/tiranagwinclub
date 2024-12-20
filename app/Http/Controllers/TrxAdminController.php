<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use DB;

class TrxAdminController extends Controller
{
   public function trx_create($gameid)
    {
	   //dd($gameid);
        $bets = DB::select("SELECT bet_logs.*,game_settings.winning_percentage AS parsantage ,game_settings.id AS id FROM `bet_logs` LEFT JOIN game_settings ON bet_logs.game_id=game_settings.id where bet_logs.game_id=$gameid Limit 10;");
  //dd($bets);
       return view('trx.index')->with('bets', $bets)->with('gameid', $gameid);
    }

    public function fetchData($gameid)
    {
        $bets = DB::select("SELECT bet_logs.*,game_settings.winning_percentage AS parsantage ,game_settings.id AS id FROM `bet_logs` LEFT JOIN game_settings ON bet_logs.game_id=game_settings.id where bet_logs.game_id=$gameid Limit 10;");

        return response()->json(['bets' => $bets, 'gameid' => $gameid]);
    }
	
	
	public function store(Request $request)
	{
		
	
	  $gamesno=$request->game_no;
		
      $gameids=$request->game_id;
      $number=$request->number;
		//$datetime=now();
		
         DB::insert("INSERT INTO `admin_winner_result`( `game_no`, `game_id`, `number`, `status`) VALUES ('$gamesno','$gameids','$number','1')");
         
        
             return redirect()->back(); 
	}
  
   public function update(Request $request)
      {
	   $gamid=$request->id;
	
        $parsantage=$request->parsantage;
               $data= DB::select("UPDATE `game_settings` SET `winning_percentage` = '$parsantage' WHERE `id` ='$gamid'");
	         
         
             return redirect()->back();
          
      }
   
      

}
