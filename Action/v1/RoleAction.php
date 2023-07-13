<?php

namespace App\Modules\ContractWork\Action\v1;
use App\Modules\ContractWork\Model\Role;
use App\Modules\ContractWork\Model\User;

class RoleAction{
    public static function get(){
        return Role::all()
            ->map(function($item){
                return [
                    'value' => $item->id,
                    'label' => $item->note,
                    'name'  => $item->name
                ];
            });
    }

    public static function list(){

        return Role::all()->flatMap(function($role){
            return [
                $role->name =>
                    $role->users->map(function($user) use ($role)
                    {

                        $departments = $user->additionalRightsDepartments;
                        $individuals = $user->additionalRightsUsers;
                        return [
                            'role_id'     => $role->id,
                            'user_id'     => $user->ID,
                            'name'        => $user->full_name,
                            'departments' => ($departments)?$departments->map(function($item)
                            {
                                return [
                                    'id'            => $item->ID,
                                    'name'          => $item->NAME,
                                    'full_access'   => $item->pivot->full_access
                                ];
                            }):[],
                            'individuals' => ($individuals)?$individuals->map(function($item)
                            {
                                return [
                                    'id'            => $item->ID,
                                    'name'          => $item->full_name,
                                    'full_access'   => $item->pivot->full_access
                                ];
                            }):[],
                        ];
                    })
            ];
        });
    }

    public static function getAdditionalRight($client_id):array
    {
        $client =  User::select(['ID','NAME', 'LAST_NAME', 'SECOND_NAME'])
            ->with(['additionalRightsUsers:ID,NAME,LAST_NAME,SECOND_NAME', 'additionalRightsDepartments:ID,NAME'])
            ->find($client_id);
        return [
            'id'          => $client->ID,
            'name'        => $client->full_name,
            'individuals' => $client->additionalRightsUsers->map(function($item)
            {
                return [
                    'id'          => $item->ID,
                    'name'        => $item->full_name,
                    'full_access' => $item->pivot->full_access,
                ];
            }),
            'departments' => $client->additionalRightsDepartments->map(function($item)
            {
                return [
                    'id'          => $item->ID,
                    'name'        => $item->NAME,
                    'full_access' => $item->pivot->full_access,
                ];
            })
        ];
    }
}
