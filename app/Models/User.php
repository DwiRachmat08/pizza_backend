<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'lat',
        'long',
        'notelp',
        'aktif',
        'status_lapak',
        'kode_penjual'
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
        ];
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($user) {
            $rolePenjual = 2;

            if (intval($user->role_id) == $rolePenjual) {
                $currentCount = User::where('role_id', $rolePenjual)->count();
                $nextNumber = $currentCount + 1;

                $user->kode_penjual = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            } else {
                $user->kode_penjual = null;
            }
        });
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Helper buat cek role
    public function isSeller()
    {
        return $this->role->slug === 'seller';
    }
}
