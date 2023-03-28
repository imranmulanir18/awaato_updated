<?php

namespace App\Traits;

use Illuminate\Http\Response as Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use DB;

trait UserTrait
{
    /*
    Author: Sujit Kapse
    Date: 2023-03-24
    Description: Get unique user id
    */
    public function getUniqueUserId()
    {
        $radUserId = substr(number_format(time() * rand(), 0, '', ''), 0, '8');

        $user_id = 'FX' . $radUserId;

        $RfCount1 = User::select('id')->where([['user_id', '=', $user_id]])->count('id');
        if ($RfCount1 == 0) {
            return $user_id;
        } else {
            $this->getUniqueUserId();
        }
    }
}
?>