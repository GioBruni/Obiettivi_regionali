<?php

namespace App\Http\Controllers;

use App\ChartTrait;
use App\Models\LocationsUsers;
use App\Models\UploatedFile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Http\Request;
use Storage;

class HomeController extends Controller
{
    use ChartTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->hasRole("uploader")) {
            $dataView['userStructures'] = LocationsUsers::where("user_id", Auth::user()->id)
                ->leftJoin("structures", "structures.id", "=", "users_structures.structure_id")
                ->leftJoin("structure_type", "structure_type.code", "=", "structures.type")
                ->orderby("structures.id")->get();
            
            $dataView['punteggi'] = [];
            // Per ogni struttura e per ogni obiettivo recupero il punteggio teorico e calcolo il punteggio ottenuto
            foreach($dataView['userStructures'] as $struttura) {
                if ($struttura->column_points === 'ao') {
                    $punteggioTeoria = DB::table("points")
                        ->select("points.target_number", "points.target", "points.sub_target", "points.ao as points");
                } elseif ($struttura->column_points === 'asp') {
                    $punteggioTeoria = DB::table("points")
                        ->select("points.target_number", "points.target", "points.sub_target", "points.asp as points");
                }

                $punteggioRaggiunto = [];
                $obiettivo = 3;
                $tmp = DB::table("uploated_files")
                    ->where("target_number", $obiettivo)
                    ->where("structure_id", $struttura->structure_id)
                    ->latest()->first();
                if (isset($tmp)) {
                    $punteggioRaggiunto[$obiettivo] = $tmp->approved == 1 ? $punteggioTeoria->clone()->where("target_number", $obiettivo)->select($struttura->column_points)->first() : 0;
                }
                foreach($punteggioTeoria->orderby("id")->get() as $rowTmp) {
                    $dataView['punteggi'][$struttura->name][] = [
                        "target_number" => $rowTmp->target_number, 
                        "target" => $rowTmp->target, 
                        "sub_target" => $rowTmp->sub_target, 
                        "points" => $rowTmp->points, 
                        "real_points" => (isset($punteggioRaggiunto[$rowTmp->target_number]) ? $punteggioRaggiunto[$rowTmp->target_number]->asp : 0)
                    ];
                }
            }
        } else {
            $dataView['userStructures'] = null;
        }

        $data = [1,2,3];
        $labels = ['gen', 'feb', 'mar'];
        $dataset = [
            [
                "label" => "Label reg",
                "backgroundColor" => "rgba(38, 185, 154, 0.31)",
                "borderColor" => "rgba(38, 185, 154, 0.7)",
                "data" => $data
            ]
            ];
        $size = ['width' => 400, 'height' => 200];
        $option = [];
        $dataView['chart'] = $this->showChart("line", "nome", $size, $labels, $dataset, $option);

        return view('home')->with("dataView", $dataView);
    }


    public function showObiettivo(Request $request) {
        $dataView['categorie'] = DB::table("target_categories")
            ->where("target_number", $request->obiettivo)
            ->orderBy("order")
            ->get();

        switch ($request->obiettivo) {
            case 3:
                $dataView['titolo'] = "check list nascite";
                $dataView['files'][] = "obiettivo3.pdf";
                $dataView['strutture'] = Auth::user()->structures();

                $dataView['filesCaricati'] = DB::table('uploated_files as uf')
                ->join('target_categories as tc', 'uf.target_category_id', '=', 'tc.id')
                ->select('uf.validator_user_id', 'uf.approved','uf.notes', 'uf.path', 'uf.filename', 'uf.target_category_id', 'tc.category', 'uf.updated_at', 'uf.user_id', 'uf.created_at')
                ->where('uf.target_number', 3)
                ->whereRaw('uf.created_at = (SELECT MAX(u2.created_at)
                                            FROM uploated_files as u2 
                                            WHERE u2.target_category_id = uf.target_category_id)')
                ->whereRaw('uf.updated_at = (SELECT MAX(u3.updated_at)
                                            FROM uploated_files as u3
                                            WHERE u3.target_category_id = uf.target_category_id
                                            AND u3.created_at = uf.created_at)')
                ->orderBy('uf.created_at', 'desc')
                ->orderBy('uf.updated_at', 'desc')
                ->get();

                break;
        }
        $dataView['obiettivo'] = $request->obiettivo;

        return view("showFormObiettivo")->with("dataView", $dataView);
    }


    public function uploadFileObiettivo(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:5096',
        ]);

        $file = $request->file('file');
        $path = $request->file('file')->store('uploads', 'public');
        $url = Storage::url($path);
       
        // Salva le informazioni nel database
        UploatedFile::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $url,
            'user_id' => Auth::user()->id, 
            'structure_id' => $request->structure_id,
            'notes' => null,
            'target_number' => $request->obiettivo,
            'target_category_id' => $request->has("categoria") ? $request->categoria : null,
        ]);

        return redirect()->back()->with('success', 'File caricato con successo e in attesa di approvazione.');
    }

}
