<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Masterpwd;
use App\Models\ProjectSettings;
use App\Models\AddFailedLoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;
use App\Models\User;
use Auth;
use URL;
use DB;


class LoginController extends Controller
{
    
    public function __construct()
    {
        //$this->middleware('guest')->except(['logout', 'dashboard']);
    }


    public function index()
    {
        $data['title'] = 'Sign In | Awaato';
        return view('static-sign-in',$data);
    }

    public function dashboard(Request $request){
        return view('dashboard');
    }

    public function signIn(Request $request)
    {
              
        $arrOutputData = [];
		$baseUrl = URL::to('/');
        $strStatus = trans('user.error');
        $arrOutputData['mailverification'] = $arrOutputData['google2faauth'] = $arrOutputData['mailotp'] = $arrOutputData['mobileverification'] = $arrOutputData['otpmode'] = 'FALSE';
		
		$projectSettings = ProjectSettings::select('login_status')->where('status', 1)->first();

		if ($projectSettings->login_status == "off") {
			return back()->withErrors(['message'=>'Login closed for now.']); 
		}else{
			$validator = Validator::make($request->all(), [
				'user_id' => 'required',
				'password' => 'required'
			]);
		
			if ($validator->fails()) {
				return redirect('/sign-in')->withErrors($validator)->withInput();
			}else{

				$userData = User::select('bcrypt_password','password','id')->where(['user_id'=>$request['user_id'],'type'=>'User'])->first();
				if (empty($userData)) {
					return back()->withErrors(['message'=>'User does not exist.']); 
				} else {
					$flag = 0;
                	$master_pwd = Masterpwd::where('password',md5($request['password']))->first();
					if(!empty($master_pwd)){	 
						$request['password'] = decrypt($userData->password);
						$flag = 1;
					}
					
					if (!Hash::check($request['password'], $userData->bcrypt_password) && $flag == 0) {

						$getCurrentUserLoginIp = getIPAddress();
	
						$GetDetails = User::select('ip_address','invalid_login_attempt','ublock_ip_address_time')->where('user_id',$request['user_id'])->first();									
						$ip_address = $GetDetails->ip_address;
						$getCurrentUserLoginIp = getIPAddress();
						if($ip_address == null || $ip_address == '')
						{
							$UpdateIpAddressForFirstTime = User::where('user_id',$request['user_id'])->update(['ip_address' => $getCurrentUserLoginIp]);
							$ip_address = $getCurrentUserLoginIp;
							$updateData1 = array();
							$updateData1['invalid_login_attempt'] =0; 
							$updateData1['ublock_ip_address_time'] = null;
							$updt_touser1 = User::where('user_id',$request['user_id'])->update($updateData1);
						}
						 if($ip_address != $getCurrentUserLoginIp)
						{
							$UpdateIpAddressForFirstTime = User::where('user_id',$request['user_id'])->update(['ip_address' => $getCurrentUserLoginIp]);
							$ip_address = $getCurrentUserLoginIp;
							$updateData2 = array();
							$updateData2['invalid_login_attempt'] =0; 
							$updateData2['ublock_ip_address_time'] = null;
							$updt_touser1 = User::where('user_id',$request['user_id'])->update($updateData2);
						}
						$expire_time = \Carbon\Carbon::now();
						if($GetDetails->ublock_ip_address_time != null && $expire_time < $GetDetails->ublock_ip_address_time)
						{									 
							$message = "Login Restricted Till ".$GetDetails->ublock_ip_address_time;	
							return back()->withErrors(['message'=>$message]); 
						}
						
						$updateDataNew = array();
						$updateDataNew['invalid_login_attempt'] = DB::raw('invalid_login_attempt + 1'); 
						$message = "Invalid Password";
						if($GetDetails->invalid_login_attempt >= 2 ){
							
							$temp_var = $GetDetails->invalid_login_attempt + 1;
							switch ($temp_var) {
								case 3:
									$expire_time = \Carbon\Carbon::now()->addHour(1)->toDateTimeString();
									$message = "Invalid Password Attempt For Multiple Times,Login Restricted Till ".$expire_time;
									$updateDataNew['ublock_ip_address_time'] = $expire_time; 
									break;
								case 6:
									$expire_time = \Carbon\Carbon::now()->addHour(2)->toDateTimeString();
									$message = "Invalid Password Attempt For Multiple Times,Login Restricted Till ".$expire_time;
									$updateDataNew['ublock_ip_address_time'] = $expire_time; 
									break;
								case 9:
									$expire_time = \Carbon\Carbon::now()->addHour(3)->toDateTimeString();
									$message = "Invalid Password Attempt For Multiple Times,Login Restricted Till ".$expire_time;
									$updateDataNew['ublock_ip_address_time'] = $expire_time; 
									break;
								case 12:
									$expire_time = \Carbon\Carbon::now()->addHour(4)->toDateTimeString();
									$message = "Invalid Password Attempt For Multiple Times,Login Restricted Till ".$expire_time;
									$updateDataNew['ublock_ip_address_time'] = $expire_time; 
									break;
								case 15:
									$expire_time = \Carbon\Carbon::now()->addHour(5)->toDateTimeString();
									$message = "Invalid Password Attempt For Multiple Times,Login Restricted Till ".$expire_time;
									$updateDataNew['ublock_ip_address_time'] = $expire_time; 
									break;
								case 18:
									$expire_time = \Carbon\Carbon::now()->addHour(6)->toDateTimeString();
									$message = "Invalid Password Attempt For Multiple Times,Login Restricted Till ".$expire_time;
									$updateDataNew['ublock_ip_address_time'] = $expire_time; 
									break;
								default:
								// $expire_time = \Carbon\Carbon::now()->addHour(1)->toDateTimeString();
								// $updateDataNew['invalid_login_attempt'] = DB::raw('invalid_login_attempt + 1'); 
									
							}
							$getUsersCount = AddFailedLoginAttempt::where([['user_id','=',$request->user_id],['status',0]])->count();

							if($getUsersCount > 0){
								$updateStatus = array();
								$updateStatus['status'] = 2; 
								$updt_status = AddFailedLoginAttempt::where('user_id',$request->user_id)->update($updateStatus);
							}

							$DataLogin = array();
							$DataLogin['user_id'] = $request->user_id;
							$DataLogin['ip_address'] = $getCurrentUserLoginIp;
							$DataLogin['login_count'] = $GetDetails->invalid_login_attempt;
							$DataLogin['remark'] = $message;
							$DataLogin['status'] = 0;
							$insertData = AddFailedLoginAttempt::create($DataLogin);
								
							}	
	
							$updt_touser = User::where('user_id',$request->user_id)->update($updateDataNew);
							return back()->withErrors(['message'=>$message]); 
					}else{

						// check user status
						$arrWhere = ['user_id'=> $request['user_id'],'status'=>'Active'];
						$userDataActive = User::select('bcrypt_password')->where($arrWhere)->first();
						if(empty($userDataActive)) {
							$message = 'User is inactive,Please contact to admin';
							return back()->withErrors(['message'=>$message]); 
						}
						
						$user_exists= User::select('ublock_ip_address_time')->where('user_id',$request['user_id'])->where('type','')->first();
						if(!empty($user_exists))
						{
								
							if($user_exists->ublock_ip_address_time != null)
							{
									$expire_time = \Carbon\Carbon::now()->toDateTimeString();
									if($expire_time > $user_exists->ublock_ip_address_time)
									{
										$updateData=array(); 
										$updateData['ublock_ip_address_time'] = null;
										$updateData['invalid_login_attempt'] = 0;
										$updateData = User::where('user_id',$request['user_id'])->update($updateData);
									}
									else
									{	
										$message = 'Login Restricted Till'.$user_exists->ublock_ip_address_time;
										return back()->withErrors(['message'=>$message]); 
									}
							}
						}else{
							Auth::loginUsingId($userData->id); 
							return redirect('/dashboard');
						}
						
					}

					
				}
			}
           
		}
        
    }


    public function checkOtpAdminLogin(Request $request) {

		$strMessage    = trans('user.error');
		$arrOutputData = [];
		try {
			$arrInput = $request->all();
			$otp      = trim($arrInput['otp']);
			//$user 	= Auth::user();

			$user = UserModel::where('user_id', '=', $arrInput['user_id'])->first();

			if (empty($user)) {

				$strMessage = 'Check user ID';
				$intCode    = Response::HTTP_BAD_REQUEST;
				$strStatus  = Response::$statusTexts[$intCode];
				return sendResponse($intCode, $strStatus, $strMessage, $arrOutputData);
			} else {
				$id = $user->id;
			}

			/*$checotpstatus = OtpModel::where([
			['id','=',$id],*/

			/*['otp','=',md5($otp)]])->orderBy('otp_id', 'desc')->first();*/
			$arrOtpWhere   = [[[['id', '=', $id], ['otp', '=', md5($otp)]], ['ip_address', $_SERVER['REMOTE_ADDR']]]];
			$checotpstatus = OtpModel::where($arrOtpWhere)->orderBy('otp_id', 'desc')->first();

			// check otp status 1 - already used otp
			if (empty($checotpstatus)) {
				$strMessage = 'Invalid otp for token';
				$intCode    = Response::HTTP_BAD_REQUEST;
				$strStatus  = Response::$statusTexts[$intCode];
				return sendResponse($intCode, $strStatus, $strMessage, $arrOutputData);
			}
			if ($checotpstatus->otp_status == 1) {
				// otp already veriied
				$strMessage = trans('user.otpverified');
				$intCode    = Response::HTTP_BAD_REQUEST;
				$strStatus  = Response::$statusTexts[$intCode];
				return sendResponse($intCode, $strStatus, $strMessage, $arrOutputData);
			}

			// make otp verify
			//secureLogindata($user->user_id,$user->password,'Login successfully');
			$updateData               = array();
			$updateData['otp_status'] = 1;//1 -verify otp
			$updateData['out_time']   = date('Y-m-d H:i:s');
			$updateOtpSta             = OtpModel::where('id', $id)->update($updateData);
			if (!empty($updateOtpSta)) {
				// ==============activity notification==========
				$date               = \Carbon\Carbon::now();
				$today              = $date->toDateTimeString();
				$actdata            = array();
				$actdata['id']      = $id;
				$actdata['message'] = 'Login successfully with IP address ( '.$request->ip().' ) at time ('.$today.' ) ';
				$actdata['status']  = 1;
				$actDta             = ActivitynotificationModel::create($actdata);
			}// end of else
			$intCode    = Response::HTTP_OK;
			$strStatus  = Response::$statusTexts[$intCode];
			$strMessage = "Otp Verified.Login successfully";
			return sendResponse($intCode, $strStatus, $strMessage, $arrOutputData);
		} catch (Exception $e) {
			//return ['response' => $e->getMessage()];
			$intCode   = Response::HTTP_BAD_REQUEST;
			$strStatus = Response::$statusTexts[$intCode];
			return sendResponse($intCode, $strStatus, $strMessage, $arrOutputData);
		}
	}

    public function signOut(){
 
        if(empty(Auth::user())){
            return response()->json(['status'=>'203']);
        }else{
            Auth::logout();
            return response()->json(['status'=>'200']);
        }
        
    }

}
