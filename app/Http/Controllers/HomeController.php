<?php

namespace App\Http\Controllers;

use App\ChartTrait;
use App\Models\LocationsUsers;
use App\Models\PCT;
use App\Models\UploatedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Storage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use App\Models\InsertMmg;
use PDF;
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
            foreach ($dataView['userStructures'] as $struttura) {
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

                foreach ($punteggioTeoria->orderby("id")->get() as $rowTmp) {
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
                        "real_points" => (isset($punteggioRaggiunto[$rowTmp->target_number]) ? $punteggioRaggiunto[$rowTmp->target_number]->asp : 0)
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
            'route' => null,//route('indexDonazioni')
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


        $data = [1, 2, 3];
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


    public function showObiettivo(Request $request)
    {
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
                    ->select('uf.validator_user_id', 'uf.approved', 'uf.notes', 'uf.path', 'uf.filename', 'uf.target_category_id', 'tc.category', 'uf.updated_at', 'uf.user_id', 'uf.created_at')
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


    public function prontoSoccorso()
    {

        $dataView['companyCode'] = DB::table('users_structures as us')
            ->join('structures as s', 'us.structure_id', '=', 's.id')
            ->select('s.company_code')
            ->distinct()
            ->pluck('company_code');


        $dataView['flowEmur'] = DB::table('flows_emur as fe')
            ->join('structures as s', 'fe.structure_id', '=', 's.id')
            ->select('fe.tmp', 'fe.year AS anno', 'fe.month AS mese', 's.name AS nome_struttura', 's.company_code', 'fe.boarding')
            ->whereIn('s.company_code', $dataView['companyCode'])
            ->get();


        $overallAverageTmp = $dataView['flowEmur']->avg('tmp');
        $complementaryValueTmp = 100 - $overallAverageTmp;

        $overallAverageBoarding = $dataView['flowEmur']->avg('boarding');
        $complementaryValueBoarding = 100 - $overallAverageBoarding;

        $labelsTmp = [
            'TMP pos',
            'TMP neg'
        ];

        $labelsBoarding = [
            'Boarding pos',
            'Boarding neg'
        ];

        $dataGraficoTmp = [$overallAverageTmp, $complementaryValueTmp];

        $dataView['chartTmp'] = Chartjs::build()
            ->name("OverallAvgTmpComplementaryPieChart")
            ->type("doughnut")
            ->size(["width" => 300, "height" => 150])
            ->labels($labelsTmp)
            ->datasets([
                [
                    "label" => "Percentuale TMP",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(217, 83, 79, 0.7)",
                    ],
                    "data" => $dataGraficoTmp
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Distribuzione Percentuale: TMP e Differenza Media Totale'
                    ]
                ]
            ]);


        $dataGraficoBoarding = [$overallAverageBoarding, $complementaryValueBoarding];

        //secondo grafico
        $dataView['chartBoarding'] = Chartjs::build()
            ->name("OverallAvgBoardingComplementaryPieChart")
            ->type("doughnut")
            ->size(["width" => 300, "height" => 150])
            ->labels($labelsBoarding)
            ->datasets([
                [
                    "label" => "Percentuale Boarding",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(217, 83, 79, 0.7)",
                    ],
                    "data" => $dataGraficoBoarding
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Distribuzione Percentuale: Boarding e Differenza Media Totale'
                    ]
                ]
            ]);


        if ($overallAverageTmp >= 85) {
            $dataView['messaggioTmp'] = [
                'text' => "Pieno raggiungimento dell'obiettivo",
                'class' => 'text-success'
            ];
        } elseif ($overallAverageTmp >= 75) {
            $dataView['messaggioTmp'] = [
                'text' => "Raggiungimento dell'obiettivo al 50%",
                'class' => 'text-warning'
            ];
        } else { // se $overallAverageTmp < 75
            $dataView['messaggioTmp'] = [
                'text' => "Obiettivo non raggiunto",
                'class' => 'text-danger'
            ];
        }

        /********************************************************* */

        if ($overallAverageBoarding <= 2) {
            $dataView['messaggioBoarding'] = [
                'text' => "Pieno raggiungimento dell'obiettivo",
                'class' => 'text-success'
            ];
        } elseif ($overallAverageBoarding > 2 && $overallAverageBoarding <= 4) {
            $dataView['messaggioBoarding'] = [
                'text' => "Raggiungimento dell'obiettivo al 50%",
                'class' => 'text-warning'
            ];
        } elseif ($overallAverageBoarding > 4) {
            $dataView['messaggioBoarding'] = [
                'text' => "Obiettivo non raggiunto",
                'class' => 'text-danger'
            ];
        }

        return view("controller.prontoSoccorso", [
            'dataView' => $dataView,
            'overallAverageTmp' => $overallAverageTmp,
            'overallAverageBoarding' => $overallAverageBoarding,
        ]);

    }

    public function donazioni()
    {

        $datoSdo = DB::table('flows_sdo')->pluck('sdo_dato')->toArray();


        $labelsTmp = ['Label 1', 'Label 2'];
        $labelsBoarding = ['Label 3', 'Label 4'];


        $dataGraficoTmp = [
            $datoSdo[0] ?? 0,
            $datoSdo[1] ?? 0
        ];

        $dataView['chartDonazioni'] = Chartjs::build()
            ->name("OverallAvgTmpComplementaryBarChart")
            ->type("bar")
            ->size(["width" => 300, "height" => 150])
            ->labels($labelsTmp)
            ->datasets([
                [
                    "label" => "Percentuale TMP",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "data" => $dataGraficoTmp
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Distribuzione Percentuale: TMP e Differenza Media Totale'
                    ],
                    'legend' => [
                        'display' => false
                    ]
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                    ],
                ],
            ]);


        return view("controller.donazioni")->with('dataView', $dataView);
    }



    public function tempiListeAttesa()
    {
        $numeratore = 99609;  //per ora statico

        $dataView['flussoC'] = DB::table('flows_c')
            ->select('denominatore as denominatore_c', 'structure_id')
            ->get();

        $dataView['flussoM'] = DB::table('flows_m')
            ->select('denominatore as denominatore_m', 'structure_id')
            ->get();

        $DenominatoreC = DB::table('flows_c')->sum('denominatore');
        $DenominatoreM = DB::table('flows_m')->sum('denominatore');

        $dataView['denominatoreTotale'] = $DenominatoreC + $DenominatoreM;

        if ($dataView['denominatoreTotale'] > 0) {
            $overallPercentuale = ($numeratore / $dataView['denominatoreTotale']) * 100;
        } else {
            $overallPercentuale = 0;
        }

        //dd($overallPercentuale);
        $dataView['percentuali'] = array_fill(0, count($dataView['flussoC']), $overallPercentuale);

        $labelsTmp = ['Label 1'];

        $dataView['tempiListeAttesa'] = Chartjs::build()
            ->name("OverallAvgTmpComplementaryBarChart")
            ->type("doughnut")
            ->size(["width" => 300, "height" => 150])
            ->labels($labelsTmp)
            ->datasets([
                [
                    "label" => "Percentuale TMP",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "data" => [$overallPercentuale]
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Distribuzione Percentuale: TMP e Differenza Media Totale'
                    ]
                ]
            ]);
        return view("controller.tempiListeAttesa")->with("dataView", $dataView);
    }



    public function screening()
{
    // Mi serve per prendere i dati solo per il file di obiettivo 5
    $dataView['file'] = DB::table('uploated_files')
        ->where('user_id', Auth::user()->id)
        ->where('target_number', 5)
        ->select('target_number')
        ->get();

    // Dati per la tabella nella view 
    $dataView['tableData'] = DB::table('insert_mmg')
        ->select('mmg_totale', 'mmg_coinvolti', 'anno')
        ->get();

    $record = DB::table('insert_mmg')->select('mmg_totale', 'mmg_coinvolti')->first();
    $dataView['noData'] = false;

    if ($record && $record->mmg_totale != 0) {
        $dataView['percentualeCoinvolti'] = ($record->mmg_coinvolti / $record->mmg_totale) * 100;
        $dataView['percentualeNonCoinvolti'] = 100 - $dataView['percentualeCoinvolti'];
    } else {
        $dataView['percentualeCoinvolti'] = 0;
        $dataView['percentualeNonCoinvolti'] = 0;
        $dataView['noData'] = true;
    }

    $dataView['mmgChart'] = Chartjs::build()
        ->name("OverallAvgTmpComplementaryBarChart")
        ->type("doughnut")
        ->size(["width" => 300, "height" => 150])
        ->labels(['MMG Coinvolti', 'MMG Non Coinvolti'])
        ->datasets([
            [
                "label" => "Percentuali MMG",
                "backgroundColor" => [
                    "rgba(38, 185, 154, 0.7)",
                    "rgba(255, 99, 132, 0.7)"
                ],
                "data" => $dataView['noData'] 
                    ? [0, 0] 
                    : [number_format($dataView['percentualeCoinvolti'], 2), number_format($dataView['percentualeNonCoinvolti'], 2)]
            ]
        ])
        ->options([
            'responsive' => true,
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => $dataView['noData'] 
                        ? 'Non ci sono dati disponibili' 
                        : 'Distribuzione Percentuale: MMG Coinvolti e Non Coinvolti'
                ]
            ]
        ]);

    return view("controller.screening")->with("dataView", $dataView);
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


    public function uploadFileScreening(Request $request)
    {
        // Validate the file input
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:5096',
        ]);

        // Check if a file is uploaded
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');
            $url = Storage::url($path);


            UploatedFile::create([
                'filename' => $file->getClientOriginalName(),
                'path' => $url,
                'user_id' => Auth::user()->id,
                'structure_id' => 93,
                'notes' => null,
                'target_number' => $request->obiettivo,
                'target_category_id' => null,
            ]);

            return redirect()->back()->with('success', 'File caricato con successo e in attesa di approvazione.');
        }

        return redirect()->back()->with('error', 'Nessun file caricato.');
    }



    public function mmgRegister(Request $request)
    {
        $messages = [
            'tot_mmg.required' => 'Il totale MMG è obbligatorio.',
            'tot_mmg.numeric' => 'Il totale MMG deve essere un numero.',
            'mmg_coinvolti.required' => 'Il numero di MMG coinvolti è obbligatorio.',
            'mmg_coinvolti.numeric' => 'Il numero di MMG coinvolti deve essere un numero.',
            'mmg_coinvolti.lte' => 'Il numero di MMG coinvolti deve essere minore o uguale al totale MMG.',
            'anno.required' => 'L\'anno è obbligatorio.',
            'anno.integer' => 'L\'anno deve essere un numero intero.',
        ];


        $request->validate([
            'tot_mmg' => 'required|numeric',
            'mmg_coinvolti' => 'required|numeric|lte:tot_mmg', // mmg_coinvolti <= tot_mmg
            'anno' => 'required|integer',
        ], $messages);


        $anno = $request->anno;
        $tot_mmg = $request->tot_mmg;
        $mmg_coinvolti = $request->mmg_coinvolti;


        InsertMmg::create([
            'mmg_totale' => $tot_mmg,
            'mmg_coinvolti' => $mmg_coinvolti,
            'anno' => $anno,
        ]);


        return redirect()->route('screening');
    }


    public function downloadPdf(Request $request)
    {

        $dataView['tableData'] = DB::table('insert_mmg')
            ->select('mmg_totale', 'mmg_coinvolti', 'anno')
            ->get();


        $pdf = PDF::loadView('emails.screeningPdf', $dataView);

        return $pdf->download('certificazione_completa.pdf');
    }


}
