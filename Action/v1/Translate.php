<?php
namespace App\Modules\ContractWork\Action\v1;

use Illuminate\Support\Arr;

class Translate {
    const MAP = [
        'contract_created'              => 'На подготовке',
        'manager_approval'              => 'На согласовании у руководителя',
        'lawyer_check'                  => 'На юридической проверке',
        'correction_primary_data'       => 'На корректировке первичных данных',
        'correction_after_approval'     => 'На корректировке после согласования',
        'contract_internal_approval'    => 'На внутреннем согласовании условий договора',
        'protocol_contragent_approval'  => 'На согласовании протокола с контрагентом',
        'wait_contragent_answer'        => 'На ожидании обратной связи от контрагента',
        'transfer_originals_to_lawyer'  => 'На передаче оригиналов юристу',
        'contract_signing'              => 'На подписании договора',
        'contract_active'               => 'Договор в действии',
        'contract_cancel'               => 'Договор отменен',
    ];

    public static function translate($value):string{
        return self::MAP[$value];
    }

    public static function exists($value):string{
        $exists = Arr::exists(self::MAP, $value);
        return $exists;
    }
}
