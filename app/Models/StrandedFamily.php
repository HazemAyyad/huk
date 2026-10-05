<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StrandedFamily extends Model
{
    protected $guarded = [];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters["code"] ?? false, function ($query, $code) {
            $query
                ->where("case_code",  $code);
        });

        $query->when($filters["national_id"] ?? false, function ($query, $national_id) {
            $query
                ->where("head_national_id",  $national_id);
        });

        $query->when($filters["name"] ?? false, function ($query, $name) {
            $query
                ->where("head_full_name", "like", "%" . $name . "%");
        });

        $query->when($filters["governorate"] ?? false, function ($query, $governorate) {
            $query
                ->where("governorate_id", $governorate);
        });

        $query->when($filters["city"] ?? false, function ($query, $city) {
            $query
                ->where("city_id", $city);
        });

        $query->when($filters["area"] ?? false, function ($query, $area) {
            $query
                ->where("area_id", $area);
        });

        $query->when($filters["area"] ?? false, function ($query, $area) {
            $query
                ->where("area_id", $area);
        });

        $query->when($filters["from"] ?? false, function ($query, $from) {
            $query
                ->whereDate("created_at", '>=', $from);
        });

        $query->when($filters["to"] ?? false, function ($query, $to) {
            $query
                ->whereDate("created_at", '<=', $to);
        });

        $query->when($filters["status"] ?? false, function ($query, $status) {
            switch ($status) {
                case "1":
                    $query->where("is_reviewed", 0)->where('is_sent_for_attendance', 0)
                        ->where('is_attended', 0)->where('is_benefited', 0);
                    break;
                case "2":
                    $query->where("is_reviewed", 1)->where('is_sent_for_attendance', 0)
                        ->where('is_attended', 0)->where('is_benefited', 0);
                    break;
                case "3":
                    $query->where("is_reviewed", 1)->where('is_sent_for_attendance', 1)
                        ->where('is_attended', 0)->where('is_benefited', 0);
                    break;
                case "4":
                    $query->where("is_reviewed", 1)->where('is_sent_for_attendance', 1)
                        ->where('is_attended', 1)->where('is_benefited', 0);
                    break;
                case "5":
                    $query->where("is_reviewed", 1)->where('is_sent_for_attendance', 1)
                        ->where('is_attended', 1)->where('is_benefited', 1);
                    break;
                default:
                    break;
            }
        });
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->case_code) {
                $last = self::orderBy('id', 'desc')->first();
                $lastNumber = $last ? intval(substr($last->case_code, 1)) : 0;
                $model->case_code = 'E' . ($lastNumber + 1);
            }
        });
    }

    // علاقة بأفراد الأسرة (الأبناء وغيرهم عدا الزوجة)
    public function members()
    {
        return $this->hasMany(StrandedFamilyMember::class);
    }

    // عدد الأسرة تلقائياً: رب الأسرة + الزوجة (إذا موجودة) + الأعضاء
    public function getFamilySizeAttribute()
    {
        $base = 1; // رب الأسرة
        if ($this->wife_name) $base += 1; // + الزوجة إذا لها اسم
        return $base + $this->members()->count();
    }

    // حساب عمر رب الأسرة تلقائياً
    protected static function booted()
    {
        static::saving(function ($family) {
            if ($family->head_birth_date) {
                $family->head_age = Carbon::parse($family->head_birth_date)->age;
            }
        });
    }
}