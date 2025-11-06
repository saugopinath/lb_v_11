<?php
namespace App\Traits;
use Illuminate\Http\Request;
trait BindsDynamicallyDoc
{
    
    protected $table = null;

    public function bind(string $table)
    {
       
        $this->setTable($table);
    }

    public function newInstance($attributes = [], $exists = false)
    {
        // Overridden in order to allow for late table binding.

        $model = parent::newInstance($attributes, $exists);
        $model->setTable($this->table);

        return $model;
    }

}