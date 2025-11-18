<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class Configduty extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $table = 'duty_assignement';
	protected $fillable = ['user_id', 'taluka_code', 'urban_body_code', 'scheme_id', 'user_id', 'mapping_level', 'district_code', 'is_urban', 'is_active'];


	public function hasRole($role)
	{
		return User::where('designation_id', $role)->get();
	}

	public function district()
	{
		return $this->belongsTo('App\Model\District', 'district_code', 'district_code');
	}
	public function urban()
	{
		return $this->belongsTo('App\Model\UrbanBody', 'urban_body_code', 'urban_body_code');
	}
	public function taluka()
	{
		return $this->belongsTo('App\Model\Taluka', 'taluka_code', 'block_code');
	}
	public function subdiv()
	{
		return $this->belongsTo('App\Model\SubDistrict', 'urban_body_code', 'sub_district_code');
	}
	public function user()
	{
		return $this->belongsTo('App\Model\User', 'user_id', 'id');
	}
	public function Scheme()
	{
		return $this->belongsTo('App\Model\Scheme', 'scheme_id', 'id');
	}
	public function Department()
	{
		return $this->belongsTo('App\Model\Department', 'urban_body_code', 'id');
	}
}
