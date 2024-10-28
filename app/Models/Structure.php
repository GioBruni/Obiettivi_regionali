<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Structure extends Generic
{

    protected $fillable = [
        "region_code",
        "company_code",
        "structure_code",
        "name",
        "address",
        "zip_code",
        "phone",
        "email",
        
    ];
    
}
