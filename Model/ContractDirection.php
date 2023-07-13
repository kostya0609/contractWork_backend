<?php

namespace App\Modules\ContractWork\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractDirection extends Model{
    protected $table = 'l_contract_work_contract_directions';

    public function contracts():HasMany{
        return $this->hasMany(Contract::class, 'contract_id', 'id');
    }

}
