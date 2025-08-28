<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetup;
use Illuminate\Http\Request;

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
            dd($e->getMessage());
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
