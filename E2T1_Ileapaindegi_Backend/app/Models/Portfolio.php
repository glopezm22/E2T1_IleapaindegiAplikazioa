<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Portfolio extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'service_id',
        'category',
        'photo_before',
        'photo_after',
        'notes',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
