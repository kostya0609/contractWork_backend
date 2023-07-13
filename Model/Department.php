<?php
namespace App\Modules\ContractWork\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Department extends Model {
    protected $table = 'b_iblock_section';
    protected $primaryKey = 'ID';

}
