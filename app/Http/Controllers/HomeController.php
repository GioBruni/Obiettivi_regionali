<?php

namespace App\Http\Controllers;

use App\ChartTrait;
use App\Models\LocationsUsers;
use App\Models\PCT;
use App\Models\UploatedFile;
use App\Models\CUPTarget1;
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    protected function punteggioOb1_1($anno, $meseInizio, $meseFine) {
        $dataView['numeratore'] = DB::table('cup_model_target1')
            ->select(DB::raw(value: 'SUM(amount) as totale_quantita'))
            ->whereYear('provision_date', $anno)
            ->whereMonth('provision_date', ">=", $meseInizio)
            ->whereMonth('provision_date', "<=", $meseFine)
            ->where("structure_id", Auth::user()->firstStructureId()->id)
            //->groupBy('nomenclator_code')
            ->sum('amount');

        $denominatoreC = DB::table(table: 'flows_c')
            ->where("structure_id", Auth::user()->firstStructureId()->id)
            ->where('year', $anno)
            ->whereBetween('month', [$meseInizio, $meseFine])
            ->sum('ob1_1');
        $DenominatoreM = DB::table('flows_m')            
            ->where("structure_id", Auth::user()->firstStructureId()->id)
            ->where('year', $anno)
            ->whereBetween('month', [$meseInizio, $meseFine])
            ->sum('ob1_1');

        $dataView['denominatore'] = $denominatoreC + $DenominatoreM;
        if ($dataView['denominatore'] > 0) {
            $overallPercentuale = round(($dataView['numeratore'] / $dataView['denominatore']) * 100, 2);
        } else {
            $overallPercentuale = 0;
        }
        $dataView['percentuale'] = $overallPercentuale;

        // Se la percentuale è sotto 100, la calcoliamo normalmente
        if ($overallPercentuale <= 100) {
            $punteggio = ($overallPercentuale / 100) * 5;
        } else {
            // Se la percentuale supera il 100%, applichiamo una penalizzazione
            // La logica penalizza linearmente in base all'eccesso rispetto al 100%.
            // Esempio: 120% darà punteggio 4, 140% darà punteggio 3, ecc.
            $eccesso = $overallPercentuale - 100;
            $penalizzazione = $eccesso / 20;  // Penalizza di 1 punto per ogni 20% in più
            $punteggio = max(5 - $penalizzazione, 0);  // Assicuriamoci che non vada sotto 0
        }
        $dataView['punteggio'] = round($punteggio, 2);
        
        return $dataView;
    }


    protected function punteggioOb3_8($obiettivo, $strutturaId, $punteggioTeorico) {
        $categories = DB::table("target_categories")
        ->where("target_number", $obiettivo)
        ->orderby("id")
        ->get();
        $mediaPercentuale = 0;

        foreach($categories as $category) {
            $tmp = DB::table("uploated_files")
                ->leftJoin("result_target3", "result_target3.uploated_file_id", "=", "uploated_files.id")
                ->where("target_number", $obiettivo)
                ->where("structure_id", $strutturaId)
                ->where("target_category_id", $category->id)
                ->latest("uploated_files.created_at")->first();
            $mediaPercentuale += (isset($tmp->numerator) && isset($tmp->denominator)) ? round($tmp->numerator / $tmp->denominator,2) : 0;
        }
        $mediaPercentuale = round($mediaPercentuale / count($categories), 2) * 100;
        $punteggioCalcolato = reset($punteggioTeorico);
        if($mediaPercentuale > 85 && $mediaPercentuale <= 95)
            $punteggioCalcolato = round($punteggioCalcolato * 90 / 100, 2);
        elseif ($mediaPercentuale > 75 && $mediaPercentuale <= 85)
            $punteggioCalcolato = round($punteggioCalcolato * 75 / 100, 2);
        elseif ($mediaPercentuale <= 75)
            $punteggioCalcolato = 0;
        return $punteggioCalcolato;
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

                $obiettivo = 1;
                $punteggioMassimo = $punteggioTeoria->clone()->where("target_number", $obiettivo)->select($struttura->column_points)->first();
                $tmpDataView = $this->punteggioOb1_1(date('Y'), 1, 12);
                $punteggioRaggiunto[$obiettivo] = $tmpDataView['punteggio'];

                $obiettivo = 3;
                $punteggioMassimo = $punteggioTeoria->clone()->where("target_number", $obiettivo)->select($struttura->column_points)->first();
                $punteggioRaggiunto[$obiettivo] = $this->punteggioOb3_8($obiettivo, $struttura->structure_id, $punteggioMassimo);
                
                $obiettivo = 8;
                $punteggioMassimo = $punteggioTeoria->clone()->where("target_number", $obiettivo)->select($struttura->column_points)->first();
                $punteggioRaggiunto[$obiettivo] = $this->punteggioOb3_8($obiettivo, $struttura->structure_id, $punteggioMassimo);

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
                        "real_points" => $raggiunto,
                    ];
                }
            }
        } else {
            $dataView['userStructures'] = null;
        }

        $dataView['saluteEFunzionamento'] = config("constants.OBIETTIVO");

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
                $dataView['titolo'] = config("constants.OBIETTIVO.3.text");
                $dataView['icona'] = config("constants.OBIETTIVO.3.icon");
                $dataView['tooltip'] = config("constants.OBIETTIVO.3.tooltip");

                $dataView['files'][] = "obiettivo3.pdf";
                $dataView['strutture'] = Auth::user()->structures();

                $dataView['categorie'] = DB::table(table: 'target_categories as tc')
                    ->where("target_number", $request->obiettivo)->get();
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
                ->orderBy("tc.category" )
                ->orderBy('uf.created_at', 'desc')
                ->orderBy('uf.updated_at', 'desc')
                ->get();

                break;

            case 8:
                $dataView['titolo'] = config("constants.OBIETTIVO.8.text");
                $dataView['icona'] = config("constants.OBIETTIVO.8.icon");
                $dataView['tooltip'] = config("constants.OBIETTIVO.8.tooltip");
                //$dataView['files'][] = "obiettivo3.pdf";
                $dataView['strutture'] = Auth::user()->structures();
                $dataView['categorie'] = DB::table(table: 'target_categories as tc')
                    ->where("target_number", $request->obiettivo)->get();

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


    public function prontoSoccorso()
    {

        $dataView['companyCode'] = DB::table('users_structures as us')
            ->join('structures as s', 'us.structure_id', '=', 's.id')
            ->where('us.user_id', Auth::user()->id)
            ->distinct()
            ->pluck('s.company_code');


        $dataView['flowEmur'] = DB::table('flows_emur as fe')
            ->join('structures as s', 'fe.structure_id', '=', 's.id')
            ->leftJoin('users_structures as us', 's.id', '=', 'us.structure_id')
            ->select('fe.tmp', 'fe.year AS anno', 'fe.month AS mese', 's.name AS nome_struttura', 's.company_code', 'fe.boarding', 's.id')
            ->whereIn('s.company_code', $dataView['companyCode'])
            ->where('us.user_id', Auth::user()->id)
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



    public function tempiListeAttesa(Request $request)
    {
        $tmpAnno = isset($request->anno) ? $request->anno : date('Y');
        $tmpMeseInizio = isset($request->mese_inizio) ? $request->mese_inizio : 1;
        $tmpMeseFine = isset($request->mese_fine) ? $request->mese_fine : date("m");
        
        $dataView = $this->punteggioOb1_1($tmpAnno, $tmpMeseInizio, $tmpMeseFine);
        $dataView['anno'] = $tmpAnno;
        $dataView['meseInizio'] = $tmpMeseInizio;
        $dataView['meseFine'] = $tmpMeseFine;

        $dataView['tempiListeAttesa'] = Chartjs::build()
            ->name("tempiListeAttesa")
            ->type("doughnut")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Num. prest. amb. I accesso pubblico o privato accreditate / Num. prest. amb. erogate'])
            ->datasets([
                [
                    "label" => "Percentuale TMP",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "data" => [$dataView['percentuale']]
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
        return view("tempiListeAttesa")->with("dataView", $dataView);
    }



    public function screening(Request $request)
    {

        // Mi serve per prendere i dati solo per il file di obiettivo 5
        $dataView['file'] = DB::table('uploated_files as up')
            ->join('target_categories as tc', 'up.target_category_id', '=', 'tc.id')
            ->where('up.user_id', Auth::user()->id)
            ->where('up.target_number', 5)
            ->select('up.target_number', 'up.target_category_id', 'tc.category', 'up.validator_user_id', 'up.approved', 'up.created_at')
            ->get();


        // Dati per la tabella nella view 
        $dataView['tableData'] = DB::table('insert_mmg')
            ->select('mmg_totale', 'mmg_coinvolti', 'anno')
            ->get();

        //avrò solo una riga nel db (per ora)
        $record = DB::table('insert_mmg')->select('mmg_totale', 'mmg_coinvolti')->first();
        $dataView['noData'] = false;



        if ($record && $record->mmg_totale != 0) {
            $dataView['percentualeAderenti'] = ($record->mmg_coinvolti / $record->mmg_totale) * 100;
            $dataView['percentualeNonAderenti'] = 100 - $dataView['percentualeAderenti'];
        } else {
            $dataView['percentualeAderenti'] = 0;
            $dataView['percentualeNonAderenti'] = 0;
            $dataView['noData'] = true;
        }

        $dataView['percentualeAderenti'] = number_format($dataView['percentualeAderenti'], 2);


        $dataView['mmgChart'] = Chartjs::build()
            ->name("OverallAvgTmpComplementaryBarChart")
            ->type("doughnut")
            ->size(["width" => 300, "height" => 150])
            ->labels(['MMG Aderenti', 'MMG non aderenti'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(255, 99, 132, 0.7)"
                    ],
                    "data" => $dataView['noData']
                        ? [0, 0]
                        : [$dataView['percentualeAderenti'], number_format($dataView['percentualeNonAderenti'], 2)]
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => $dataView['noData']
                            ? 'Non ci sono dati disponibili'
                            : 'Distribuzione Percentuale: MMG aderenti e Non aderenti'
                    ]
                ]
            ]);

        //*************Secondo grafico **********//

        $dataView['prestazioniInappropriate'] = 642;
        $dataView['totalePrestazioni'] = 3963;

        $dataView['percentualeCodiciDD'] = number_format($dataView['prestazioniInappropriate'] / $dataView['totalePrestazioni'] * 100, 2);


        $dataView['codiciDD'] = Chartjs::build()
            ->name("chartCodiciDD")
            ->type("doughnut")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Prestazioni Inappropriate', 'Prestazione appropriate'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(255, 99, 132, 0.7)"
                    ],
                    "data" => [$dataView['totalePrestazioni'], $dataView['prestazioniInappropriate']]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => '% Prestazioni inappropriate'

                    ]
                ]
            ]);






        /***********************Grafico MMG****************************/


        if ($dataView['percentualeAderenti'] > 60) {
            $dataView['messaggioTmp'] = [
                'text' => "Raggiungimento dell'obiettivo con punteggio: 2",
                'class' => 'text-success'
            ];
        } elseif ($dataView['percentualeAderenti'] >= 20) {
            $dataView['messaggioTmp'] = [
                'text' => "Raggiungimento dell'obiettivo parziale con punteggio: 1",
                'class' => 'text-warning'
            ];
        } else {
            $dataView['messaggioTmp'] = [
                'text' => "Obiettivo non raggiunto con punteggio: 0",
                'class' => 'text-danger'
            ];
        }

        /*****************Grafico D02 E D03*******************/


        if ($dataView['percentualeCodiciDD'] >= 0 && $dataView['percentualeCodiciDD'] <= 10) {
            $dataView['messaggioTmpCodiciDD'] = [
                'textt' => "Pieno raggiungimento dell'obiettivo con punteggio: 1",
                'classs' => 'text-success'
            ];

        } elseif ($dataView['percentualeCodiciDD'] > 10) {
            $dataView['messaggioTmpCodiciDD'] = [
                'textCodiciDD' => "Obiettivo non raggiunto con punteggio: 0",
                'classCodiciDD' => 'text-danger'
            ];

        }


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
            'year' => $request->anno,
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
                'target_category_id' => $request->category,
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


        return redirect()->route('caricamentoScreening');
    }

    public function farmaciPCT(Request $request) {

    }

    public function importTarget1(Request $request)
    {
        $file = $request->file('file');
        $fileContents = file($file->getPathname());
        $colonneAttese = 4;
        $formatoData = 'Y-m-d';
        $row = 0;
        $errori = [];
        foreach ($fileContents as $line) {
            $row += 1;
            $data = str_getcsv($line);

            if (count($data) !== $colonneAttese) {
                $errori[$row][] = "Il numero di colonne deve essere " . $colonneAttese . "; letto: " . count($data);
            }
            $dateObj = \DateTime::createFromFormat($formatoData, $data[0]);
            if (!$dateObj || $dateObj->format($formatoData) !== $data[0]) {
                $errori[$row][] = "Formato della data deve essere aaaa-mm-gg; dato letto: ".$data[0];
            }
            if (!is_numeric($data[1]) || $data[1] <= 0) {
                $errori[$row][] = "Quantità errata: ".$data[1];
            }
        }
        if (count($errori) == 0) {
            foreach ($fileContents as $line) {
                $data = str_getcsv($line);

                CUPTarget1::create([
                    'user_id' => Auth::user()->id,
                    'structure_id' => Auth::user()->firstStructureId()->id,
                    'provision_date' => $data[0],
                    'amount' => $data[1],
                    'doctor_code' => $data[2],
                    'nomenclator_code' => $data[3],
                ]);
            }
            $dataView['success'] = "CSV importato correttamente";
        } else
            $dataView['errors'] = $errori;
        
        return view("uploadTempiListaAttesa")->with("dataView", $dataView);
    }

    public function getDescription($id)
    {

        $description = DB::table('target_categories')
            ->where('id', $id)
            ->value('description');

        return response()->json(['description' => $description]);
    }




    public function caricamentoScreening(Request $request){


        $dataView['file'] = DB::table('uploated_files as up')
        ->join('target_categories as tc', 'up.target_category_id', '=', 'tc.id')
        ->where('up.user_id', Auth::user()->id)
        ->where('up.target_number', 5)
        ->select('up.target_number', 'up.target_category_id', 'tc.category', 'up.validator_user_id', 'up.approved', 'up.created_at')
        ->get();


    // Dati per la tabella nella view 
    $dataView['tableData'] = DB::table('insert_mmg')
        ->select('mmg_totale', 'mmg_coinvolti', 'anno')
        ->get();




        return view('controller.caricamentoScreening')->with("dataView", $dataView);
    }

}
