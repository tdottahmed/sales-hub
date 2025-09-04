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

if (!function_exists('env_format_value')) {
    /**
     * Format a value for .env line.
     * - Keeps null (unquoted) if the submitted value is "null" (case-insensitive).
     * - Wraps in double quotes when value contains spaces or special chars.
     * - Escapes backslashes and quotes inside quoted values.
     */
    function env_format_value(?string $value): string
    {
        if ($value === null) {
            return 'null';
        }

        $trim = trim($value);

        if ($trim === '') {
            // Prefer empty string as ""
            return '""';
        }

        if (strtolower($trim) === 'null') {
            return 'null';
        }

        // Quote if it contains spaces or characters that commonly break parsing
        if (preg_match('/\s|["\'#]/', $trim)) {
            $escaped = addcslashes($trim, "\\\"");
            return "\"{$escaped}\"";
        }

        return $trim;
    }
}

if (!function_exists('setEnvValues')) {
    /**
     * Batch update .env values in one pass.
     * Accepts an associative array: ['KEY' => 'value', ...]
     * Returns true on success, false otherwise.
     */
    function setEnvValues(array $pairs): bool
    {
        $path = base_path('.env');
        if (!file_exists($path)) {
            return false;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return false;
        }

        // Detect original line endings to preserve style
        $eol = str_contains($content, "\r\n") ? "\r\n" : "\n";
        $lines = preg_split("/(\r\n|\n|\r)/", $content);

        // Track which keys we updated
        $keys = array_keys($pairs);
        $updated = array_fill_keys($keys, false);

        foreach ($lines as &$line) {
            // Skip comments and blank lines
            if (preg_match('/^\s*#/', $line) || trim($line) === '') {
                continue;
            }

            // Match KEY=VALUE (allowing spaces around '=')
            if (preg_match('/^\s*([A-Z0-9_]+)\s*=\s*(.*)$/', $line, $m)) {
                $key = $m[1];
                if (array_key_exists($key, $pairs)) {
                    $line = $key . '=' . env_format_value($pairs[$key]);
                    $updated[$key] = true;
                }
            }
        }
        unset($line);

        // Append any keys that were not found
        foreach ($updated as $key => $wasUpdated) {
            if (!$wasUpdated) {
                $lines[] = $key . '=' . env_format_value($pairs[$key]);
            }
        }

        $new = implode($eol, $lines);
        // Ensure ending newline
        if (!str_ends_with($new, $eol)) {
            $new .= $eol;
        }

        return file_put_contents($path, $new) !== false;
    }
}

if (!function_exists('overWriteEnvFile')) {
    /**
     * Backward-compatible single-key updater.
     * Prefer using setEnvValues([...]) to update multiple keys at once.
     */
    function overWriteEnvFile(string $type, ?string $val): bool
    {
        return setEnvValues([$type => $val]);
    }
}

