<?php

namespace App\Http\Controllers;

use App\Models\PCT;
use App\Models\Structure;
use App\Models\UploatedFile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Storage;

class PdfController extends Controller
{
    public function farmaciAutodichiarazionePdf(Request $request)
    {

        if ($request->has("numeratore")) {
            $request->validate(rules: [
                'numeratore' => 'required|int',
                'denominatore' => 'required|int',
                'structure_id' => 'required|int',
            ]);
    
    
            $struttura = Structure::find($request->input('structure_id'))->first();
            $rapporto = round(($request->input('numeratore') / $request->input('denominatore')) * 100, 2);
            PCT::create([
                "year" => $request->input( 'year'),
                "begin_month" => $request->input('begin_month'),
                "end_month" => $request->input('end_month'),
                "numerator" => $request->input('numeratore'),
                'denominator' => $request->input('denominatore'),
                'structure_id' => $struttura->id,
                'user_id' => Auth::user()->id, 
            ]);
    
            $data = [
                'numeratore' => $request->input('numeratore'),
                'denominatore' => $request->input('denominatore'),
                'struttura' => $struttura,
                'rapporto' => $rapporto,
                'data' => date('d/m/Y'),
            ];
    
        } else { // Recupero l'ultimo dato inserito
            $pct = PCT::where("user_id", Auth::user()->id)
            ->latest()->first();
            $rapporto = round(($pct->numerator / $pct->denominator) * 100, 2);
            $struttura = Structure::find($pct->structure_id)->first();

            $data = [
                'numeratore' => $pct->numerator,
                'denominatore' => $pct->denominator,
                'struttura' => $struttura,
                'rapporto' => $rapporto,
                'data' => date('d/m/Y', strtotime($pct->updated_at)),
            ];
    
        }

        // Carica la vista e passa i dati
        $pdf = PDF::loadView('pdfs.farmaciAutodichiarazione', $data);

        // Scarica il PDF
        return $pdf->download('documento.pdf');
    }



    public function farmaciAutodichiarazionePdfUpolad(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:5096',
        ]);

        $file = $request->file('file');
        $path = $request->file('file')->store('uploads', 'public');
        $url = Storage::url($path);
       
        $pct = PCT::where("user_id", Auth::user()->id)->latest()->first();

        // Salva le informazioni nel database
        $uploatedFile = UploatedFile::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $url,
            'user_id' => Auth::user()->id, 
            'structure_id' => $pct->structure_id,
            'notes' => null,
            'target_number' => 9,
            'target_category_id' => null,
        ]);
        if ($uploatedFile) // Recupero l'ultimo dato inserito
            $pct->update([
                "uploated_file_id" => $uploatedFile->id,
            ]);

        return redirect()->back()->with('success', 'File caricato con successo e in attesa di approvazione.');

    }


}
