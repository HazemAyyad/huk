<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait Blameable
{
    public static function bootBlameable()
    {
        // عند الإنشاء
        static::creating(function ($model) {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }
        });

        // عند التعديل
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        // عند الحذف
        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }
}