<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicAndNonAcademic extends Model
{
	use Sluggable;
    use SoftDeletes;
    protected $table = 'ac_non_ac';

     protected $fillable = [
         'title_en','title_hi','title_ur'
    ];


    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title_en'
            ]
        ];
    }

    public function getSubType()
    {

        return $this->belongsTo('App\SubType','sub_type','id');
    }

    public function getLink()
    {

        return $this->belongsTo('App\RelatedLink','id','ac_non_ac_id');
    }

    public function getHeadType()
    {

        return $this->belongsTo('App\RoleType','head','id');
    }

    public function getdepFaculty()
    {

        return $this->belongsTo('App\MappingDepartments','id','dep_id')->select('id','ac_non_ac_id','dep_id')->where('type','faculties');
    }

    public function getMapId()
    {

        return $this->hasMany('App\MappingDepartments','ac_non_ac_id','id')->select('id','ac_non_ac_id','dep_id')->where('type','faculties');
    }
    public function faculties()
    {

        return $this->hasMany('App\MappingDepartments','ac_non_ac_id','id')->select('id','ac_non_ac_id','dep_id')->where('type','faculties');
    }

    public function colleges()
    {

        return $this->hasMany('App\MappingDepartments','ac_non_ac_id','id')->select('id','ac_non_ac_id','dep_id')->where('type','colleges');
    }

    public function getMapData()
    {
        return $this->hasMany('App\MappingDepartments','ac_non_ac_id','id')->select('id','ac_non_ac_id','dep_id');
        
    }  
    public function getUser()
    {
        return $this->hasMany('App\UserVisibility', 'ac_non_ac_id');
    }
     


}
