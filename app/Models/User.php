<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'location',
        'number',
        'photo',
        'id_image',
        'role',
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
     * @var mixed
     */
    private $stripe_account_id;


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

    public function create_hall_request()
    {
        return $this->hasMany(create_hall_request::class, 'id'); // or 'user_name' if using name
    }

    public function hallsAsEmployee()
    {
        return $this->belongsToMany(Hall::class, 'hall_employees');
    }


    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function paymentConfirm() {
        return $this->hasMany(paymentConfirm::class);
    }

    public function deviceToken () {
        return $this->hasOne(DeviceToken::class);
    }

    public function notifications () {
        return $this->hasMany(Notifications::class);
    }


    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset($this->photo) : null;
    }
    public function getIdImageUrlAttribute()
    {
        return $this->id_image ? asset($this->id_image) : null;
    }


}
