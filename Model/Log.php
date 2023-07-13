<?php

namespace App\Modules\ContractWork\Model;;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    protected $table = 'l_contract_work_logs';
    protected $dates = ['date'];

    public function contract():BelongsTo{
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }

    public function user():BelongsTo{
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    public function setLog($contract_id, $user_id, $event){
        $this->contract_id  = $contract_id;
        $this->user_id      = $user_id;
        $this->date         = Carbon::now();
        $this->event        = $event;
        $this->save();
    }

}
