<?php

namespace App\Models\Pdf;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfGeneration extends Model
{
    use HasFactory;
    protected $table = 'pdf_body_data';
    protected $primaryKey = 'id';
    public $incrementing = false;
}
