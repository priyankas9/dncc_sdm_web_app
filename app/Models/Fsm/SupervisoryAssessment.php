<?php

namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupervisoryAssessment extends Model
{
    use HasFactory;
    protected $table = 'fsm.supervisory_assessments';
}
