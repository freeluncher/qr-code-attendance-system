<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    use HasFactory;
    protected $fillable = [
        'location_id',
        'shift_id',
        'code',
        'expires_at',
    ];

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function shift(){
        return $this->belongsTo(Shift::class);
    }
}
