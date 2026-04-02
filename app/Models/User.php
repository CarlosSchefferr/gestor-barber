<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'date_of_birth',
        'phone',
        'cpf',
        'professional_name',
        'gender',
        'salary',
        'cargo',
        'usuario_admin',
        'navigation_layout',
        'sidebar_collapsed',
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
            'sidebar_collapsed' => 'boolean',
            'usuario_admin' => 'boolean',
        ];
    }

    /**
     * Helper: is owner
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Helper: is barber
     */
    public function isBarber(): bool
    {
        return $this->role === 'barber';
    }

    /**
     * Agendamentos do barbeiro
     */
    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'barbeiro_id');
    }

    /**
     * Professional services configuration
     */
    public function professionalServices()
    {
        return $this->hasMany(ProfessionalService::class);
    }

    /**
     * Schedule configuration
     */
    public function schedule()
    {
        return $this->hasOne(ProfessionalSchedule::class);
    }

    /**
     * Override password reset notification to use custom template
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
