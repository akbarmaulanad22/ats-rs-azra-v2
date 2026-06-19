<?php

namespace App\Http\Requests;

use App\Enums\VacancyStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isHrAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jumlah_posisi' => ['required', 'integer', 'min:1'],
            'tenggat_lamaran' => ['required', 'date', 'after_or_equal:today'],
            'flyer' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'kualifikasi' => ['nullable', 'string'],
            'status' => ['required', Rule::in([VacancyStatus::Draft->value, VacancyStatus::Published->value])],
        ];
    }

    public function messages(): array
    {
        return [
            'jumlah_posisi.required' => 'Jumlah posisi wajib diisi.',
            'jumlah_posisi.min' => 'Jumlah posisi minimal 1.',
            'tenggat_lamaran.required' => 'Tenggat lamaran wajib diisi.',
            'tenggat_lamaran.after_or_equal' => 'Tenggat lamaran tidak boleh di masa lalu.',
            'flyer.required' => 'Flyer lowongan wajib diunggah.',
            'flyer.image' => 'Flyer harus berupa gambar.',
            'flyer.mimes' => 'Flyer harus berformat JPG, PNG, atau WEBP.',
            'flyer.max' => 'Ukuran flyer maksimal 4 MB.',
            'status.required' => 'Status wajib dipilih.',
        ];
    }
}
