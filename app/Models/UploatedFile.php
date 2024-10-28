<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploatedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename', 
        'path', 
        'user_id', 
        'structure_id', 
        'approved', 
        'validator_user_id', 
        'notes', 
        'target_number', 
        'target_category_id', 
    ];
}