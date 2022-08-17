<?php

namespace App\Models;

use App\Services\CacheManagement;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BaseModelTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, BaseModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'language'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function findByEmail($email): User|null
    {
        return $this::where('email', $email)
            ->first();
    }

    public function findById(int $user_id): User|null
    {
        return User::find($user_id);
    }

    public function updateUser($user_info, $user_id, array $filters = []): bool
    {
        return $this->where('id', $user_id)
            ->when(array_key_exists('company_id', $filters), function ($query) use ($filters) {
                return $query->where('company_id', $filters['company_id']);
            })
            ->update(
                $this->prepareUserInfo($user_info)
            );
    }

    public function createNewUser(array $user_info): User
    {
        return User::create(
            $this->prepareUserInfo($user_info)
        );
    }

    public function getUsers(array $filters = [], array $columns = ['*'], bool $pagination = false): array|Collection|LengthAwarePaginator
    {
        $users = User::query();

        if (array_key_exists('company_id', $filters)) {
            $users->where('company_id', $filters['company_id']);
        }

        if (array_key_exists('name', $filters)) {
            $users->where('name', 'like', "%{$filters['name']}%");
        }

        if (array_key_exists('email', $filters)) {
            $users->where('email', $filters['email']);
        }

        $users->orderBy($filters['order_by'] ?? 'updated_at', $filters['order_by_direction'] ?? 'DESC');

        $users->select($columns);

        if ($pagination) {
            return $users
                ->paginate(
                    $this->getItemPerPage($filters['item_per_page'] ?? null)
                )
                ->withQueryString();
        }

        return $users->get();
    }

    public function deleteUser(int $user_id, array $filters = []): bool
    {
        return User::query()
            ->where('id', $user_id)
            ->when(array_key_exists('company_id', $filters), function ($query) use ($filters) {
                return $query->where('company_id', $filters['company_id']);
            })
            ->delete();
    }

    private function prepareUserInfo(array $data): array
    {
        $user_data = [];

        if (array_key_exists('company_id', $data)) {
            $user_data['company_id'] = $data['company_id'];
        }

        if (array_key_exists('name', $data)) {
            $user_data['name'] = $data['name'];
        }

        if (array_key_exists('email', $data)) {
            $user_data['email'] = $data['email'];
        }

        if (array_key_exists('password', $data)) {
            $user_data['password'] = Hash::make($data['password']);
        }

        if (array_key_exists('language', $data)) {
            $user_data['language'] = $data['language'];
        }

        return $user_data;
    }
}
