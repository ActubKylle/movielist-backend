<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'genre',
        'release_year',
        'description',
        'user_id',  // Make sure user_id is fillable!
    ];

    /**
     * Get the user that owns the movie.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
