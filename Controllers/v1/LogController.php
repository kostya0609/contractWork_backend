<?php
namespace App\Modules\ContractWork\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Modules\ContractWork\Model\Log;
use Illuminate\Http\Request;

class LogController extends Controller {
    public function get(Request $request){

        if(!$request->contract_id)
            return response()->json([
                'success'   => false,
                'message'   => 'Возникла ошибка, нет contract_id',
            ]);

        $logModels = Log::where('contract_id','=',$request->contract_id)->with('user')->orderBy('id', 'desc')->get();

        $data = $logModels->map(function($item){
            return [
                'date' => $item->date,
                'event' => $item->event,
                'user' =>  [
                    'link'=>"https://bitrix.bsi.local/company/personal/user/{$item->user->ID}/",
                    'photo'=> "https://bitrix.bsi.local/".$item->user->getPhoto()
                ]
            ];
        });

        return response()->json([
            'success'   => true,
            'data'      => $data,
            'message'   => 'OK',

        ]);

    }

}
