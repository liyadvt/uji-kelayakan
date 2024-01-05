<?php
namespace App\Exports;

use App\Models\Late;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;

class LateExport implements FromCollection, WithHeadings, WithMapping
{
    protected $rayonId;

    public function __construct($rayonId)
    {
        $this->rayonId = $rayonId;
    }

    public function collection()
    {
        return Late::select('student_id', DB::raw('count(*) as total'))
            ->whereIn('student_id', Student::where('rayon_id', $this->rayonId)->pluck('id'))
            ->groupBy('student_id')
            ->with(['student' => function ($query) {
                $query->with('rombel', 'rayon'); 
            }])
            ->get();
    }

    public function headings(): array
    {
        return [
            "Nis", "Nama", "Rombel", "Rayon", "Total Keterlambatan"
        ];
    }

    public function map($late): array
    {
        $student = $late->student;

        return [
            $student->nis,
            $student->name,
            $student->rombel->rombel,
            $student->rayon->rayon,
            $late->total,
        ];
    }
}
