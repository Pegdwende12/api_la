<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\ContactController;

class Contact extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'id', 
        'nom',
        'prenom',
        'tel',
        'mail',
        'residence',
        'categorie',
        'user_id',
    ];
}
