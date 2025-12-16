<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_]+$/',
                Rule::unique('sites', 'name'),
            ],
            'repo_url' => [
                'required',
                'url',
                'regex:/^https:\/\/(github\.com|gitlab\.com)\/[\w\-]+\/[\w\-]+(.git)?$/',
            ],
            'branch' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-_\/]+$/',
            ],
            'deploy_path' => [
                'required',
                'string',
                'max:255',
                'regex:/^\/[a-zA-Z0-9\-_\/]+$/',
                function ($attribute, $value, $fail) {
                    // Prevent path traversal
                    if (str_contains($value, '..')) {
                        $fail('The deployment path cannot contain ".."');
                    }
                    // Ensure path is absolute and within safe directories
                    if (!str_starts_with($value, '/var/www/') && !str_starts_with($value, '/home/')) {
                        $fail('The deployment path must be within /var/www/ or /home/');
                    }
                },
            ],
            'port' => [
                'nullable',
                'integer',
                'min:1024',
                'max:65535',
            ],
            'domain' => [
                'required_if:domain_strategy,custom',
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/',
            ],
            'domain_strategy' => [
                'required',
                'string',
                Rule::in(['subdomain', 'custom']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'The site name may only contain letters, numbers, hyphens, and underscores.',
            'name.unique' => 'A site with this name already exists.',
            'repo_url.regex' => 'The repository URL must be a valid GitHub or GitLab URL.',
            'branch.regex' => 'The branch name contains invalid characters.',
            'deploy_path.regex' => 'The deployment path must be a valid absolute path.',
            'port.min' => 'Port must be greater than 1024 (privileged ports not allowed).',
            'domain.required_if' => 'A custom domain is required when using custom domain strategy.',
            'domain.regex' => 'The domain name is not valid.',
        ];
    }
}
