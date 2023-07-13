<?php

namespace App\Modules\ContractWork\Model;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ContractType extends Model{
    protected $table = 'l_contract_work_contract_types';

    public function contracts():HasMany{
        return $this->hasMany(Contract::class, 'contract_id', 'id');
    }
}
