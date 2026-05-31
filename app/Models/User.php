<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailNotification;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Auditable, HasFactory, HasRoles, Notifiable;

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
        'patient_public_token',
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
            'patient_public_token_regenerated_at' => 'datetime',
        ];
    }

    public function ensurePublicExpedienteToken(): string
    {
        if ($this->patient_public_token) {
            return $this->patient_public_token;
        }

        return $this->regeneratePublicExpedienteToken();
    }

    public function regeneratePublicExpedienteToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::query()
            ->where('patient_public_token', $token)
            ->whereKeyNot($this->getKey())
            ->exists());

        $this->forceFill([
            'patient_public_token' => $token,
            'patient_public_token_regenerated_at' => now(),
        ])->save();

        return $token;
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
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

    public function sharedExpedientePermissions()
    {
        return $this->hasMany(SharedExpedientePermission::class, 'patient_id');
    }

    public function receivedSharedExpedientePermissions()
    {
        return $this->hasMany(SharedExpedientePermission::class, 'doctor_id');
    }

    public function recursos()
    {
        return $this->hasMany(\App\Models\Recurso::class);
    }

    public function reservasRecursos()
    {
        return $this->hasMany(\App\Models\RecursoReserva::class);
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

    public function configuracion()
    {
        return $this->hasOne(Configuracion::class);
    }

    public function getActivePackageTypeAttribute()
    {
        $subscription = $this->suscripciones()
            ->where('tipo', 'paquete')
            ->pagadaVigente()
            ->with('paquete')
            ->latest()
            ->first();

        return $subscription ? $subscription->paquete->tipo : null;
    }

    public function auditSection(): string
    {
        return 'usuarios';
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
            ? asset('storage/'.$this->profile_photo_path)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }

    public function getClinicLogoUrlAttribute()
    {
        $clinica = $this->clinicas->first();

        if ($clinica && $clinica->logo) {
            return asset('storage/'.$clinica->logo);
        }

        if ($clinica && $clinica->logotipo) {
            return asset($clinica->logotipo);
        }

        return null;
    }

    public function getBrandingLogoPathAttribute()
    {
        return $this->configuracion?->branding_logo_path;
    }

    public function getBrandingLogoUrlAttribute()
    {
        return $this->branding_logo_path
            ? asset('storage/'.$this->branding_logo_path)
            : null;
    }
}
