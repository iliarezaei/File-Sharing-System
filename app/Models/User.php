<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Get the files uploaded by the user.
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }
    
    /**
     * Get the files shared with the user.
     */
    public function sharedFiles()
    {
        return $this->belongsToMany(File::class, 'file_shares', 'user_id', 'file_id')->withTimestamps();
    }
    
    /**
     * Get the total size of all files uploaded by the user.
     */
    public function getTotalStorageUsedAttribute()
    {
        return $this->files()->sum('size');
    }
    
    /**
     * Check if the user has enough storage space left.
     */
    public function hasEnoughStorage($fileSize)
    {
        $maxStorage = 5 * 1024 * 1024 * 1024; // 5GB in bytes
        return ($this->total_storage_used + $fileSize) <= $maxStorage;
    }
}
