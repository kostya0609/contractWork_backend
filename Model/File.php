<?php

namespace App\Modules\ContractWork\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;


class File extends Model{
    protected $table = 'l_contract_work_files';

    public function fileable():MorphTo{

        return $this->morphTo();
    }
}
