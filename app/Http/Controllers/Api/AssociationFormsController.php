<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Association\AssociationForms;
use App\Services\Association\AssociationMedia;
use Illuminate\Http\Request;

final class AssociationFormsController extends Controller
{
    public function __construct(private AssociationForms $forms) {}
    public function index(Request $request, string $module)
    {
        $filter = $request->validate(['q' => 'nullable|string|max:120', 'status' => 'nullable|string|max:30', 'page' => 'nullable|integer|min:1', 'form_id' => 'nullable|integer|min:1']);

        return response()->json(['data' => $this->forms->adminList($request->user(), $module, $filter)])->header('Cache-Control', 'no-store');
    }
    public function show(Request $request, string $module, int $id) { return response()->json(['data' => $this->forms->adminShow($request->user(), $module, $id)])->header('Cache-Control', 'no-store'); }
    public function save(Request $request, string $module, ?int $id = null)
    {
        abort_unless($module === 'forms' || $id !== null, 404);

        return response()->json(['data' => $module === 'forms' ? $this->forms->saveForm($request->user(), $request->all(), $id) : $this->forms->editSubmission($request->user(), $id, $request->all())]);
    }
    public function action(Request $request, string $module, int $id)
    {
        abort_unless($request->input('action') === 'delete' || ($module === 'submissions' && $request->input('action') === 'review'), 422);

        return response()->json(['data' => $request->input('action') === 'delete' ? $this->forms->delete($request->user(), $module, $id, $request->all()) : $this->forms->review($request->user(), $id, $request->all())]);
    }
    public function portalIndex(Request $request) { return response()->json(['data' => $this->forms->portalList($request->user())])->header('Cache-Control', 'no-store'); }
    public function portalShow(Request $request, int $form) { return response()->json(['data' => $this->forms->portalShow($request->user(), $form)])->header('Cache-Control', 'no-store'); }
    public function submit(Request $request, int $form) { return response()->json(['data' => $this->forms->submit($request->user(), $form, $request->all())]); }
    public function upload(Request $request, int $form, AssociationMedia $media)
    {
        $data = $request->validate(['field' => 'required|string|max:64', 'file' => 'required|file']);
        $this->forms->attachmentField($request->user(), $form, $data['field']);

        return response()->json(['data' => $media->store($request->user(), 'submissions', $data['field'], $request->file('file'), true)]);
    }
}
