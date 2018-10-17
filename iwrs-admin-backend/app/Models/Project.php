<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Site;

class Project extends Site
{
	public $timestamp = false;

    public function sponsor()
    {
        return $this->belongsTo('App\Models\Organization', 'sponsor')->select('id', 'name');
    }

    public function adminer()
    {
        return $this->belongsTo('App\Models\User', 'conductor_id')->select('id', 'realname', 'organization_id');
    }

    public function field()
    {
        return $this->hasOne('App\Models\Field', 'id', 'field')->select('id', 'name');
    }
}
