<?php

use App\Models\ApplicationSetup;
use Illuminate\Support\Facades\Storage;

if (!function_exists('uploadFile')) {
    /**
     * Upload a file to the specified directory.
     *
     * @param \Illuminate\Http\UploadedFile $file The file to be uploaded.
     * @param string $directory The directory to which the file should be uploaded.
     * @return string|null The path of the file if the upload is successful, or null if the upload fails.
     */
    function uploadFile($file, $directory)
    {
        if (!$file) {
            return null;
        }
        Storage::makeDirectory($directory);
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs($directory, $fileName, 'public');
        return $filePath;
    }
}

if (!function_exists('deleteFile')) {
    /**
     * Delete a file from storage.
     *
     * @param string $filePath The path to the file to be deleted.
     * @return void
     */

    function deleteFile($filePath)
    {
        Storage::delete($filePath);
    }
}

if (!function_exists('getFilePath')) {
    /**
     * Get the full URL for a given file path.
     *
     * @param string|null $path The relative file path stored in the public storage.
     * @return string The full URL to access the file or a default image if the path is null.
     */

    function getFilePath($path)
    {
        if ($path) {
            return asset('storage/' . $path);
        } else {
            return asset('/assets/admin/images/user-illustarator-1.png');
        }
    }
}
if (!function_exists('filepondUpload')) {
    /**
     * Upload an image file.
     *
     * @param string $base64Image The base64 encoded image data.
     * @param string $directory The directory to store the image.
     * @return string|null The file path of the uploaded image or null on failure.
     */
    function filepondUpload($base64Image, $directory = 'uploads')
    {
        $imageData = json_decode($base64Image, true);
        if (isset($imageData['data']) && isset($imageData['name'])) {
            $decodedFile = base64_decode($imageData['data']);
            $fileName = uniqid() . '-' . $imageData['name'];
            $filePath = $directory . '/' . $fileName;
            Storage::disk('public')->put($filePath, $decodedFile);
            return $filePath;
        }
        return null;
    }
}
if (!function_exists('getSetting')) {
    /**
     * Retrieve the value of a setting by its name.
     *
     * @param string $name The name of the setting to retrieve.
     * @return string The value of the setting or an empty string if not found.
     */

    function getSetting($name)
    {
        $setting = ApplicationSetup::where('type', $name)->first();
        return $setting ? $setting->value : '';
    }
}
