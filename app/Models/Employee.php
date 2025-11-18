<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Employee extends Model implements Auditable
{
    /**
    * The attributes that aren't mass assignable.
    *
    * @var array
    */
    use \OwenIt\Auditing\Auditable;
    protected $guarded = [];
    protected $primaryKey='id';


    public function Departments()
    {
        
        return $this->belongsTo('App\Department','department_id');
    }
}
