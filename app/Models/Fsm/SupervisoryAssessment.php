<?php

namespace App\Models\Fsm;

use App\Models\BuildingInfo\Owner;
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
    // App\Models\SupervisoryAssessment.php

public function containmentType()
{
    return $this->belongsTo(ContainmentType::class, 'containment_type', 'id');
}



public function owner()
{
    return $this->belongsTo(Owner::class); // Adjust if your relationship is different
}
}
