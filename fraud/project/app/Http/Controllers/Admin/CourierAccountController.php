<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CourierAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /** @return array<string, array{label:string, fields:array<int, array{key:string, label:string, type:string}>}> */
    private function couriers(): array
    {
        return [
            'pathao' => [
                'label'  => 'Pathao',
                'icon'   => 'fas fa-motorcycle',
                'fields' => [
                    ['key' => 'PATHAO_USER', 'label' => 'User ID / Email', 'type' => 'text'],
                    ['key' => 'PATHAO_PASSWORD', 'label' => 'Password', 'type' => 'password'],
                ],
            ],
            'steadfast' => [
                'label'  => 'SteadFast',
                'icon'   => 'fas fa-truck',
                'fields' => [
                    ['key' => 'STEADFAST_USER', 'label' => 'User ID / Email', 'type' => 'text'],
                    ['key' => 'STEADFAST_PASSWORD', 'label' => 'Password', 'type' => 'password'],
                ],
            ],
            'redx' => [
                'label'  => 'RedX',
                'icon'   => 'fas fa-shipping-fast',
                'fields' => [
                    ['key' => 'REDX_PHONE', 'label' => 'Phone / User ID', 'type' => 'text'],
                    ['key' => 'REDX_PASSWORD', 'label' => 'Password', 'type' => 'password'],
                ],
            ],
            'paperfly' => [
                'label'  => 'PaperFly',
                'icon'   => 'fas fa-paper-plane',
                'fields' => [
                    ['key' => 'PAPERFLY_USER', 'label' => 'User ID / Email', 'type' => 'text'],
                    ['key' => 'PAPERFLY_PASSWORD', 'label' => 'Password', 'type' => 'password'],
                ],
            ],
            'carrybee' => [
                'label'  => 'CarryBee',
                'icon'   => 'fas fa-box',
                'fields' => [
                    ['key' => 'CARRYBEE_PHONE', 'label' => 'Phone / User ID', 'type' => 'text'],
                    ['key' => 'CARRYBEE_PASSWORD', 'label' => 'Password', 'type' => 'password'],
                ],
            ],
        ];
    }

    public function index()
    {
        $couriers = $this->couriers();
        $envValues = $this->readEnvValues();
        $values = [];

        foreach ($couriers as $courier) {
            foreach ($courier['fields'] as $field) {
                $values[$field['key']] = $envValues[$field['key']]
                    ?? config('fraud-checker-bd-courier.' . $this->configKeyForEnv($field['key']), '')
                    ?? '';
            }
        }

        return view('admin.fraud.courier-accounts', compact('couriers', 'values'));
    }

    /** @return array<string, string> */
    private function readEnvValues(): array
    {
        $path = app()->environmentFilePath();
        if (! is_file($path)) {
            return [];
        }

        $values = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return [];
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            if (
                (str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))
            ) {
                $val = substr($val, 1, -1);
                $val = stripcslashes($val);
            }
            $values[$key] = $val;
        }

        return $values;
    }

    private function configKeyForEnv(string $envKey): string
    {
        $map = [
            'PATHAO_USER' => 'pathao.user',
            'PATHAO_PASSWORD' => 'pathao.password',
            'STEADFAST_USER' => 'steadfast.user',
            'STEADFAST_PASSWORD' => 'steadfast.password',
            'REDX_PHONE' => 'redx.phone',
            'REDX_PASSWORD' => 'redx.password',
            'PAPERFLY_USER' => 'paperfly.user',
            'PAPERFLY_PASSWORD' => 'paperfly.password',
            'CARRYBEE_PHONE' => 'carrybee.phone',
            'CARRYBEE_PASSWORD' => 'carrybee.password',
        ];

        return $map[$envKey] ?? '';
    }

    public function update(Request $request)
    {
        $couriers = $this->couriers();
        $rules = [];

        foreach ($couriers as $courier) {
            foreach ($courier['fields'] as $field) {
                $rules[$field['key']] = 'nullable|string|max:255';
            }
        }

        $validated = $request->validate($rules);

        foreach ($validated as $key => $value) {
            // Empty password fields keep previous value
            if (str_ends_with($key, '_PASSWORD') && ($value === null || $value === '')) {
                continue;
            }
            $this->setEnvValue($key, (string) ($value ?? ''));
        }

        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
        } catch (\Throwable $e) {
            // ignore artisan failures in restricted hosts
        }

        return redirect()
            ->route('admin.fraud.couriers')
            ->with('success', 'কুরিয়ার অ্যাকাউন্ট ইউজার আইডি ও পাসওয়ার্ড সফলভাবে আপডেট হয়েছে।');
    }

    private function setEnvValue(string $key, string $value): void
    {
        $envPath = app()->environmentFilePath();
        if (! is_file($envPath) || ! is_writable($envPath)) {
            abort(500, '.env ফাইল লিখা যাচ্ছে না। পারমিশন চেক করুন।');
        }

        $content = file_get_contents($envPath);
        if ($content === false) {
            abort(500, '.env ফাইল পড়া যাচ্ছে না।');
        }

        $escaped = $this->escapeEnvValue($value);
        $line = $key . '=' . $escaped;
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $line, $content, 1);
        } else {
            $content = rtrim($content) . PHP_EOL . $line . PHP_EOL;
        }

        file_put_contents($envPath, $content);
    }

    private function escapeEnvValue(string $value): string
    {
        if ($value === '' || preg_match('/[\s#"\'\\\\$]/', $value)) {
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
        }

        return $value;
    }
}
