<?php

namespace App\Modules\ContractWork\Action\v1;

use App\Modules\ContractWork\Model\CompanyType;
use App\Modules\ContractWork\Model\ContractDirection;
use App\Modules\ContractWork\Model\ContractType;
use App\Modules\ContractWork\Model\ContrAgent;
use App\Modules\ContractWork\Model\Organization;
use App\Modules\ContractWork\Model\User;
use App\Modules\ContractWork\Model\Department;


class ContractAction{
    const   FILE_TYPES = [
        'contract'  => 'contract_file',
        'addition'  => 'addition_file',
        'annex'     => 'annex_file',
        'note'      => 'note_file',
        'info'      => 'info_file',
        'about'     => 'about_file',
        'extract'   => 'extract_file',
        'report'    => 'report_file',
        'agree'     => 'agree_file',
        'vicarious' => 'vicarious_file',
        'form'      => 'form_file',
        'others'    => 'others_file'
    ];

    public static function  getContractFile($model_files, $type){
        $files = [
            'contract_file'  => [],
            'addition_file'  => [],
            'annex_file'     => [],
            'note_file'      => [],
            'info_file'      => [],
            'about_file'     => [],
            'extract_file'   => [],
            'report_file'    => [],
            'agree_file'     => [],
            'vicarious_file' => [],
            'form_file'      => [],
            'others_file'    => [],
        ];

        foreach ($model_files as $file){
            $str = $file->id.',\''.$file->translated_name.'.' . $file->type_file . '\'';

            if ($type === 'link')
                $files[ self::FILE_TYPES[$file['type']] ] [] = '<span class="link_download_files"><a onclick="contractWorkLoadFile('.$str.')" href="#'.$file->id.'">'.$file->original_name.'</a></span>';
                    else
                        $files[ self::FILE_TYPES[$file['type']] ] [] = ['id' => $file['id'], 'name' => $file['original_name'], 'type' => $file['type_file']];
        };

        return $files;
    }

    public static function setContract($model, $data){
        if($data['contract_type_id'])       $model->contract_type_id      = $data['contract_type_id'];
        if($data['organization_id'])        $model->organization_id       = $data['organization_id'];
        if($data['department_id'])          $model->department_id         = $data['department_id'];
        if($data['contragent_id'])          $model->contragent_id         = $data['contragent_id'];
        if($data['company_type_id'])        $model->company_type_id       = $data['company_type_id'];
        if($data['responsible_id'])         $model->responsible_id        = $data['responsible_id'];
        if($data['signatory_id'])           $model->signatory_id          = $data['signatory_id'];
        if($data['content'])                $model->content               = $data['content'];
        $model->save();

        if($data['looker_ids'])             $model->lookers()->sync($data['looker_ids']);

        return $model;
    }

    public static function listGrid($models, $user_id){
        return $models
            ->get()
            ->map(function($item) use ($user_id){
                $contract_type      = ContractType::find($item->contract_type_id)->type;
                $organization       = Organization::find($item->organization_id)->name;
                $department         = Verifications::userDepartment($item->responsible_id)->NAME;
                $contragent         = ContrAgent::find($item->contragent_id)->name;
                $company_type       = CompanyType::find($item->company_type_id)->type;
                $responsible        = User::find($item->responsible_id)->full_name;
                $lookers            = $item->lookers->map(function($looker){
                    return [
                      'value' => $looker->full_name
                    ];
                });
                $signatory          = User::find($item->signatory_id)->full_name;

//                $main_files     = [];
//                $comment_files  = [];
//
//                foreach ($item->files as $file){
//                    if($file['type'] === 'main'){
//                        $str = $file->id.',\''.$file->translated_name.'.' . $file->type_file . '\'';
//                        $main_files [] = [
//                            'value'  => [
//                                'params' => [
//                                    'class'   => 'link_download_files',
//                                    'href'    => '#' . $file->id,
//                                    'onclick' => 'contractWorkLoadFile('.$str.')',
//                                ],
//                                'tag'    => 'a',
//                                'value'  => $file->original_name . '.' . $file->type_file,
//                            ],
//                        ];
//                    }
//
//                    if($file['type'] === 'comment'){
//                        $str = $file->id.',\''.$file->translated_name.'.' . $file->type_file . '\'';
//                        $comment_files [] = [
//                            'value'  => [
//                                'params' => [
//                                    'class'   => 'link_download_files',
//                                    'href'    => '#' . $file->id,
//                                    'onclick' => 'contractWorkLoadFile('.$str.')',
//                                ],
//                                'tag'    => 'a',
//                                'value'  => $file->original_name . '.' . $file->type_file,
//                            ],
//                        ];
//                    }
//                };

                return [
                    'id'                    => [['value' => $item->id]],
                    'status'                => [['value' => Translate::translate($item->status)]],
                    'status_eng'            => [['value' => $item->status]],
                    'contract_type'         => [['value' => $contract_type]],
                    'organization'          => [['value' => $organization]],
                    'department'            => [['value' => $department]],
                    'contragent'            => [['value' => $contragent]],
                    'company_type'          => [['value' => $company_type]],
                    'responsible'           => [['value' => $responsible]],
                    'lookers'               => $lookers,
                    'signatory'             => [['value' => $signatory]],
//                    'main_files'            => $main_files,
//                    'comment_files'         => $comment_files,
                    'date_created'          => [['value' => $item->created_at->format('d.m.Y')]],
                    'full_access'           => [['value' => Verifications::checkFullAccess($user_id, $item->responsible_id)]],
                ];
            });
    }

    public static function detailContract($model, $user_id){

        $contract_type      = ContractType::find($model->contract_type_id)->type;

        $organization       = Organization::find($model->organization_id)->name;

        $department         = Verifications::userDepartment($model->responsible_id)->NAME;

        $contragent         = ContrAgent::find($model->contragent_id)->name;

        $company_type       = CompanyType::find($model->company_type_id)->type;

        $responsible        = User::find($model->responsible_id)->full_name;

        $lookers            = $model->lookers->map(function($looker){
            return $looker->full_name;
        });

        $signatory    = User::find($model->signatory_id)->full_name;

        $files = self::getContractFile($model->files, 'link');

//        $files = [
//            'contract_file'  => [],
//            'addition_file'  => [],
//            'annex_file'     => [],
//            'note_file'      => [],
//            'info_file'      => [],
//            'about_file'     => [],
//            'extract_file'   => [],
//            'report_file'    => [],
//            'agree_file'     => [],
//            'vicarious_file' => [],
//            'form_file'      => [],
//            'others_file'    => [],
//        ];
//
//
//        foreach ($model->files as $file){
//            $str = $file->id.',\''.$file->translated_name.'.' . $file->type_file . '\'';
//
//            $files[ self::FILE_TYPES[$file['type']] ] [] = '<span class="link_download_files"><a onclick="contractWorkLoadFile('.$str.')" href="#'.$file->id.'">'.$file->original_name.'</a></span>';
//        };

        $contract_data = [
            ['name' => 'Тип договора',                          'value' => [$contract_type]],
            ['name' => 'Организация',                           'value' => [$organization]],
            ['name' => 'Подразделение',                         'value' => [$department]],
            ['name' => 'Контрагент',                            'value' => [$contragent]],
            ['name' => 'Вид правовой собственности контрагента','value' => [$company_type]],
            ['name' => 'Инициатор',                             'value' => [$responsible], 'eng_name' => 'responsible'],
            ['name' => 'Наблюдатели',                           'value' => $lookers],
            ['name' => 'Подписант оригинальной подписью',       'value' => [$signatory]],
            ['name' => 'Содержание документа',                  'value' => [$model->content]],
            ['name' => 'Дата создания',                         'value' => [$model->created_at->format('d.m.Y')], 'eng_name' => 'date']
        ];

        $files = [
            ['name' => 'Договор',                         'value' => $files['contract_file']],
            ['name' => 'Доп. соглашение',                 'value' => $files['addition_file']],
            ['name' => 'Приложение к договору',           'value' => $files['annex_file']],
            ['name' => '',                                'value' => ''],
            ['name' => 'Пояснительная записка',           'value' => $files['note_file']],
            ['name' => 'Сведения о контрагенте(в формате PDF), полученные на официальном сайте ИФНС', 'value' => $files['info_file']],
            ['name' => 'Пояснения контрагента',           'value' => $files['about_file']],
            ['name' => 'Выписка из ЕГРЮЛ/ЕГРИП',          'value' => $files['extract_file']],
            ['name' => 'Отчет по форме 1(Бухгалтерский баланс) и Форме 2(Отчет о прибылях и убытках) за два последних года, для оценки надежности контрагента', 'value' => $files['report_file']],
            ['name' => 'Разрешение на осуществление деятельности(если предмет договора касается деятельности, подлежащей лицензированию)', 'value' => $files['agree_file']],
            ['name' => 'Копия доверенности, заверенная печатью организации/ИП, копия страниц №№2,3 паспорта представителя', 'value' => $files['vicarious_file']],
            ['name' => 'Анкета оптового клинта для получения товарного кредита(см. Приложение к "ИП предоставление скидок и товарного кредита при работе с оптовыми клиентами")', 'value' => $files['form_file']],
            ['name' => 'Дополнительные файлы  ',          'value' => $files['others_file']],

        ];

        $lawyer = [];

        $lawyer['data'] = $model->lawyerComments->map(function($comment){
            return [
              'id'      => $comment->id,
              'FIO'     => User::find($comment->user_id)->full_name,
              'comment' => $comment->comment,
              'date'    => $comment->date->format('d.m.Y'),
              'files'   => $comment->files->map(function($file){
                  return [
                      '<span class="link_download_files"><a onclick="contractWorkLoadFile('.$file->id.',\''.$file->translated_name.'.' . $file->type_file . '\''.')" href="#'.$file->id.'">'.$file->original_name.'</a></span>'
                  ];
              })
            ];
        });

        $protocols = [];

        $protocols['data'] = $model->protocols->map(function($protocol){
            return [
                'id'      => $protocol->id,
                'version' => $protocol->version,
                'FIO'     => User::find($protocol->user_id)->full_name,
                'comment' => $protocol->comment,
                'date'    => $protocol->date->format('d.m.Y'),
                'files'   => $protocol->files->map(function($file){
                    return [
                        '<span class="link_download_files"><a onclick="contractWorkLoadFile('.$file->id.',\''.$file->translated_name.'.' . $file->type_file . '\''.')" href="#'.$file->id.'">'.$file->original_name.'</a></span>'
                    ];
                })
            ];
        });

        $scans = [];

        $scans['data'] = $model->scans->map(function($scan){
            return [
                'id'      => $scan->id,
                'FIO'     => User::find($scan->user_id)->full_name,
                'comment' => $scan->comment,
                'date'    => $scan->date->format('d.m.Y'),
                'files'   => $scan->files->map(function($file){
                    return [
                        '<span class="link_download_files"><a onclick="contractWorkLoadFile('.$file->id.',\''.$file->translated_name.'.' . $file->type_file . '\''.')" href="#'.$file->id.'">'.$file->original_name.'</a></span>'
                    ];
                })
            ];
        });

        return [
            'status'          => $model->status,
            'contract'        => $contract_data,
            'files'           => $files,
            'full_access'     => Verifications::checkFullAccess($user_id, $model->responsible_id),
            'lawyer'          => $lawyer,
            'protocols'       => $protocols,
            'count_protocols' => count($protocols['data']),
            'scans'           => $scans,
            'waiting_edit'    => $model->waiting_edit,
            'responsible_id'  => $model->responsible_id,
        ];

    }
}
