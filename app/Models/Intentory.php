<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intentory extends Model
{
    use HasFactory;
    protected $fillable = [
        'material_name',
        'stock'
    ];
}