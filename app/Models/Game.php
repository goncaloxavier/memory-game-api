<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'games';  // Make sure the table name is correct
    
    // Allow mass assignment for these fields
    protected $fillable = [
        'type',
        'status',
        'began_at',
        'ended_at',
        'board_id',
        'created_at',
        'total_time', 
        'created_user_id'
    ];

    // Define the relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }
}