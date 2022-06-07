<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

 trait GenerateUUIDTrait {
     public static function boot()
     {
         parent::boot();

         self::creating(function ($model) {
             $model->{$model->getKeyName()} = (string) Str::orderedUuid();
         });
     }
 }
