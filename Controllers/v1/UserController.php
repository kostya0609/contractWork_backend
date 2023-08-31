<?php

namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Action\v1\Verifications;
use App\Modules\ContractWork\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController{
    public function get(Request $request){

        if(!is_numeric($request->user_id))
            return response()->json(['success' => false, 'message' => 'Возникла ошибка с user_id']);
        $user = User::with(['roles'])->find($request->user_id);

        $roles = ($user->roles->isNotEmpty())?$user->roles->pluck('name'):[];

        $department = Verifications::userDepartment($request->user_id);

        return response()->json([
            'success'    => true,
            'data'      => [
                'user'    => [
                    'id'         => $user->ID,
                    'FIO'        => $user->full_name,
                    'department' => ['id' => $department->ID ?: null, 'name' => $department->NAME ?: null],
                ],
                'roles'    => $roles,
            ]
        ]);
    }
}
