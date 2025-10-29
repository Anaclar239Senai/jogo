<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Battle extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'phase',
        'enemy_element',
        'enemy_health',
        'player_health',
        'status',
        'battle_log'
    ];

    protected $casts = [
        'battle_log' => 'array'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}