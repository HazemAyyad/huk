<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolidarityHousing extends Model
{

    protected $guarded = [];

    protected $casts = [
        'needs_prosthetic' => 'boolean',
        'received_aid_from_haq' => 'boolean',
        'has_companion' => 'boolean',
        'has_fixed_income' => 'boolean',
        'birth_date' => 'date',
        'entry_date' => 'date',
        'amputation_date' => 'date',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->case_code) {
                $last = self::orderBy('id', 'desc')->first();
                $lastNumber = $last ? intval(substr($last->case_code, 1)) : 0;
                $model->case_code = 'C' . ($lastNumber + 1);
            }
        });
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters["code"] ?? false, function ($query, $code) {
            $query
                ->where("case_code",  $code);
        });

        $query->when($filters["national_id"] ?? false, function ($query, $national_id) {
            $query
                ->where("national_id",  $national_id);
        });

        $query->when($filters["name"] ?? false, function ($query, $name) {
            $query
                ->where("full_name", "like", "%" . $name . "%");
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

    public function familyMembers()
    {
        return $this->hasMany(SolidarityFamilyMember::class);
    }

    public function messages()
    {
        return $this->hasMany(SolidarityHousingMessage::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}
