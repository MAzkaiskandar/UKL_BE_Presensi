<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class attendance extends Model
{
    use HasFactory;

    public $timestamps = null;
    protected $table = 'attendance';
    protected $primaryKey = 'id';
    protected $fillable = ['id_user', 'date', 'time', 'status'];
}

