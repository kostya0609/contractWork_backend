<?php

namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Action\v1\RoleAction;
use App\Modules\ContractWork\Model\Role;
use App\Modules\ContractWork\Model\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdditionalRightsController extends \Illuminate\Routing\Controller{

    public function setAdditionalRights(Request $request){
        if(!$request->user_id || !$request->client_id || !is_array($request->departments) || !is_array($request->individuals))
            return response()->json(['success' => false, 'message' => 'Возникла ошибка, нет user_id или client_id иил нет departments или нет individuals']);

        $user = User::find($request->client_id);

        $user->additionalRightsUsers()->sync($request->individuals);
        $user->additionalRightsDepartments()->sync($request->departments);

        return response()->json(['success' => true, 'message'=> 'Права успешно установлены']);

    }

    public function listAdditionalRights(Request $request){
        $role = Role::where('name', '=', 'additional')->first();
        $users = $role->users;
        $data = $users->map(function($item)
        {
            return [
                $item->full_name    => [
                    'users'         => $item->additionalRightsUsers->pluck('full_name'),
                    'departments'   => $item->additionalRightsDepartments->pluck('NAME'),
                ]
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getAdditionalRights(Request $request){
        if(!$request->user_id || !$request->client_id)
            return response()->json(['success' => true, 'message' => 'Возникла ошибка, нет user_id или client_id']);
        return response()->json(['success' => true, 'data' => RoleAction::getAdditionalRight($request->client_id)]);
    }

}
