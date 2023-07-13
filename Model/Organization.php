<?php
namespace App\Modules\ContractWork\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Organization extends Model {
    protected $table = 'l_organizations';

    public function contracts():HasMany{
        return $this->hasMany(Contract::class, 'contract_id', 'id');
    }

}
