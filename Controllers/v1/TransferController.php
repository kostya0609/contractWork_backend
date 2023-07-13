<?php

namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Model\ContrAgent;
use App\Modules\ContractWork\Model\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TransferController{
    public function getContrAgents(){

        $response = Http::withBasicAuth('bitrix','78523')->timeout(600)
            ->get('http://c-it-s-1c/upp/hs/crm/customer', [
                //'customer' => '92e76e3e-2122-11ec-ae32-1458d057f898'
            ]);

        $data = substr($response, strpos($response, '[')); // убираем символ который до скобочки (спасибо 1С)
        $data = json_decode($data, 1);

        $total = count($data);
        $kol = 0;

        DB::beginTransaction();
        try {
            foreach ($data as $item){
                $contr_agent_model = new ContrAgent();
                $contr_agent_model->name           = $item['Контрагент']['Наименование'] ?: 'Нет данных от 1С';
                $contr_agent_model->full_name      = $item['НаименованиеПолное'] ?: 'Нет данных от 1С';
                $contr_agent_model->guid           = $item['Контрагент']['GUID'] ?: 'Нет данных от 1С';
                $contr_agent_model->email          = $item['АдресЭлектроннойПочты'] ?: null;
                $contr_agent_model->account_number = $item['БанковскийСчет'] ?: 'Нет данных от 1С';
                $contr_agent_model->parent_name    = $item['РодительскийКонтрагент'] ? $item['РодительскийКонтрагент']['Наименование'] : null;
                $contr_agent_model->parent_guid    = $item['РодительскийКонтрагент'] ? $item['РодительскийКонтрагент']['GUID'] : null;
                $contr_agent_model->fact_address   = $item['ФактАдресКонтрагента'] ?: 'Нет данных от 1С';
                $contr_agent_model->ur_address     = $item['ЮрАдресКонтрагента'] ?: 'Нет данных от 1С';
                $contr_agent_model->inn            = $item['ИНН'] ?: 'Нет данных от 1С';
                $contr_agent_model->kpp            = $item['КПП'] ?: 'Нет данных от 1С';
                $contr_agent_model->okopf          = $item['ОКОПФ'] ?: null;
                $contr_agent_model->okpo           = $item['ОКПО'] ?: null;
                $contr_agent_model->type           = $item['ЮрФизЛицо'] ?: null;
                $contr_agent_model->status         = $item['Статус'] ?: null;
                $contr_agent_model->trade_area     = $item['ОбщаяТорговаяПлощадь'] ?: null;
                $contr_agent_model->save();
                $kol++;
            };
            DB::commit();
            echo 'transfer OK. Скопировано ' . strval($kol) . ' записей контрагентов из '. strval($total);

        } catch (\Exception $e){
            DB::rollBack();
            echo $e->getMessage();
        }


    }

    public function getOrganizations(){

        $response = Http::withBasicAuth('bitrix','78523')->timeout(600)
            ->get('http://c-it-s-1c/upp/hs/crm/organizations', [
            ]);

        $data = substr($response, strpos($response, '[')); // убираем символ который до скобочки (спасибо 1С)
        $data = json_decode($data, 1);

        $total = count($data);
        $kol = 0;

        DB::beginTransaction();
        try {
            foreach ($data as $item){
                $organization_model = new Organization();
                $organization_model->name         = $item['Организация']['Наименование'] ?: 'Нет данных от 1С';
                $organization_model->guid         = $item['Организация']['GUID'] ?: 'Нет данных от 1С';
                $organization_model->direction    = $item['Направление'] ?: null;
                $organization_model->inn          = $item['ИНН'] ?: 'Нет данных от 1С';
                $organization_model->kpp          = $item['КПП'] ?: 'Нет данных от 1С';
                $organization_model->ur_address   = $item['ЮА'] ?: null;
                $organization_model->fact_address = $item['ФА'] ?: null;
                $organization_model->mail_address = $item['ПА'] ?: null;
                $organization_model->save();
                $kol++;
            };
            DB::commit();
            echo 'transfer OK. Скопировано ' . strval($kol) . ' записей организаций из '. strval($total);

        } catch (\Exception $e){
            DB::rollBack();
            echo $e->getMessage();
        }


    }
}
