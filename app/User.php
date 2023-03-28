<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tbl_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','user_id','fullname', 'email','type','google2fa_secret','remember_token','status'
    ];
    //protected $primaryKey = 'id';
    public $timestamps = false; 
    
    
     public static function loginValidationRules(){
        $arrRulesData  = [];
        $arrRulesData['arrMessage'] = array(
            'user_id.required'    =>'Username Required',
            'password.required'  =>'Password Required',
        );

        $arrRulesData['arrRules'] = array(
            'user_id'      => 'required',
            'password'       =>  'required',
        );
        return $arrRulesData;

    }

    public static function registrationValidationRules(){
        $arrRulesData  = [];
        $arrRulesData['arrMessage'] = array(
         'email.email' => 'Email should be in format abc@abc.com',
         'fullname.regex' => 'Special characters not allowed in fullname');
        /*'password.regex' => 'Pasword contains first character letter, contains atleast 1 capital letter,combination of alphabets,numbers and special character i.e. ! @ # $ *',*/
        // |regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{7,}/
        // |regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{7,}/
        $arrRulesData['arrRules'] = array(
            //'fullname'      => 'required|min:3|max:30|regex:/^[A-Za-z0-9 _]*[A-Za-z0-9][A-Za-z0-9 _]*$/',
            'email'         => 'required|email',
            // 'email'         => 'required|email|unique:tbl_users',
            //'password'      => 'required|min:6|max:30',
            //'password_confirmation' => 'required|min:6|max:30|same:password',
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6'
           // 'ref_user_id'   => 'required',
            //'mobile'        => 'required|numeric',
            //'position' => 'required|',
            //'mode' => 'required',
        );
        return $arrRulesData;

    }
    /**
     * Get the login user_id to be used by the controller.
     *
     * @return string
     */
    public static function username() {
        return 'user_id';
    }
     
}