<?php

namespace App\Modules\ContractWork\Action\v1;
use Illuminate\Support\Facades\Http;

class Notification{
    public static function send($userId, $contract_id, $status, $type){
        $url = "[URL=https://bitrix.bsi.local/contract-work/contracts/detail/$contract_id]Договор[/URL]";

        $message = [
            'responsible' => [
                'contract_created'             => ' возвращен на подготовку.',
                'lawyer_check'                 => ' находится на юридической проверке.',
                'correction_primary_data'      => ' возвращен юристом на корректировку первичных данных.',
                'correction_after_approval'    => ' передан на корректировку после внутреннего согласования.',
                'contract_cancel'              => ' не утвержден руководителем и принято решение прекратить дальнейшую работу по документу.',
                'protocol_contragent_approval' => ' передан согласование протоколов разногласия с контрагентом. Требуются действия инициатора!',
                'contract_active'              => ' переведен в действие. Работа с документом завершена'
            ],
            'lawyer'       => [
                'lawyer_check'                 => ' передан на юридическую проверку.',
                'correction_after_approval'    => ' передан на корректировку после внутреннего согласования.',
                'transfer_originals_to_lawyer' => ' ожидает передачи оригиналов юристу.'
            ]
        ];


        Http::withOptions(['verify' => false])->withHeaders([
            'Authorization' => 'Basic cmVzdDpSRVNUcmVzdCEhIQ==',
        ])->get('https://bitrix.bsi.local/local/rest/message/add.php', [
            'to' => $userId,
            'from' => 9455,//Системный пользователь Битрикс
            'message' => $url . $message[$type][$status]
        ]);
    }

}
