<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileShare extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'file_id',
        'user_id'
    ];
    
    /**
     * Get the file that is shared.
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }
    
    /**
     * Get the user the file is shared with.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
