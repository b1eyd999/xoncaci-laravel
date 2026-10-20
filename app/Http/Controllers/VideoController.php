<?php

namespace App\Http\Controllers;

use App\Models\Xonca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * The xonça's video: the owner picks it in the admin
 * (resources/js/admin-video.js) and his browser sends it in small pieces,
 * so the host's upload limit never sees the whole file. The pieces are
 * joined in a private folder and the film replaces the old one only once
 * it is complete and really is a video.
 */
class VideoController extends Controller
{
    public const MAX_BYTES = 300 * 1024 * 1024;

    public function store(Request $request, Xonca $xonca)
    {
        $data = $request->validate([
            'token' => ['required', 'regex:/^[a-z0-9]{8,32}$/'],
            'offset' => 'required|integer|min:0|max:' . self::MAX_BYTES,
            'chunk' => 'nullable|file|max:2048',
            'finish' => 'nullable|boolean',
            'poster' => 'nullable|file|mimes:webp,jpg,jpeg,png|max:2048',
        ]);

        $tmp = Storage::disk('local');
        $part = "video-parts/{$xonca->id}-{$data['token']}.part";
        $size = $tmp->exists($part) ? $tmp->size($part) : 0;

        if ($chunk = $request->file('chunk')) {
            $bytes = file_get_contents($chunk->getRealPath());
            // A retried piece that already arrived is accepted and skipped.
            if ($size === $data['offset'] + strlen($bytes)) {
                return ['ok' => true, 'size' => $size];
            }
            abort_unless($size === (int) $data['offset'], 409, 'Out of order');
            abort_if($size + strlen($bytes) > self::MAX_BYTES, 413, 'Too large');
            // Appended straight to the file: Storage::append() would read the whole film back each time.
            $tmp->makeDirectory('video-parts');
            file_put_contents($tmp->path($part), $bytes, $size ? FILE_APPEND : 0);
            $size += strlen($bytes);
        }

        if (! $request->boolean('finish')) {
            return ['ok' => true, 'size' => $size];
        }

        abort_unless($size > 0 && $request->hasFile('poster'), 422, 'Nothing to save');
        $head = (string) file_get_contents($tmp->path($part), false, null, 0, 12);
        $ext = match (true) {
            substr($head, 4, 4) === 'ftyp' => 'mp4',
            str_starts_with($head, "\x1A\x45\xDF\xA3") => 'webm',
            default => null,
        };
        if (! $ext) {
            $tmp->delete($part);
            abort(422, 'Not a video');
        }

        $public = Storage::disk('public');
        $public->deleteDirectory("video/{$xonca->id}");
        $public->writeStream("video/{$xonca->id}/{$data['token']}.{$ext}", $stream = fopen($tmp->path($part), 'rb'));
        fclose($stream);
        $tmp->delete($part);
        $poster = $public->putFileAs("video/{$xonca->id}", $request->file('poster'), "{$data['token']}.webp");

        $xonca->update(['video' => "video/{$xonca->id}/{$data['token']}.{$ext}", 'video_poster' => $poster]);

        return ['ok' => true, 'url' => $xonca->videoUrl()];
    }

    public function destroy(Xonca $xonca)
    {
        Storage::disk('public')->deleteDirectory("video/{$xonca->id}");
        $xonca->update(['video' => null, 'video_poster' => null]);

        return ['ok' => true];
    }
}
