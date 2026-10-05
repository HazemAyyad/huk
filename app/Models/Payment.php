<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // دالة لحساب عدد الشهور
    public function getMonthsCoveredAttribute()
    {
        $basicSalary = $this->user->basic_salary;
        if ($basicSalary > 0) {
            return floor($this->amount / $basicSalary); // عدد الشهور الكاملة
        }
        return 0;
    }
}
