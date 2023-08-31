<?php

namespace App\Modules\ContractWork\Action\v1;

class SelectNotification{
    public static function select($contractModel, $lawyer_ids, $users_id){

        $contract_id    = $contractModel->id;
        $status         = $contractModel->status;
        $responsible_id = $contractModel->responsible_id;

        switch($status){
            case 'contract_created':
            case 'correction_primary_data':{
                SetNeedAction::update($contract_id, [$responsible_id]);
                Notification::send($responsible_id, $contract_id, $status, 'responsible' );
                break;
            }
            case 'contract_active':
            case 'contract_cancel':{
                SetNeedAction::update($contract_id, []);
                Notification::send($responsible_id, $contract_id, $status, 'responsible' );
                break;
            }
            case 'contract_internal_approval':
            case 'manager_approval':{
                SetNeedAction::update($contract_id, $users_id);
                break;
            }
            case 'lawyer_check':{
                Notification::send($responsible_id, $contract_id, $status, 'responsible' );

                SetNeedAction::update($contract_id, $lawyer_ids);
                foreach ($lawyer_ids as $lawyer)
                    Notification::send($lawyer, $contract_id, $status, 'lawyer' );
                break;
            }
            case 'correction_after_approval':{
                if($contractModel->waiting_edit){
                    Notification::send($responsible_id, $contract_id, $status, 'responsible' );
                    SetNeedAction::update($contract_id, [$responsible_id]);
                } else {
                    SetNeedAction::update($contract_id, $lawyer_ids);
                    foreach ($lawyer_ids as $lawyer)
                        Notification::send($lawyer, $contract_id, $status, 'lawyer' );
                }
                break;
            }
            case 'protocol_contragent_approval':{
                Notification::send($responsible_id, $contract_id, $status, 'responsible' );
                SetNeedAction::update($contract_id, [$responsible_id]);
                break;
            }
            case 'transfer_originals_to_lawyer':{
                Notification::send($responsible_id, $contract_id, $status, 'lawyer' );

                SetNeedAction::update($contract_id, $lawyer_ids);
                foreach ($lawyer_ids as $lawyer)
                    Notification::send($lawyer, $contract_id, $status, 'lawyer' );
                break;
            }
        }
    }
}
