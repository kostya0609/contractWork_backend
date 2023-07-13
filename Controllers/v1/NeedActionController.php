<?php

namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Model\NeedAction;
use Illuminate\Http\Request;
use App\Modules\ContractWork\Action\v1\SetNeedAction;

class NeedActionController{

    public function update(Request $request){

        $contract_id = $request->contract_id;

        $users_id    = $request->users_id ?: [];

        $users_id    = array_map(function ($el){
            return $el['user'];
        }, $users_id);

        SetNeedAction::update($contract_id, $users_id);

        return response()->json([
            'success'     => true,
        ]);
    }

    public function badge(Request $request){

        $res = NeedAction::where('user_id', $request->user_id)->get();
        $res = $res->count() ?: '';

        return response()->json([
            'success'     => true,
            'data'        => ['count' => $res]
        ]);
    }

}
