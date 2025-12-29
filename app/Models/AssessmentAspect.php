<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentAspect extends Model
{
    protected $fillable = [
        'seminar_jenis_id',
        'evaluator_type',
        'nama_aspek',
        'urutan',
    ];

    public function seminarJenis()
    {
        return $this->belongsTo(SeminarJenis::class, 'seminar_jenis_id');
    }

    public function assessmentScores()
    {
        return $this->hasMany(AssessmentScore::class, 'assessment_aspect_id');
    }
}
