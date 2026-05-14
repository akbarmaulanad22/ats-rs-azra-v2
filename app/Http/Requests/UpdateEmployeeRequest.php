<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('employee'));
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;

        return [
            'nip' => ['required', 'string', 'max:50', "unique:employees,nip,{$employeeId}"],
            'nama_karyawan' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:100'],
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
            'unit' => 'Unit',
            'posisi_pekerjaan' => 'Posisi Pekerjaan',
            'profesi' => 'Profesi',
            'jabatan' => 'Jabatan',
        ];
    }
}
