<?php

namespace App\Modules\ContractWork\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyType extends Model{
    protected $table = 'l_contract_work_company_types';

    public function contracts():HasMany{
        return $this->hasMany(Contract::class, 'contract_id', 'id');
    }

}
