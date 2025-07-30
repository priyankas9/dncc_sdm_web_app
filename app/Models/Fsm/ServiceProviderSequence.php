<?php

namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProviderSequence extends Model
{
    use HasFactory;
    protected $table = 'fsm.service_provider_sequence';
    protected $primaryKey = 'id';

    public function service_provider()
{
    return $this->belongsTo(ServiceProvider::class, 'service_provider_id','id');
}
}
