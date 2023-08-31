<?php

namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Action\v1\FileAction;
use App\Modules\ContractWork\Action\v1\ContractAction;
use App\Modules\ContractWork\Action\v1\SelectNotification;
use App\Modules\ContractWork\Action\v1\Verifications;
use App\Modules\ContractWork\Action\v1\Filter;
use App\Modules\ContractWork\Action\v1\Translate;

use App\Http\Controllers\Controller;

use App\Modules\ContractWork\Model\ContrAgent;
use App\Modules\ContractWork\Model\LawyerComment;
use App\Modules\ContractWork\Model\Organization;
use App\Modules\ContractWork\Model\CompanyType;
use App\Modules\ContractWork\Model\Contract;
use App\Modules\ContractWork\Model\NeedAction;
use App\Modules\ContractWork\Model\ContractType;
use App\Modules\ContractWork\Model\File;
use App\Modules\ContractWork\Model\Log;
use App\Modules\ContractWork\Model\Protocol;
use App\Modules\ContractWork\Model\Role;
use App\Modules\ContractWork\Model\Scan;
use App\Modules\ContractWork\Model\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ContractController extends Controller{
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
            'form'      => 'form_file'
        ];

    public function validation($data){
        $validation = Validator::make($data->all(),
            [
                'user_id'               => 'required | numeric',

                'contract_type_id'      => 'required | numeric',
                'organization_id'       => 'required | numeric',
                'department_id'         => 'required | numeric',
                'contragent_id'         => 'required | numeric',
                'company_type_id'       => 'required | numeric',

                'responsible_id'        => 'required | numeric',
                'signatory_id'          => 'required | numeric',

                'content'               => 'required',
            ],
            [
                'user_id.required'               => 'Некорректный запрос - нет user_id',

                'contract_type_id.required'      => 'Поле "Тип договора" обязательно!',
                'organization_id.required'       => 'Поле "Организация" обязательно!',
                'department_id.required'         => 'Поле "Департамент" обязательно!',
                'contragent_id.required'         => 'Поле "Контрагент" обязательно!',
                'company_type_id.required'       => 'Поле "Вид правовой собственности" обязательно!',

                'responsible_id.required'        => 'Поле "Инициатор" обязательно!',
                'recording_id.required'          => 'Поле "Регистратор" обязательно!',
                'signatory_id.required'          => 'Поле "Подписант" обязательно!',

                'content.required'               => 'Поле "Содержание документа" обязательно!',
            ]);

        return $validation;
    }

    public function create(Request $request){
        $data = collect(json_decode($request->data));

        $validation = self::validation($data);

        if($validation->fails())
            return response()->json([
                'success'    => false,
                'message'   => implode(' <br/> ', $validation->errors()->all()),
            ]);


        if(!isset($request->contract_file))
            return response()->json([
                'success' => false,
                'message' => 'Возникла ошибка, нет файла договора!',
            ]);

        DB::beginTransaction();
        try {
            $newContract           = new Contract();
            $newContract->status   = 'contract_created';
            $newContract           = ContractAction::setContract($newContract, $data);
            $contract_id           = $newContract->id;

            foreach (self::FILE_TYPES as $key => $value){
                $newFile = new File();
                FileAction::saveFile($newFile, $contract_id, Contract::class, ucfirst($key), $request[$value], $key);
            }

            if(isset($request->others_file) && count($request->others_file) > 0){
                foreach ($request->others_file as $item){
                    $newFile = new File();
                    FileAction::saveFile($newFile, $contract_id, Contract::class, 'Others', $item, 'others');
                }
            }

            $log = new Log();
            $logMessage = 'Новый договор создан';
            $log->setLog(
                $newContract->id,
                $data['user_id'],
                $logMessage
            );

            DB::commit();
            return response()->json([
                'success'     => true,
                'data'        => [
                    'contract_id' => $contract_id,
                ],
                'notify'      => [
                    'title'    => 'Создание нового договора',
                    'message'  => "Создан новый договор с ID - $contract_id",
                    'type'     => 'success',
                    'duration' => 3000,
                ]
            ]);

        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function list(Request $request){
        $sort   = $request->sort['name']  ?: 'id';
        $order  = $request->sort['order'] ?: 'asc';
        $limit  = $request->count;
        $offset = ($request->page - 1) * $limit;

        $contractModels    = Contract::orderBy($sort, $order);

        $contractModels    = Verifications::checkContractAccess($contractModels, $request->user_id);

        if($request->filter){
            $contractModels = Filter::filter($request->filter, $contractModels);
            $total = $contractModels->count();
        } else {
            //$contractModels->whereNotIn('status',['contract_cancel', 'contract_active']);
            $total = $contractModels->count();
        }

        $grid_list = ContractAction::listGrid($contractModels->offset($offset)->limit($limit)->with('lookers'), $request->user_id);

        return response()->json([
            'success'     => true,
            'data'        => [
                'grid_list' => $grid_list,
                'total'     => $total,
            ],
        ]);
    }

    public function listNeedAction(Request $request){

        $sort   = ($request->sort['name'])  ?: 'id';
        $order  = ($request->sort['order']) ?: 'asc';
        $limit  = $request->count;
        $offset = ($request->page - 1) * $limit;

        $contractsIds = NeedAction::where('user_id', $request->user_id)->pluck('contract_id')->toArray();

        $contractModels = Contract::orderBy($sort, $order)->whereIn('id', $contractsIds);

        $total = $contractModels->count();

        if($request->filter){
            $contractModels = Filter::filter($request->filter, $contractModels);
            $total = $contractModels->count();
        }

        $grid_list = ContractAction::listGrid($contractModels->offset($offset)->limit($limit)->with('lookers'), $request->user_id);

        return response()->json([
            'success'     => true,
            'data'        => [
                'grid_list' => $grid_list,
                'total'     => $total,
            ],
        ]);
    }

    public function get(Request $request){

        $contract_id = $request->contract_id;
        $user_id     = $request->user_id;

        if(!is_numeric($user_id) || !is_numeric($contract_id) )
            return response()->json(['success'    => false, 'message'   => 'Возникла ошибка, нет contract_id или user_id']);

        $contractModel = Contract::with('lookers', 'files')->find($contract_id);

        $contractModel = Verifications::checkAccess($contractModel, $user_id);

        if(!$contractModel)

            return response()->json(['success' => false, 'message' => 'Отсутствует доступ для просмотра', 'data' => null]);

        $contragent = ContrAgent::find($contractModel->contragent_id)->name;

        $department = Verifications::userDepartment($contractModel->responsible_id);

        $looker_ids = $contractModel->lookers->map(function($looker){
            return
               $looker->ID;
        });

        $lookers_list = $contractModel->lookers->map(function($looker){
            return [
                'value' => $looker->ID, 'label' => $looker->full_name
            ];
        });

        $organization = Organization::find($contractModel->organization_id)->name;

        $responsible  = User::find($contractModel->responsible_id)->full_name;

        $signatory    = User::find($contractModel->signatory_id)->full_name;

        $files = ContractAction::getContractFile($contractModel->files, 'file');

        $contract_data = [
            'company_type_id'       => $contractModel->company_type_id,
            'content'               => $contractModel->content,
            'department_id'         => $contractModel->department_id,
            'contract_type_id'      => $contractModel->contract_type_id,
            'contragent_id'         => $contractModel->contragent_id,
            'looker_ids'            => $looker_ids,
            'organization_id'       => $contractModel->organization_id,
            'responsible_id'        => $contractModel->responsible_id,
            'signatory_id'          => $contractModel->signatory_id,
            'status'                => $contractModel->status,
            'waiting_edit'          => $contractModel->waiting_edit,
        ];

        $options = [
            'contragent_list'   => [['value' => $contractModel->contragent_id,'label' => $contragent]],
            'lookers_list'      => $lookers_list,
            'organization_list' => [['value' => $contractModel->organization_id,'label' => $organization]],
            'responsible_list'  => [['value' => $contractModel->responsible_id, 'label' => $responsible]],
            'signatory_list'    => [['value' => $contractModel->signatory_id, 'label' => $signatory]],
            'department_list'   => [['value' => $department->ID, 'label' => $department->NAME]],
        ];

        return response()->json([
            'success'     => true,
            'data'        => [
                'contract_data' => $contract_data,
                'options'       => $options,
                'files'         => [
                    'left'  => [
                        'contract'  => $files['contract_file'],
                        'addition'  => $files['addition_file'],
                        'annex'     => $files['annex_file'],
                        'others'    => $files['others_file'],
                    ],
                    'right' => [
                        'note'      => $files['note_file'],
                        'info'      => $files['info_file'],
                        'about'     => $files['about_file'],
                        'extract'   => $files['extract_file'],
                        'report'    => $files['report_file'],
                        'agree'     => $files['agree_file'],
                        'vicarious' => $files['vicarious_file'],
                        'form'      => $files['form_file'],
                    ],
                ],
            ],
        ]);
    }

    public function edit(Request $request){
        $data = collect(json_decode($request->data));

        $contract_id = $data['id'];
        $user_id     = $data['user_id'];

        if(!is_numeric($user_id) || !is_numeric($contract_id) )
            return response()->json([ 'success'    => false, 'message'   => 'Возникла ошибка, нет contract_id или user_id']);

        $validation = self::validation($data);

        if($validation->fails())
            return response()->json([
                'success'    => false,
                'message'   => implode(' <br/> ', $validation->errors()->all()),
            ]);

        if(!isset($request->contract_file) && !isset($request->contract_file_id) )
            return response()->json([
                'success' => false,
                'message' => 'Возникла ошибка, нет файла договора!',
            ]);

        $others_file    = [
            'file_save' => $request->others_file_id ? $request->others_file_id : null,
            'file'      => $request->others_file    ? $request->others_file    : null,
        ];

        DB::beginTransaction();
        try {

            $contractModel = Contract::with('lookers', 'files')->find($contract_id);

            ContractAction::setContract($contractModel, $data);

            foreach (self::FILE_TYPES as $key => $value){
                $files  = [
                    'file_save' => $request[$value.'_id'] ? [$request[$value.'_id']] : null,
                    'file'      => $request[$value]       ? [$request[$value]]       : null,
                ];
                FileAction::updateFile($contract_id,Contract::class, ucfirst($key), $files, $key);
            }

            FileAction::updateFile($contract_id,Contract::class,'Others', $others_file, 'others');

            $log = new Log();
            $logMessage = 'Изменения в договоре сохранены';
            $log->setLog(
                $contract_id,
                $data['user_id'],
                $logMessage
            );

            DB::commit();
            return response()->json([
                'success'     => true,
                'data'        => [
                    'contract_id' => $contract_id,
                ],
                'notify'      => [
                    'title'    => 'Редактирование договора',
                    'message'  => "Изменения в договоре с ID - $contract_id сохранены.",
                    'type'     => 'success',
                    'duration' => 3000,
                ]
            ]);

        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

    }

    public function detail(Request $request){

        $contract_id = $request->contract_id;
        $user_id = $request->user_id;

        if(!$contract_id || !$user_id)
            return response()->json(['success'   => false, 'message'   => 'Возникла ошибка, нет contract_id или user_id']);

        $contractModel = Contract::with('lookers', 'files', 'lawyerComments.files', 'protocols.files', 'scans.files')->find($contract_id);

        $contractModel = Verifications::checkAccess($contractModel, $user_id);

        if(!$contractModel)

            return response()->json(['success' => false, 'message' => 'Отсутствует доступ для просмотра', 'data' => null]);

        $data = ContractAction::detailContract($contractModel, $user_id);;

        return response()->json([
            'success'     => true,
            'data'        => $data,
        ]);
    }

    public function delete(Request $request){
        $contract_id = $request->contract_id;
        $user_id     = $request->user_id;

        if(!is_numeric($user_id) || !is_numeric($contract_id) )
            return response()->json(['success' => false, 'message' => 'Возникла ошибка, нет user_id или contract_id']);

        $contractModel = Contract::with('lawyerComments', 'protocols', 'scans')->find($contract_id);

        DB::beginTransaction();
        try{
            foreach (self::FILE_TYPES as $key => $value)
                FileAction::deleteFiles($contract_id,Contract::class, $key);

            FileAction::deleteFiles($contract_id,Contract::class, 'others');

            $contractModel->lookers()->sync([]);

            foreach ($contractModel->lawyerComments as $comment){
                FileAction::deleteFiles($comment->id,LawyerComment::class, 'lawyer');
            }

            foreach ($contractModel->protocols as $protocol){
                FileAction::deleteFiles($protocol->id,Protocol::class, 'protocol');
            }

            foreach ($contractModel->scans as $scan){
                FileAction::deleteFiles($scan->id,Scan::class, 'scan');
            }

            LawyerComment::where('contract_id', '=', $contract_id)->delete();

            Protocol::where('contract_id', '=', $contract_id)->delete();

            Scan::where('contract_id', '=', $contract_id)->delete();

            $contractModel->delete();

            $log = new Log();
            $logMessage = 'Договор удален';
            $log->setLog(
                $contract_id,
                $user_id,
                $logMessage,
            );

            DB::commit();
            return response()->json([
                'success'     => true,
                'data'        => [],
                'notify'      => [
                    'title'    => 'Удаление договора',
                    'message'  => "Договор с ID - $contract_id удален.",
                    'type'     => 'success',
                    'duration' => 3000,
                ]
            ]);

        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function changeStatus(Request $request){
        $contract_id  = $request->contract_id;
        $user_id      = $request->user_id;
        $status       = $request->status;
        $waiting_edit = $request->waiting_edit ?: 0;
        $users_id     = $request->users_id ?: [];

        $users_id     = array_map(function ($el){
            return $el['user'];
        }, $users_id);

        if(!is_numeric($contract_id) || !$user_id || !Translate::exists($status))
            return response()->json(['success' => false, 'message' => 'Возникла ошибка, нет user_id или contract_id, или не верный новый статус']);

        //ниже получить id всех юристов
        $role_id = Role::where('name', '=', 'lawyer')->value('id');
        $lawyer_ids = DB::table('l_contract_work_role_user')->where('role_id',$role_id)->pluck('user_id')->toArray();

        $contractModel = Contract::find($contract_id);
        if($contractModel->status === $status)
            return response()->json([
                'success'     => true,
                'data'        => [],
            ]);

        DB::beginTransaction();
        try{
            $contractModel->status       = $status;
            $contractModel->waiting_edit = $waiting_edit;

            $contractModel->save();

            $log = new Log();

            $logMessage = 'Договор переведен в статус - ' . Translate::translate($status) . '.';

            $log->setLog(
                $contract_id,
                $user_id,
                $logMessage
            );

            SelectNotification::select($contractModel, $lawyer_ids, $users_id);

            DB::commit();
            return response()->json([
                'success'     => true,
                'message'     => 'Успешно',
                'data'        => [],
            ]);

        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getAdditionalInfo(Request $request){
        $user_id = $request->user_id;

        $responsible        = User::find($user_id)->full_name;

        $companyTypes       = CompanyType::get();
        $contractTypes      = ContractType::get();

        return response()->json([
            'success' => true,
            'data'    => [
                'companyTypes'       => $companyTypes,
                'contractTypes'      => $contractTypes,
                'responsible_list'   => ['value' => $user_id, 'label' => $responsible],
            ],
        ]);

    }

    //ниже методы для фильтра грида

    public function getContractType(){
        $contractTypes      = ContractType::get();

        return response()->json([
            'success' => true,
            'data'    => $contractTypes->map(function ($el){
                return [
                    'value' => $el->id,
                    'label' => $el->type,
                ];
            }),
        ]);
    }

    public function getCompanyType(){
        $companyTypes       = CompanyType::get();

        return response()->json([
            'success' => true,
            'data'    => $companyTypes->map(function ($el){
                return [
                    'value' => $el->id,
                    'label' => $el->type,
                ];
            }),
        ]);
    }

}


