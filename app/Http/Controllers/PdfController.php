<?php

namespace App\Http\Controllers;

use App\Models\Gare;
use App\Models\PCT;
use App\Models\Structure;
use App\Models\UploatedFile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Storage;
use DB;

class PdfController extends Controller
{

    public function tempiListeAttesaAutodichiarazionePdf($data)
    {
        // Carica la vista e passa i dati
        $pdf = PDF::loadView('pdfs.tempiListaAttesaAutodichiarazione', $data);

        // Scarica il PDF
        return $pdf->download('tempiListaAttesa.pdf');
    }


    public function farmaciGarePdf(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:5096',
            'dataAppalto' => 'required|date',
            'numeroDecreto' => 'required',
            'protocolloDecreto' => 'required',
            'dataProtocolloDecreto' => 'required|date',
            'structure_id' => 'required|int|gt:0',
        ]);
        $file = $request->file('file');
        $path = $request->file('file')->store('uploads', 'public');
        $url = Storage::url($path);

        // Salva le informazioni nel database
        $uploatedFile = UploatedFile::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $url,
            'user_id' => Auth::user()->id, 
            'structure_id' => $request->structure_id,
            'notes' => null,
            'target_number' => 91, // imposto 91 per indicare 9.1; così lo escludo dal controller che cerca solo obiettivi senza punto e <=10
            'target_category_id' => null,
            'year' => date('Y', strtotime($request->dataAppalto)),
        ]);
        Gare::create([
            "year" => date('Y', strtotime($request->dataAppalto)),
            "data_appalto" => $request->dataAppalto,
            "structure_id" => $request->structure_id,
            "uploated_file_gara_id" => $uploatedFile->id,
            "numero_decreto" => $request->numeroDecreto,
            "protocollo_decreto" => $request->protocolloDecreto,
            "data_protocollo_decreto" => $request->dataProtocolloDecreto,
        ]);

        $dataView['gare'] = Gare::where("structure_id", $request->structure_id)
            ->get();

        return redirect()->back()->with('success', 'File di gara caricato con successo.')
            ->with( "dataView", $dataView);
    }


    public function farmaciDeliberePdf(Request $request)
    {
        $request->validate([
            'gara' => 'required|int',
            'fileDelibera' => 'required|file|mimes:pdf|max:5096',
            'dataDelibera' => 'required|date',
            'numeroDelibera' => 'required',
        ]);
        $gara = Gare::where("id", $request->gara);

        $file = $request->file('fileDelibera');
        $path = $request->file('fileDelibera')->store('uploads', 'public');
        $url = Storage::url($path);
        // Salva le informazioni nel database
        $uploatedFile = UploatedFile::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $url,
            'user_id' => Auth::user()->id, 
            'structure_id' => $gara->first()->structure_id,
            'notes' => null,
            'target_number' => 91, // imposto 91 per indicare 9.1; così lo escludo dal controller che cerca solo obiettivi senza punto e <=10
            'target_category_id' => null,
            'year' => date('Y', strtotime($request->dataDelibera)),
        ]);
        $gara->update([
            "numero_delibera" => $request->numeroDelibera,
            "data_delibera" => $request->dataDelibera,
            "anno_delibera" => date('Y', strtotime($request->dataDelibera)),
            "uploated_file_delibera_id" => $uploatedFile->id,
        ]);

        $dataView['gare'] = Gare::where("structure_id", $request->structure_id)
            ->get();

        return redirect()->back()->with('success', 'File di gara caricato con successo.')
            ->with("dataView", $dataView);
    }




    public function farmaciAutodichiarazionePdf(Request $request)
    {
        if ($request->has("numeratore")) {
    
            $request->validate([
                'numeratore' => 'required|int|min:0',
                'structure_id' => 'required|int|gt:0',
            ]);
            $denominatore = DB::table("flows_sdo")
                ->select(DB::raw("sum(ob9_2) as tot"))
                ->where("year", $request->input( 'year'))
                ->first();
            $struttura = Structure::where("id", $request->input('structure_id'))->first();
            $rapporto = round(($request->input('numeratore') / $denominatore->tot) * 100, 2);
            PCT::where("year", $request->input( 'year'))->delete();
            PCT::create([
                "year" => $request->input( 'year'),
                "begin_month" => -1, //$request->input('begin_month'),
                "end_month" => -1, //$request->input('end_month'),
                "numerator" => $request->input('numeratore'),
                'structure_id' => $request->input('structure_id'),
            ]);
    
            $data = [
                'anno' => $request->input('year'),
                'numeratore' => $request->input('numeratore'),
                'denominatore' => $denominatore->tot,
                'struttura' => $struttura,
                'rapporto' => $rapporto,
                'data' => date('d/m/Y'),
            ];
    
        } else { // Recupero l'ultimo dato inserito
            $strutturaId = Auth::user()->firstStructureId()->id;
            dd($strutturaId);
            $pct = PCT::where("structure_id", $strutturaId)
            ->latest()->first();
            $ultimoAnno = DB::table('flows_sdo')
                ->where("structure_id", $strutturaId)
                ->max('year');
            $denominatore = DB::table("flows_sdo")
                ->select(DB::raw("sum(ob9_2) as tot"))
                ->where("year", $ultimoAnno)
                ->where("structure_id", $strutturaId)
                ->get();

            $rapporto = round(($pct->numerator / $denominatore) * 100, 2);
            $struttura = Structure::where("id", $pct->structure_id)->first();
            $data = [
                'anno' => $pct->year,
                'numeratore' => $pct->numerator,
                'denominatore' => $denominatore,
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
            'structure_id' => 'required|int|gt:0',
        ]);

        $file = $request->file('file');
        $path = $request->file('file')->store('uploads', 'public');
        $url = Storage::url($path);
       
        $strutturaId = Auth::user()->firstStructureId()->id;
        $pct = PCT::where("structure_id", $strutturaId)
            ->where("year", $request->year)
            ->orderBy('created_at', 'desc')
            ->first();
        $gare = Gare::where("structure_id", $strutturaId)
            ->where("year", $request->year)
            ->orderBy('created_at', 'desc')
            ->first();

        // Salva le informazioni nel database
        $uploatedFile = UploatedFile::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $url,
            'user_id' => Auth::user()->id, 
            'structure_id' => $strutturaId,
            'notes' => null,
            'target_number' => 9,
            'target_category_id' => null,
            'year' => $request->year,
        ]);
        if ($uploatedFile) { // Recupero l'ultimo dato inserito
            $pct->update([
                "uploated_file_id" => $uploatedFile->id,
            ]);
            $gare->update([
                "uploated_file_id" => $uploatedFile->id,
            ]);
        }
        return redirect()->back()->with('success', 'File caricato con successo e in attesa di approvazione.');

    }

}
