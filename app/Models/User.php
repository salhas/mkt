<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Memeriksa apakah user termasuk jajaran Manajemen Internal MKT
     */
    public function isManagement(): bool
    {
        return in_array($this->role, ['webmaster', 'administrator', 'admin', 'finance', 'staff']);
    }

    public function isWebmaster(): bool
    {
        return $this->role === 'webmaster';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['administrator', 'admin', 'webmaster']);
    }

    public function isFinance(): bool
    {
        return in_array($this->role, ['finance', 'keuangan', 'webmaster', 'administrator']);
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['staff', 'staf', 'webmaster', 'administrator']);
    }
}
