<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name','email','password',
        'dni','nombre','apellido','nacionalidad','nacimiento','domicilio','localidad',
        'telefono','celular','categoria','socio_id','barcode','estado_socio','avatar_path','api_update_ts',
    ];

    protected $hidden = ['password', 'remember_token'];
}
