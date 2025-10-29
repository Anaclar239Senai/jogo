<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_name',
        'player_element',
        'player_health',
        'player_level',
        'current_phase',
        'score',
        'is_completed'
    ];

    public function battles()
    {
        return $this->hasMany(Battle::class);
    }

    public function currentBattle()
    {
        return $this->battles()
                    ->where('phase', $this->current_phase)
                    ->orWhere('phase', 'final')
                    ->first();
    }
}