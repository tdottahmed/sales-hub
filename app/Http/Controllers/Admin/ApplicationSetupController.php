<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ApplicationSetupController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:view applicationSettings')->only('index');
    //     $this->middleware('permission:create applicationSettings')->only('update');
    // }

    public function index()
    {
        $applicationSetup = ApplicationSetup::get();
        return view('admin.application-setup.index', compact('applicationSetup'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token', 'app_logo', 'app_favicon', 'login_banner');
        try {
            foreach ($data as $type => $value) {
                ApplicationSetup::updateOrCreate(['type' => $type], ['value' => $value]);
            }
            if ($request->has('app_logo') || $request->has('app_favicon') || $request->has('login_banner')) {
                if ($request->has('app_logo')) {
                    $filePath = filepondUpload($request->app_logo, 'organization');
                    if ($filePath) {
                        ApplicationSetup::updateOrCreate(['type' => 'app_logo'], ['value' => $filePath]);
                    }
                }
                if ($request->has('app_favicon')) {
                    $imagePath = filepondUpload($request->app_favicon, 'organization');
                    ApplicationSetup::updateOrCreate(['type' => 'app_favicon'], ['value' => $imagePath]);
                }
                if ($request->has('login_banner')) {
                    $imagePath = filepondUpload($request->login_banner, 'organization');
                    ApplicationSetup::updateOrCreate(['type' => 'login_banner'], ['value' => $imagePath]);
                }
            }
            return redirect()->route('applicationSetup.index')->with('success', 'Organization Updated Successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function updateProfitMargin(Request $request)
    {
        $request->validate([
            'profit_margin' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            ApplicationSetup::updateOrCreate(
                ['type' => 'profit_margin'],
                ['value' => $request->profit_margin]
            );

            return redirect()->route('applicationSetup.index')
                ->with('success', 'Profit Margin Updated Successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }


    public function setupApi()
    {
        return view('admin.application-setup.setup-api');
    }

    public function updateEnv(Request $request)
    {
        $allowedKeys = [
            'SUPPLIER_BASE_URL',
            'SUPPLIER_API_KEY',
            'MAIL_MAILER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_ENCRYPTION',
            'MAIL_FROM_ADDRESS',
            'MAIL_FROM_NAME',
            'SELLER_API_KEY',
        ];

        $payload = collect($request->only($allowedKeys))
            ->filter(fn($v) => $v !== null)
            ->all();

        $rules = [
            'SUPPLIER_BASE_URL' => ['sometimes', 'url'],
            'SUPPLIER_API_KEY' => ['sometimes', 'string'],
            'SELLER_API_KEY' => ['sometimes', 'string'],
            'MAIL_MAILER' => ['sometimes', 'string'],
            'MAIL_HOST' => ['sometimes', 'string'],
            'MAIL_PORT' => ['sometimes', 'integer', 'between:1,65535'],
            'MAIL_USERNAME' => ['sometimes', 'nullable', 'string'],
            'MAIL_PASSWORD' => ['sometimes', 'nullable', 'string'],
            'MAIL_ENCRYPTION' => ['sometimes', 'nullable', 'in:tls,ssl,null'],
            'MAIL_FROM_ADDRESS' => ['sometimes', 'email'],
            'MAIL_FROM_NAME' => ['sometimes', 'string'],
        ];

        $request->validate(array_intersect_key($rules, $payload));
        foreach (['MAIL_PASSWORD'] as $secretKey) {
            if (array_key_exists($secretKey, $payload) && $payload[$secretKey] === '') {
                unset($payload[$secretKey]);
            }
        }
        setEnvValues($payload);
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
        } catch (\Throwable $e) {
            logger()->warning('Config/cache clear failed after env update: ' . $e->getMessage());
        }

        return back()->with('success', __('Environment variables updated successfully.'));
    }
}
