<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Sluggable;
    use Notifiable;
    use SoftDeletes;
    use HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'email', 'password','first_name','last_name','middle_name','first_name_hi','last_name_hi','middle name_hi','first_name_ur','last_name_ur','middle name_ur','status','slug' ,'profile','designation','image','address','eid','for_id','retired','csv','otp','url'
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

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => ['first_name', 'last_name'],
            ]
        ];
    }



    public function getRole()
    {
        return $this->hasMany('App\UserRole', 'user_id');
    }

    public function getAdminRole()
    {
        return $this->belongsTo('App\UserRole','id', 'user_id');
    }

    public function getContact()
    {
        return $this->hasMany('App\UserContact', 'user_id')/*->select('id','user_id','email','mobile_no','telephone_no','address')*/;
    }

    public function getPrimaryContact()
    {
        return $this->belongsTo('App\UserContact','id', 'user_id')->where('primary','1')->select('id','user_id','email','mobile_no','primary');
    }

   /* public function getPrimaryContact()
    {
        return $this->hasMany('App\UserContact', 'user_id')->where('primary','1')->select('id','user_id','email','mobile_no','primary');
    }*/

    public function getStudyMaterial()
    {
        return $this->hasMany('App\StudyMatrial', 'user_id')->orderBy('order_on','ASC');
    }

    public function getStudyMaterialFile()
    {
        return $this->hasMany('App\StudyMatrial', 'user_id')->where('file','!=', '')->select('id','user_id','title','type','file','status','order_on','created_at','updated_at')->orderBy('order_on','ASC');
    }

    public function getStudyMaterialVideo()
    {
        return $this->hasMany('App\StudyMatrial', 'user_id')->where('video','!=', '')->select('id','user_id','title','type','video','status','order_on','created_at','updated_at')->orderBy('order_on','ASC');
    }

    public function getTimeTable()
    {
        return $this->hasMany('App\TimeTable', 'user_id')->orderBy('order_on','ASC');
    }

    public function getQualification()
    {
        return $this->hasMany('App\Qualification', 'user_id')->select('id','user_id','qualification','from_year','to_year','from_where')->orderBy('order_on','asc');
    }

    public function getJournal()
    {
        return $this->hasMany('App\Journal', 'user_id')->select('id','user_id','publish_date','status','title','pdf_url','description', 'featured','updated_at')->where('status','1')->where('featured','0')->orderBy('updated_at','DESC');
    }
    public function getKeyJournal()
    {
        return $this->hasMany('App\Journal', 'user_id')->select('id','user_id','publish_date','status','title','pdf_url','description', 'featured','updated_at')->where('status','1')->where('featured','1')->orderBy('updated_at','DESC');
    }

    public function getThrustArea()
    {
        return $this->hasMany('App\ThrustArea', 'user_id')->select('id','user_id','title');
    }

    public function getDesignation()
    {
        return $this->belongsTo('App\Designation', 'designation');
    }
    
    public function getDepartment()
    {
        return $this->hasOne('App\UserVisibility', 'user_id')->select('id','user_id','ac_non_ac_id','designation_id','core')->where('core','1');
    }



    public function getUserCoreRole()
    {
        return $this->hasOne('App\UserVisibility','user_id')->select('id','user_id','role_id','core','ac_non_ac_id')->where('core','1');
    }
}
