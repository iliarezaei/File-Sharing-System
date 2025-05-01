<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'path',
        'mime_type',
        'size'
    ];
    
    /**
     * Get the user that owns the file.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the users this file is shared with.
     */
    public function sharedWithUsers()
    {
        return $this->belongsToMany(User::class, 'file_shares', 'file_id', 'user_id')->withTimestamps();
    }
    
    /**
     * Get the file shares for this file.
     */
    public function fileShares()
    {
        return $this->hasMany(FileShare::class);
    }
    
    /**
     * Format the file size for display.
     */
    public function getFormattedSizeAttribute()
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->size;
        $i = 0;
        
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }
}
