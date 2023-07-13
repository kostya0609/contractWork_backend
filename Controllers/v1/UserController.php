<?php

namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController{
    public function get(Request $request){

        if(!is_numeric($request->user_id))
            return response()->json(['success' => false, 'message' => 'Возникла ошибка с user_id']);
        $user = User::with(['roles'])->find($request->user_id);

        $roles = ($user->roles->isNotEmpty())?$user->roles->pluck('name'):[];

        return response()->json([
            'success'    => true,
            'data'      => [
                'user'    => [
                    'id'  => $user->ID,
                    'FIO' => $user->full_name,
                ],
                'roles'    => $roles,
            ]
        ]);
    }
}
