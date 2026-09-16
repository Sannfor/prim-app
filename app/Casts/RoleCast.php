<?php

namespace App\Casts;

use App\Enums\Role;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast kolom `role` ke enum Role dengan nilai cadangan yang aman.
 *
 * Cast enum bawaan Eloquent mengembalikan null ketika atribut belum diset
 * (misalnya pada model yang baru dibuat dalam satu request sebelum di-refresh).
 * Cast ini memastikan properti `role` selalu berupa instance Role sehingga
 * pemanggilan seperti $user->role->label() tidak pernah gagal.
 *
 * @implements CastsAttributes<Role, Role|string>
 */
class RoleCast implements CastsAttributes
{
    /**
     * Ubah nilai kolom menjadi enum Role.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Role
    {
        if ($value instanceof Role) {
            return $value;
        }

        if (is_string($value)) {
            return Role::tryFrom($value) ?? Role::User;
        }

        return Role::User;
    }

    /**
     * Siapkan nilai enum untuk disimpan ke database.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof Role) {
            return $value->value;
        }

        if (is_string($value) && Role::tryFrom($value) !== null) {
            return $value;
        }

        return $value === null ? null : Role::User->value;
    }
}
