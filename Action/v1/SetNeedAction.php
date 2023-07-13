<?php

namespace App\Modules\ContractWork\Action\v1;

use App\Modules\ContractWork\Model\NeedAction;

class SetNeedAction{

    public static function update($contract_id, $users_id){

        $usersNeedActions = NeedAction::where('contract_id', $contract_id)->get()->toArray();

        foreach ($usersNeedActions as $key => $userAction){

            $key = array_search($userAction['user_id'],  $users_id);

            if(!$key)
                NeedAction::where([['user_id','=',$userAction['user_id']],['contract_id', '=', $userAction['contract_id']]])->delete();
        }

        foreach ($users_id as $user){

            $res = NeedAction::where([['user_id','=', $user], ['contract_id', '=',$contract_id]])->get();

            if(!$res->count()){
                NeedAction::insert([ 'user_id' => $user, 'contract_id' => $contract_id]);
            }
        }
    }

}
