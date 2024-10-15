<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    public $table = 'password_reset_tokens'; // nombre de la tabla
    public $timestamps = false; // no necesitamos los campos created_at y updated_at
    protected $primaryKey = 'email'; // clave primaria
    protected $keyType = 'string'; // tipo de dato de la clave primaria

    // Campos que se pueden rellenar
    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];
}
