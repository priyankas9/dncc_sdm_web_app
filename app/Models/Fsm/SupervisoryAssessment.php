<?php

namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupervisoryAssessment extends Model
{
    use HasFactory;
    protected $table = 'fsm.supervisory_assessments';
    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id', 'id');
    }
}
