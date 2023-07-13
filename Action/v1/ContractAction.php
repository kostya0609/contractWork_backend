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
    public static function setContract($model, $data){
        if($data['contract_type_id'])       $model->contract_type_id      = $data['contract_type_id'];
        if($data['organization_id'])        $model->organization_id       = $data['organization_id'];
        if($data['contract_direction_id'])  $model->contract_direction_id = $data['contract_direction_id'];
        if($data['contragent_id'])          $model->contragent_id         = $data['contragent_id'];
        if($data['company_type_id'])        $model->company_type_id       = $data['company_type_id'];
        if($data['responsible_id'])         $model->responsible_id        = $data['responsible_id'];
        if($data['recording_id'])           $model->recording_id          = $data['recording_id'];
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
                $contract_direction = ContractDirection::find($item->contract_direction_id)->direction;
                $contragent         = ContrAgent::find($item->contragent_id)->name;
                $company_type       = CompanyType::find($item->company_type_id)->type;
                $responsible        = User::find($item->responsible_id)->full_name;
                $recording          = User::find($item->recording_id)->full_name;
                $lookers            = $item->lookers->map(function($looker){
                    return [
                      'value' => $looker->full_name
                    ];
                });
                $signatory          = User::find($item->signatory_id)->full_name;

                $main_files     = [];
                $comment_files  = [];

                foreach ($item->files as $file){
                    if($file['type'] === 'main'){
                        $str = $file->id.',\''.$file->translated_name.'.' . $file->type_file . '\'';
                        $main_files [] = [
                            'value'  => [
                                'params' => [
                                    'class'   => 'link_download_files',
                                    'href'    => '#' . $file->id,
                                    'onclick' => 'contractWorkLoadFile('.$str.')',
                                ],
                                'tag'    => 'a',
                                'value'  => $file->original_name . '.' . $file->type_file,
                            ],
                        ];
                    }

                    if($file['type'] === 'comment'){
                        $str = $file->id.',\''.$file->translated_name.'.' . $file->type_file . '\'';
                        $comment_files [] = [
                            'value'  => [
                                'params' => [
                                    'class'   => 'link_download_files',
                                    'href'    => '#' . $file->id,
                                    'onclick' => 'contractWorkLoadFile('.$str.')',
                                ],
                                'tag'    => 'a',
                                'value'  => $file->original_name . '.' . $file->type_file,
                            ],
                        ];
                    }
                };

                return [
                    'id'                    => [['value' => $item->id]],
                    'status'                => [['value' => Translate::translate($item->status)]],
                    'status_eng'            => [['value' => $item->status]],
                    'contract_type'         => [['value' => $contract_type]],
                    'organization'          => [['value' => $organization]],
                    'contract_direction'    => [['value' => $contract_direction]],
                    'contragent'            => [['value' => $contragent]],
                    'company_type'          => [['value' => $company_type]],
                    'responsible'           => [['value' => $responsible]],
                    'recording'             => [['value' => $recording]],
                    'lookers'               => $lookers,
                    'signatory'             => [['value' => $signatory]],
                    'main_files'            => $main_files,
                    'comment_files'         => $comment_files,
                    'date_created'          => [['value' => $item->created_at->format('d.m.Y')]],
                    'full_access'           => [['value' => Verifications::checkFullAccess($user_id, $item->responsible_id)]],
                ];
            });
    }

    public static function detailContract($model, $user_id){

        $contract_type      = ContractType::find($model->contract_type_id)->type;

        $organization       = Organization::find($model->organization_id)->name;

        $contract_direction = ContractDirection::find($model->contract_direction_id)->direction;

        $contragent         = ContrAgent::find($model->contragent_id)->name;

        $company_type       = CompanyType::find($model->company_type_id)->type;

        $responsible        = User::find($model->responsible_id)->full_name;

        $lookers            = $model->lookers->map(function($looker){
            return $looker->full_name;
        });

        $recording    = User::find($model->recording_id)->full_name;

        $signatory    = User::find($model->signatory_id)->full_name;

        $main_files     = [];
        $comment_files  = [];

        foreach ($model->files as $file){
            if($file['type'] === 'main'){
                $str = $file->id.',\''.$file->translated_name.'.' . $file->type_file . '\'';
                $main_files [] = '<span class="link_download_files"><a onclick="contractWorkLoadFile('.$str.')" href="#'.$file->id.'">'.$file->original_name.'</a></span>';
            }

            if($file['type'] === 'comment'){
                $str = $file->id.',\''.$file->translated_name.'.' . $file->type_file . '\'';
                $comment_files [] = '<span class="link_download_files"><a onclick="contractWorkLoadFile('.$str.')" href="#'.$file->id.'">'.$file->original_name.'</a></span>';
            }
        };

        $contract_data = [
            ['name' => 'Тип договора',              'value' => [$contract_type]],
            ['name' => 'Организация',               'value' => [$organization]],
            ['name' => 'Направление',               'value' => [$contract_direction]],
            ['name' => 'Контрагент',                'value' => [$contragent]],
            ['name' => 'Вид правовой собственности','value' => [$company_type]],
            ['name' => 'Инициатор',                 'value' => [$responsible], 'eng_name' => 'responsible'],
            ['name' => 'Наблюдатели',               'value' => $lookers],
            ['name' => 'Регистратор',               'value' => [$recording]],
            ['name' => 'Подписант',                 'value' => [$signatory]],
            ['name' => 'Содержание документа',      'value' => [$model->content]],
            ['name' => 'Основные файлы',            'value' => $main_files],
            ['name' => 'Дополнительные файлы',      'value' => $comment_files],
            ['name' => 'Дата создания',             'value' => [$model->created_at->format('d.m.Y')], 'eng_name' => 'date']
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
