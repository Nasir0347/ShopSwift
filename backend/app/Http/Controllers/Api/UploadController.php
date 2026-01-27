<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:10240', // Max 10MB
        ]);

        if ($request->file('file')->isValid()) {
            $file = $request->file('file');
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            
            // Store in 'public/uploads'
            $path = $file->storeAs('uploads', $filename, 'public');
            
            if ($path) {
                // Return full URL
                $url = asset('storage/' . $path);
                // For development with 'php artisan serve', asset() might point correctly if configured.
                // If using Valet/Xampp, it depends.
                // Let's ensure storage link exists.
                return $this->success(['url' => $url, 'path' => $path], 'File uploaded successfully');
            }
        }

        return $this->error('File upload failed', 500);
    }
}
