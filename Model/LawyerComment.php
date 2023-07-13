<?php

namespace App\Modules\ContractWork\Model;;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LawyerComment extends Model{

    protected $table = 'l_contract_work_lawyer_comments';
    protected $dates = ['date'];

    public function contract():BelongsTo{
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }

    public function user():BelongsTo{
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    public function files():morphMany{
        return $this->morphMany(File::class, 'fileable');
    }

}
