<?php

namespace App\Exports;

use App\Models\MeatRegistration;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MeatRegistrationsExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = MeatRegistration::with('telegramUser');

        // البحث
        if ($this->request->search) {
            $search = $this->request->search;

            $query->whereHas('telegramUser', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('egypt_number', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // كرشة
        if ($this->request->want_tripe !== null && $this->request->want_tripe !== '') {
            $query->where('want_tripe', $this->request->want_tripe);
        }

        // رجلين
        if ($this->request->want_legs !== null && $this->request->want_legs !== '') {
            $query->where('want_legs', $this->request->want_legs);
        }

        // مرافق منفصل
        if ($this->request->separate_family !== null && $this->request->separate_family !== '') {
            $query->where('separate_family', $this->request->separate_family);
        }

        // عدد الأفراد
        if ($this->request->min_members) {
            $query->whereHas('telegramUser', function ($q) {
                $q->where('family_members', '>=', $this->request->min_members);
            });
        }

        if ($this->request->max_members) {
            $query->whereHas('telegramUser', function ($q) {
                $q->where('family_members', '<=', $this->request->max_members);
            });
        }

        // التاريخ
        if ($this->request->date_from) {
            $query->whereDate('created_at', '>=', $this->request->date_from);
        }

        if ($this->request->date_to) {
            $query->whereDate('created_at', '<=', $this->request->date_to);
        }

        // الإيجار
        if ($this->request->max_rent) {
            $query->whereHas('telegramUser', function ($q) {
                $q->where('rent_amount', '<=', $this->request->max_rent);
            });
        }

        return $query->get()->map(function ($reg, $index) {
            return [
                '#' => $index + 1,
                'الاسم' => $reg->telegramUser->full_name ?? '-',
                'رقم الهوية' => $reg->telegramUser->national_id ?? '-',
                'عدد الأفراد' => $reg->telegramUser->family_members ?? '-',
                'رقم الهاتف' => $reg->telegramUser->egypt_number ?? '-',
                'المحافظة' => $reg->telegramUser->governorate ?? '-',
                'العنوان' => $reg->telegramUser->address ?? '-',
                'الإيجار' => $reg->telegramUser->rent_amount ?? '-',
                'كرشة' => $reg->want_tripe ? 'نعم' : 'لا',
                'رجلين' => $reg->want_legs ? 'نعم' : 'لا',
                'مرافق منفصل' => $reg->separate_family ? 'نعم' : 'لا',
                'تاريخ التسجيل' => optional($reg->created_at)->format('Y-m-d'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'الاسم',
            'رقم الهوية',
            'عدد الأفراد',
            'رقم الهاتف',
            'المحافظة',
            'العنوان',
            'الإيجار',
            'كرشة',
            'رجلين',
            'مرافق منفصل',
            'تاريخ التسجيل',
        ];
    }
}