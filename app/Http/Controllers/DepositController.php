<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class DepositController extends Controller
{
    public function deposit_index($id)
    {
         $deposits= DB::select("SELECT payins.*,users.username AS uname,users.id As userid, users.mobile As mobile FROM `payins` LEFT JOIN users ON payins.user_id=users.id WHERE payins.status = '$id' && payins.type=2");
        return view('work_order_assign.deposit')->with('deposits',$deposits)->with('id',$id);
    }
  
}
