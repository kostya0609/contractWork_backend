<?php

namespace App\Modules\ContractWork\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model{
    protected $table = 'l_contract_work_roles';

    public function users():BelongsToMany{
        return $this->belongsToMany(User::class,'l_contract_work_role_user', 'role_id', 'user_id');
    }
}
