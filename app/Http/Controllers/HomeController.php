<?php

namespace App\Http\Controllers;

use App\ChartTrait;
use App\Models\LocationsUsers;
use App\Models\PCT;
use App\Models\UploatedFile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Http\Request;
use Storage;

class HomeController extends Controller
{
    use ChartTrait;

    protected $dataViewSaluteEFunzionamento = [];

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
                $obiettivo = 8;
                $tmp = DB::table("uploated_files")
                    ->where("target_number", $obiettivo)
                    ->where("structure_id", $struttura->structure_id)
                    ->latest()->first();
                if (isset($tmp)) {
                    $punteggioRaggiunto[$obiettivo] = $tmp->approved == 1 ? $punteggioTeoria->clone()->where("target_number", $obiettivo)->select($struttura->column_points)->first() : 0;
                }
                $obiettivo = 9;
                $punteggioRaggiunto[$obiettivo] = 0;
                $tmp = DB::table("uploated_files")
                    ->join("target_PCT", "target_PCT.uploated_file_id", "=", "uploated_files.id")
                    ->where("target_number", $obiettivo)
                    ->where("target_PCT.structure_id", $struttura->structure_id)
                    ->select("target_PCT.numerator", "target_PCT.denominator", "uploated_files.approved")
                    ->latest("target_PCT.updated_at")->first();
                if (isset($tmp) && $tmp->approved == 1) {
                    $rapporto = round($tmp->numerator / $tmp->denominator * 100, 2);
                    $punteggioRaggiunto[$obiettivo] += ($rapporto >= 80) ? 2.5 : (round($rapporto / 80 * 2.5, 2));
                }

                foreach($punteggioTeoria->orderby("id")->get() as $rowTmp) {
                    //if (isset($punteggioRaggiunto[$rowTmp->target_number]))
                    //    dd($punteggioRaggiunto[$rowTmp->target_number]->{$struttura->column_points});
                    $raggiunto = 0;
                    switch ($rowTmp->target_number) {
                        case 3:
                        case 8:
                            $raggiunto = isset($punteggioRaggiunto[$rowTmp->target_number]->{$struttura->column_points}) ? $punteggioRaggiunto[$rowTmp->target_number]->{$struttura->column_points} : 0;
                            break;                            
                        case 9:
                            $raggiunto = $punteggioRaggiunto[9];

                    }
                    $dataView['punteggi'][$struttura->name][] = [
                        "target_number" => $rowTmp->target_number, 
                        "target" => $rowTmp->target, 
                        "sub_target" => $rowTmp->sub_target, 
                        "points" => $rowTmp->points, 
                        "real_points" => $raggiunto,
                    ];
                }
            }
        } else {
            $dataView['userStructures'] = null;
        }

        $this->dataViewSaluteEFunzionamento[1] = [
            'icon' => 'fas fa-stopwatch',
            'text' => 'Prestazioni sanitarie',
            'tooltip' => 'Riduzione dei tempi delle liste di attesa delle prestazioni sanitarie ',
            'route' => null, //route('indexAmbulatoriale')
            'enable' => false,
        ];
        $this->dataViewSaluteEFunzionamento[2] = [
            'icon' => 'fas fa-bed-pulse',
            'text' => 'Esiti',
            'tooltip' => 'Esiti',
            'route' => null, //route('chart-esiti')
            'enable' => false,
        ];
        $this->dataViewSaluteEFunzionamento[3] = [
            'icon' => 'fa-solid fa-person-pregnant',
            'text' => 'Checklist punti nascita',
            'tooltip' => 'Rispetto degli standard di sicurezza dei punti nascita',
            'route' => route("showObiettivo", ["obiettivo" => 3]),
            'enable' => true,
        ];
        $this->dataViewSaluteEFunzionamento[4] = [
            'icon' => 'fas fa-truck-medical',
            'text' => 'Sovraffollamento PS',
            'tooltip' => 'Pronto Soccorso - Gestione del sovraffollamento',
            'route' => null, //route('chart-ps')
            'enable' => false,
        ];
        $this->dataViewSaluteEFunzionamento[5] = [
            'icon' => 'fas fa-heartbeat',
            'text' => 'Screening',
            'tooltip' => 'Screening oncologici',
            'route' => null, //route('indexScreening')
            'enable' => false,
        ];
        $this->dataViewSaluteEFunzionamento[6] = [
            'icon' => 'fas fa-hand-holding-medical',
            'text' => 'Donazioni',
            'tooltip' => 'Donazione sangue, plasma, organi e tessuti',
            'route' => null ,//route('indexDonazioni')
            'enable' => false,
        ];
        $this->dataViewSaluteEFunzionamento[7] = [
            'icon' => 'fas fa-file-medical',
            'text' => 'Fascicolo Sanitario Elettronico',
            'tooltip' => 'Fascicolo Sanitario Elettronico',
            'route' => null,
            'enable' => false,
        ];
        $this->dataViewSaluteEFunzionamento[8] = [
            'icon' => 'fas fa-check-circle',
            'text' => 'Percorso attuativo di certificabilità',
            'tooltip' => 'Percorso attuativo di certificabilità (P.A.C.)',
            'route' => route("showObiettivo", ["obiettivo" => 8]),
            'enable' => true,
        ];
        $this->dataViewSaluteEFunzionamento[9] = [
            'icon' => 'fas fa-pills',
            'text' => 'Farmaci',
            'tooltip' => 'Approvvigionamento farmaci e gestione I ciclo di terapia',
            'route' => route('indexFarmaci'),
            'enable' => true,
        ];
        $this->dataViewSaluteEFunzionamento[10] = [
            'icon' => 'fas fa-tasks',
            'text' => 'Garanzia dei LEA',
            'tooltip' => 'Area della Performance: garanzia dei LEA nell\'Area della Prevenzione, dell\'Assistenza Territoriale e dell\'Assistenza Ospedaliera secondo il Nuovo Sistema di Garanzia (NSG)',
            'route' => null, //route('indexLEA')
            'enable' => false,
        ];
        $dataView['saluteEFunzionamento'] = $this->dataViewSaluteEFunzionamento;


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

        $vista = null;
        switch ($request->obiettivo) {
            case 3:
                $dataView['titolo'] = $this->dataViewSaluteEFunzionamento[$request->obiettivo]['text'];
                $dataView['icona'] = $this->dataViewSaluteEFunzionamento[$request->obiettivo]['icon'];
                $dataView['tooltip'] = $this->dataViewSaluteEFunzionamento[$request->obiettivo]['tooltip'];

                $dataView['files'][] = "obiettivo3.pdf";
                $dataView['strutture'] = Auth::user()->structures();

                $dataView['filesCaricati'] = DB::table('uploated_files as uf')
                ->join('target_categories as tc', 'uf.target_category_id', '=', 'tc.id')
                ->select('uf.id', 'uf.validator_user_id', 'uf.approved','uf.notes', 'uf.path', 'uf.filename', 'uf.target_category_id', 'tc.category', 'uf.updated_at', 'uf.user_id', 'uf.created_at')
                ->where('uf.target_number', $request->obiettivo)
                ->whereIn("uf.structure_id", $dataView['strutture']->pluck("id")->toArray())
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

            case 8:
                $dataView['titolo'] = $this->dataViewSaluteEFunzionamento[$request->obiettivo]['text'];
                $dataView['icona'] = $this->dataViewSaluteEFunzionamento[$request->obiettivo]['icon'];
                $dataView['tooltip'] = $this->dataViewSaluteEFunzionamento[$request->obiettivo]['tooltip'];
                //$dataView['files'][] = "obiettivo3.pdf";
                $dataView['strutture'] = Auth::user()->structures();

                $dataView['filesCaricati'] = DB::table('uploated_files as uf')
                ->join('target_categories as tc', 'uf.target_category_id', '=', 'tc.id')
                ->select('uf.id', 'uf.validator_user_id', 'uf.approved','uf.notes', 'uf.path', 'uf.filename', 'uf.target_category_id', 'tc.category', 'uf.updated_at', 'uf.user_id', 'uf.created_at')
                ->where('uf.target_number', $request->obiettivo)
                ->whereIn("uf.structure_id", $dataView['strutture']->pluck("id")->toArray())
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


    public function indexFarmaci(Request $request) {
        $dataView['strutture'] = Auth::user()->structures();
        $dataView['PCT'] = PCT::where("user_id", Auth::user()->id)
        ->latest()->first();

        if(! ($dataView['PCT'])) {
            $pct = new PCT();
            $pct->year = date('Y');
            $pct->begin_month = 1;
            $pct->end_month = date('n');
            $pct->structure_id = $dataView['strutture']->first()->id;

            $dataView['PCT'] = $pct;
        }

        return view("farmaci")->with("dataView", $dataView);
    }

    public function farmaciPCT(Request $request) {

    }
}
