<?php

namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Model\Role;
use App\Modules\ContractWork\Model\User;
use App\Modules\ContractWork\Action\v1\RoleAction;

use Illuminate\Http\Request;

class RoleController{
    public function add(Request $request){
        if(!$request->user_id || !$request->role_id){
            return response()->json(['success' => false, 'message' => 'Возникла ошибка нет user_id или role_id']);
        }

        $current_roles = User::find($request->user_id)->roles()->where('role_id', '=', $request->role_id)->get()->toArray();

        if($current_roles)
            return response()->json(['success' => false, 'message' => 'Возникла ошибка. Пользователь с такой ролью уже существует.']);

        $role = Role::find($request->role_id);
        $role->users()->attach($request->user_id);

        return response()->json([
            'success'     => true,
            'data'        => [],
            'notify'      => [
                'title'    => 'Создание нового пользователя',
                'message'  => "Пользователь создан",
                'type'     => 'success',
                'duration' => 3000,
            ]
        ]);
    }

    public function delete(Request $request){
        if(!$request->user_id || !$request->role_id){
            return response()->json(['success' => false,'message' => 'Возникла ошибка нет user_id или role_id']);
        }
        $role = Role::find($request->role_id);

        if($role->name == 'additional'){

            $userModel = User::find($request->user_id);
            $userModel->additionalRightsUsers()->sync([]);
            $userModel->additionalRightsDepartments()->sync([]);
        }

        $role->users()->detach($request->user_id);

        return response()->json([
            'success'     => true,
            'data'        => [],
            'notify'      => [
                'title'    => 'Удаление пользователя',
                'message'  => "Пользователь удален",
                'type'     => 'success',
                'duration' => 3000,
            ]
        ]);
    }

    public function list(){
        $data = RoleAction::list();
        return response()->json([
            'success'     => true,
            'data'        => $data,
        ]);
    }

    public function get(){
        return response()->json(['success' => true, 'data' => RoleAction::get()]);
    }

}
