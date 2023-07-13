<?php
namespace App\Modules\ContractWork\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class Contract extends Model {

    protected $table = 'l_contract_work_contracts';

    protected $dates = ['date-created'];

    public function lookers():BelongsToMany{
        return $this->belongsToMany(User::class,'l_contract_work_contract_looker', 'contract_id', 'looker_id');
    }

    public function contactType():BelongsTo{
        return $this->belongsTo(ContractType::class, 'contract_id', 'id');
    }

    public function companyType():BelongsTo{
        return $this->belongsTo(ContractType::class, 'contract_id', 'id');
    }

    public function contractDirection():BelongsTo{
        return $this->belongsTo(ContractDirection::class, 'contract_id', 'id');
    }

    public function contrAgent():BelongsTo{
        return $this->belongsTo(ContrAgent::class, 'contract_id', 'id');
    }

    public function organization():BelongsTo{
        return $this->belongsTo(Organization::class, 'contract_id', 'id');
    }

    public function logs():HasMany{
        return $this->hasMany(Log::class, 'contract_id', 'id');
    }

    public function lawyerComments():HasMany{
        return $this->hasMany(LawyerComment::class, 'contract_id', 'id');
    }

    public function protocols():HasMany{
        return $this->hasMany(Protocol::class, 'contract_id', 'id');
    }

    public function scans():HasMany{
        return $this->hasMany(Scan::class, 'contract_id', 'id');
    }

    public function files():morphMany{
        return $this->morphMany(File::class, 'fileable');
    }
}
