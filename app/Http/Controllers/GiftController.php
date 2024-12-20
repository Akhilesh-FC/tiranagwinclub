<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\Invoice;
use DB;

class GiftController extends Controller
{

     public function index()
     {
          $gifts = DB::select("SELECT * FROM `gift_cart` ORDER BY `gift_cart`.`id` DESC");
        // $invoice = DB::select("SELECT invoices.*,products.name AS pname ,products.price AS pprice FROM `invoices` LEFT JOIN products ON invoices.product_id=products.id;");
    // dd($sudhir);
         return view('gift.index')->with('gifts',$gifts);

     }

    public function gift_store(Request $request)
    {
     // $amounts->amount =$request['amount'];
     // $member_peoples->member_people =$request['member_people'];
		$datetime=now();
     $amount=$request->amount;
     $number_people=$request->number_people;
     $rand=rand(000000000000000,999999999999999);
        $data = DB::insert("INSERT INTO `gift_cart`(`amount`, `number_people`,`code`,`status`,`datetime`) VALUES ('$amount','$number_people','$rand','1','$datetime')");
            return redirect()->route('gift')->with('data',$data)->with('success','Gift Added Successfully ..!');    
    }
	
	 public function giftredeemed()
{
    $gifts = DB::select("
        SELECT gift_claim.*, users.username 
        FROM gift_claim 
        LEFT JOIN users ON gift_claim.userid = users.id 
        ORDER BY gift_claim.id DESC
    ");

    return view('gift.giftredeemed')->with('gifts', $gifts);
}

}
