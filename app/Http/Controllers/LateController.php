<?php

namespace App\Http\Controllers;

use App\Models\Late;
use App\Models\Rayon;
use App\Models\Rombel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PDF;
use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Dompdf\Dompdf;
use Excel;
use App\Exports\LateExport;
use Illuminate\Support\Facades\Log;


class LateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $late = Late::with('student')->get();
        return view('admin.late.index', compact('late'));
        // dd($late);
    }

    public function indexPs(){
        $rayon = Rayon::where('user_id', Auth::user()->id)->first();
        $student = Student::where('rayon_id', $rayon->id)->get();
        
        // Fetch late entries related to students in the current rayon
        $late = Late::whereIn('student_id', $student->pluck('id'))->get();
        $rombel = Rombel::all();
    
        return view('pembimbing.late.index', compact('late', 'student', 'rayon', 'rombel'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $student = Student::all();
        return view('admin.late.create', compact('student'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return $request->file('bukti')->store('post_bukti');

        $validateData = $request->validate([
            'date_time_late'=>'required',
            'information'=>'required',
            'bukti'=>'image|file|max:1024',
            'student_id'=>'required',
        ]);

        if($request->file('bukti')) {
            $validateData['bukti'] = $request->file('bukti')->store('post_bukti');
        }

        Late::create($validateData);
        return redirect()->back()->with('success', 'Berhasil menambahkan data keterlambatan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $late = Late::find($id);
        return view('admin.late.edit', compact('late'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $late = Late::with('student')->find($id);
        $student = Student::all();
        return view('admin.late.edit', compact('late', 'student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate ([
            'date_time_late'=>'required',
            'information'=>'required',
            'bukti'=>'image|file|max:1024',
            'student_id'=>'required',
        ]);
        
        $late = Late::findOrFail($id);

        $late->student_id  = $request->get('student_id');
        $late->information = $request->get('information');
        $late->date_time_late = $request->get('date_time_late');

        if($request->file('bukti')) {
            if($request->oldImage) {
                Storage::delete($request->oldImage);
            }
            $late['bukti'] = $request->file('bukti')->store('post_bukti');
        }

        Late::where('id', $late->id)
        ->update([
            'student_id' => $request->get('student_id'),
            'information' => $request->get('information'),
            'date_time_late' => $request->get('date_time_late'),
            'bukti' => $request->file('bukti') ? $request->file('bukti')->store('post_bukti') : $late->bukti,
        ]);
        
        return redirect()->route('late.home')->with('success', 'Berhasil mengubah data!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $late = Late::find($id);
        
        if ($late && $late->bukti) {
            Storage::delete($late->bukti);
        }
    
        Late::destroy($id);
        return redirect()->back()->with('deleted', 'Berhasil menghapus data!');
    } 

    // public function rekap($id = null)
    // {
    //     if ($id) {
    //         $student = Student::findOrFail($id);
    //         $late = Late::with('student')->where('student_id', $id)->get();
    //         return view('admin.late.rekap', compact('student', 'late'));
    //     }
    
    //     $students = Student::with('late')->get();
    //     return view('admin.late.rekap', compact('students'));
    // }

    public function detail($id)
    {
        $student = Student::findOrFail($id);
        $late = Late::with('student')->where('student_id', $id)->get();
        return view('admin.late.detail', compact('late', 'student'));
    }

    public function detailPs($id)
    {
        $student = Student::findOrFail($id);
        $late = Late::with('student')->where('student_id', $id)->get();
        return view('Pembimbing.late.detail', compact('late', 'student'));
    }

    public function laterekap()
    {
        $rekap = Late::with('student')
            ->select('student_id', DB::raw('count(*) as total'))
            ->groupBy('student_id')
            ->get();
        // dd($rekap);

        return view('admin.late.rekap', compact('rekap'));
    }

    public function laterekapPs()
    {
        $rayon = Rayon::where('user_id', Auth::user()->id)->first();
        $student = Student::where('rayon_id', $rayon->id)->get();
        $late = Late::whereIn('student_id', $student->pluck('id'))->get();
        $rekap = Late::select('student_id', DB::raw('count(*) as total'))
            ->whereIn('student_id', $student->pluck('id'))
            ->groupBy('student_id')
            ->get();

        return view('Pembimbing.late.rekap', compact('rekap', 'late', 'rayon', 'student'));
    }

    public function print($id) 
    {
        $student = Student::findOrFail($id);
        $late = Late::with('student')->where('student_id', $id)->get();
        return view("admin.late.print", compact('late', 'student'));
    }

    public function printPs($id) 
    {
        $student = Student::findOrFail($id);
        $late = Late::with('student')->where('student_id', $id)->get();
        return view("pembimbing.late.print", compact('late', 'student'));
    }

    public function downloadPDF($id)
    {
        $late = Late::find($id)->toArray();
        view()->share('late',$late);

        $student = Student::where('id', $late['student_id'])->first()->toArray();
        view()->share('student',$student);

        $rayon = Rayon::where('id', $student['rayon_id'])->first()->toArray();
        view()->share('rayon',$rayon);
        
        $rombel = Rombel::where('id', $student['rombel_id'])->first()->toArray();
        view()->share('rombel',$rombel);

        $ps = User::where('id', $rayon['user_id'])->first()->toArray();
        view()->share('ps',$ps);

        $pdf = PDF::loadview('admin.late.download', $late);
        return $pdf->download('Surat_Pernyataan.pdf');
    }

    public function downloadPDFps($id)
    {
        $late = Late::find($id)->toArray();
        view()->share('late',$late);

        $student = Student::where('id', $late['student_id'])->first()->toArray();
        view()->share('student',$student);

        $rayon = Rayon::where('id', $student['rayon_id'])->first()->toArray();
        view()->share('rayon',$rayon);
        
        $rombel = Rombel::where('id', $student['rombel_id'])->first()->toArray();
        view()->share('rombel',$rombel);

        $ps = User::where('id', $rayon['user_id'])->first()->toArray();
        view()->share('ps',$ps);

        $pdf = PDF::loadview('pembimbing.late.download', $late);
        return $pdf->download('Surat_Pernyataan.pdf');
    }

    public function data()
    {
        $late = Late::with('student')->simplePaginate(5);
        return view("admin.late.index", compact('late'));
    }

    public function dataPs()
    {
        $late = Late::with('student')->simplePaginate(5);
        return view("pembimbing.late.index", compact('late'));
    }

    public function exportexcel()
    {
        $file_name = 'data_keterlambatan'.'.xlsx';
        return Excel::download(new LateExport, $file_name);
    }

    // public function exportexcelPs()
    // {
    //     $file_name = 'data_keterlambatan'.'.xlsx';
    //     return Excel::download(new LateExport, $file_name);
    // }

    public function exportexcelPs()
    {
    $rayonId = Rayon::where('user_id', Auth::user()->id)->first()->id;
    $export = new LateExport($rayonId);

    return Excel::download($export, 'late_export.xlsx');
    }



}