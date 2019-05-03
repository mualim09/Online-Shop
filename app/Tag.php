<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    public function products()
  {
      return $this->belongsToMany('App\Product', 'product_tag')
        ->withTimestamps();
  }
}