<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalaryController extends Controller
{
	
	public function salary_index(){
		
		return view('salary.index');
		
	}
	
	
	public function salary_store(Request $request) {
    try {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'userid' => 'required|exists:users,id',
            'salary_type' => 'required|string',
            'salary_amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Retrieve validated input data
        $userid = $request->userid;
        $salary_type = $request->salary_type;
        $salary_amount = $request->salary_amount;

        // Start transaction
        DB::beginTransaction();

        // Update user wallet
        $userdata = DB::table('users')->where('id', $userid)->increment('wallet', $salary_amount);

        if ($userdata) {
            // Insert into wallet history
            DB::table('wallet_history')->insert([
                'userid' => $userid,
                'description' => $salary_type,
                'amount' => $salary_amount,
                'subtypeid' => 13,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Commit transaction
            DB::commit();

            return redirect()->back()->with('success', 'Salary added Successfully');
        } else {
            // Rollback transaction if user update fails
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update salary');
        }
    } catch (\Exception $e) {
        // Rollback transaction in case of exception
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
	
	public function salary_list(){
	    
		$salary_list = DB::table('wallet_history')
    ->join('users', 'users.id', '=', 'wallet_history.userid')
    ->where('wallet_history.subtypeid', 13)
    ->select('wallet_history.*', 'users.mobile as mobile')
    ->get();
		
		return view ('salary.salary_list')->with('salary_list',$salary_list);
		
	}
	

}