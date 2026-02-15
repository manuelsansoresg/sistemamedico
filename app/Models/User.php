<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'email',
        'password',
        'cedula_profesional',
        'especialidad_id',
        'curp',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'numero_imss',
        'activo',
        'perfil_compartido',
        'created_by',
        'peso',
        'estatura',
        'alergias',
        'estatus_cedula',
        'cedula_validada_at',
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
            'fecha_nacimiento' => 'date',
            'activo' => 'boolean',
            'perfil_compartido' => 'boolean',
        ];
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function clinicas()
    {
        return $this->belongsToMany(Clinica::class, 'clinica_user');
    }

    public function consultorios()
    {
        return $this->belongsToMany(Consultorio::class, 'consultorio_user');
    }

    // Relación: Un médico tiene muchos pacientes (a través de la tabla pivote)
    public function patients()
    {
        return $this->belongsToMany(User::class, 'doctor_patient', 'doctor_id', 'patient_id')->withTimestamps();
    }

    // Relación: Un paciente tiene muchos médicos (a través de la tabla pivote)
    public function doctors()
    {
        return $this->belongsToMany(User::class, 'doctor_patient', 'patient_id', 'doctor_id')->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class);
    }

    public function getActivePackageTypeAttribute()
    {
        $subscription = $this->suscripciones()
            ->where('tipo', 'paquete')
            ->where('estatus_pago', 'pagado')
            ->where(function($q) {
                $q->whereNull('fecha_fin')
                  ->orWhere('fecha_fin', '>', now());
            })
            ->with('paquete')
            ->latest()
            ->first();

        return $subscription ? $subscription->paquete->tipo : null;
    }
}
