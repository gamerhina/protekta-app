<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarNilai extends Model
{
    use HasFactory;

    protected $table = 'seminar_nilai';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seminar_id',
        'dosen_id',
        'jenis_penilai',
        'nilai_angka',
        'komponen_nilai',
        'catatan',
    ];

    /**
     * Cast attributes to proper types
     */
    protected $casts = [
        'komponen_nilai' => 'array',
    ];

    /**
     * Relationship with seminar
     */
    public function seminar()
    {
        return $this->belongsTo(Seminar::class, 'seminar_id');
    }

    /**
     * Relationship with dosen
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Relationship with assessment scores
     */
    public function assessmentScores()
    {
        return $this->hasMany(AssessmentScore::class, 'seminar_nilai_id');
    }

    /**
     * Calculate final score based on assessment aspects
     * Returns the average of all aspect scores (not weighted by aspect percentages)
     */
    public function calculateFinalScore()
    {
        $scores = $this->assessmentScores;
        
        if ($scores->isEmpty()) {
            return 0;
        }
        
        // Calculate simple average of all aspect scores
        $total = $scores->sum('nilai');
        $count = $scores->count();
        
        return round($total / $count, 2);
    }
}
