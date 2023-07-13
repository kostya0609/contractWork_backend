<?php

namespace App\Modules\ContractWork\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContrAgent extends Model{
    protected $table = 'l_contragents';

    public function contracts():HasMany{
        return $this->hasMany(Contract::class, 'contract_id', 'id');
    }
}
