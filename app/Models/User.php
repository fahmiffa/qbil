<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

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
        'phone',
        'photo',
        'uri',
        'whatsapp_server_id',
        'allow_multi_router'
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
            'allow_multi_router' => 'boolean',
        ];
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function router()
    {
        return $this->hasOne(Router::class)->oldest();
    }

    public function routers()
    {
        return $this->hasMany(Router::class);
    }

    public function hotspotUsers()
    {
        return $this->hasMany(HotspotUser::class);
    }

    public function appSetting()
    {
        return $this->hasOne(AppSetting::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function piutangs()
    {
        return $this->hasMany(Piutang::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function pppProfiles()
    {
        return $this->hasMany(PppProfile::class);
    }

    public function ipPools()
    {
        return $this->hasMany(IpPool::class);
    }

    public function dhcpServers()
    {
        return $this->hasMany(DhcpServer::class);
    }

    public function voucherOrders()
    {
        return $this->hasMany(VoucherOrder::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function whatsappServer()
    {
        return $this->belongsTo(WhatsappServer::class);
    }

    public function hasFeature(string $parameter): bool
    {
        // Super Admin (role 0) can access everything
        if ($this->role == 0) {
            return true;
        }

        return $this->features->contains('parameter', $parameter);
    }

    protected static function booted()
    {
        static::deleting(function ($user) {
            // Delete related models one by one to trigger their own deleting hooks (e.g. Customer -> Invoices)
            $user->customers->each->delete();
            
            // Bulk delete other related models
            $user->packages()->delete();
            $user->router()->delete();
            $user->routers()->delete();
            $user->hotspotUsers()->delete();
            $user->appSetting()->delete();
            $user->assets()->delete();
            $user->piutangs()->delete();
            $user->deposits()->delete();
            $user->pppProfiles()->delete();
            $user->ipPools()->delete();
            $user->dhcpServers()->delete();
            $user->voucherOrders()->delete();
            
            // Detach features
            $user->features()->detach();
        });
    }
}
