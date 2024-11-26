<?php

namespace App\Http\Controllers;

use App\ChartTrait;
use App\Models\Gare;
use App\Models\LocationsUsers;
use App\Models\PCT;
use App\Models\Target6_data;
use App\Models\Target7_data;
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
use App\Models\Structure;
use App\Http\Controllers\DateTime;


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


    protected function punteggioOb1_1($anno, $meseInizio, $meseFine)
    {
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


    protected function punteggioOb3_8($obiettivo, $strutturaId, $punteggioTeorico)
    {
        $categories = DB::table("target_categories")
            ->where("target_number", $obiettivo)
            ->orderby("id")
            ->get();
        $mediaPercentuale = 0;

        foreach ($categories as $category) {
            $tmp = DB::table("uploated_files")
                ->leftJoin("result_target3", "result_target3.uploated_file_id", "=", "uploated_files.id")
                ->where("target_number", $obiettivo)
                ->where("structure_id", $strutturaId)
                ->where("target_category_id", $category->id)
                ->latest("uploated_files.created_at")->first();
            $mediaPercentuale += (isset($tmp->numerator) && isset($tmp->denominator)) ? round($tmp->numerator / $tmp->denominator, 2) : 0;
        }

        $mediaPercentuale = round($mediaPercentuale / count($categories), 2) * 100;
        $punteggioCalcolato = reset($punteggioTeorico);
        if ($mediaPercentuale > 85 && $mediaPercentuale <= 95)
            $punteggioCalcolato = round($punteggioCalcolato * 90 / 100, 2);
        elseif ($mediaPercentuale > 75 && $mediaPercentuale <= 85)
            $punteggioCalcolato = round($punteggioCalcolato * 75 / 100, 2);
        elseif ($mediaPercentuale <= 75)
            $punteggioCalcolato = 0;
        return $punteggioCalcolato;
    }

    protected function calcoloPunteggioSub2($percentualeData)
    {

        $dataView = [];

        $array2024 = array(5, 3, 2);
        $array2025 = array(15, 10, 7);
        $array2026 = array(25, 15, 10);


        $anno = date('Y');

        if ($percentualeData < 0) {

            $dataView['messaggioTmp'] = [
                'text' => $anno . ": " . "Percentuale negativa, obiettivo non raggiunto con punteggio: 0",
                'class' => 'text-danger'
            ];
        } else {
            if ($anno == 2024) {
                $targetArray = $array2024;
            } elseif ($anno == 2025) {
                $targetArray = $array2025;
            } elseif ($anno == 2026) {
                $targetArray = $array2026;
            }

            if ($percentualeData > $targetArray[0]) {

                $dataView['messaggioTmp'] = [
                    'text' => $anno . ": " . "Raggiungimento dell'obiettivo con punteggio: 1.5",
                    'class' => 'text-success'
                ];
            } elseif ($percentualeData >= $targetArray[1] && $percentualeData <= $targetArray[0]) {

                $dataView['messaggioTmp'] = [
                    'text' => $anno . ": " . "Raggiungimento dell'obiettivo all'80% con punteggio: 1.2",
                    'class' => 'text-warning'
                ];
            } elseif ($percentualeData >= $targetArray[2] && $percentualeData < $targetArray[1]) {

                $dataView['messaggioTmp'] = [
                    'text' => $anno . ": " . "Raggiungimento dell'obiettivo al 50% con punteggio: 0.75",
                    'class' => 'text-warning'
                ];
            } else {

                $dataView['messaggioTmp'] = [
                    'text' => $anno . ": " . "Obiettivo non raggiunto con punteggio: 0",
                    'class' => 'text-danger'
                ];
            }
        }


        return $dataView;
    }

    protected function calcoloPunteggioSub4($percentualeData)
    {
        $dataView = [];


        $array2024 = array(10, 5, 3);
        $array2025 = array(15, 10, 7);
        $array2026 = array(30, 25, 20, 15);

        $anno = date('Y');


        if ($percentualeData < 0) {
            $dataView['messaggioTmpIncremento'] = [
                'textIncremento' => $anno . ": Percentuale negativa, obiettivo non raggiunto con punteggio: 0",
                'classIncremento' => 'text-danger'
            ];
        } else {

            if ($anno == 2024) {
                $targetArray = $array2024;
            } elseif ($anno == 2025) {
                $targetArray = $array2025;
            } elseif ($anno == 2026) {
                $targetArray = $array2026;
            }


            if ($percentualeData > $targetArray[0]) {
                $dataView['messaggioTmpIncremento'] = [
                    'textIncremento' => $anno . ": Raggiungimento dell'obiettivo con punteggio: 1.5",
                    'classIncremento' => 'text-success'
                ];
            } elseif ($percentualeData >= $targetArray[1] && $percentualeData <= $targetArray[0]) {
                $dataView['messaggioTmpIncremento'] = [
                    'textIncremento' => $anno . ": Raggiungimento dell'obiettivo all'80% con punteggio: 1.2",
                    'classIncremento' => 'text-warning'
                ];
            } elseif ($percentualeData >= $targetArray[2] && $percentualeData < $targetArray[1]) {
                $dataView['messaggioTmpIncremento'] = [
                    'textIncremento' => $anno . ": Raggiungimento dell'obiettivo al 50% con punteggio: 0.75",
                    'classIncremento' => 'text-warning'
                ];
            } else {
                $dataView['messaggioTmpIncremento'] = [
                    'textIncremento' => $anno . ": Obiettivo non raggiunto con punteggio: 0",
                    'classIncremento' => 'text-danger'
                ];
            }
        }

        return $dataView;
    }

/*
    protected function punteggioOb7_1($dataView)
    {


        $array2024 = array();//metto la percentuale del 2024);
        $array2025 = array(15, 10, 7);
        $array2026 = array(30, 25, 20, 15);

        $anno = date('Y');


        if ($percentualeData < 0) {
            $dataView['messaggioTmpIncremento'] = [
                'textIncremento' => $anno . ": Percentuale negativa, obiettivo non raggiunto con punteggio: 0",
                'classIncremento' => 'text-danger'
            ];
        } else {

            if ($anno == 2024) {
                $targetArray = $array2024;
            } elseif ($anno == 2025) {
                $targetArray = $array2025;
            } elseif ($anno == 2026) {
                $targetArray = $array2026;
            }


            if ($percentuale > 40) {
                $dataView['messaggioTmpIncremento'] = [
                    'textIncremento' => $anno . ": Raggiungimento dell'obiettivo con punteggio: 1.5",
                    'classIncremento' => 'text-success'
                ];
            } elseif ($percentuale >30) {
                $dataView['messaggioTmpIncremento'] = [
                    'textIncremento' => $anno . ": Raggiungimento dell'obiettivo all'80% con punteggio: 1.2",
                    'classIncremento' => 'text-warning'
                ];
            } elseif ($percentualeData > 80){
                $dataView['messaggioTmpIncremento'] = [
                    'textIncremento' => $anno . ": Raggiungimento dell'obiettivo al 50% con punteggio: 0.75",
                    'classIncremento' => 'text-warning'
                ];
            } 
        }

        return $dataView;
    }
*/


    protected function initView(int $obiettivo)
    {
        $dataView['titolo'] = config("constants.OBIETTIVO." . $obiettivo . ".text");
        $dataView['icona'] = config("constants.OBIETTIVO." . $obiettivo . ".icon");
        $dataView['tooltip'] = config("constants.OBIETTIVO." . $obiettivo . ".tooltip");
        $dataView['obiettivo'] = $obiettivo;
        $dataView['strutture'] = Auth::user()->structures();
        $dataView['categorie'] = DB::table(table: 'target_categories as tc')
            ->where("target_number", $obiettivo)->get();

        return $dataView;
    }

    protected function fileCaricati($obiettivo, $strutture)
    {
        return DB::table('uploated_files as uf')
            ->join('target_categories as tc', 'uf.target_category_id', '=', 'tc.id')
            ->select('uf.id', 'uf.validator_user_id', 'uf.approved', 'uf.notes', 'uf.path', 'uf.filename', 'uf.target_category_id', 'tc.category', 'uf.updated_at', 'uf.user_id', 'uf.created_at')
            ->where('uf.target_number', $obiettivo)
            ->whereIn("uf.structure_id", $strutture->pluck("id")->toArray())
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
                    ->join("result_target3", "result_target3.uploated_file_id", "=", "uploated_files.id")
                    ->where("target_number", $obiettivo)
                    ->where("structure_id", $struttura->structure_id)
                    ->select("numerator", "denominator", "uploated_files.approved")
                    ->latest("uploated_files.updated_at")->first();
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

    public function caricamentoPuntoNascite()
    {
        $dataView = $this->initView(3);
        $dataView['files'][] = "obiettivo3.pdf";

        return view("caricamentoPuntoNascite")->with("dataView", $dataView);

    }


    public function caricamentoPercorsoCertificabilita()
    {
        $dataView = $this->initView(8);
        $dataView['files'] = null;

        return view("caricamentoPercorsoCertificabilita")->with("dataView", $dataView);

    }

    public function uploadTempiListaAttesa()
    {
        $dataView = $this->initView(1);
        $dataView['files'] = null;

        return view("uploadTempiListaAttesa")->with("dataView", $dataView);

    }

    public function caricamentoFarmaci()
    {
        $dataView = $this->initView(9);
        $strutturaId = Auth::user()->firstStructureId()->id;
        $dataView['PCT'] = PCT::where("structure_id", $strutturaId)
            ->distinct("year")
            ->latest()->get();
        $dataView['gare'] = Gare::where("structure_id", $strutturaId)
            ->distinct("year")
            ->latest()->get();
        $dataView['pct.denominatori'] = DB::table("flows_sdo")
            ->select(DB::raw("sum(ob9_2) as tot"), "year")
            ->where("structure_id", $strutturaId)
            ->groupby("year")
            ->orderby("year", "desc")
            ->get();
        $dataView['anni'] = DB::table('flows_sdo')
            ->where("structure_id", $strutturaId)
            ->distinct()
            ->pluck("year");

        return view("caricamentoFarmaci")->with("dataView", $dataView);
    }

    public function showObiettivo(Request $request)
    {
        $dataView = $this->initView($request->obiettivo);
        $dataView['categorie'] = DB::table("target_categories")
            ->where("target_number", $request->obiettivo)
            ->orderBy("order")
            ->get();

        switch ($request->obiettivo) {
            case 3:
                $dataView['files'][] = "obiettivo3.pdf";
                $dataView['filesCaricati'] = $this->fileCaricati(3, $dataView['strutture']);
                break;

            case 8:
                $dataView['filesCaricati'] = $this->fileCaricati(8, $dataView['strutture']);
                break;

        }
        $dataView['obiettivo'] = $request->obiettivo;

        return view("showFormObiettivo")->with("dataView", $dataView);
    }


    public function prontoSoccorso()
    {
        /*
                $dataView['companyCode'] = DB::table('users_structures as us')
                    ->join('structures as s', 'us.structure_id', '=', 's.id')
                    ->where('us.user_id', Auth::user()->id)
                    ->distinct()
                    ->pluck('s.company_code');
        */

        $dataView['flowEmur'] = DB::table('flows_EMUR as fe')
            //->join('structures as s', 'fe.structure_id', '=', 's.id')
            ->leftJoin('users_structures as us', 'fe.structure_id', '=', 'us.structure_id')
            //->select('fe.tmp', 'fe.year AS anno', 'fe.month AS mese', 's.name AS nome_struttura', 's.company_code', 'fe.boarding', 's.id')
            ->select('fe.tmp', 'fe.year AS anno', 'fe.month AS mese', 'fe.boarding')
            //->whereIn('s.company_code', $dataView['companyCode'])
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

        return view("prontoSoccorso", [
            'dataView' => $dataView,
            'overallAverageTmp' => $overallAverageTmp,
            'overallAverageBoarding' => $overallAverageBoarding,
        ]);

    }

    public function donazioni(Request $request)
    {
        $dataView['strutture'] = Auth::user()->structures();
        $dataView['categorie'] = DB::table(table: 'target_categories as tc')
            ->where("target_number", $request->obiettivo)->get();

        $dataView['file'] = DB::table('uploated_files as uf')
            ->join('target_categories as tc', 'uf.target_category_id', '=', 'tc.id')
            ->select('uf.id', 'uf.validator_user_id', 'uf.approved', 'uf.notes', 'uf.path', 'uf.filename', 'uf.target_category_id', 'tc.category', 'uf.updated_at', 'uf.user_id', 'uf.created_at')
            ->where('uf.target_number', 6)
            ->whereIn("uf.structure_id", $dataView['strutture']->pluck("id")->toArray())
            ->whereRaw('uf.created_at = (SELECT MAX(u2.created_at)
                                    FROM uploated_files as u2 
                                    WHERE u2.target_category_id = uf.target_category_id)')
            ->whereRaw('uf.updated_at = (SELECT MAX(u3.updated_at)
                                    FROM uploated_files as u3
                                    WHERE u3.target_category_id = uf.target_category_id
                                    AND u3.created_at = uf.created_at)')
            ->orderBy("tc.category")
            ->orderBy('uf.created_at', 'desc')
            ->orderBy('uf.updated_at', 'desc')
            ->get();


        // Numeratore sub.2
        $dataView['target6_data'] = DB::table('target6_data')
            ->whereIn(DB::raw('(anno, id)'), function ($query) {
                $query->select(DB::raw('anno, MAX(id)'))
                    ->from('target6_data')
                    ->groupBy('anno');
            })
            ->select('totale_accertamenti', 'anno', 'numero_opposti', 'totale_cornee')
            ->orderBy('anno', 'asc')
            ->get();

        //Denominatore preso dal flusso
        $dataView['denominatore'] = DB::table('flows_sdo')
            ->join('users_structures AS us', 'flows_sdo.structure_id', '=', 'us.structure_id')
            ->where('us.user_id', 7)
            ->select(
                DB::raw('MAX(flows_sdo.ob6) as ob6'),
                DB::raw('MAX(flows_sdo.id) as id'),
                'flows_sdo.year'
            )
            ->groupBy('flows_sdo.year')
            ->orderByDesc('flows_sdo.year')
            ->get();


        //$dataView['punteggioTotale'] = 0;
        $percentuali = [];
        $labelsTmp = [];
        $dataView['result'] = [];


        // Calcolo la percentuale per ogni anno
        foreach ($dataView['target6_data'] as $target) {
            $denominatore = $dataView['denominatore']->firstWhere('year', $target->anno);

            $percentuale = ($target->totale_accertamenti / $denominatore->ob6) * 100;
            $dataView['result'][] = [
                'anno' => $target->anno,
                'percentuale' => $percentuale,
                'totale_accertamenti' => $target->totale_accertamenti,
                'numero_opposti' => $target->numero_opposti,
                'totale_cornee' => $target->totale_cornee,
                'ob6_sum' => $denominatore->ob6
            ];


            $percentuali[] = $percentuale;
            $labelsTmp[] = $target->anno;
        }



        // $dataView['result'] = collect($dataView['result'])->sortBy('anno')->values()->all();

        $dataView['totale_cornee2024'] = 0;
        $dataView['totale_cornee2023'] = 0;
        $dataView['percentuale2024'] = 0;
        $dataView['percentuale2023'] = 0;
        $dataView['percentuale'] = $percentuali;


        foreach ($dataView['result'] as $res) {
            if ($res['anno'] == 2023) {
                $dataView['percentuale2023'] = $res['percentuale'];
                $dataView['totale_cornee2023'] = $res['totale_cornee'];

            }
            if ($res['anno'] == date("Y")) {
                $dataView['percentuale2024'] = $res['percentuale'];
                $dataView['totale_cornee2024'] = $res['totale_cornee'];
            }
        }


        if ($dataView['percentuale2023'] !== 0 && $dataView['percentuale2024'] !== 0) {
            $dataView['incremento'] = number_format((($dataView['percentuale2024'] - $dataView['percentuale2023']) / $dataView['percentuale2023']) * 100, 2);
        } else {

            $dataView['incremento'] = 0;
            $dataView['messaggioTmp'] = [
                'text' => "Impossibile calcolare l'incremento. Dati insufficienti.",
                'class' => 'text-danger'
            ];
        }

        //calcolo punteggio sub.2
        $punteggioTotale = $this->calcoloPunteggioSub2($dataView['incremento']);
        $dataView = array_merge($punteggioTotale, $dataView);

        //grafico Sub.2
        $dataView['chartDonazioni'] = Chartjs::build()
            ->name("OverallAvgTmpComplementaryBarChart")
            ->type("bar")
            ->size(["width" => 300, "height" => 150])
            ->labels($labelsTmp)
            ->datasets([
                [
                    "label" => "Percentuale TMP per Anno",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "data" => $percentuali //percentuali per ogni anno
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Distribuzione Percentuale per Anno'
                    ],
                    'legend' => [
                        'display' => true
                    ]
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                    ],
                ],
            ]);

        /***************************Chart sub. 3************************************ */

        $dataView['dataSelezionata'] = $request->annoSelezionato ?? date('Y');

        $dataView['numeratoreSecondo'] = 0;
        $dataView['denominatoreSecondo'] = 0;

        foreach ($dataView['result'] as $risultato) {
            if ($risultato['anno'] == $dataView['dataSelezionata']) {
                $dataView['denominatoreSecondo'] = $risultato['percentuale'];
                $dataView['denominatoreSecondo'] = (float) str_replace('%', '', $dataView['denominatoreSecondo']); // Conversione in float
                $dataView['numeratoreSecondo'] = $risultato['numero_opposti'];
            }
        }

        //calcoloPercentualeOpposizione
        if ($dataView['denominatoreSecondo'] > 0) {
            $dataView['percentualeOpposizione'] = number_format(($dataView['numeratoreSecondo'] / $dataView['denominatoreSecondo']) * 100, 2);
            $percOpposizioneComplementare = 100 - $dataView['percentualeOpposizione'];
        } else {
            $dataView['percentualeOpposizione'] = 0;
            $percOpposizioneComplementare = 100;

        }

        //grafico sub.3
        $dataView['chartSubObiettivo3'] = Chartjs::build()
            ->name("chartSubObiettivo3")
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
                    "data" => [($dataView['percentualeOpposizione']), ($percOpposizioneComplementare)]
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

        //calcolo punteggio 3
        if ($dataView['percentualeOpposizione'] <= 38) {

            // Obiettivo pienamente raggiunto
            $dataView['messaggioTmpOpposizione'] = [
                'textOpposizione' => date("Y") . ": Il tasso di opposizione è del " . $dataView['percentualeOpposizione'] . "%.Obiettivo pienamente raggiunto con punteggio: 1.5",
                'classOpposizione' => 'text-success'
            ];
        } elseif ($dataView['percentualeOpposizione'] > 38 && $dataView['percentualeOpposizione'] <= 41) {

            // Obiettivo raggiunto all'80%
            $dataView['messaggioTmpOpposizione'] = [
                'textOpposizione' => date("Y") . ": Il tasso di opposizione è del " . $dataView['percentualeOpposizione'] . "%.Obiettivo raggiunto all'80% con punteggio: 1.2",
                'classOpposizione' => 'text-warning'
            ];
        } elseif ($dataView['percentualeOpposizione'] > 41 && $dataView['percentualeOpposizione'] <= 45) {

            // Obiettivo raggiunto al 50%
            $dataView['messaggioTmpOpposizione'] = [
                'textOpposizione' => date("Y") . ": Il tasso di opposizione è del " . $dataView['percentualeOpposizione'] . "%.Obiettivo raggiunto al 50% con punteggio: 0.75",
                'classOpposizione' => 'text-warning'
            ];
        } else {

            // Obiettivo non raggiunto
            $dataView['messaggioTmpOpposizione'] = [

                'textOpposizione' => date("Y") . ": Il tasso di opposizione è del " . $dataView['percentualeOpposizione'] . "%. Obiettivo non raggiunto con punteggio: 0",
                'classOpposizione' => 'text-danger'

            ];
        }


        /******************************Sub.ob 4************************************ */


        //calcolo incremento sub.4
        if ($dataView['totale_cornee2024'] != 0 && $dataView['totale_cornee2023'] != 0) {
            $dataView['percIncrementoSub4'] = (($dataView['totale_cornee2024'] - $dataView['totale_cornee2023']) / $dataView['totale_cornee2023']) * 100;
        } else {
            $dataView['percIncrementoSub4'] = 0;
        }


        //grafico sub.4
        $dataView['chartSubObiettivo4'] = Chartjs::build()
            ->name("chartSubObiettivo4")
            ->type("bar")
            ->size(["width" => 300, "height" => 150])
            ->labels($labelsTmp)
            ->datasets([
                [
                    "label" => "Percentuale TMP",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "data" => [$dataView['totale_cornee2023'], $dataView['totale_cornee2024']]
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


        $punteggioSub4 = $this->calcoloPunteggioSub4($dataView['percIncrementoSub4']);
        $dataView = array_merge($punteggioSub4, $dataView);

        return view("donazioni")->with('dataView', $dataView);
    }



    public function tempiListeAttesa(Request $request)
    {
        $tmpAnno = isset($request->anno) ? $request->anno : date('Y');
        $tmpMeseInizio = isset($request->mese_inizio) ? $request->mese_inizio : 1;
        $tmpMeseFine = isset($request->mese_fine) ? $request->mese_fine : date("m");

        $dataView = $this->initView(1);
        $dataView = array_merge($dataView, $this->punteggioOb1_1($tmpAnno, $tmpMeseInizio, $tmpMeseFine));
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

        $dataView['filesCaricati'] = $this->fileCaricati(1, $dataView['strutture']);

        return view("tempiListeAttesa")->with("dataView", $dataView);
    }



    public function screening(Request $request)
    {

        // Mi serve per prendere i dati solo per il file di obiettivo 5
        /*$dataView['file'] = DB::table('uploated_files as up')
            ->join('target_categories as tc', 'up.target_category_id', '=', 'tc.id')
            ->where('up.user_id', Auth::user()->id)
            ->where('up.target_number', 5)
            ->select('up.target_number', 'up.target_category_id', 'tc.category', 'up.validator_user_id', 'up.approved', 'up.created_at')
            ->get();
*/

        $dataView['strutture'] = Auth::user()->structures();

        $dataView['categorie'] = DB::table(table: 'target_categories as tc')
            ->where("target_number", $request->obiettivo)->get();
        $dataView['file'] = DB::table('uploated_files as uf')
            ->join('target_categories as tc', 'uf.target_category_id', '=', 'tc.id')
            ->select('uf.id', 'uf.validator_user_id', 'uf.approved', 'uf.notes', 'uf.path', 'uf.filename', 'uf.target_category_id', 'tc.category', 'uf.updated_at', 'uf.user_id', 'uf.created_at')
            ->where('uf.target_number', 5)
            ->whereIn("uf.structure_id", $dataView['strutture']->pluck("id")->toArray())
            ->whereRaw('uf.created_at = (SELECT MAX(u2.created_at)
                                    FROM uploated_files as u2 
                                    WHERE u2.target_category_id = uf.target_category_id)')
            ->whereRaw('uf.updated_at = (SELECT MAX(u3.updated_at)
                                    FROM uploated_files as u3
                                    WHERE u3.target_category_id = uf.target_category_id
                                    AND u3.created_at = uf.created_at)')
            ->orderBy("tc.category")
            ->orderBy('uf.created_at', 'desc')
            ->orderBy('uf.updated_at', 'desc')
            ->get();



        // Dati per la tabella nella view 
        $dataView['tableData'] = DB::table('insert_mmg')
            ->join('structures as s', 'insert_mmg.structure_id', '=', 's.id')
            ->select('mmg_totale', 'mmg_coinvolti', 'year', 'structure_id', 's.name as nome_struttura')
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

        $dataView['datiFlussoM'] = DB::table('flows_m')
            ->select('ob5_num as numeratore_m', 'ob5_den as denominatore_m')
            ->join('users_structures as us', 'flows_m.structure_id', '=', 'us.structure_id')
            ->where('us.user_id', Auth::user()->id, )
            ->get();


        $dataView['datiFlussoC'] = DB::table('flows_c')
            ->select('ob5_num as numeratore_c', 'ob5_den as denominatore_c')
            ->join('users_structures as us', 'flows_c.structure_id', '=', 'us.structure_id')
            ->where('us.user_id', Auth::user()->id, )
            ->get();

        $numeratoreM = $dataView['datiFlussoM']->sum('numeratore_m');
        $denominatoreM = $dataView['datiFlussoM']->sum('denominatore_m');


        $numeratoreC = $dataView['datiFlussoC']->sum('numeratore_c');
        $denominatoreC = $dataView['datiFlussoC']->sum('denominatore_c');
        $dataView['numeratoreTotale'] = $numeratoreM + $numeratoreC;
        $dataView['denominatoreTotale'] = $denominatoreM + $denominatoreC;

        $dataView['percentuale'] = number_format($dataView['numeratoreTotale'] / $dataView['denominatoreTotale'] * 100, 2);

        $dataView['percentualeComplementare'] = 100 - $dataView['percentuale'];

        /*  $dataView['prestazioniInappropriate'] = 642;
          $dataView['totalePrestazioni'] = 3963;

          $dataView['percentualeCodiciDD'] = number_format($dataView['prestazioniInappropriate'] / $dataView['totalePrestazioni'] * 100, 2);
        */

        $dataView['codiciEsenzioneChart'] = Chartjs::build()
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
                    "data" => [$dataView['percentuale'], $dataView['percentualeComplementare']]

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


        /***********************Messaggio punteggio MMG****************************/


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

        /********************Messagggio punteggio D02 E D03************************/


        if ($dataView['percentuale'] >= 0 && $dataView['percentuale'] <= 10) {
            $dataView['messaggioTmpCodiciDD'] = [
                'textCodiciDD' => "Pieno raggiungimento dell'obiettivo con punteggio: 1",
                'classCodiciDD' => 'text-success'
            ];

        } elseif ($dataView['percentuale'] > 10) {
            $dataView['messaggioTmpCodiciDD'] = [
                'textCodiciDD' => "Obiettivo non raggiunto con punteggio: 0",
                'classCodiciDD' => 'text-danger'
            ];

        }


        return view("screening")->with("dataView", $dataView);
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

        return redirect()->back()->with('status', 'File caricato con successo e in attesa di approvazione.');
    }


    public function uploadFileScreening(Request $request)
    {

        $request->validate([
            'file' => 'required|file|mimes:pdf|max:5096',
        ]);

        // Check if a file is uploaded
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');
            $url = Storage::url($path);
            $categoriaId = $request->input('categoria');

            UploatedFile::create([
                'filename' => $file->getClientOriginalName(),
                'path' => $url,
                'user_id' => Auth::user()->id,
                'structure_id' => $request->structure_id,
                'notes' => null,
                'target_number' => $request->obiettivo,
                'target_category_id' => $categoriaId,
                'year' => $request->anno
            ]);

            return redirect()->back()->with('success', 'File caricato con successo e in attesa di approvazione.');
        }
        return redirect()->back()->with('error', 'Nessun file caricato.');
    }



    public function mmgRegister(Request $request)
    {


        $anno = $request->year;
        $tot_mmg = $request->tot_mmg;
        $mmg_coinvolti = $request->mmg_coinvolti;
        $structure_id = $request->structure_id;

        $messages = [
            'tot_mmg.required' => 'Il totale MMG è obbligatorio.',
            'tot_mmg.numeric' => 'Il totale MMG deve essere un numero.',
            'mmg_coinvolti.required' => 'Il numero di MMG coinvolti è obbligatorio.',
            'mmg_coinvolti.numeric' => 'Il numero di MMG coinvolti deve essere un numero.',
            'mmg_coinvolti.lte' => 'Il numero di MMG coinvolti deve essere minore o uguale al totale MMG.',
            'year.required' => 'L\'anno è obbligatorio.',
            'year.integer' => 'L\'anno deve essere un numero intero.',
        ];


        $request->validate([
            'tot_mmg' => 'required|numeric',
            'mmg_coinvolti' => 'required|numeric|lte:tot_mmg', // mmg_coinvolti <= tot_mmg
            'year' => 'required|integer',
        ], $messages);



        InsertMmg::create([
            'mmg_totale' => $tot_mmg,
            'mmg_coinvolti' => $mmg_coinvolti,
            'year' => $anno,
            'structure_id' => $structure_id,

        ]);


        return redirect()->route('caricamentoScreening', ['obiettivo' => $request->obiettivo]);

    }


    public function uploadDatiDonazione(Request $request)
    {
        $anno = $request->anno;
        $structure_id = $request->structure_id;
        $totale_accertamenti = $request->totale_accertamenti;
        $numero_opposti = $request->numero_opposti;
        $totale_cornee = $request->totale_cornee;
        /*
                $messages = [
                    'tot_mmg.required' => 'Il totale MMG è obbligatorio.',
                    'tot_mmg.numeric' => 'Il totale MMG deve essere un numero.',
                    'mmg_coinvolti.required' => 'Il numero di MMG coinvolti è obbligatorio.',
                    'mmg_coinvolti.numeric' => 'Il numero di MMG coinvolti deve essere un numero.',
                    'mmg_coinvolti.lte' => 'Il numero di MMG coinvolti deve essere minore o uguale al totale MMG.',
                    'year.required' => 'L\'anno è obbligatorio.',
                    'year.integer' => 'L\'anno deve essere un numero intero.',
                ];


                $request->validate([
                    'tot_mmg' => 'required|numeric',
                    'mmg_coinvolti' => 'required|numeric|lte:tot_mmg', // mmg_coinvolti <= tot_mmg
                    'year' => 'required|integer',
                ], $messages);
        */

        Target6_data::create([
            'totale_accertamenti' => $totale_accertamenti,
            'numero_opposti' => $numero_opposti,
            'totale_cornee' => $totale_cornee,
            'anno' => $anno,
            'structure_id' => $structure_id,
        ]);


        return redirect()->route('caricamentoDonazioni', ['obiettivo' => $request->obiettivo]);

    }
    public function uploadDatiFse(Request $request)
    {
        $selezioneServizio = $request->input('prestazioni_ospedaliere');
        $numeratoreFse = $request->input('numeratore_fse');
        $obiettivo = $request->input('obiettivo');
        $anno = $request->input('anno');
        $structureId = $request->input('structure_id');
        $documentiCda2 = $request->input('documenti_cda2');
        $documentiIndicizzatiCda2 = $request->input('documenti_indicizzatiCDA2');
        $documentiPades = $request->input('documenti_pades');
        $documentiIndicizzatiPades = $request->input('documenti_indicizzati_pades');
    
        
        $existingRecord = Target7_data::where('anno', $anno)
            ->where('structure_id', $structureId)
            ->first();
    
            if($documentiIndicizzatiCda2 && $documentiCda2 != null){
            $fieldsToUpdate = [
                'documenti_indicizzati_cda2' => $documentiIndicizzatiCda2,
                'documenti_cda2' => $documentiCda2,
            ];
        }

        if($documentiPades && $documentiIndicizzatiPades != null){
        $fieldsToUpdate = [
            'documenti_pades' => $documentiPades,
            'documenti_indicizzati_pades' => $documentiIndicizzatiPades,
        ];
        }
     
        if ($selezioneServizio != null) {
            switch ($selezioneServizio) {
                case '1':
                    $fieldsToUpdate['dimissioni_ospedaliere'] = $numeratoreFse;
                    break;
                case '2':
                    $fieldsToUpdate['dimissioni_ps'] = $numeratoreFse;
                    break;
                case '3':
                    $fieldsToUpdate['prestazioni_laboratorio'] = $numeratoreFse;
                    break;
                case '4':
                    $fieldsToUpdate['prestazioni_radiologia'] = $numeratoreFse;
                    break;
                case '5':
                    $fieldsToUpdate['prestazioni_ambulatoriali'] = $numeratoreFse;
                    break;
                case '6':
                    $fieldsToUpdate['vaccinati'] = $numeratoreFse;
                    break;
                case '7':
                    $fieldsToUpdate['documenti_indicizzati'] = $numeratoreFse;
                    break;
                case '8':
                    $fieldsToUpdate['certificati_indicizzati'] = $numeratoreFse;
                    break;
            }
        }
    
    
        if ($existingRecord) {
            \Log::info("prova: " . $existingRecord->id);
            $existingRecord->update(array_merge($fieldsToUpdate, ['updated_at' => now()]));
        } else {
            \Log::info("aaa");
            Target7_data::create(array_merge($fieldsToUpdate, [
                'anno' => $anno,
                'structure_id' => $structureId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    
        // Redirect back with a success message
        return redirect()->back()->with('success', 'Data caricati con successo!');
    }
    


    public function indexFarmaci(Request $request)
    {
        $strutturaId = Auth::user()->firstStructureId()->id;
        $anno = $request->has("year") ? $request->year : date('Y');
        if ($request->has("structure_id"))
            $strutturaId = $request->structure_id;

        $dataView['strutture'] = Auth::user()->structures();
        $dataView['anni'] = DB::table('flows_sdo')
            ->where("structure_id", $strutturaId)
            ->distinct()
            ->pluck("year");

        $numeratore = PCT::join('uploated_files as f', 'target9_PCT.uploated_file_id', '=', 'f.id')
            ->where('target9_PCT.year', $anno)
            ->where('target9_PCT.structure_id', $strutturaId)
            ->where('f.approved', 1)
            ->orderBy('target9_PCT.created_at', 'desc')
            //->select("numerator")
            ->first();
        $denominatore = DB::table("flows_sdo")
            ->where("year", $anno)
            ->where("structure_id", $strutturaId)
            ->sum("ob9_2");
        $rapporto = ($denominatore > 0 && isset($numeratore)) ? round($numeratore->numerator / $denominatore * 100, 2) : 0;

        $dataView['pct'] = [
            "numeratore" => $numeratore,
            "denominatore" => $denominatore,
            "rapporto" => $rapporto,
        ];
        $gareTotali = Gare::where('year', $anno)
            ->where('structure_id', $strutturaId)
            ->whereNotNull("uploated_file_gara_id")
            ->count();
        $gareConDelibere = Gare::where('year', $anno)
            ->where('structure_id', $strutturaId)
            ->whereNotNull("uploated_file_delibera_id")
            ->count();
        $rapporto = $gareTotali > 0 ? (($gareConDelibere / $gareTotali) * 100) : 0;

        /*
        if ($rapporto >= 95) {
            $risultato = 2.5;
        } else {
            $risultato = round((($rapporto / 95) * 2.5), 2);
        }
        */
        $dataView['gare'] = [
            "totali" => $gareTotali,
            "conDelibere" => $gareConDelibere,
            "rapporto" => $rapporto,
        ];

        $dataView['chart91'] = Chartjs::build()
            ->name("Gare")
            ->type("doughnut")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Gare con delibere', 'Gare totali'])
            ->datasets([
                [
                    "label" => "Gare (?)",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(255, 99, 132, 0.7)"
                    ],
                    "data" => [$gareConDelibere, $gareTotali]

                ]
            ]);
        /*
        ->options([
            'responsive' => true,
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Gare'

                ]
            ]
        ]);*/
        /*
                    if ($percentuale > 95) {
                        $dataView['messaggioTmp'] = [
                            'text' => "Obiettivo raggiunto",
                            'class' => 'text-success'
                        ];
                    } else {
                        $dataView['messaggioTmp'] = [
                            'text' => "Obiettivo non raggiunto",
                            'class' => 'text-danger'
                        ];
                    }
        */

        return view("farmaci")->with("dataView", $dataView);
    }


    public function farmaciPCT(Request $request)
    {

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
                $errori[$row][] = "Formato della data deve essere aaaa-mm-gg; dato letto: " . $data[0];
            }
            if (!is_numeric($data[1]) || $data[1] <= 0) {
                $errori[$row][] = "Quantità errata: " . $data[1];
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




    public function caricamentoScreening(Request $request)
    {

        $dataView['categorie'] = DB::table("target_categories")
            ->where("target_number", $request->obiettivo)
            ->orderBy("order")
            ->get();

        $dataView['structures'] = Auth::user()->structures();
        $dataView['titolo'] = config("constants.OBIETTIVO.5.text");
        $dataView['icona'] = config("constants.OBIETTIVO.5.icon");
        $dataView['tooltip'] = config("constants.OBIETTIVO.5.tooltip");

        $dataView['file'] = DB::table('uploated_files as up')
            ->join('target_categories as tc', 'up.target_category_id', '=', 'tc.id')
            ->where('up.user_id', Auth::user()->id)
            ->where('up.target_number', $request->obiettivo)
            ->select('up.target_number', 'up.target_category_id', 'tc.category', 'up.validator_user_id', 'up.approved', 'up.created_at')
            ->get();


        // Dati per la tabella nella view 
        $dataView['tableData'] = DB::table('insert_mmg')
            ->select('mmg_totale', 'mmg_coinvolti', 'year', 'structure_id')
            ->get();


        $dataView['obiettivo'] = $request->obiettivo;

        return view('caricamentoScreening')->with("dataView", $dataView);
    }



    public function downloadPdf($obiettivo, Request $request)
    {
        switch ($obiettivo) {
            case 5:
                $dataView['tableData'] = DB::table('insert_mmg')
                    ->join('structures as s', 'insert_mmg.structure_id', '=', 's.id')
                    ->select('mmg_totale', 'mmg_coinvolti', 'year', 'structure_id', 's.name as nome_struttura')
                    ->get();

                $pdf = PDF::loadView('pdfs.screeningPdf', $dataView);

                return $pdf->download('certificazione_completa.pdf');
            case 6:

                $dataView['target6_data'] = DB::table('target6_data')
                    ->whereIn(DB::raw('(anno, id)'), function ($query) {
                        $query->select(DB::raw('anno, MAX(id)'))
                            ->from('target6_data')
                            ->groupBy('anno');
                    })
                    ->select('totale_accertamenti', 'anno', 'numero_opposti', 'totale_cornee')
                    ->orderBy('anno', 'asc')
                    ->get();

                $pdf = PDF::loadView('pdfs.donazioniPdf', $dataView);

                return $pdf->download('certificazione_completa.pdf');

        }
    }


    public function garanziaLea(Request $request)
    {
        $dataView['dataInizioDefault'] = $request->data_inizio ?? date('Y') . '-01-01';
        $dataView['dataFineDefault'] = $request->data_fine ?? date('Y-m-d');


        /*
            $meseFine = $request->data_fine ? (new \DateTime($request->data_fine))->format('m') : null;
            $annoFine = $request->data_fine ? (new \DateTime($request->data_fine))->format('Y') : null;
            
            $meseInizio = $request->data_inizio ? (new \DateTime($request->data_inizio))->format('m') : null;
            $annoInizio = $request->data_inizio ? (new \DateTime($request->data_inizio))->format('Y') : null;
        */

        $dataInizio = $request->data_inizio ?: $dataView['dataInizioDefault'];
        $dataFine = $request->data_fine ?: $dataView['dataFineDefault'];

        $dataInizio = (new \DateTime($dataInizio))->format('Y-m-d');
        $dataFine = (new \DateTime($dataFine))->format('Y-m-d');

        $dataView['primoGrafico'] = DB::table('flows_sdo')
            ->select('flows_sdo.ob10_1', 'flows_sdo.year', 'flows_sdo.month', 'flows_sdo.structure_id', 's.name as nome_struttura')
            ->join('structures as s', 'flows_sdo.structure_id', '=', 's.id')
            ->join('users_structures as us', 'flows_sdo.structure_id', '=', 'us.structure_id')
            ->where('us.user_id', Auth::user()->id, )
            ->whereRaw("STR_TO_DATE(CONCAT(year, '-', month, '-01'), '%Y-%m-%d') BETWEEN ? AND ?", [$dataInizio, $dataFine])
            ->get();

        $dataView['denominatore'] = 250;
        $mediaNumeratore = $dataView['primoGrafico']->avg('ob10_1');

        $percentuale = ($mediaNumeratore / $dataView['denominatore']) * 100;

        $complementare = 100 - $percentuale;

        //dd($complementaryValueTmp);
        //dd($dataView['primoGrafico']);

        $dataView['areaPrevenzione'] = Chartjs::build()
            ->name("chartCodiciDD")
            ->type("doughnut")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Non vaccinati', 'Vaccinati'])
            ->datasets([
                [
                    "label" => "Vaccinati",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(255, 99, 132, 0.7)"
                    ],
                    "data" => [$percentuale, $complementare]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura vaccinale ciclo base'

                    ]
                ]
            ]);

        if ($percentuale > 95) {
            $dataView['messaggioTmp'] = [
                'text' => "Obiettivo raggiunto",
                'class' => 'text-success'
            ];
        } else {
            $dataView['messaggioTmp'] = [
                'text' => "Obiettivo non raggiunto",
                'class' => 'text-danger'
            ];
        }

        /*********************************************************************************/
        /*
                $dataView['prevenzioneDue'] = DB::table('flows_sdo')
                ->select('ob10_2', 'year', 'month', 'structure_id', 's.name as nome_struttura')
                ->join('structures as s', 'flows_sdo.structure_id', '=', 's.id')
                ->whereRaw("STR_TO_DATE(CONCAT(year, '-', month, '-01'), '%Y-%m-%d') BETWEEN ? AND ?", [$dataInizio, $dataFine])
                ->get();
        */

        $dataView['prevenzioneDue'] = DB::table('flows_sdo')
            ->select('flows_sdo.ob10_2', 'flows_sdo.year', 'flows_sdo.month', 'flows_sdo.structure_id', 's.name as nome_struttura')
            ->join('structures as s', 'flows_sdo.structure_id', '=', 's.id')
            ->join('users_structures as us', 'flows_sdo.structure_id', '=', 'us.structure_id')
            ->where('us.user_id', Auth::user()->id, )
            ->whereRaw("STR_TO_DATE(CONCAT(flows_sdo.year, '-', flows_sdo.month, '-01'), '%Y-%m-%d') BETWEEN ? AND ?", [$dataInizio, $dataFine])
            ->get();



        $dataView['denominatore'] = 100;
        $mediaNumeratore = $dataView['prevenzioneDue']->avg('ob10_2');
        $percentuale = ($mediaNumeratore / $dataView['denominatore']) * 100;
        $complementare = 100 - $percentuale;

        $dataView['areaPrevenzionePrimaDose'] = Chartjs::build()
            ->name("areaPrevenzionePrimaDose")
            ->type("doughnut")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Non vaccinati', 'Vaccinati'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(255, 99, 132, 0.7)"
                    ],
                    "data" => [$percentuale, $complementare]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura vaccinale ciclo base'

                    ]
                ]
            ]);

        if ($percentuale > 95) {
            $dataView['messaggioTmpPrevenzioneDue'] = [
                'textPrevenzioneDue' => "Obiettivo raggiunto",
                'classPrevenzioneDue' => 'text-success'
            ];
        } else {
            $dataView['messaggioTmpPrevenzioneDue'] = [
                'textPrevenzioneDue' => "Obiettivo non raggiunto",
                'classPrevenzioneDue' => 'text-danger'
            ];
        }

        /***************************************************************************************/

        $dataView['prevenzioneTre'] = DB::table('flows_sdo')
            ->select('flows_sdo.ob10_3', 'flows_sdo.year', 'flows_sdo.month', 'flows_sdo.structure_id', 's.name as nome_struttura')
            ->join('structures as s', 'flows_sdo.structure_id', '=', 's.id')
            ->join('users_structures as us', 'flows_sdo.structure_id', '=', 'us.structure_id')
            ->where('us.user_id', Auth::user()->id, )
            ->whereRaw("STR_TO_DATE(CONCAT(year, '-', month, '-01'), '%Y-%m-%d') BETWEEN ? AND ?", [$dataInizio, $dataFine])
            ->get();


        $dataView['denominatore'] = 150;
        $mediaNumeratore = $dataView['prevenzioneTre']->avg('ob10_3');
        $percentuale = ($mediaNumeratore / $dataView['denominatore']) * 100;
        $complementare = 100 - $percentuale;



        $dataView['Veterinaria'] = Chartjs::build()
            ->name("Veterinaria")
            ->type("doughnut")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Non vaccinati', 'Vaccinati'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(255, 99, 132, 0.7)"
                    ],
                    "data" => [$percentuale, $complementare]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura vaccinale ciclo base'

                    ]
                ]
            ]);


        if ($percentuale > 80) {
            $dataView['messaggioTmpPrevenzioneTre'] = [
                'textPrevenzioneTre' => "Obiettivo raggiunto",
                'classPrevenzioneTre' => 'text-success'
            ];
        } else {
            $dataView['messaggioTmpPrevenzioneTre'] = [
                'textPrevenzioneTre' => "Obiettivo non raggiunto",
                'classPrevenzioneTre' => 'text-danger'
            ];
        }

        /****************************************************************************************************/

        $dataView['ospedalizzazioneAdulta'] = Chartjs::build()
            ->name("ospedalizzazioneAdulta")
            ->type("line")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'])
            ->datasets([
                [
                    "label" => "Non Vaccinati",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "borderColor" => "rgba(38, 185, 154, 1)",
                    "data" => [100, 200, 300, 500, 100, 50, 400, 700, 95, 150, 250, 180],
                    "fill" => false,
                    "lineTension" => 0.1
                ],
                [
                    "label" => "Vaccinati",
                    "backgroundColor" => "rgba(255, 99, 132, 0.7)",
                    "borderColor" => "rgba(255, 99, 132, 1)",
                    "data" => [50, 120, 210, 320, 430, 520, 600, 450, 380, 330, 290, 240],
                    "fill" => false,
                    "lineTension" => 0.1
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura Vaccinale Ciclo Base per Mese'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Numero di Persone'
                        ]
                    ]
                ]
            ]);

        /********************************************************************************* */

        $dataView['asmaGastroenterite'] = Chartjs::build()
            ->name("asmaGastroenterite")
            ->type("line")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'])
            ->datasets([
                [
                    "label" => "Non Vaccinati",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "borderColor" => "rgba(38, 185, 154, 1)",
                    "data" => [45, 76, 45, 433, 123, 333, 400, 333, 95, 150, 250, 180],
                    "fill" => false,
                    "lineTension" => 0.1
                ],
                [
                    "label" => "Vaccinati",
                    "backgroundColor" => "rgba(255, 99, 132, 0.7)",
                    "borderColor" => "rgba(255, 99, 132, 1)",
                    "data" => [435, 33, 22, 67, 111, 445, 33, 434, 35, 353, 353, 433],
                    "fill" => false,
                    "lineTension" => 0.1
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura Vaccinale Ciclo Base per Mese'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Numero di Persone'
                        ]
                    ]
                ]
            ]);

        /**********************CIA1********************************************** */

        $dataView['CIA1'] = Chartjs::build()
            ->name("CIA1")
            ->type("line")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'])
            ->datasets([
                [
                    "label" => "Non Vaccinati",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "borderColor" => "rgba(38, 185, 154, 1)",
                    "data" => [45, 76, 45, 433, 123, 333, 400, 333, 95, 150, 250, 180],
                    "fill" => false,
                    "lineTension" => 0.1
                ],
                [
                    "label" => "Vaccinati",
                    "backgroundColor" => "rgba(255, 99, 132, 0.7)",
                    "borderColor" => "rgba(255, 99, 132, 1)",
                    "data" => [435, 33, 22, 67, 111, 445, 33, 434, 35, 353, 353, 433],
                    "fill" => false,
                    "lineTension" => 0.1
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura Vaccinale Ciclo Base per Mese'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Numero di Persone'
                        ]
                    ]
                ]
            ]);


        /****************************************CIA 2*****************************************************/

        $dataView['CIA2'] = Chartjs::build()
            ->name("CIA2")
            ->type("line")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'])
            ->datasets([
                [
                    "label" => "Non Vaccinati",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "borderColor" => "rgba(38, 185, 154, 1)",
                    "data" => [45, 76, 45, 433, 123, 333, 400, 333, 95, 150, 250, 180],
                    "fill" => false,
                    "lineTension" => 0.1
                ],
                [
                    "label" => "Vaccinati",
                    "backgroundColor" => "rgba(255, 99, 132, 0.7)",
                    "borderColor" => "rgba(255, 99, 132, 1)",
                    "data" => [435, 33, 22, 67, 111, 445, 33, 434, 35, 353, 353, 433],
                    "fill" => false,
                    "lineTension" => 0.1
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura Vaccinale Ciclo Base per Mese'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Numero di Persone'
                        ]
                    ]
                ]
            ]);



        /****************************************CIA3***************************************************** */


        $dataView['CIA3'] = Chartjs::build()
            ->name("CIA3")
            ->type("line")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'])
            ->datasets([
                [
                    "label" => "Non Vaccinati",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "borderColor" => "rgba(38, 185, 154, 1)",
                    "data" => [45, 76, 45, 433, 123, 333, 400, 333, 95, 150, 250, 180],
                    "fill" => false,
                    "lineTension" => 0.1
                ],
                [
                    "label" => "Vaccinati",
                    "backgroundColor" => "rgba(255, 99, 132, 0.7)",
                    "borderColor" => "rgba(255, 99, 132, 1)",
                    "data" => [435, 33, 22, 67, 111, 445, 33, 434, 35, 353, 353, 433],
                    "fill" => false,
                    "lineTension" => 0.1
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura Vaccinale Ciclo Base per Mese'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Numero di Persone'
                        ]
                    ]
                ]
            ]);

        /*************************************************************************************/

        $dataView['decessiTumore'] = Chartjs::build()
            ->name("decessiTumore")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Non vaccinati', 'Vaccinati'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(255, 99, 132, 0.7)"
                    ],
                    "data" => [100, 200]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura vaccinale ciclo base'

                    ]
                ]
            ]);

        /********************************************************************************* */

        $dataView['mammellaTumore'] = Chartjs::build()
            ->name("mammellaTumore")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Non vaccinati', 'Vaccinati'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(255, 99, 132, 0.7)"
                    ],
                    "data" => [100, 200]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura vaccinale ciclo base'

                    ]
                ]
            ]);

        /******************************************************************************************/


        $dataView['chartDRG'] = Chartjs::build()
            ->name("chartDRG")
            ->type("line")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'])
            ->datasets([
                [
                    "label" => "Non Vaccinati",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "borderColor" => "rgba(38, 185, 154, 1)",
                    "data" => [45, 76, 45, 433, 123, 333, 400, 333, 95, 150, 250, 180],
                    "fill" => false,
                    "lineTension" => 0.1
                ],
                [
                    "label" => "Vaccinati",
                    "backgroundColor" => "rgba(255, 99, 132, 0.7)",
                    "borderColor" => "rgba(255, 99, 132, 1)",
                    "data" => [435, 33, 22, 67, 111, 445, 33, 434, 35, 353, 353, 433],
                    "fill" => false,
                    "lineTension" => 0.1
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura Vaccinale Ciclo Base per Mese'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Numero di Persone'
                        ]
                    ]
                ]
            ]);

        /********************************************************************************** */



        $dataView['chartInfezioniPostChirurgiche'] = Chartjs::build()
            ->name("chartInfezioniPostChirurgiche")
            ->type("line")
            ->size(["width" => 300, "height" => 150])
            ->labels(['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'])
            ->datasets([
                [
                    "label" => "Non Vaccinati",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "borderColor" => "rgba(38, 185, 154, 1)",
                    "data" => [45, 76, 45, 433, 123, 333, 400, 333, 95, 150, 250, 180],
                    "fill" => false,
                    "lineTension" => 0.1
                ],
                [
                    "label" => "Vaccinati",
                    "backgroundColor" => "rgba(255, 99, 132, 0.7)",
                    "borderColor" => "rgba(255, 99, 132, 1)",
                    "data" => [435, 33, 22, 67, 111, 445, 33, 434, 35, 353, 353, 433],
                    "fill" => false,
                    "lineTension" => 0.1
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Copertura Vaccinale Ciclo Base per Mese'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Numero di Persone'
                        ]
                    ]
                ]
            ]);

        return view("garanzia-lea")->with("dataView", $dataView);
    }



    public function fse(Request $request)
    {
        $dataView['dataSelezionata'] = $request->annoSelezionato ?? date('Y');
       
/*
        $dataView['dataInizioDefault'] = $request->data_inizio ?? date('Y') . '-01-01';
        $dataView['dataFineDefault'] = $request->data_fine ?? date('Y-m-d');

        $dataInizio = $request->data_inizio ?: $dataView['dataInizioDefault'];
        $dataFine = $request->data_fine ?: $dataView['dataFineDefault'];

        $dataInizio = (new \DateTime($dataInizio))->format('Y-m-d');
        $dataFine = (new \DateTime($dataFine))->format('Y-m-d');
*/


        /*****************************Dimissioni Ospedaliere**********************************/

        $dataView['prevenzioneTre'] = DB::table('target7_data')
            ->select('*')
            ->where('anno', "=",  $dataView['dataSelezionata'])
            ->get();

        //numeratori
        foreach ($dataView['prevenzioneTre'] as $row) {
            $dataView['dimissioniOspedaliere'] = $row->dimissioni_ospedaliere;
            $dataView['dimissioniPS'] = $row->dimissioni_ps;
            $dataView['prestazioniLab'] = $row->prestazioni_laboratorio;
            $dataView['prestazioniRadiologia'] = $row->prestazioni_radiologia;
            $dataView['specialisticaAmbulatoriale'] = $row->prestazioni_ambulatoriali;
            $dataView['vaccinati'] = $row->vaccinati;
            $dataView['certificatiIndicizzati'] = $row->certificati_indicizzati;
            $dataView['documentiIndicizzati'] = $row->documenti_indicizzati;
            $dataView['documentiIndicizzatiCDA2'] = $row->documenti_indicizzati_cda2;
            $dataView['documentiCDA2'] = $row->documenti_cda2;
            $dataView['documentiPades'] = $row->documenti_pades;
            $dataView['documentiIndicizzatiPades'] = $row->documenti_indicizzati_pades;
        }


        // Estrai i dati del denominatore
        $dataView['denominatore'] = DB::table('flows_sdo')
            ->join('users_structures AS us', 'flows_sdo.structure_id', '=', 'us.structure_id')
            ->where('us.user_id', 7)
            ->where('flows_sdo.year',  $dataView['dataSelezionata']) // Filtro per l'anno corrente
            ->select(
                DB::raw('MAX(flows_sdo.ob7_1) as ob7'),
                DB::raw('MAX(flows_sdo.id) as id'),
                'flows_sdo.year'
            )
            ->groupBy('flows_sdo.year')
            ->orderByDesc('flows_sdo.year')
            ->get();

        foreach ($dataView['denominatore'] as $row) {
            $dataView['ob7'] = $row->ob7;
        }


        // if (isset($dataView['dimissioniOspedaliere']) && isset($dataView['ob7']) && $dataView['ob7'] != 0) {
        $dataView['percentualeDimissioniOspedaliere'] = round(($dataView['dimissioniOspedaliere'] / $dataView['ob7']) * 100, 2);
        // }

        $dataView['percentualeDimissioniOspedaliereComplementare'] = 100 - $dataView['percentualeDimissioniOspedaliere'];

        $dataView['chartDimissioniOspedaliere'] = Chartjs::build()
            ->name("chartDimissioniOspedaliere")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Indicizzati', 'Non indicizzati'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                        

                    ],
                    "data" => [$dataView['percentualeDimissioniOspedaliere'], $dataView['percentualeDimissioniOspedaliereComplementare'],]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => ''

                    ]
                ]
            ]);

        /*****************************Dimissioni Pronto Soccorso****************************************************/

        $dataView['denoProntoSoccorso'] = DB::table('flows_emur')
            ->select('ia1_2', 'year', 'month')
            ->where('year', "=", date('Y'))
            ->get();

        foreach ($dataView['denoProntoSoccorso'] as $row) {
            $dataView['ob7PS'] = $row->ia1_2;
        }

      
        if ($dataView['dimissioniPS'] != 0) {
            $dataView['percentualePS'] = round($dataView['dimissioniPS'] / $dataView['ob7PS']  * 100, 2);
           
            $dataView['percentualeComplementarePS'] = 100 - $dataView['percentualePS'];
          
        } else {
            $dataView['percentualePS'] = 0; 
            $dataView['percentualeComplementarePS'] = 100;
        }
        


        $dataView['chartProntoSoccorso'] = Chartjs::build()
            ->name("chartProntoSoccorso")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Indicizzati', 'Non Indicizzati'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                        
                    ],
                    "data" => [$dataView['percentualePS'], $dataView['percentualeComplementarePS']]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => ''

                    ]
                ]
            ]);

        /*********************Prestazioni di Laboratorio****************************** */

        $dataView['denFlussoC'] = DB::table('flows_c')
            ->select('ia1_3', 'ia1_4', 'ia1_5', 'ia1_6', 'year', 'month')
            ->where('year', "=", date('Y'))
            ->get();

        foreach ($dataView['denFlussoC'] as $dati) {
            $dataView['PrestazioniLabDen'] = $dati->ia1_3;
            $dataView['PrestazioniRadDen'] = $dati->ia1_4;
            $dataView['PrestazioniAmbulatoriale'] = $dati->ia1_5;
            $dataView['prestazioniErogate'] = $dati->ia1_6;

        }

   

        $dataView['percentualePrestLab'] = round($dataView['prestazioniLab'] / $dataView['PrestazioniLabDen'] * 100, 2);
        $dataView['percentualeComplementarePrestLab'] = 100 - $dataView['percentualePrestLab'];



        $dataView['chartRefertiLaboratorio'] = Chartjs::build()
            ->name("chartRefertiLaboratorio")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Indicizzati', 'Non Indicizzati'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                        
                    ],
                    "data" => [$dataView['percentualePrestLab'], $dataView['percentualeComplementarePrestLab']]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => ''

                    ]
                ]
            ]);


  
        /*********************Ref radiologia*********************************************************** */


        $dataView['percentualeRefRadiologia'] = round($dataView['prestazioniRadiologia'] / $dataView['PrestazioniRadDen'] * 100, 2);
        $dataView['percentualeComplementareRefRadiologia'] = 100 - $dataView['percentualeRefRadiologia'];

        $dataView['chartRefertiRadiologia'] = Chartjs::build()
            ->name("chartRefertiRadiologia")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Indicizzati', 'Non indicizzati'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                        
                    ],
                    "data" => [$dataView['percentualeRefRadiologia'], $dataView['percentualeComplementareRefRadiologia']]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => ''

                    ]
                ]
            ]);

        /**********************Specialistica Ambulatoriale**********************************************************/

        $dataView['percentualeSpecAmbulatoriale'] = round($dataView['specialisticaAmbulatoriale'] / $dataView['PrestazioniAmbulatoriale'] * 100, 2);
        $dataView['percentualeComplementareSpecAmbulatoriale'] = 100 - $dataView['percentualeSpecAmbulatoriale'];

        $dataView['chartSpecialisticaAmbulatoriale'] = Chartjs::build()
            ->name("chartSpecialisticaAmbulatoriale")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Indicizzati', 'Non Indicizzati'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                        
                    ],
                    "data" => [$dataView['percentualeSpecAmbulatoriale'], $dataView['percentualeComplementareSpecAmbulatoriale']]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => ''

                    ]
                ]
            ]);


        /****************************Vaccinati****************************************************** */



        if ($dataView['vaccinati'] != 0) {
            $dataView['percentualeVaccinati'] = round($dataView['certificatiIndicizzati'] / $dataView['vaccinati'] * 100, 2);
            $dataView['percentualeComplementareVaccinati'] = 100 - $dataView['percentualeVaccinati'];
        } else {
            $dataView['percentualeVaccinati'] = 0; 
            $dataView['percentualeComplementareVaccinati'] = 100;
        }
        
      

        $dataView['chartCertificatiVaccinali'] = Chartjs::build()
            ->name("chartCertificatiVaccinali")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Indicizzati', 'Non Indicizzati'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                    ],
                    "data" => [$dataView['percentualeVaccinati'], $dataView['percentualeComplementareVaccinati']]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => ''

                    ]
                ]
            ]);


        /**************************Documentazione FSE************************************************************ */



        $dataView['percentualeDocumentazioneFse'] = round($dataView['documentiIndicizzati'] / $dataView['prestazioniErogate'] * 100, 2);
        $dataView['percentualeComplementareDocumentazioneFse'] = 100 - $dataView['percentualeDocumentazioneFse'];

        $dataView['chartDocumentiFSE'] = Chartjs::build()
            ->name("chartDocumentiFSE")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Indicizzati', 'Non Indicizzati'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                    ],
                    "data" => [$dataView['percentualeDocumentazioneFse'], $dataView['percentualeComplementareDocumentazioneFse']]
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => ''

                    ]
                ]
            ]);

        /***************************Documenti in CDA2************************************************************* */


        if ($dataView['documentiIndicizzatiCDA2'] != 0) {
            $dataView['percentualeDocumentiCDA2'] = round($dataView['documentiCDA2'] / $dataView['documentiIndicizzatiCDA2'] * 100, 2);
            $dataView['percentualeComplementareDocumentiCDA2'] = 100 - $dataView['percentualeDocumentiCDA2'];
        } else {
            $dataView['percentualeDocumentiCDA2'] = 0; 
            $dataView['percentualeComplementareDocumentiCDA2'] = 100;
        }
        

        $dataView['chartDocumentiCDA2'] = Chartjs::build()
            ->name("chartDocumentiCDA2")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Non CDA2', 'CDA2'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                        
                    ],
                    "data" => [$dataView['percentualeDocumentiCDA2'], $dataView['percentualeComplementareDocumentiCDA2']]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => ''

                    ]
                ]
            ]);

        /***************************Documenti Pades************************************************************ */
        if ($dataView['documentiIndicizzatiPades'] != 0) {
            $dataView['percentualePades'] = round($dataView['documentiPades'] / $dataView['documentiIndicizzatiPades'] * 100, 2);
            $dataView['percentualeComplementarePades'] = 100 - $dataView['percentualePades'];
        } else {
            $dataView['percentualePades'] = 0; 
            $dataView['percentualeComplementarePades'] = 100;
        }
        

        $dataView['chartDocumentiPades'] = Chartjs::build()
            ->name("chartDocumentiPades")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Pades', 'Non pades'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                        
                    ],
                    "data" => [$dataView['percentualePades'] , $dataView['percentualeComplementarePades']]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => ''

                    ]
                ]
            ]);

           // $punteggio = $this->punteggioOb7_1($dataView);
          //  $dataView = array_merge($punteggio, $dataView);


        return view("fse")->with("dataView", $dataView);
    }



    public function caricamentoFse(Request $request)
    {

        $dataView['structures'] = Auth::user()->structures();
        $dataView['titolo'] = config("constants.OBIETTIVO.$request->obiettivo.text");
        $dataView['icona'] = config("constants.OBIETTIVO.$request->obiettivo.icon");
        $dataView['tooltip'] = config("constants.OBIETTIVO.$request->obiettivo.tooltip");

        $dataView['categorie'] = DB::table("target_categories")
            ->where("target_number", $request->obiettivo)
            ->orderBy("order")
            ->get();

        $dataView['file'] = DB::table('uploated_files as up')
            ->join('target_categories as tc', 'up.target_category_id', '=', 'tc.id')
            ->where('up.user_id', Auth::user()->id)
            ->where('up.target_number', $request->obiettivo)
            ->select('up.target_number', 'up.target_category_id', 'tc.category', 'up.validator_user_id', 'up.approved', 'up.created_at')
            ->get();

 
        $dataView['tableData'] = DB::table('target7_data')
            ->join('structures as s', 'target7_data.structure_id', '=', 's.id')
            ->select('dimissioni_ospedaliere', 'dimissioni_ps', 'anno', 'structure_id', 's.name as nome_struttura')
            ->get();


        $dataView['obiettivo'] = $request->obiettivo;

        return view('caricamentoFse')->with("dataView", $dataView);
    }


    public function caricamentoGaranziaLea(Request $request){


        $dataView['structures'] = Auth::user()->structures();
        $dataView['titolo'] = config("constants.OBIETTIVO.$request->obiettivo.text");
        $dataView['icona'] = config("constants.OBIETTIVO.$request->obiettivo.icon");
        $dataView['tooltip'] = config("constants.OBIETTIVO.$request->obiettivo.tooltip");

        $dataView['categorie'] = DB::table("target_categories")
            ->where("target_number", $request->obiettivo)
            ->orderBy("order")
            ->get();

        $dataView['file'] = DB::table('uploated_files as up')
            ->join('target_categories as tc', 'up.target_category_id', '=', 'tc.id')
            ->where('up.user_id', Auth::user()->id)
            ->where('up.target_number', $request->obiettivo)
            ->select('up.target_number', 'up.target_category_id', 'tc.category', 'up.validator_user_id', 'up.approved', 'up.created_at')
            ->get();

 
        $dataView['tableData'] = DB::table('target7_data')
            ->join('structures as s', 'target7_data.structure_id', '=', 's.id')
            ->select('dimissioni_ospedaliere', 'dimissioni_ps', 'anno', 'structure_id', 's.name as nome_struttura')
            ->get();


        $dataView['obiettivo'] = $request->obiettivo;


        return view("caricamentogaranziaLea")->with("dataView", $dataView);
    }

    public function esiti(Request $request){

        
        $dataView['chartFratturaFemore'] = Chartjs::build()
            ->name("chartDocumentiPades")
            ->type("doughnut")
            ->size(["width" => 200, "height" => 100])
            ->labels(['Intervento >= 2 giorni', 'Intervento <= 2 giorni'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                        
                    ],
                    "data" => [20,40]

                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => ''

                    ]
                ]
            ]);



        return view("esisti")->with("dataView", $dataView);
    }





}
