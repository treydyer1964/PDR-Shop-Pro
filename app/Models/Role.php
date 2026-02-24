<?php

namespace App\Models;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $primaryKey = 'name';
    protected $keyType    = 'string';
    public    $incrementing = false;
    public    $timestamps   = false;

    protected $fillable = ['name', 'label'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_name', 'user_id');
    }

    public function toEnum(): RoleEnum
    {
        return RoleEnum::from($this->name);
    }

    public function label(): string
    {
        return $this->toEnum()->label();
    }
}
