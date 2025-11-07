<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            $user->activation_token = str_random(30);
        });
    }


    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    // 粉丝集合
    public function followers()
    {                                    // 目标模型类         中间表名                    当前模型在中间表的外键列名    目标模型在中间表的外键列名
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }

    public function followings() //关注哪些人
    {                                      // 目标模型类         中间表名                    当前模型在中间表的外键列名    目标模型在中间表的外键列名
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }

    public function feed()
    {
        $user_ids = $this->followings->pluck("user_id")->toArray();
        array_push($user_ids, $this->id);
        return Status::whereIn('user_id', $user_ids)
            ->with('user')
            ->orderBy('created_at', 'desc');
    }

    //关注某人
    public function follow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = [$user_ids];
        }
        $this->followings()->sync($user_ids, false);
    }

    //不关注某人
    public function unfollow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = [$user_ids];
        }
        $this->followings()->detach($user_ids);
    }

    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }


}
