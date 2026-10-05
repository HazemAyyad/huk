<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramUser;


use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class QrController extends Controller
{


public function generateQr($id)
{
    $user = TelegramUser::findOrFail($id);

    // ✅ إذا الباركود موجود مسبقًا وما بدك تعيد إنشاؤه
    if (!empty($user->qr_code) && file_exists(public_path($user->qr_code))) {
        return view('users.qr', [
            'user' => $user,
            'nationalId' => $user->national_id
        ]);
    }

    $nationalId = $user->national_id;
    $fileName = 'qr_' . $nationalId . '.png';
    $folderPath = public_path('uploads/qr_codes');

    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    $result = Builder::create()
        ->writer(new PngWriter())
        ->data($nationalId)
        ->size(300)
        ->margin(10)
        ->build();

    $result->saveToFile($folderPath . '/' . $fileName);

    // حفظ المسار في الداتا بيز
    $user->qr_code = 'uploads/qr_codes/' . $fileName;
    $user->save();

    return view('users.qr', compact('user', 'nationalId'));
}

}   
