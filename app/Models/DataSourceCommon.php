<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BindsDynamicallyDatasource;
use OwenIt\Auditing\Contracts\Auditable;


class DataSourceCommon extends Model implements Auditable
{
    use BindsDynamicallyDatasource;
    use \OwenIt\Auditing\Auditable;
    protected $connection = 'pgsql';

    protected $primaryKey = 'application_id';

    protected $guarded = [];
    public function getBenidAttribute()
    {
        return $this->created_by_dist_code . substr('0' . $this->scheme_id, -$this->scheme_length) . substr('0000000' . $this->id, -$this->id_length);
        //  return "{$this->created_by_dist_code}{$this->scheme_id}{$this->id}";
    }
    public function getName()
    {
        return "{$this->ben_fname}";
    }
    public function getFatherName()
    {
        return "{$this->father_fname} {$this->father_mname} {$this->father_lname}";
    }
    public function district()
    {
        return $this->belongsTo('App\District', 'dist_code', 'district_code');
    }
    public function assembly()
    {
        return $this->belongsTo('App\Assembly', 'assembly_code', 'ac_no');
    }
    public function urban()
    {
        return $this->belongsTo('App\UrbanBody', 'block_ulb_code', 'urban_body_code');
    }
    public function taluka()
    {
        return $this->belongsTo('App\Taluka', 'block_ulb_code', 'block_code');
    }

    public function gp()
    {
        return $this->belongsTo('App\GP', 'gp_ward_code', 'gram_panchyat_code');
    }
    public function ward()
    {
        return $this->belongsTo('App\Ward', 'gp_ward_code', 'urban_body_ward_code');
    }

    public function Scheme()
    {
        return $this->belongsTo('App\Scheme', 'scheme_id', 'id');
    }
}
