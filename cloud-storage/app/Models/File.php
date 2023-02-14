<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'path',
        'folder_id',
    ];

    public function folder()
    {
        return $this->belongsTo(File::class, 'folder_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'folder_id');
    }

}
