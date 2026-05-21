<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Employee::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nip' => ['required', 'string', 'max:50', 'unique:employees,nip'],
            'nama_karyawan' => ['required', 'string', 'max:255'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'posisi_pekerjaan' => ['required', 'string', 'max:100'],
            'profesi' => ['required', 'string', 'max:100'],
            'jabatan' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nip' => 'NIP',
            'nama_karyawan' => 'Nama Karyawan',
            'unit_id' => 'Unit',
            'posisi_pekerjaan' => 'Posisi Pekerjaan',
            'profesi' => 'Profesi',
            'jabatan' => 'Jabatan',
        ];
    }
}
