<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Traits\UserTrait;

class RegisterController extends Controller
{
    
    use UserTrait;
    public function __construct()
    {
        
    }

    public function index()
    {
        $data['title'] = 'Sign Up | Awaato';
        return view('static-sign-up',$data);
    }

    public function signUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:tbl_users'],
            'sponser_id'=>['required'],
            'position'=> ['required'],           
            'password' => ['required', 'min:5', 'max:20'],
            'confirm_password' => ['required','same:password'],
            'agreement' => ['accepted']
        ]);
 
        if ($validator->fails()) {
            return redirect('/sign-up')->withErrors($validator)->withInput();
        }else{
            $request['bcrypt_password'] = Hash::make($request['password']);
            $request['password'] = encrypt($request['password']);			
            $request['fullname'] = $request['first_name'].' '.$request['last_name'];
            $request['type'] = $request['User'];           
            $user = User::create($request->except(['_token','agreement','first_name','last_name','confirm_password','sponser_id','sponser_name']));
            Auth::loginUsingId($user->id); 
            return redirect('/dashboard');
        }
                
    }

    public function getUserId(){

        $user_id =  $this->getUniqueUserId();
        if(!empty($user_id)){
            return response()->json(['status'=>'200','data'=>$user_id]);
        }else{
            return response()->json(['status'=>'203','data'=>'']);
        }        
    }

    public function getSponserId(Request $request){

        if(!empty($request['sponser_id'])){
            $sponser = User::where('user_id',$request['sponser_id'])->get(['fullname','id']);
            if(count($sponser)>0){
                return response()->json(['status'=>'200','data'=>$sponser[0]]);
            }else{
                return response()->json(['status'=>'203','data'=>'']);
            }            
        }else{
            return response()->json(['status'=>'203','data'=>'']);
        }
    }
}
