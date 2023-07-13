<?php

namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Model\ContrAgent;
use App\Modules\ContractWork\Model\Organization;
use App\Modules\ContractWork\Model\User;
use App\Modules\ContractWork\Model\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController{

    public function user(Request $request){
        $to_str = explode(' ',trim($request->q));
        $result = User::where('ACTIVE','Y')->where('ID', '!=', 1)->where(function ($query) use ($to_str){
            foreach ($to_str as $word){
                if(!empty($word)){
                    $query->where(DB::raw('CONCAT_WS(LAST_NAME, " ", NAME, " ", SECOND_NAME)'),'like','%'.$word.'%');
                }
            }
        })
            ->limit(10)
            ->get();
        $data = [];
        foreach ($result as $el){
            if($el->ACTIVE == 'Y'){
                $data[] = ['value' => $el->ID, 'label' => $el->full_name];
            } else {
                $data[] = ['value' => $el->ID, 'label' => $el->full_name . ' (Уволен)'];
            }
        }

        return response()->json([
            'success'     => true,
            'data'        => $data
        ]);

    }

    public function contragent(Request $request){
        $result = ContrAgent::where('name','like','%'.trim($request->q).'%' )
            ->limit(10)
            ->get();
        $data = [];
        foreach ($result as $el){
            $data[] = ['value' => $el->id, 'label' => $el->name . ', ИНН - '. $el->inn];
        }

        return response()->json([
            'success'     => true,
            'data'        => $data
        ]);
    }

    public function organization(Request $request){
        $result = Organization::where('name','like','%'.trim($request->q).'%' )
            ->limit(10)
            ->get();
        $data = [];
        foreach ($result as $el){
            $data[] = ['value' => $el->id, 'label' => $el->name . ', ИНН - '. $el->inn];
        }

        return response()->json([
            'success'     => true,
            'data'        => $data
        ]);
    }

    public function department(Request $request){
        $to_str = explode(' ',trim($request->q));
        $result = Department::where('IBLOCK_ID','=' ,5)
            ->where(function($query) use ($to_str){
                foreach($to_str as $word){
                    if(!empty($word)){
                        $query->where('NAME', 'like', '%'.$word.'%');
                    }
                }
            })
            ->limit(20)
            ->get();

        $data = $result->map(function($el){
            return [
                'value' => $el->ID,
                'label' => $el->NAME
            ];
        });

        return response()->json([
            'success'     => true,
            'data'        => $data
        ]);
    }

}
