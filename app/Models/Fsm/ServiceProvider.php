<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class ServiceProvider extends Model
{
    use HasFactory;
    use SoftDeletes;
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;
    protected $table = 'fsm.service_providers';
    protected $primaryKey = 'id';

    public function sludgeCollections()
    {
        return $this->hasMany('App\SludgeCollection');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class, 'service_provider_id', 'id');

    }

    public function vacutugTypes()
    {
        return $this->hasMany(VacutugType::class, 'service_provider_id', 'id');

    }
      public function serviceproviderSequence()
    {
        return $this->hasMany(ServiceProviderSequence::class, 'service_provider_id', 'id');

    }

    public function employees()
    {
        return $this->hasMany(EmployeeInfo::class, 'service_provider_id', 'id');
    }

        /**
     * Scope a query to only include operational status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOperational($query)
    {
        return $query->where('status', 1);
    }

}
