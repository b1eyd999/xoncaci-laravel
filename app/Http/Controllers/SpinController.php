<?php

namespace App\Http\Controllers;

use App\Models\Xonca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * The 360° view of a xonça: frames cut from the owner's turntable video in
 * his browser (resources/js/admin-spin.js), sent here a few at a time so no
 * request runs into the host's upload limits. A new set is written beside
 * the old one and replaces it only when it is complete.
 */
class SpinController extends Controller
{
    public function store(Request $request, Xonca $xonca)
    {
        $data = $request->validate([
            'token' => ['required', 'regex:/^[a-z0-9]{8,32}$/'],
            'start' => 'required|integer|min:0|max:200',
            'frames' => 'array|max:12',
            'frames.*' => 'file|mimes:webp,jpg,jpeg,png|max:2048',
            'finish' => 'nullable|boolean',
        ]);

        $disk = Storage::disk('public');
        $dir = "spin/{$xonca->id}/{$data['token']}";

        foreach ($request->file('frames', []) as $i => $file) {
            $n = str_pad((string) ($data['start'] + $i), 3, '0', STR_PAD_LEFT);
            $disk->putFileAs($dir, $file, "{$n}." . ($file->extension() === 'jpeg' ? 'jpg' : $file->extension()));
        }

        if (! $request->boolean('finish')) {
            return ['ok' => true];
        }

        $frames = collect($disk->files($dir))->sort()->values()->all();
        abort_if(count($frames) < 8, 422, 'Too few frames');

        foreach ($disk->directories("spin/{$xonca->id}") as $old) {
            if ($old !== $dir) {
                $disk->deleteDirectory($old);
            }
        }
        $xonca->update(['spin' => $frames]);

        return ['ok' => true, 'frames' => count($frames)];
    }

    public function destroy(Xonca $xonca)
    {
        Storage::disk('public')->deleteDirectory("spin/{$xonca->id}");
        $xonca->update(['spin' => null]);

        return ['ok' => true];
    }
}
