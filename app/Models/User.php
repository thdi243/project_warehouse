<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Models\Wsp\TransaksiModel;
use App\Models\Permission\Permission;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
        'nama_lengkap',
        'username',
        'email',
        'password',
        'jabatan',
        'nik',
        'departemen',
        'bagian',
        'image',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiModel::class, 'id_user');
    }

    // app/Models/User.php
    public function getImageUrlAttribute()
    {
        return $this->image && url(Storage::disk('public')->exists($this->image))
            ? url(Storage::url($this->image))
            : asset('material/assets/images/users/user-dummy-img.jpg');
    }

    public function principal()
    {
        return $this->hasOne(UserPrincipalModel::class);
    }

    public function signature()
    {
        return $this->hasOne(UserSignatureModel::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function hasPermission(string $permissionName): bool
    {
        // Check direct permissions
        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        // Check role permissions
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permissionName)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function scopeRole($query, $roleName)
    {
        return $query->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }
}
