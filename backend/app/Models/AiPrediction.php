<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrediction extends Model
{
    protected $fillable = [
        'user_id',
        'location_id',
        'predicted_for_date',
        'risk_score',
        'reason',
    ];

    // Relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
