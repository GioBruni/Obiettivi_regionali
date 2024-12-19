<?php

namespace App\Http\Controllers;

use App\ChartTrait;
use App\Models\Gare;
use App\Models\LocationsUsers;
use App\Models\PCT;
use App\Models\Target1;
use App\Models\Target5;
use App\Models\Target6_data;
use App\Models\Target7_data;
use App\Models\Target10_data;
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
use Illuminate\Support\Facades\Validator;

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
                ->leftJoin("target3_data", "target3_data.uploated_file_id", "=", "uploated_files.id")
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


    //Tmp
    protected function calcoloPunteggioOb4_1($overallAverageTmp, $overallAverageBoarding)
    {
        $dataView = [];

        $dataView['userStructures'] = LocationsUsers::where("user_id", Auth::user()->id)
            ->leftJoin("structures", "structures.id", "=", "users_structures.structure_id")
            ->leftJoin("structure_type", "structure_type.code", "=", "structures.type")
            ->orderby("structures.id")->get();

        //dd($dataView['userStructures']);
        $dataView['punteggi'] = [];
        // Per ogni struttura e per ogni obiettivo recupero il punteggio teorico e calcolo il punteggio ottenuto
        foreach ($dataView['userStructures'] as $struttura) {
            //dd($struttura->column_points);
            if ($struttura->column_points === 'ao') {
                if ($overallAverageTmp >= 85) {
                    $dataView['messaggioTmp'] = [
                        'text' => "Pieno raggiungimento dell'obiettivo",
                        'class' => 'text-success',
                        'percentuale' => 100,
                        'punteggio' => 5.6
                    ];
                } elseif ($overallAverageTmp >= 75) {
                    $dataView['messaggioTmp'] = [
                        'text' => "Raggiungimento dell'obiettivo al 50%",
                        'class' => 'text-warning',
                        'percentuale' => 50,
                        'punteggio' => 2.8
                    ];
                } else { // se $overallAverageTmp < 75
                    $dataView['messaggioTmp'] = [
                        'text' => "Obiettivo non raggiunto",
                        'class' => 'text-danger',
                        'percentuale' => 0,
                        'punteggio' => 0
                    ];
                }

            } elseif ($struttura->column_points === 'asp') {
                if ($overallAverageTmp >= 85) {
                    $dataView['messaggioTmp'] = [
                        'text' => "Pieno raggiungimento dell'obiettivo",
                        'class' => 'text-success',
                        'percentuale' => 100,
                        'punteggio' => 2.8
                    ];
                } elseif ($overallAverageTmp >= 75) {
                    $dataView['messaggioTmp'] = [
                        'text' => "Raggiungimento dell'obiettivo al 50%",
                        'class' => 'text-warning',
                        'percentuale' => 50,
                        'punteggio' => 1.4
                    ];
                } else { // se $overallAverageTmp < 75
                    $dataView['messaggioTmp'] = [
                        'text' => "Obiettivo non raggiunto",
                        'class' => 'text-danger',
                        'percentuale' => 0,
                        'punteggio' => 0
                    ];
                }
            }

            return [
                'messaggioTmp' => $dataView['messaggioTmp'],
                // 'messaggioBoarding' => $dataView['messaggioBoarding'],
                'overallAverageTmp' => $overallAverageTmp,
                'overallAverageBoarding' => $overallAverageBoarding,
            ];
        }
    }
    //Boarding
    protected function calcoloPunteggioOb4_2($overallAverageBoarding)
    {

        $dataView = [];

        $dataView['userStructures'] = LocationsUsers::where("user_id", Auth::user()->id)
            ->leftJoin("structures", "structures.id", "=", "users_structures.structure_id")
            ->leftJoin("structure_type", "structure_type.code", "=", "structures.type")
            ->orderby("structures.id")->get();

        // dd($dataView['userStructures']);
        $dataView['punteggi'] = [];
        // Per ogni struttura e per ogni obiettivo recupero il punteggio teorico e calcolo il punteggio ottenuto
        foreach ($dataView['userStructures'] as $struttura) {
            //dd($struttura->column_points);
            if ($struttura->column_points === 'ao') {
                if ($overallAverageBoarding <= 2) {
                    $dataView['messaggioBoarding'] = [
                        'text' => "Pieno raggiungimento dell'obiettivo",
                        'class' => 'text-success',
                        'percentuale' => 100,
                        'punteggio' => 2.4 // Punteggio massimo
                    ];
                } elseif ($overallAverageBoarding > 2 && $overallAverageBoarding <= 4) {
                    $dataView['messaggioBoarding'] = [
                        'text' => "Raggiungimento dell'obiettivo al 50%",
                        'class' => 'text-warning',
                        'percentuale' => 50,
                        'punteggio' => 1.2 // 50% del punteggio massimo
                    ];
                } else { // se $overallAverageBoarding > 4
                    $dataView['messaggioBoarding'] = [
                        'text' => "Obiettivo non raggiunto",
                        'class' => 'text-danger',
                        'percentuale' => 0,
                        'punteggio' => 0 // Punteggio nullo
                    ];
                }


            } elseif ($struttura->column_points === 'asp') {

                if ($overallAverageBoarding <= 2) {
                    $dataView['messaggioBoarding'] = [
                        'text' => "Pieno raggiungimento dell'obiettivo",
                        'class' => 'text-success',
                        'percentuale' => 100,
                        'punteggio' => 1.2 // Punteggio massimo
                    ];
                } elseif ($overallAverageBoarding > 2 && $overallAverageBoarding <= 4) {
                    $dataView['messaggioBoarding'] = [
                        'text' => "Raggiungimento dell'obiettivo al 50%",
                        'class' => 'text-warning',
                        'percentuale' => 50,
                        'punteggio' => 0.6 // 50% del punteggio massimo
                    ];
                } else { // se $overallAverageBoarding > 4
                    $dataView['messaggioBoarding'] = [
                        'text' => "Obiettivo non raggiunto",
                        'class' => 'text-danger',
                        'percentuale' => 0,
                        'punteggio' => 0 // Punteggio nullo
                    ];
                }

            }

            return [
                'messaggioBoarding' => $dataView['messaggioBoarding'],
                //'overallAverageTmp' => $overallAverageTmp,
                'overallAverageBoarding' => $overallAverageBoarding,
            ];
        }
    }


    protected function calcoloPunteggioOb6_2($percentualeData)
    {

        $dataView = [];

        $array2024 = array(5, 3, 2);
        $array2025 = array(15, 10, 7);
        $array2026 = array(25, 15, 10);

        $dataView['userStructures'] = LocationsUsers::where("user_id", Auth::user()->id)
            ->leftJoin("structures", "structures.id", "=", "users_structures.structure_id")
            ->leftJoin("structure_type", "structure_type.code", "=", "structures.type")
            ->orderby("structures.id")->get();

        // dd($dataView['userStructures']);
        $dataView['punteggi'] = [];

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


            foreach ($dataView['userStructures'] as $struttura) {
                if ($struttura->column_points === 'ao') {
                    if ($percentualeData > $targetArray[0]) {
                        $dataView['messaggioTmp'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo con punteggio: 2.7",
                            'class' => 'text-success'
                        ];
                    } elseif ($percentualeData >= $targetArray[1] && $percentualeData <= $targetArray[0]) {

                        $dataView['messaggioTmp'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo all'80% con punteggio: 2.16",
                            'class' => 'text-warning'
                        ];
                    } elseif ($percentualeData >= $targetArray[2] && $percentualeData < $targetArray[1]) {

                        $dataView['messaggioTmp'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo al 50% con punteggio: 1.35",
                            'class' => 'text-warning'
                        ];
                    } else {

                        $dataView['messaggioTmp'] = [
                            'text' => $anno . ": " . "Obiettivo non raggiunto con punteggio: 0",
                            'class' => 'text-danger'
                        ];
                    }
                } elseif ($struttura->column_points === 'asp') {
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


            }
        }
        return $dataView;
    }


    protected function calcoloPunteggioOb6_3($percentualeData)
    {

        $dataView = [];

        $targetArray = array(38, 41, 45);
        // $array2025 = array(15, 10, 7);
        //  $array2026 = array(25, 15, 10);

        $dataView['userStructures'] = LocationsUsers::where("user_id", Auth::user()->id)
            ->leftJoin("structures", "structures.id", "=", "users_structures.structure_id")
            ->leftJoin("structure_type", "structure_type.code", "=", "structures.type")
            ->orderby("structures.id")->get();

        // dd($dataView['userStructures']);
        $dataView['punteggi'] = [];

        $anno = date('Y');

        if ($percentualeData < 0) {
            $dataView['messaggiOb6_3'] = [
                'text' => $anno . ": " . "Percentuale negativa, obiettivo non raggiunto con punteggio: 0",
                'class' => 'text-danger'
            ];
        } else {
            foreach ($dataView['userStructures'] as $struttura) {
                if ($struttura->column_points === 'asp') {

                    if ($percentualeData <= $targetArray[0]) {
                        // Se il valore è inferiore o uguale a 38%, obiettivo pienamente raggiunto (1.5 punti)
                        $dataView['messaggiOb6_3'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo con punteggio: 1.5",
                            'class' => 'text-success'
                        ];
                    } elseif ($percentualeData > $targetArray[0] && $percentualeData <= $targetArray[1]) {
                        // Se il valore è compreso tra 38% e 41%, obiettivo raggiunto all'80% (1.2 punti)
                        $dataView['messaggiOb6_3'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo all'80% con punteggio: 1.2",
                            'class' => 'text-warning'
                        ];
                    } elseif ($percentualeData > $targetArray[1] && $percentualeData <= $targetArray[2]) {
                        // Se il valore è compreso tra 41% e 45%, obiettivo raggiunto al 50% (0.75 punti)
                        $dataView['messaggiOb6_3'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo al 50% con punteggio: 0.75",
                            'class' => 'text-warning'
                        ];
                    } else {
                        // Se il valore è maggiore di 45%, obiettivo non raggiunto (0 punti)
                        $dataView['messaggiOb6_3'] = [
                            'text' => $anno . ": " . "Obiettivo non raggiunto con punteggio: 0",
                            'class' => 'text-danger'
                        ];
                    }

                } elseif ($struttura->column_points === 'ao') {
                    if ($percentualeData <= $targetArray[0]) {

                        $dataView['messaggiOb6_3'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo con punteggio: 2.7",
                            'class' => 'text-success'
                        ];
                    } elseif ($percentualeData > $targetArray[0] && $percentualeData <= $targetArray[1]) {

                        $dataView['messaggiOb6_3'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo all'80% con punteggio: 2.16",
                            'class' => 'text-warning'
                        ];
                    } elseif ($percentualeData > $targetArray[1] && $percentualeData <= $targetArray[2]) {

                        $dataView['messaggiOb6_3'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo al 50% con punteggio: 1.35",
                            'class' => 'text-warning'
                        ];
                    } else {

                        $dataView['messaggiOb6_3'] = [
                            'text' => $anno . ": " . "Obiettivo non raggiunto con punteggio: 0",
                            'class' => 'text-danger'
                        ];
                    }
                }
            }
        }
        return $dataView;
    }

    //da fare ancora in dubbio 
    protected function calcoloPunteggioOb6_4($percentualeData)
    {

        $dataView = [];

        $array2024 = array(10, 5, 3);
        $array2025 = array(15, 10, 7);
        $array2026 = array(30, 25, 20, 15);

        $dataView['userStructures'] = LocationsUsers::where("user_id", Auth::user()->id)
            ->leftJoin("structures", "structures.id", "=", "users_structures.structure_id")
            ->leftJoin("structure_type", "structure_type.code", "=", "structures.type")
            ->orderby("structures.id")->get();

        // dd($dataView['userStructures']);
        $dataView['punteggi'] = [];

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

            foreach ($dataView['userStructures'] as $struttura) {
                if ($struttura->column_points === 'ao') {
                    if ($percentualeData > $targetArray[0]) {
                        $dataView['messaggioTmp'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo con punteggio: 2.7",
                            'class' => 'text-success'
                        ];
                    } elseif ($percentualeData >= $targetArray[1] && $percentualeData <= $targetArray[0]) {

                        $dataView['messaggioTmp'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo all'80% con punteggio: 2.16",
                            'class' => 'text-warning'
                        ];
                    } elseif ($percentualeData >= $targetArray[2] && $percentualeData < $targetArray[1]) {

                        $dataView['messaggioTmp'] = [
                            'text' => $anno . ": " . "Raggiungimento dell'obiettivo al 50% con punteggio: 1.35",
                            'class' => 'text-warning'
                        ];
                    } else {

                        $dataView['messaggioTmp'] = [
                            'text' => $anno . ": " . "Obiettivo non raggiunto con punteggio: 0",
                            'class' => 'text-danger'
                        ];
                    }
                } elseif ($struttura->column_points === 'asp') {
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


            }
        }
        return $dataView;
    }

    //non abbiamo l'indicatore 2
    protected function calcoloPunteggioOb7($percentualeOb7_1, $percentualeOb7_3, $percentualeOb7_4)
    {
        $dataView = [];

        $array2024 = [40, 80, 80];
        $array2025 = [65, 90, 90];
        $array2026 = [90, 90, 90];

        $anno = date('Y');

        if ($percentualeOb7_1 < 0) {
            $dataView['messaggioTmpIncremento'] = [
                'textIncremento' => $anno . ": Percentuale negativa, obiettivo non raggiunto con punteggio",
                'punteggio' => 0,
                'classIncremento' => 'text-danger'
            ];
        } else {

            if ($anno == 2024) {
                $targetArray = $array2024;
            } elseif ($anno == 2025) {
                $targetArray = $array2025;
            } elseif ($anno == 2026) {
                $targetArray = $array2026;
            } else {
                $targetArray = [];
            }

            if ($percentualeOb7_1 >= $targetArray[0]) {
                $dataView['punteggioOb7_1'] = [
                    'textOb7_1' => $anno . ": Raggiungimento dell'obiettivo massimo con punteggio",
                    'punteggioOb7_1' => 2,
                    'percentualeOb7_1' => $percentualeOb7_1,
                    'classOb7_1' => 'text-success'
                ];
            } else {
                $dataView['punteggioOb7_1'] = [
                    'textOb7_1' => $anno . ": Obiettivo non raggiunto",
                    'punteggioOb7_1' => 0,
                    'percentualeOb7_1' => $percentualeOb7_1,
                    'classIncremento' => 'text-warning'
                ];
            }



        }
        if ($percentualeOb7_3 == $targetArray[1]) {
            $dataView['punteggioOb7_3'] = [
                'textOb7_3' => $anno . ": Raggiungimento dell'obiettivo massimo con punteggio",
                'punteggioOb7_3' => 2,
                'percentualeOb7_3' => $percentualeOb7_3,
                'classOb7_3' => 'text-success'
            ];
        } else {
            $dataView['punteggioOb7_3'] = [
                'textOb7_3' => $anno . ": Obiettivo non raggiunto",
                'punteggioOb7_3' => 0,
                'percentualeOb7_3' => $percentualeOb7_3,
                'classOb7_3' => 'text-warning'
            ];


            if ($percentualeOb7_4 == $targetArray[2]) {
                $dataView['punteggioOb7_4'] = [
                    'textOb7_4' => $anno . ": Raggiungimento dell'obiettivo massimo con punteggio",
                    'punteggioOb7_4' => 2,
                    'percentualeOb7_4' => $percentualeOb7_4,
                    'classOb7_4' => 'text-success'
                ];
            } else {
                $dataView['punteggioOb7_4'] = [
                    'textOb7_4' => $anno . ": Obiettivo non raggiunto",
                    'punteggioOb7_4' => 0,
                    'percentualeOb7_4' => $percentualeOb7_4,
                    'classOb7_4' => 'text-warning'
                ];
            }
        }

        return $dataView;
    }


    protected function calcoloPunteggioOb3Ob8($obiettivo)
    {
        $dataView = $this->initView($obiettivo);

        $dataView['filesCaricati'] = $this->fileCaricati($obiettivo, $dataView['strutture']);
    
        $dataView['categorie'] = DB::table("target_categories")
            ->where("target_number", $obiettivo)
            ->orderBy("order")
            ->get();
    
           


            $fileIds = $dataView['filesCaricati']->pluck('id')->toArray();
        
            $dataView['target3_data'] = DB::table("target3_data")
            ->select("numerator", "denominator", "uploated_file_id")
            ->join("uploated_files", "target3_data.uploated_file_id", "=", "uploated_files.id")
            ->whereIn("uploated_files.id", $fileIds)
            ->get();
        
       
        
                
            
        $dataView['userStructures'] = LocationsUsers::where("user_id", Auth::user()->id)
            ->leftJoin("structures", "structures.id", "=", "users_structures.structure_id")
            ->leftJoin("structure_type", "structure_type.code", "=", "structures.type")
            ->orderBy("structures.id")->get();
    

 
        $dataView['percentuali'] = [];
    
        switch ($obiettivo) {
            case 3:
                // Variabile per verificare se il pre-requisito è caricato e approvato
                $annullaTuttiPunteggi = false;
            
                // Verifica se il pre-requisito è caricato e approvato
                foreach ($dataView['filesCaricati'] as $file) {
                    if ($file->category == 'Pre-requisito per il calcolo dell indicatore' && 
                        ($file->approved === null || $file->approved == 0)) {
                        // Se il pre-requisito non è caricato o approvato, azzera i punteggi
                        $annullaTuttiPunteggi = true;
                        break; 
                    }
                }
            
                // Se il pre-requisito non è approvato o caricato, azzera i punteggi per tutti
                if ($annullaTuttiPunteggi) {
                    foreach ($dataView['filesCaricati'] as $file) {
                        foreach ($dataView['userStructures'] as $struttura) {
                            $dataView['punteggioOb8'][] = 0;  // Azzera i punteggi
                        }
                    }
                } else {
                    // Altrimenti, calcola i punteggi come previsto
                    foreach ($dataView['target3_data'] as $target3) {
                        $numeratore = $target3->numerator;
                        $denominatore = $target3->denominator;
            
                        if ($denominatore > 0) { 
                            $percentuale = ($numeratore / $denominatore) * 100;
                        } else {
                            $percentuale = 0; 
                        }
            
                        $dataView['percentuali'][$target3->uploated_file_id] = $percentuale;  
                    }
            
                    // Calcola i punteggi se il pre-requisito è approvato
                    foreach ($dataView['filesCaricati'] as $file) {
                        foreach ($dataView['userStructures'] as $struttura) {
                            $punteggio = 0;
            
                            if ($file->approved !== null && $file->approved != 0) { 
                                switch ($struttura->column_points) {
                                    case 'ao':
                                        if ($percentuale == 100) {
                                            $punteggio = 8;  // Livello I
                                        } elseif ($percentuale >= 90) {
                                            $punteggio = 7.2;  // Livello II
                                        } elseif ($percentuale >= 75) {
                                            $punteggio = 6;  // Livello III
                                        } else {
                                            $punteggio = 0;  // Obiettivo non raggiunto
                                        }
                                        break;
                                    case 'asp':
                                        if ($percentuale == 100) {
                                            $punteggio = 5;  // Livello I
                                        } elseif ($percentuale >= 90) {
                                            $punteggio = 4.5;  // Livello II
                                        } elseif ($percentuale >= 75) {
                                            $punteggio = 3.75;  // Livello III
                                        } else {
                                            $punteggio = 0;  // Obiettivo non raggiunto
                                        }
                                        break;
                                }
                            }
            
                            // Aggiungi il punteggio calcolato all'array
                            $dataView['punteggioOb8'][] = $punteggio;
                        }
                    }
                }
                break;
            
    
            case 8:
                foreach ($dataView['filesCaricati'] as $file) {
                    if ($file->approved === null || $file->approved == 0) {
                        $dataView['punteggioOb8'][] = 0;
                    } else {
                        $dataView['punteggioOb8'][] = 8;
                    }
                }
                break;
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

    protected function calcoloPunteggioOb5_1($percentualeAderenti)
    {
        $dataView = [];

        if ($percentualeAderenti > 60) {
            $dataView['messaggioOb5_1'] = [
                'text' => "Raggiungimento dell'obiettivo con punteggio: 2",
                'class' => 'text-success',
                'punteggio' => 1
            ];
        } elseif ($percentualeAderenti >= 20) {
            $dataView['messaggioOb5_1'] = [
                'text' => "Raggiungimento dell'obiettivo parziale con punteggio: 1",
                'class' => 'text-warning',
                'punteggio' => 1
            ];
        } else {
            $dataView['messaggioOb5_1'] = [
                'text' => "Obiettivo non raggiunto con punteggio: 0",
                'class' => 'text-danger',
                'punteggio' => 0
            ];
        }

        return [
            'messaggioOb5_1' => $dataView['messaggioOb5_1'],
            //'percentualeAderenti' => $percentualeAderenti,
        ];
    }

    protected function calcoloPunteggioOb5_2($percentualeSub2)
    {
        $dataView = [];

        if ($percentualeSub2 >= 0 && $percentualeSub2 <= 10) {
            $dataView['messaggioTmpCodiciDD'] = [
                'textCodiciDD' => "Pieno raggiungimento dell'obiettivo con punteggio: 1",
                'classCodiciDD' => 'text-success',
                'punteggio' => 1
            ];
        } elseif ($percentualeSub2 > 10) {
            $dataView['messaggioTmpCodiciDD'] = [
                'textCodiciDD' => "Obiettivo non raggiunto con punteggio: 0",
                'classCodiciDD' => 'text-danger',
                'punteggio' => 0
            ];
        }

        // Restituzione dei dati
        return [
            'messaggioTmpCodiciDD' => $dataView['messaggioTmpCodiciDD'],
            'percentualeSub2' => $percentualeSub2,
        ];
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
            ->where("target_number", $obiettivo)
            ->orderBy('order')
            ->get();

        return $dataView;
    }

    protected function fileCaricati($obiettivo, $strutture)
    {
        return DB::table(table: 'uploated_files as uf')
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
            ->orderBy("tc.category")
            ->orderBy('uf.created_at', 'desc')
            ->orderBy('uf.updated_at', 'desc')
            ->get();
    }


    protected function mmgData($strutture)
    {
        return DB::table(table: 'insert_mmg as mmg')
            ->join('structures as s', 'mmg.structure_id', '=', 's.id')
            ->whereIn("mmg.structure_id", $strutture->pluck("id")->toArray())
            ->select('mmg.mmg_totale', 'mmg.mmg_coinvolti', 'mmg.year', 'mmg.structure_id', 's.name as nome_struttura')
            ->get();
    }


    protected function screeningCommon()
    {
        $dataView = $this->initView(obiettivo: 5);
        $dataView['tableData'] = $this->mmgData(Auth::user()->structures());

        return $dataView;
    }

    protected function donazioniCommon()
    {
        $dataView = $this->initView(6);

        /*
        $dataView['file'] = DB::table('uploated_files as up')
        ->join('target_categories as tc', 'up.target_category_id', '=', 'tc.id')
        ->where('up.user_id', Auth::user()->id)
        ->where('up.target_number', 6)
        ->select('up.target_number', 'up.target_category_id', 'tc.category', 'up.validator_user_id', 'up.approved', 'up.created_at')
        ->get();
   */
        $dataView['file'] = $this->fileCaricati(6, $dataView['strutture']);

        // Dati per la tabella nella view 
        $dataView['tableData'] = DB::table('target6_data')
            ->select('totale_accertamenti', 'numero_opposti', 'totale_cornee', 'anno', 'structure_id', 's.name')
            ->join('structures as s', 'target6_data.structure_id', '=', 's.id')
            ->get();

        return $dataView;
    }

    protected function chartFSE($name, $labels, $data)
    {
        return $this->showChart(
            "doughnut",
            $name,
            $labels,
            [
                [
                    "label" => "",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",

                    ],
                    "data" => $data

                ]
            ],
            [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => ''
                    ]
                ]
            ]
        );
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

            //         dd($dataView['userStructures']);
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
                    ->join("target3_data", "target3_data.uploated_file_id", "=", "uploated_files.id")
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

                $calcoloPunteggioOb3 = $this->calcoloPunteggioOb3Ob8($request->obiettivo);
                $dataView = array_merge($calcoloPunteggioOb3, $dataView);
               
                /*
                                foreach ($dataView['filesCaricati'] as $file) {
                                    if ($file->approved === null || $file->approved == 0) {

                                        $dataView['punteggioOb8'][] = 0;
                                     }else{
                                        $dataView['punteggioOb8'][] = 8;
                                     }
                 
                                }
                                */
                break;

            case 8:
                $dataView['filesCaricati'] = $this->fileCaricati(8, $dataView['strutture']);
                $calcoloPunteggioOb8 = $this->calcoloPunteggioOb3Ob8($request->obiettivo);


                $dataView = array_merge($calcoloPunteggioOb8, $dataView);
                /*
                foreach ($dataView['filesCaricati'] as $file) {
                    if ($file->approved === null || $file->approved == 0) {

                        $dataView['punteggioOb8'][] = 0;
                     }else{
                        $dataView['punteggioOb8'][] = 8;
                     }
 
                }

             */
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

        $dataView['flowEmur'] = DB::table('flows_emur as fe')
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

        $dataView['calcoloPunteggioOb4_1'] = $this->calcoloPunteggioOb4_1($overallAverageTmp, $overallAverageBoarding);
        $dataView['calcoloPunteggioOb4_2'] = $this->calcoloPunteggioOb4_2($overallAverageBoarding, $complementaryValueBoarding);

        /*
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

                */

        return view("prontoSoccorso")->with("dataView", $dataView);
    }

    public function donazioni(Request $request)
    {
        $dataView = $this->initView(6);
        $dataView['file'] = $this->fileCaricati(6, $dataView['strutture']);

        // Numeratori sub.2
        $dataView['target6_data'] = DB::table('target6_data')
            ->select('target6_data.totale_accertamenti', 'target6_data.anno', 'target6_data.numero_opposti', 'target6_data.totale_cornee', 'target6_data.structure_id', 'target6_data.created_at')
            ->join('structures as s', 'target6_data.structure_id', '=', 's.id')
            ->where('target6_data.structure_id', '=', Auth::user()->firstStructureId()->id)
            ->whereIn('target6_data.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('target6_data')
                    ->where('structure_id', '=', Auth::user()->firstStructureId()->id)
                    ->groupBy('anno')
                    ->orderBy('target6_data.created_at', 'desc');
            })
            ->orderBy('target6_data.anno')
            ->get();


        //Denominatore preso dal flusso
        /*
        $denominatore = DB::table('flows_sdo')
            ->where('structure_id', Auth::user()->firstStructureId()->id)
            ->select(
                DB::raw('MAX(ob6) as ob6'),
                DB::raw('MAX(id) as id'),
                'year'
            )
            ->groupBy('year')
            ->orderByDesc('year')
            ->get();
*/

        $labelsTmp = [];
        $dataView['result'] = [];

        // Calcolo la percentuale per ogni anno
        foreach ($dataView['target6_data'] as $target) {
            //    $denominatoreTmp = $denominatore->firstWhere('year',  $target->anno);
            if ($target->anno == 2023) {
                $accertamenti2023 = $target->totale_accertamenti;
                $cornee2023 = $target->totale_cornee;
            }

            /*
                    $percentualeAccertamenti = ($denominatoreTmp && $denominatoreTmp->ob6 != 0) 
                        ? round(($target->totale_accertamenti / $denominatoreTmp->ob6) * 100, 2) 
                        : 0;

                    $percentualeCornee = ($denominatoreTmp && $denominatoreTmp->ob6 != 0) 
                        ? round(($target->totale_cornee / $denominatoreTmp->ob6) * 100, 2) 
                        : 0;
    */
            $dataView['result'][] = [
                'anno' => $target->anno,
                //'percentualeAccertamenti' => $percentualeAccertamenti,
                //  'percentualeCornee' => $percentualeCornee,
                'totale_accertamenti' => $target->totale_accertamenti,
                'numero_opposti' => $target->numero_opposti,
                'totale_cornee' => $target->totale_cornee,
                'incrementoAccertamenti' => ($target->anno > 2023 && $accertamenti2023 != 0) ? round((($target->totale_accertamenti - $accertamenti2023) / $accertamenti2023) * 100, 2) : 0,
                'incrementoCornee' => ($target->anno > 2023 && $cornee2023 != 0) ? round((($target->totale_cornee - $cornee2023) / $cornee2023) * 100, 2) : 0,
            ];
            $labelsTmp[] = $target->anno;
        }



        $incrementoSub2AnnoCorrente = 0;
        $incrementoSub4AnnoCorrente = 0;
        foreach ($dataView['result'] as $row) {
            if ($row['anno'] == date('Y')) {
                $incrementoSub2AnnoCorrente = $row['incrementoAccertamenti'];
                $incrementoSub4AnnoCorrente = $row['incrementoCornee'];
            }
        }



        $punteggioTotale = $this->calcoloPunteggioOb6_2($incrementoSub2AnnoCorrente);


        $dataView = array_merge($punteggioTotale, $dataView);


        //grafico Sub.2
        $dataView['chartDonazioni'] = $this->showChart(
            "bar",
            "OverallAvgTmpComplementaryBarChart",
            $labelsTmp,
            [
                [
                    "label" => "Percentuale TMP per Anno",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "data" => array_column($dataView['result'], 'totale_accertamenti') //percentuali per ogni anno
                ]
            ],
            [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Totale accertamenti per anno'
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
            ]
        );

        /***************************Chart sub. 3************************************ */

        $dataSelezionata = $request->annoSelezionato ?? date('Y');

        $dataView['numeratoreSecondo'] = 0;
        $dataView['denominatoreSecondo'] = 0;

        foreach ($dataView['result'] as $risultato) {
            if ($risultato['anno'] == $dataSelezionata) {
                $dataView['denominatoreSecondo'] = $risultato['totale_accertamenti'];
                //$dataView['denominatoreSecondo'] = (float) str_replace('%', '', $dataView['denominatoreSecondo']); // Conversione in float
                $dataView['numeratoreSecondo'] = $risultato['numero_opposti'];
            }
        }

        //calcoloPercentualeOpposizione
        if ($dataView['denominatoreSecondo'] > 0) {
            $dataView['percentualeOpposizione'] = round(($dataView['numeratoreSecondo'] / $dataView['denominatoreSecondo']) * 100, 2);
        } else {
            $dataView['percentualeOpposizione'] = 0;
        }
        $percOpposizioneComplementare = 100 - $dataView['percentualeOpposizione'];

        //grafico sub.3
        $dataView['chartSubObiettivo3'] = $this->showChart(
            "doughnut",
            "chartSubObiettivo3",
            $labelsTmp,
            [
                [
                    "label" => "Percentuale TMP",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(217, 83, 79, 0.7)",
                    ],
                    "data" => [$dataView['percentualeOpposizione'], $percOpposizioneComplementare]
                ]
            ],
            [
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
            ]
        );


        $punteggioOb6_3 = $this->calcoloPunteggioOb6_3($dataView['percentualeOpposizione']);
        $dataView = array_merge($punteggioOb6_3, $dataView);

        //calcolo punteggio 3
        /*
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
*/
        /******************************Sub.ob 4************************************ */

        //grafico sub.4
        $dataView['chartSubObiettivo4'] = $this->showChart(
            "bar",
            "chartSubObiettivo4",
            $labelsTmp,
            [
                [
                    "label" => "Percentuale TMP",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "data" => array_column($dataView['result'], 'totale_cornee'), //[$totaleCornee2023, $totaleCornee2024]
                ]
            ],
            [
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
            ]
        );


        $punteggioSub4 = $this->calcoloPunteggioSub4($incrementoSub4AnnoCorrente);
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

        $dataView['tempiListeAttesa'] = $this->showChart(
            "doughnut",
            "tempiListeAttesa",
            ['Num. prest. amb. I accesso pubblico o privato accreditate / Num. prest. amb. erogate'],
            [
                [
                    "label" => "Percentuale TMP",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "data" => [$dataView['percentuale']]
                ]
            ],
            [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Distribuzione Percentuale: TMP e Differenza Media Totale'
                    ]
                ]
            ]
        );

        $dataView['filesCaricati'] = $this->fileCaricati(1, $dataView['strutture']);
        return view("tempiListeAttesa")->with("dataView", $dataView);
    }

    public function saveTempiListeAttesa(Request $request)
    {

        $year = $request->year;
        $structure_id = $request->structure_id;
        $numero_agende = $request->numeroAgende;
        $prestazioni_specialista_riferimento = $request->prestazioniSpecialistaRiferimento;
        $prestazioni_specialista_precedente = $request->prestazioniSpecialistaPrecedente;
        $prestazioni_MMG_riferimento = $request->prestazioniMMGRiferimento;
        $prestazioni_MMG_precedente = $request->prestazioniMMGPrecedente;
        //dd($request);
        $request->validate([
            'year' => 'required|integer',
            'structure_id' => 'required|numeric',
            'numeroAgende' => 'required|numeric|gte:0', // numeroAgende >= 0
            'prestazioniSpecialistaRiferimento' => 'required|numeric',
            'prestazioniSpecialistaPrecedente' => 'required|numeric',
            'prestazioniMMGRiferimento' => 'required|numeric',
            'prestazioniMMGPrecedente' => 'required|numeric',
        ]);


        $target = Target1::where("year", $year)
            ->where("structure_id", $structure_id)
            ->exists();
        if ($target) {
            Target1::where("year", $year)
                ->where("structure_id", $structure_id)
                ->update([
                    'numero_agende' => $numero_agende,
                    'prestazioni_specialista_riferimento' => $prestazioni_specialista_riferimento,
                    'prestazioni_specialista_precedente' => $prestazioni_specialista_precedente,
                    'prestazioni_MMG_riferimento' => $prestazioni_MMG_riferimento,
                    'prestazioni_MMG_precedente' => $prestazioni_MMG_precedente,
                ]);
        } else {
            Target1::create([
                'year' => $year,
                'structure_id' => $structure_id,
                'numero_agende' => $numero_agende,
                'prestazioni_specialista_riferimento' => $prestazioni_specialista_riferimento,
                'prestazioni_specialista_precedente' => $prestazioni_specialista_precedente,
                'prestazioni_MMG_riferimento' => $prestazioni_MMG_riferimento,
                'prestazioni_MMG_precedente' => $prestazioni_MMG_precedente,
            ]);
        }
        $data = [
            'anno' => $year,
            'struttura' => Structure::where("id", $structure_id)->first(),
            'numero_agende' => $numero_agende,
            'prestazioni_specialista_riferimento' => $prestazioni_specialista_riferimento,
            'prestazioni_specialista_precedente' => $prestazioni_specialista_precedente,
            'prestazioni_MMG_riferimento' => $prestazioni_MMG_riferimento,
            'prestazioni_MMG_precedente' => $prestazioni_MMG_precedente,
            'data' => date('d/m/Y'),
        ];

        $pdf = new PdfController();
        return $pdf->tempiListeAttesaAutodichiarazionePdf($data);
    }

    public function screening(Request $request)
    {
        $dataView = $this->screeningCommon();
        $dataView['file'] = $this->fileCaricati(5, $dataView['strutture']);

        //avrò solo una riga nel db (per ora)
        $record = $dataView['tableData']->first();

        $noData = false;
        if ($record && $record->mmg_totale != 0) {
            $dataView['percentualeAderenti'] = round(($record->mmg_coinvolti / $record->mmg_totale) * 100, 2);
        } else {
            $dataView['percentualeAderenti'] = 0;
            $noData = true;
        }
        $percentualeNonAderenti = round(100 - $dataView['percentualeAderenti'], 2);

        $idR = DB::table("target5_data")
            ->select(DB::raw("concat(month, '/', year) as mese"), "mammografico", "cercocarcinoma", "colonretto")
            ->where("year", date('Y'))
            ->where('structure_id', Auth::user()->firstStructureId()->id)
            ->orderBy("year", "desc")
            ->orderby("month");

        $dataView['lineChart'] = $this->showChart(
            "line",
            "IndicatoriDiRisultatoTarget5"
            ,
            $idR->pluck("mese")->toArray() // labels
            ,
            [
                [
                    "label" => "Memmografico",
                    "data" => $idR->pluck("mammografico")->toArray(),
                ],
                [
                    "label" => "Cervicocarcinoma",
                    "data" => $idR->pluck("cercocarcinoma")->toArray(),
                ],
                [
                    "label" => "test",
                    "data" => $idR->pluck("colonretto")->toArray(),
                ]
            ] //datasets
            ,
            [
                'responsive' => true,
                'plugins' => [

                    'scales' => [
                        "x" => [
                            "title" => [
                                'display' => true,
                                'text' => 'Mese'
                            ]
                        ],
                        "y" => [
                            "title" => [
                                'display' => true,
                                'text' => 'Indicatore LEA %'
                            ]
                        ],
                    ],

                ]
            ] // options
        );

        $dataView['mmgChart'] = $this->showChart(
            "doughnut",
            "OverallAvgTmpComplementaryBarChart"
            ,
            ['MMG Aderenti', 'MMG non aderenti'] // labels
            ,
            [
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgba(38, 185, 154, 0.7)",
                        "rgba(255, 99, 132, 0.7)"
                    ],
                    "data" => [$dataView['percentualeAderenti'], $percentualeNonAderenti]
                ]
            ] //datasets
            ,
            [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => $noData
                            ? 'Non ci sono dati disponibili'
                            : 'Distribuzione Percentuale: MMG aderenti e Non aderenti'
                    ]
                ]
            ] // options
        );

        //*************Secondo grafico **********//

        $datiFlussoM = DB::table('flows_m')
            ->select('ob5_num as numeratore_m', 'ob5_den as denominatore_m')
            ->where('structure_id', Auth::user()->firstStructureId()->id)
            ->get();


        $datiFlussoC = DB::table('flows_c')
            ->select('ob5_num as numeratore_c', 'ob5_den as denominatore_c')
            ->where('structure_id', Auth::user()->firstStructureId()->id)
            ->get();

        $dataView['numeratoreTotale'] = $datiFlussoM->sum('numeratore_m') + $datiFlussoC->sum('numeratore_c');
        $dataView['denominatoreTotale'] = $datiFlussoM->sum('denominatore_m') + $datiFlussoC->sum('denominatore_c');

        if ($dataView['denominatoreTotale'] > 0) {
            $dataView['percentuale'] = round($dataView['numeratoreTotale'] / $dataView['denominatoreTotale'] * 100, 2);
        } else {
            $dataView['percentuale'] = 0;
        }
        $dataView['percentualeComplementare'] = 100 - $dataView['percentuale'];

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
        /*
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
*/
        $dataView['calcoloPunteggioOb5_2'] = $this->calcoloPunteggioOb5_2($dataView['percentualeAderenti']);

        $dataView['calcoloPunteggioOb5_1'] = $this->calcoloPunteggioOb5_1($dataView['percentuale']);

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
        $dataView = $this->screeningCommon();

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

        $validator = Validator::make($request->all(), [
            'tot_mmg' => 'required|numeric',
            'mmg_coinvolti' => 'required|numeric|lte:tot_mmg', // mmg_coinvolti <= tot_mmg
            'year' => 'required|integer',
        ], $messages);

        if ($validator->fails()) {
            $dataView['errorsMMG'] = $validator->errors()->getMessages();
        } else {

            InsertMmg::create([
                'mmg_totale' => $tot_mmg,
                'mmg_coinvolti' => $mmg_coinvolti,
                'year' => $anno,
                'structure_id' => $structure_id,

            ]);
        }

        return redirect()->route("caricamentoScreening");
        //return view("caricamentoScreening")->with("dataView", $dataView);
    }


    public function caricamentoDonazioni(Request $request)
    {

        $dataView = $this->donazioniCommon();
        return view("caricamentoDonazioni")->with('dataView', $dataView);
    }



    public function uploadDatiDonazione(Request $request)
    {
        $anno = $request->anno;
        $structure_id = $request->structure_id;
        $totale_accertamenti = $request->totale_accertamenti;
        $numero_opposti = $request->numero_opposti;
        $totale_cornee = $request->totale_cornee;

        $messages = [
            'totale_accertamenti.required' => 'Il totale accertamenti è obbligatorio.',
            'totale_accertamenti.numeric' => 'Il totale accertamenti deve essere un numero.',
            'totale_accertamenti.gte' => 'Il totale accertamenti deve essere positivo.',
            'numero_opposti.required' => 'Il numero opposti è obbligatorio.',
            'numero_opposti.numeric' => 'Il numero opposti deve essere un numero.',
            'numero_opposti.gte' => 'Il totale opposti deve essere positivo.',
            'numero_opposti.lte' => 'Il numero opposti deve essere minore o uguale al totale accertamenti.',
            'totale_cornee.required' => 'Il numero cornee è obbligatorio.',
            'totale_cornee.numeric' => 'Il numero cornee deve essere un numero intero.',
            'totale_cornee.gte' => 'Il totale cornee deve essere positivo.',
            'totale_cornee.lte' => 'Il totale cornee deve essere minore o uguale al totale accertamenti.',
        ];


        $validator = Validator::make($request->all(), [
            'totale_accertamenti' => 'required|numeric|gte:0',
            'numero_opposti' => 'required|numeric|gte:0|lte:totale_accertamenti', // numero_opposti <= totale_accertamenti
            'totale_cornee' => 'required|numeric|gte:0|lte:totale_accertamenti', // totale_cornee <= totale_accertamenti
        ], $messages);

        $dataView = $this->donazioniCommon();

        if ($validator->fails()) {
            $dataView['errors'] = $validator->errors()->getMessages();
        } else {

            Target6_data::updateOrInsert(
                [
                    'structure_id' => $structure_id,
                    'anno' => $anno,
                ],
                [
                    'structure_id' => $structure_id,
                    'anno' => $anno,
                    'totale_accertamenti' => $totale_accertamenti,
                    'numero_opposti' => $numero_opposti,
                    'totale_cornee' => $totale_cornee,
                ]
            );
        }

        return view("caricamentoDonazioni")->with('dataView', $dataView);

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

        if ($documentiIndicizzatiCda2 && $documentiCda2 != null) {
            $fieldsToUpdate = [
                'documenti_indicizzati_cda2' => $documentiIndicizzatiCda2,
                'documenti_cda2' => $documentiCda2,
            ];
        }

        if ($documentiPades && $documentiIndicizzatiPades != null) {
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
            $existingRecord->update(array_merge($fieldsToUpdate, ['updated_at' => now()]));
        } else {
            Target7_data::create(array_merge($fieldsToUpdate, [
                'anno' => $anno,
                'structure_id' => $structureId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

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

    public function importTarget5LEA(Request $request)
    {
        $dataView = $this->screeningCommon();

        $file = $request->file('fileCSV');
        $fileContents = file($file->getPathname());
        $colonneAttese = 5;
        $row = 0;
        $errori = [];
        foreach ($fileContents as $line) {
            $row += 1;
            $data = str_getcsv($line);

            if (count($data) !== $colonneAttese) {
                $errori[$row][] = "Il numero di colonne deve essere " . $colonneAttese . "; letto: " . count($data);
            }
            if (!is_numeric($data[0]) || $data[0] <= 2022 || $data[0] > date('Y')) {
                $errori[$row][] = "Anno errato: " . $data[0];
            }
            if (!is_numeric($data[1]) || $data[1] <= 0 || $data[1] > 12) {
                $errori[$row][] = "Mese errato: " . $data[1];
            }
            if (!is_numeric($data[2]) || $data[2] < 0) {
                $errori[$row][] = "Numero popolazione bersaglio mammografico errato: " . $data[2];
            }
            if (!is_numeric($data[3]) || $data[3] < 0) {
                $errori[$row][] = "Numero popolazione bersaglio cervicocarcinoma errato: " . $data[3];
            }
            if (!is_numeric($data[4]) || $data[4] < 0) {
                $errori[$row][] = "Numero popolazione bersaglio colon-retto errato: " . $data[4];
            }
        }
        if (count($errori) == 0) {
            foreach ($fileContents as $line) {
                $data = str_getcsv($line);

                Target5::updateOrInsert(
                    [
                        'structure_id' => Auth::user()->firstStructureId()->id,
                        'year' => $data[0],
                        'month' => $data[1],
                    ],
                    [
                        'structure_id' => Auth::user()->firstStructureId()->id,
                        'year' => $data[0],
                        'month' => $data[1],
                        'mammografico' => $data[2],
                        'cercocarcinoma' => $data[3],
                        'colonretto' => $data[4],
                    ]
                );
            }
            $dataView['successCSV'] = "CSV importato correttamente";
        } else
            $dataView['errorsCSV'] = $errori;


        return view("caricamentoScreening")->with("dataView", $dataView);
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
        $dataView = $this->screeningCommon();
        return view('caricamentoScreening')->with("dataView", $dataView);
    }



    public function downloadPdf($obiettivo, Request $request)
    {
        switch ($obiettivo) {
            case 5:
                $dataView['tableData'] = $this->mmgData(Auth::user()->structures());
                $pdf = PDF::loadView('pdfs.screeningPdf', $dataView);

                return $pdf->download('certificazione_completa.pdf');

            case 6:

                $dataView['target6_data'] = DB::table('target6_data')
                    ->select('totale_accertamenti', 'anno', 'numero_opposti', 'totale_cornee', 'structure_id')
                    ->join('structures as s', 'target6_data.structure_id', '=', 's.id')
                    ->where('structure_id', '=', Auth::user()->firstStructureId()->id)
                    ->orderBy('anno')
                    ->get();

                $pdf = PDF::loadView('pdfs.donazioniPdf', $dataView);

                return $pdf->download('certificazione_completa.pdf');

            case 7:

        }
    }

    public function uploadDatiLea(Request $request)
    {
        $anno = $request->anno;
        $structure_id = $request->structure_id;

        Target10_data::updateOrCreate([
            'anno' => $anno,
            'structure_id' => $structure_id
        ], [
            'ob10_1_numeratore' => $request->ob10_1_numeratore,
            'ob10_1_denominatore' => $request->ob10_1_denominatore,
            'ob10_2_numeratore' => $request->ob10_2_numeratore,
            'ob10_2_denominatore' => $request->ob10_2_denominatore,
            'anno' => $anno,
            'structure_id' => $structure_id,
        ]);

        return redirect()->route('caricamentoGaranziaLea', ['obiettivo' => $request->obiettivo]);
    }


    public function garanziaLea(Request $request)
    {

        $dataView['dataSelezionata'] = $request->annoSelezionato ?? date('Y');



        $dataView['dataSDO'] = DB::table('flows_sdo')
            ->select('*')
            ->where('year', "=", $dataView['dataSelezionata'])
            ->join('structures as s', 'flows_sdo.structure_id', '=', 's.id')
            ->where('structure_id', '=', Auth::user()->firstStructureId()->id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();




        $dataView['dataLea'] = DB::table('target10_data')
            ->select('*')
            ->where('anno', "=", $dataView['dataSelezionata'])
            ->join('structures as s', 'target10_data.structure_id', '=', 's.id')
            ->where('structure_id', '=', Auth::user()->firstStructureId()->id)
            ->orderByDesc('anno')
            ->orderByDesc('mese')
            ->get();


        $dataView['ultimoData'] = $dataView['dataLea']->first();


        if ($dataView['ultimoData'] !== null) {
            $nome = $dataView['ultimoData']->name;
        } else {

            $nome = 'Nessun dato disponibile';
        }


        $dataView['nome'] = $nome;


        $dataView['ultimoDataSDO'] = $dataView['dataSDO']->first();


        if (!empty($dataView['ultimoData']) && isset($dataView['ultimoData']->ob10_1_numeratore, $dataView['ultimoData']->ob10_1_denominatore) && $dataView['ultimoData']->ob10_1_denominatore != 0) {

            $dataView['percentualeCicloBase'] = round(
                ($dataView['ultimoData']->ob10_1_numeratore / $dataView['ultimoData']->ob10_1_denominatore) * 100,
                2
            );
            $dataView['percentualeCicloBaseCompl'] = 100 - $dataView['percentualeCicloBase'];
        } else {

            $dataView['percentualeCicloBase'] = 0;
            $dataView['percentualeCicloBaseCompl'] = 100;
        }



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
                    "data" => [$dataView['percentualeCicloBase'], $dataView['percentualeCicloBaseCompl']]

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

        /*********************************************************************************/

        /*
                $dataView['percentualePrimaDose'] = round(($dataView['ultimoData']->ob10_2_numeratore / $dataView['ultimoData']->ob10_2_denominatore) * 100, 2);
                $dataView['percentualePrimaDoseCompl'] = 100 - $dataView['percentualePrimaDose'];
        */

        if (!empty($dataView['ultimoData']?->ob10_2_numeratore) && !empty($dataView['ultimoData']?->ob10_2_denominatore)) {
            $dataView['percentualePrimaDose'] = round(
                ($dataView['ultimoData']->ob10_2_numeratore / $dataView['ultimoData']->ob10_2_denominatore) * 100,
                2
            );
            $dataView['percentualePrimaDoseCompl'] = 100 - $dataView['percentualePrimaDose'];
        } else {
            $dataView['percentualePrimaDose'] = 0;
            $dataView['percentualePrimaDoseCompl'] = 100;
        }



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
                    "data" => [$dataView['percentualePrimaDose'], $dataView['percentualePrimaDoseCompl']]

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

        /*********************************************************VETERINARIA********************************************************/

        /*

        $dataView['percentualeAziendeOvicaprine'] = round(($dataView['ultimoData']->num_aziende_ovicaprine_controllate / $dataView['ultimoData']->num_aziende_ovicaprine_totali) * 100, 2) * 0.05;

        $dataView['percentualeCapiOvicaprini'] = round(($dataView['ultimoData']->num_capi_ovicaprini_controllati / $dataView['ultimoData']->num_capi_ovicaprini_totali) * 100, 2) * 0.05;

        $dataView['percentualeAziendeSuineControllate'] = round(($dataView['ultimoData']->num_aziende_suine_controllate / $dataView['ultimoData']->num_aziende_suine_totali) * 100, 2) * 0.1;

        $dataView['percentualeAziendeEquine'] = round(($dataView['ultimoData']->num_aziende_equine_controllate / $dataView['ultimoData']->num_aziende_equine_totali) * 100, 2) * 0.1;

        $dataView['percentualeAllevamentiApistici'] = round(($dataView['ultimoData']->num_allevamenti_apistici_controllati / $dataView['ultimoData']->num_allevamenti_apistici_totali) * 100, 2) * 0.1;

        $dataView['percentualePNAA7'] = round(($dataView['ultimoData']->pnaa7_esecuzione / $dataView['ultimoData']->pnaa7_esecuzione_totali) * 100, 2) * 0.3;

        $dataView['percentualeFarmacoSorveglianza'] = round(($dataView['ultimoData']->controlli_farmacosorveglianza_veterinaria / $dataView['ultimoData']->controlli_farmacosorveglianza_veterinaria_totali) * 100, 2) * 0.2;
        */
        //aziende bovine
        if (!empty($dataView['ultimoData']?->num_aziende_bovine_controllate) && !empty($dataView['ultimoData']?->num_aziende_bovine_totali)) {
            $dataView['percentualeAziendeBovine'] = round(
                ($dataView['ultimoData']->num_aziende_bovine_controllate / $dataView['ultimoData']->num_aziende_bovine_totali) * 100,
                2
            ) * 0.1;
        } else {
            $dataView['percentualeAziendeBovine'] = 0;
        }

        $dataView['percentualeAziendeOvicaprine'] =
            !empty($dataView['ultimoData']?->num_aziende_ovicaprine_controllate) && !empty($dataView['ultimoData']?->num_aziende_ovicaprine_totali)
            ? round(($dataView['ultimoData']->num_aziende_ovicaprine_controllate / $dataView['ultimoData']->num_aziende_ovicaprine_totali) * 100, 2) * 0.05
            : 0;

        $dataView['percentualeCapiOvicaprini'] =
            !empty($dataView['ultimoData']?->num_capi_ovicaprini_controllati) && !empty($dataView['ultimoData']?->num_capi_ovicaprini_totali)
            ? round(($dataView['ultimoData']->num_capi_ovicaprini_controllati / $dataView['ultimoData']->num_capi_ovicaprini_totali) * 100, 2) * 0.05
            : 0;

        $dataView['percentualeAziendeSuineControllate'] =
            !empty($dataView['ultimoData']?->num_aziende_suine_controllate) && !empty($dataView['ultimoData']?->num_aziende_suine_totali)
            ? round(($dataView['ultimoData']->num_aziende_suine_controllate / $dataView['ultimoData']->num_aziende_suine_totali) * 100, 2) * 0.1
            : 0;

        $dataView['percentualeAziendeEquine'] =
            !empty($dataView['ultimoData']?->num_aziende_equine_controllate) && !empty($dataView['ultimoData']?->num_aziende_equine_totali)
            ? round(($dataView['ultimoData']->num_aziende_equine_controllate / $dataView['ultimoData']->num_aziende_equine_totali) * 100, 2) * 0.1
            : 0;

        $dataView['percentualeAllevamentiApistici'] =
            !empty($dataView['ultimoData']?->num_allevamenti_apistici_controllati) && !empty($dataView['ultimoData']?->num_allevamenti_apistici_totali)
            ? round(($dataView['ultimoData']->num_allevamenti_apistici_controllati / $dataView['ultimoData']->num_allevamenti_apistici_totali) * 100, 2) * 0.1
            : 0;

        $dataView['percentualePNAA7'] =
            !empty($dataView['ultimoData']?->pnaa7_esecuzione) && !empty($dataView['ultimoData']?->pnaa7_esecuzione_totali)
            ? round(($dataView['ultimoData']->pnaa7_esecuzione / $dataView['ultimoData']->pnaa7_esecuzione_totali) * 100, 2) * 0.3
            : 0;

        $dataView['percentualeFarmacoSorveglianza'] =
            !empty($dataView['ultimoData']?->controlli_farmacosorveglianza_veterinaria) && !empty($dataView['ultimoData']?->controlli_farmacosorveglianza_veterinaria_totali)
            ? round(($dataView['ultimoData']->controlli_farmacosorveglianza_veterinaria / $dataView['ultimoData']->controlli_farmacosorveglianza_veterinaria_totali) * 100, 2) * 0.2
            : 0;


        $dataView['percentualeTotaleVeterinaria'] = $dataView['percentualeAziendeBovine'] + $dataView['percentualeAziendeOvicaprine'] + $dataView['percentualeCapiOvicaprini'] + $dataView['percentualeAziendeSuineControllate'] + $dataView['percentualeAziendeEquine'] + $dataView['percentualeAllevamentiApistici'] + $dataView['percentualePNAA7'] + $dataView['percentualeFarmacoSorveglianza'];

        $dataView['percentualeTotaleVeterinariaCompl'] = 100 - $dataView['percentualeTotaleVeterinaria'];


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
                    "data" => [$dataView['percentualeTotaleVeterinaria'], $dataView['percentualeTotaleVeterinariaCompl']]

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



        /********************************************************ALIMENTI***************************************************/


        $dataView['percentualePNR'] =
            !empty($dataView['ultimoData']?->copertura_pnr_num) && !empty($dataView['ultimoData']?->copertura_pnr_den)
            ? round(($dataView['ultimoData']->copertura_pnr_num / $dataView['ultimoData']->copertura_pnr_den) * 100, 2) * 0.4
            : 0;

        $dataView['percentualeCoperturaFitofarmaci'] =
            !empty($dataView['ultimoData']?->copertura_fitofarmaci_num) && !empty($dataView['ultimoData']?->copertura_fitofarmaci_den)
            ? round(($dataView['ultimoData']->copertura_fitofarmaci_num / $dataView['ultimoData']->copertura_fitofarmaci_den) * 100, 2) * 0.4
            : 0;

        $dataView['percentualeCoperturaAdditivi'] =
            !empty($dataView['ultimoData']?->copertura_additivi_num) && !empty($dataView['ultimoData']?->copertura_additivi_den)
            ? round(($dataView['ultimoData']->copertura_additivi_num / $dataView['ultimoData']->copertura_additivi_den) * 100, 2) * 0.2
            : 0;


        $dataView['percentualeTotaleAlimenti'] = $dataView['percentualePNR'] + $dataView['percentualeCoperturaFitofarmaci'] + $dataView['percentualeCoperturaAdditivi'];
        $dataView['percentualeTotaleAlimentiCompl'] = 100 - $dataView['percentualeTotaleAlimenti'];



        $dataView['Alimenti'] = Chartjs::build()
            ->name("Alimenti")
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
                    "data" => [$dataView['percentualeTotaleAlimenti'], $dataView['percentualeTotaleAlimentiCompl']]
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


        /*******************************************AT>=18***************************************************************** */

        // Query per ottenere i dati da flows_sdo
        $dataSDO = DB::table('flows_sdo')
            ->select('*')
            ->join('structures as s', 'flows_sdo.structure_id', '=', 's.id')
            ->where('flows_sdo.structure_id', '=', Auth::user()->firstStructureId()->id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        // Query per ottenere i dati da target10_data
        $dataLea = DB::table('target10_data')
            ->select('*')
            ->join('structures as s', 'target10_data.structure_id', '=', 's.id')
            ->where('target10_data.structure_id', '=', Auth::user()->firstStructureId()->id)
            ->orderByDesc('anno')
            ->orderByDesc('mese')
            ->get();


        $mesi = [
            1 => 'Gennaio',
            2 => 'Febbraio',
            3 => 'Marzo',
            4 => 'Aprile',
            5 => 'Maggio',
            6 => 'Giugno',
            7 => 'Luglio',
            8 => 'Agosto',
            9 => 'Settembre',
            10 => 'Ottobre',
            11 => 'Novembre',
            12 => 'Dicembre'
        ];


        $risultati = [];
        $risultatiPediatrico = [];

        // Ciclo per confrontare i dati tra $dataLea e $dataSDO
        foreach ($dataLea as $lea) {
            foreach ($dataSDO as $sdo) {
                if ($lea->mese == $sdo->month && $lea->anno == $sdo->year && $lea->structure_id == $sdo->structure_id) {
                    $numeratoreAdulti = $sdo->ob10_at_1_num;
                    $denominatoreAdulti = $lea->ob10_at_1_den;

                    $numeratorePediatrico = $sdo->ob10_at_2_num;
                    $denominatorePediatrico = $lea->ob10_at_2_den;

                    // Calcolo percentuale adulti se denominatore è valido
                    if ($denominatoreAdulti != 0) {
                        $percentualeAdulti = ($numeratoreAdulti / $denominatoreAdulti) * 100;
                        $risultati[$lea->anno][$lea->mese] = $percentualeAdulti;
                    }

                    // Calcolo percentuale pediatrici se denominatore è valido
                    if ($denominatorePediatrico != 0) {
                        $percentualePediatrico = ($numeratorePediatrico / $denominatorePediatrico) * 100;
                        $risultatiPediatrico[$lea->anno][$lea->mese] = $percentualePediatrico;
                    }
                }
            }
        }

        // Calcolo dei rapporti per mese per adulti
        $rapportiPerMese = [];
        foreach ($mesi as $meseNumero => $meseNome) {
            $percentuale = 0;
            foreach ($risultati as $anno => $mesiData) {
                if (isset($mesiData[$meseNumero])) {
                    $percentuale = $mesiData[$meseNumero];
                    break;
                }
            }
            $rapportiPerMese[] = $percentuale;
        }

        // Calcolo dei rapporti per mese per pediatrici
        $rapportiPerMesePediatrico = [];
        foreach ($mesi as $meseNumero => $meseNome) {
            $percentuale = 0;
            foreach ($risultatiPediatrico as $anno => $mesiDataPediatrico) {
                if (isset($mesiDataPediatrico[$meseNumero])) {
                    $percentuale = $mesiDataPediatrico[$meseNumero];
                    break;
                }
            }
            $rapportiPerMesePediatrico[] = $percentuale;
        }

        // Crea il grafico con i dati
        $dataView['ospedalizzazioneAdulta'] = Chartjs::build()
            ->name("ospedalizzazioneAdulta")
            ->type("line")
            ->size(["width" => 600, "height" => 300])
            ->labels(array_values($mesi))
            ->datasets([
                [
                    "label" => $dataView['nome'],
                    "backgroundColor" => "rgba(60, 179, 113, 0.2)",
                    "borderColor" => "rgb(60, 179, 113)",
                    "data" => $rapportiPerMese,
                    "fill" => false,
                    "lineTension" => 0.1
                ],
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Interventi effettuati entro 0-2 giorni dal ricovero / numero totale di casi di frattura femore su pazienti over 65'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);


        /******************************AT<=18****************************************************/

        // Crea il grafico con i dati
        $dataView['ospedalizzazionePediatrica'] = Chartjs::build()
            ->name("ospedalizzazionePediatrica")
            ->type("line")
            ->size(["width" => 600, "height" => 300])
            ->labels(array_values($mesi))
            ->datasets([
                [
                    "label" => $dataView['nome'],
                    "backgroundColor" => "rgba(60, 179, 113, 0.2)",
                    "borderColor" => "rgb(60, 179, 113)",
                    "data" => $rapportiPerMesePediatrico,
                    "fill" => false,
                    "lineTension" => 0.1
                ],
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Interventi effettuati entro 0-2 giorni dal ricovero / numero totale di casi di frattura femore su pazienti over 65'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);



        /*********************************************CIA1********************************************** */


        $mesi = [
            1 => 'Gennaio',
            2 => 'Febbraio',
            3 => 'Marzo',
            4 => 'Aprile',
            5 => 'Maggio',
            6 => 'Giugno',
            7 => 'Luglio',
            8 => 'Agosto',
            9 => 'Settembre',
            10 => 'Ottobre',
            11 => 'Novembre',
            12 => 'Dicembre'
        ];



        $percentualiPerMese = [];
        foreach ($dataLea as $record) {
            if ($record->cia_1_den != 0) {
                $percentualiPerMese[$record->mese] = ($record->cia_1_num / $record->cia_1_den) * 100;
            }
        }

        // Preparazione dei dati per il grafico
        $graficoDati = [];
        foreach (array_keys($mesi) as $meseNumero) {

            $graficoDati[] = $percentualiPerMese[$meseNumero] ?? 0;
        }


        $dataView['CIA1'] = Chartjs::build()
            ->name("CIA1")
            ->type("line")
            ->size(["width" => 600, "height" => 300])
            ->labels(array_values($mesi))
            ->datasets([
                [
                    "label" => $dataView['nome'],
                    "backgroundColor" => "rgba(75, 192, 192, 0.2)",
                    "borderColor" => "rgb(75, 192, 192)",
                    "data" => $graficoDati,
                    "fill" => false,
                    "lineTension" => 0.1
                ],
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Percentuali mensili (cia1_num / den)'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);

        /****************************************CIA 2*****************************************************/




        $percentualiPerMeseCia2 = [];
        foreach ($dataLea as $record) {
            if ($record->cia_2_den != 0) {
                $percentualiPerMeseCia2[$record->mese] = ($record->cia_2_num / $record->cia_2_den) * 100;
            }
        }


        $graficoDatiCia2 = [];
        foreach (array_keys($mesi) as $meseNumero) {

            $graficoDatiCia2[] = $percentualiPerMeseCia2[$meseNumero] ?? 0;
        }



        $dataView['CIA2'] = Chartjs::build()
            ->name("CIA2")
            ->type("line")
            ->size(["width" => 600, "height" => 300])
            ->labels(array_values($mesi))
            ->datasets([
                [
                    "label" => $dataView['nome'],
                    "backgroundColor" => "rgba(75, 192, 192, 0.2)",
                    "borderColor" => "rgb(75, 192, 192)",
                    "data" => $graficoDatiCia2,
                    "fill" => false,
                    "lineTension" => 0.1
                ],
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Percentuali mensili (cia1_num / den)'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);



        /****************************************CIA3***************************************************** */

        $percentualiPerMeseCia3 = [];
        foreach ($dataLea as $record) {
            if ($record->cia_3_den != 0) {
                $percentualiPerMeseCia3[$record->mese] = ($record->cia_3_num / $record->cia_3_den) * 100;
            }
        }


        $graficoDatiCia2 = [];
        foreach (array_keys($mesi) as $meseNumero) {

            $graficoDatiCia3[] = $percentualiPerMeseCia3[$meseNumero] ?? 0;
        }



        $dataView['CIA3'] = Chartjs::build()
            ->name("CIA3")
            ->type("line")
            ->size(["width" => 600, "height" => 300])
            ->labels(array_values($mesi))
            ->datasets([
                [
                    "label" => $dataView['nome'],
                    "backgroundColor" => "rgba(75, 192, 192, 0.2)",
                    "borderColor" => "rgb(75, 192, 192)",
                    "data" => $graficoDatiCia3,
                    "fill" => false,
                    "lineTension" => 0.1
                ],
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Percentuali mensili (cia1_num / den)'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);

        /****************************Tumore**********************************/


        if ($dataView['ultimoData'] && $dataView['ultimoData']->ob10_ao_4_den != 0) {

            $dataView['percentualeOb10_ao_4'] = round(($dataView['ultimoData']->ob10_ao_4_num / $dataView['ultimoData']->ob10_ao_4_den) * 100, 2);
            $dataView['percentualeOb10_ao_4Compl'] = 100 - $dataView['percentualeOb10_ao_4'];
        } else {

            $dataView['percentualeOb10_ao_4'] = 0;
            $dataView['percentualeOb10_ao_4Compl'] = 100;
        }





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
                    "data" => [$dataView['percentualeOb10_ao_4'], $dataView['percentualeOb10_ao_4Compl']]

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

        /************************************AREA OSPEDALIERA 1********************************************* */

        $num = $dataView['ultimoDataSDO']->ob10_ao_1_num ?? 0;
        $den = $dataView['ultimoDataSDO']->ob10_ao_1_den ?? 0;

        $dataView['percentualeOb10_ao_1'] = ($den > 0) ? round(($num / $den) * 100, 2) : 0;
        $dataView['percentualeOb10_ao_1Compl'] = 100 - $dataView['percentualeOb10_ao_1'];

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
                    "data" => [$dataView['percentualeOb10_ao_1'], $dataView['percentualeOb10_ao_1Compl']]

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

        /************************************AREA OSPEDALIERA 2*********************************************************/

        $percentualiMensili = [];


        foreach ($dataView['dataSDO'] as $record) {
            if ($record->ob10_ao_2_den != 0) {
                $percentualiMensili[$record->month] = round(($record->ob10_ao_2_num / $record->ob10_ao_2_den) * 100, 2);
            } else {
                $percentualiMensili[$record->month] = 0;
            }
        }


        $datiGrafico = [];
        for ($mese = 1; $mese <= 12; $mese++) {
            $datiGrafico[] = $percentualiMensili[$mese] ?? 0;
        }




        $dataView['chartDRG'] = Chartjs::build()
            ->name("chartDRG")
            ->type("line")
            ->size(["width" => 600, "height" => 300])
            ->labels(array_values($mesi))
            ->datasets([
                [
                    "label" => $dataView['nome'],
                    "backgroundColor" => "rgba(60, 179, 113, 0.2)",
                    "borderColor" => "rgba(60, 179, 113, 1)",
                    "data" => $datiGrafico,
                    "fill" => false,
                    "lineTension" => 0.1
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Percentuale Mensile Obiettivo 10 (AO)'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale (%)'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);

        /***************************************AREA OSPEDALIERA 3******************************************* */

        $percentualiMensiliAo3 = [];


        foreach ($dataView['dataSDO'] as $record) {
            if ($record->ob10_ao_3_den != 0) {
                $percentualiMensiliAo3[$record->month] = round(($record->ob10_ao_3_num / $record->ob10_ao_3_den) * 100, 2);
            } else {
                $percentualiMensiliAo3[$record->month] = 0;
            }
        }


        $datiGraficoAo3 = [];
        for ($mese = 1; $mese <= 12; $mese++) {
            $datiGraficoAo3[] = $percentualiMensiliAo3[$mese] ?? 0; // Usa 0 se non ci sono dati per il mese
        }

        $dataView['chartInfezioniPostChirurgiche'] = Chartjs::build()
            ->name("chartInfezioniPostChirurgiche")
            ->type("line")
            ->size(["width" => 600, "height" => 300])
            ->labels(array_values($mesi))
            ->datasets([
                [
                    "label" => $dataView['nome'],
                    "backgroundColor" => "rgba(60, 179, 113, 0.2)",
                    "borderColor" => "rgba(60, 179, 113, 1)",
                    "data" => $datiGraficoAo3,
                    "fill" => false,
                    "lineTension" => 0.1
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Percentuale Mensile Obiettivo 10 (AO)'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale (%)'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);


        return view("garanzia-lea")->with("dataView", $dataView);
    }



    public function fse(Request $request)
    {


        //     $dataSelezionata = $request->annoSelezionato ?? date('Y');

        $anno = $request->has("year") ? $request->year : date('Y');
        $dataView['strutture'] = Auth::user()->structures();
        $dataView['annoSelezionato'] = $anno;

        $dataView['anni'] = DB::table('flows_sdo')
            ->distinct()
            ->pluck("year");

        /*****************************Dimissioni Ospedaliere**********************************/
        $prevenzioneTre = DB::table('target7_data')
            ->where('anno', "=", $anno)
            ->where('structure_id', '=', Auth::user()->firstStructureId()->id)
            ->first();


        //numeratori
        $dataView['dimissioniOspedaliere'] = isset($prevenzioneTre->dimissioni_ospedaliere) ? $prevenzioneTre->dimissioni_ospedaliere : 0;
        $dataView['dimissioniPS'] = isset($prevenzioneTre->dimissioni_ps) ? $prevenzioneTre->dimissioni_ps : 0;
        $dataView['prestazioniLab'] = isset($prevenzioneTre->prestazioni_laboratorio) ? $prevenzioneTre->prestazioni_laboratorio : 0;
        $dataView['prestazioniRadiologia'] = isset($prevenzioneTre->prestazioni_radiologia) ? $prevenzioneTre->prestazioni_radiologia : 0;
        $dataView['specialisticaAmbulatoriale'] = isset($prevenzioneTre->prestazioni_ambulatoriali) ? $prevenzioneTre->prestazioni_ambulatoriali : 0;
        $dataView['vaccinati'] = isset($prevenzioneTre->vaccinati) ? $prevenzioneTre->vaccinati : 0;
        $dataView['certificatiIndicizzati'] = isset($prevenzioneTre->certificati_indicizzati) ? $prevenzioneTre->certificati_indicizzati : 0;
        $dataView['documentiIndicizzati'] = isset($prevenzioneTre->documenti_indicizzati) ? $prevenzioneTre->documenti_indicizzati : 0;
        $dataView['documentiIndicizzatiCDA2'] = isset($prevenzioneTre->documenti_indicizzati_cda2) ? $prevenzioneTre->documenti_indicizzati_cda2 : 0;
        $documentiCDA2 = isset($prevenzioneTre->documenti_cda2) ? $prevenzioneTre->documenti_cda2 : 0;
        $dataView['documentiPades'] = isset($prevenzioneTre->documenti_pades) ? $prevenzioneTre->documenti_pades : 0;
        $dataView['documentiIndicizzatiPades'] = isset($prevenzioneTre->documenti_indicizzati_pades) ? $prevenzioneTre->documenti_indicizzati_pades : 0;

        // Estrai i dati del denominatore
        $denominatore = DB::table('flows_sdo')
            ->where('structure_id', '=', Auth::user()->firstStructureId()->id)
            ->where('year', $anno) // Filtro per l'anno corrente
            ->select('ob7_1')
            ->orderByDesc('month')
            ->first();

        if ($denominatore) {
            $dataView['ob7'] = $denominatore->ob7_1;
        } else {
            $dataView['ob7'] = 0;
        }


        if ($dataView['ob7'] != 0) {
            $dataView['percentualeDimissioniOspedaliere'] = round(($dataView['dimissioniOspedaliere'] / $dataView['ob7']) * 100, 2);
        } else
            $dataView['percentualeDimissioniOspedaliere'] = 0;
        $dataView['percentualeDimissioniOspedaliereComplementare'] = 100 - $dataView['percentualeDimissioniOspedaliere'];

        $dataView['chartDimissioniOspedaliere'] = $this->chartFSE(
            "chartDimissioniOspedaliere",
            ['Indicizzati', 'Non indicizzati'],
            [$dataView['percentualeDimissioniOspedaliere'], $dataView['percentualeDimissioniOspedaliereComplementare']]
        );

        /*****************************Dimissioni Pronto Soccorso****************************************************/
        $dataView['ob7PS'] = DB::table('flows_emur')
            ->where('structure_id', '=', Auth::user()->firstStructureId()->id)
            ->where('year', "=", date('Y'))
            ->sum('ia1_2');

        if ($dataView['ob7PS'] > 0) {
            $dataView['percentualePS'] = round($dataView['dimissioniPS'] / $dataView['ob7PS'] * 100, 2);
        } else {
            $dataView['percentualePS'] = 0;
        }
        $dataView['percentualeComplementarePS'] = 100 - $dataView['percentualePS'];

        $dataView['chartProntoSoccorso'] = $this->chartFSE(
            "chartProntoSoccorso",
            ['Indicizzati', 'Non Indicizzati'],
            [$dataView['percentualePS'], $dataView['percentualeComplementarePS']]
        );

        /*********************Prestazioni di Laboratorio****************************** */
        $denFlussoC = DB::table('flows_c')
            ->select('ia1_3', 'ia1_4', 'ia1_5', 'ia1_6')
            ->where('year', "=", date('Y'))
            ->where('structure_id', '=', Auth::user()->firstStructureId()->id)
            ->get();

        $dataView['PrestazioniLabDen'] = 0;
        $dataView['PrestazioniRadDen'] = 0;
        $dataView['PrestazioniAmbulatoriale'] = 0;
        $dataView['prestazioniErogate'] = 0;
        foreach ($denFlussoC as $dati) {
            $dataView['PrestazioniLabDen'] += $dati->ia1_3;
            $dataView['PrestazioniRadDen'] += $dati->ia1_4;
            $dataView['PrestazioniAmbulatoriale'] += $dati->ia1_5;
            $dataView['prestazioniErogate'] += $dati->ia1_6;
        }


        if ($dataView['PrestazioniLabDen'] > 0) {
            $dataView['percentualePrestLab'] = round($dataView['prestazioniLab'] / $dataView['PrestazioniLabDen'] * 100, 2);
        } else {
            $dataView['percentualePrestLab'] = 0;
        }
        $dataView['percentualeComplementarePrestLab'] = 100 - $dataView['percentualePrestLab'];


        $dataView['chartRefertiLaboratorio'] = $this->chartFSE(
            "chartRefertiLaboratorio",
            ['Indicizzati', 'Non Indicizzati'],
            [$dataView['percentualePrestLab'], $dataView['percentualeComplementarePrestLab']]
        );

        /*********************Ref radiologia*********************************************************** */
        if ($dataView['PrestazioniRadDen'] > 0) {
            $dataView['percentualeRefRadiologia'] = round($dataView['prestazioniRadiologia'] / $dataView['PrestazioniRadDen'] * 100, 2);
        } else {
            $dataView['percentualeRefRadiologia'] = 0;
        }
        $dataView['percentualeComplementareRefRadiologia'] = 100 - $dataView['percentualeRefRadiologia'];

        $dataView['chartRefertiRadiologia'] = $this->chartFSE(
            "chartRefertiRadiologia",
            ['Indicizzati', 'Non indicizzati'],
            [$dataView['percentualeRefRadiologia'], $dataView['percentualeComplementareRefRadiologia']]
        );

        /**********************Specialistica Ambulatoriale**********************************************************/
        if ($dataView['PrestazioniAmbulatoriale'] > 0) {
            $dataView['percentualeSpecAmbulatoriale'] = round($dataView['specialisticaAmbulatoriale'] / $dataView['PrestazioniAmbulatoriale'] * 100, 2);
        } else {
            $dataView['percentualeSpecAmbulatoriale'] = 0;
        }
        $dataView['percentualeComplementareSpecAmbulatoriale'] = 100 - $dataView['percentualeSpecAmbulatoriale'];

        $dataView['chartSpecialisticaAmbulatoriale'] = $this->chartFSE(
            "chartSpecialisticaAmbulatoriale",
            ['Indicizzati', 'Non Indicizzati'],
            [$dataView['percentualeSpecAmbulatoriale'], $dataView['percentualeComplementareSpecAmbulatoriale']]
        );

        /****************************Vaccinati****************************************************** */
        if ($dataView['vaccinati'] > 0) {
            $dataView['percentualeVaccinati'] = round($dataView['certificatiIndicizzati'] / $dataView['vaccinati'] * 100, 2);
        } else {
            $dataView['percentualeVaccinati'] = 0;
        }
        $dataView['percentualeComplementareVaccinati'] = 100 - $dataView['percentualeVaccinati'];

        $dataView['chartCertificatiVaccinali'] = $this->chartFSE(
            "chartCertificatiVaccinali",
            ['Indicizzati', 'Non Indicizzati'],
            [$dataView['percentualeVaccinati'], $dataView['percentualeComplementareVaccinati']]
        );

        /**************************Documentazione FSE************************************************************ */
        if ($dataView['prestazioniErogate'] > 0) {
            $dataView['percentualeDocumentazioneFse'] = round($dataView['documentiIndicizzati'] / $dataView['prestazioniErogate'] * 100, 2);
        } else {
            $dataView['percentualeDocumentazioneFse'] = 0;
        }
        $dataView['percentualeComplementareDocumentazioneFse'] = 100 - $dataView['percentualeDocumentazioneFse'];

        $dataView['chartDocumentiFSE'] = $this->chartFSE(
            "chartDocumentiFSE",
            ['Indicizzati', 'Non Indicizzati'],
            [$dataView['percentualeDocumentazioneFse'], $dataView['percentualeComplementareDocumentazioneFse']]
        );

        /***************************Documenti in CDA2************************************************************* */

        if ($dataView['documentiIndicizzatiCDA2'] > 0) {
            $dataView['percentualeDocumentiCDA2'] = round($documentiCDA2 / $dataView['documentiIndicizzatiCDA2'] * 100, 2);
        } else {
            $dataView['percentualeDocumentiCDA2'] = 0;
        }
        $dataView['percentualeComplementareDocumentiCDA2'] = 100 - $dataView['percentualeDocumentiCDA2'];

        $dataView['chartDocumentiCDA2'] = $this->chartFSE(
            "chartDocumentiCDA2",
            ['Non CDA2', 'CDA2'],
            [$dataView['percentualeDocumentiCDA2'], $dataView['percentualeComplementareDocumentiCDA2']]
        );

        /***************************Documenti Pades************************************************************ */
        if ($dataView['documentiIndicizzatiPades'] > 0) {
            $dataView['percentualePades'] = round($dataView['documentiPades'] / $dataView['documentiIndicizzatiPades'] * 100, 2);
        } else {
            $dataView['percentualePades'] = 0;
        }
        $dataView['percentualeComplementarePades'] = 100 - $dataView['percentualePades'];

        $dataView['chartDocumentiPades'] = $this->chartFSE(
            "chartDocumentiPades",
            ['Pades', 'Non pades'],
            [$dataView['percentualePades'], $dataView['percentualeComplementarePades']]
        );


        $calcoloPunteggio = $this->calcoloPunteggioOb7($dataView['percentualeDocumentazioneFse'], $dataView['percentualeDocumentiCDA2'], $dataView['percentualePades']);
        $dataView = array_merge($calcoloPunteggio, $dataView);

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


    public function caricamentoGaranziaLea(Request $request)
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


        return view("caricamentoGaranziaLea")->with("dataView", $dataView);
    }

    public function esiti(Request $request)
    {

        //  $dataView['dataSelezionata'] = $request->annoSelezionato ?? date('Y');

        $anno = $request->has("year") ? $request->year : date('Y');
        $dataView['strutture'] = Auth::user()->structures();
        $dataView['annoSelezionato'] = $anno;
        $dataView['anni'] = DB::table('flows_sdo')
            ->distinct()
            ->pluck("year");

        $flowsSdo = DB::table('flows_sdo')
            ->select(
                'flows_sdo.ob2_3_numeratore',
                'flows_sdo.ob2_3_denominatore',
                'flows_sdo.ob2_2_minore_mille_numeratore',
                'flows_sdo.ob2_2_minore_mille_denominatore',
                'flows_sdo.ob2_2_maggiore_mille_numeratore',
                'flows_sdo.ob2_2_maggiore_mille_denominatore',
                'flows_sdo.ob2_1_numeratore',
                'flows_sdo.ob2_1_denominatore',
                'flows_sdo.ob2_4_numeratore',
                'flows_sdo.ob2_4_denominatore',
                'flows_sdo.year',
                'flows_sdo.month',
                's.name'
            )
            ->join('structures as s', 'flows_sdo.structure_id', '=', 's.id')
            ->where('flows_sdo.structure_id', '=', Auth::user()->firstStructureId()->id)
            ->where('flows_sdo.year', '=', $anno)
            ->orderBy('flows_sdo.month', 'DESC')
            ->get();


        $flowsSdo = $flowsSdo->sortBy('month');

        // il mese più grande
        $flowsMeseMassimo = $flowsSdo->first();


        $labels = [];
        $data = [];

        foreach ($flowsSdo as $item) {
            $labels[] = Carbon::createFromFormat('m', $item->month)->translatedFormat('F');
            $data[] = $item->ob2_1_denominatore > 0
                ? ($item->ob2_1_numeratore / $item->ob2_1_denominatore) * 100 : 0;
        }

        /*
                foreach ($flowsSdo as $row) {
                    $dataView['nome_struttura'] = $row->name;
                    $dataView['numeratoreFratturaFemore'] = $row->ob2_1_numeratore;
                    $dataView['denominatoreFratturaFemore'] = $row->ob2_1_denominatore;
                    $dataView['numeratorePartiMinoreMille'] = $row->minore_mille_numeratore;
                    $dataView['denominatorePartiMinoreMille'] = $row->minore_mille_denominatore;
                    $dataView['numeratorePartiMaggioreMille'] = $row->maggiore_mille_numeratore;
                    $dataView['denominatorePartiMaggioreMille'] = $row->maggiore_mille_denominatore;
                    $dataView['numeratoreIma'] = $row->ob2_3_numeratore;
                    $dataView['denominatoreIma'] = $row->ob2_3_denominatore;
                    $dataView['numeratoreCole'] = $row->ob2_4_numeratore;
                    $dataView['denominatoreCole'] = $row->ob2_4_denominatore;
                }
        */
        $dataView['nome_struttura'] = $flowsMeseMassimo->name;
        $dataView['NumMaxFemore'] = $flowsMeseMassimo->ob2_1_numeratore;
        $dataView['DenMaxFemore'] = $flowsMeseMassimo->ob2_1_denominatore;

        if ($flowsMeseMassimo->ob2_1_denominatore > 0) {
            $dataView['percentualeFratturaFemore'] = round(($flowsMeseMassimo->ob2_1_numeratore / $flowsMeseMassimo->ob2_1_denominatore) * 100, 2);
            $dataView['percentualeFratturaFemoreCompl'] = 100 - $dataView['percentualeFratturaFemore'];
        } else {
            $dataView['percentualeFratturaFemore'] = 0;
            $dataView['percentualeFratturaFemoreCompl'] = 100;
        }


        $dataView['chartFratturaFemore'] = Chartjs::build()
            ->name("chartFratturaFemore")
            ->type("doughnut")
            ->size(["width" => 100, "height" => 100])
            ->labels(['Intervento <= 2', 'Intervento >= 2'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                    ],
                    "data" => [$dataView['percentualeFratturaFemore'], $dataView['percentualeFratturaFemoreCompl']]
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

        $dataView['chartFratturaFemoreLine'] = Chartjs::build()
            ->name("chartFratturaFemoreLine")
            ->type("line")
            ->size(["width" => 50, "height" => 15])
            ->labels($labels)
            ->datasets([
                [
                    "label" => $dataView['nome_struttura'],
                    "backgroundColor" => "rgba(60, 179, 113, 0.2)",
                    "borderColor" => "rgb(60, 179, 113)",
                    "data" => $data
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'interventi effettuati entro 0-2 giorni dal ricovero / numero totale di casi di frattura femore su pazienti over 65'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);


        /*********************************Minore di mille********************************************/


        $dataView['numeratoreMaxPartiMinoreMille'] = $flowsMeseMassimo->ob2_2_minore_mille_numeratore;
        $dataView['denominatoreMaxPartiMinoreMille'] = $flowsMeseMassimo->ob2_2_minore_mille_denominatore;

        $labelsMinoreMille = [];
        $dataMinoreMille = [];


        foreach ($flowsSdo as $item) {

            $labelsMinoreMille[] = Carbon::createFromFormat('m', $item->month)->translatedFormat('F');

            $dataMinoreMille[] = $item->ob2_2_minore_mille_denominatore > 0
                ? ($item->ob2_2_minore_mille_numeratore / $item->ob2_2_minore_mille_denominatore) * 100 : 0;
        }

        if ($flowsMeseMassimo->ob2_2_minore_mille_denominatore > 0) {
            $dataView['percentualePartiMinoreMille'] = round(($flowsMeseMassimo->ob2_2_minore_mille_numeratore / $flowsMeseMassimo->ob2_2_minore_mille_denominatore) * 100, 2);
            $dataView['percentualePartiComplMinoreMille'] = 100 - $dataView['percentualePartiMinoreMille'];
        } else {
            $dataView['percentualePartiMinoreMille'] = 0;
            $dataView['percentualePartiComplMinoreMille'] = 100;
        }

        $dataView['chartPartiCesareiMenoMille'] = Chartjs::build()
            ->name("chartPartiCesareiMenoMille")
            ->type("doughnut")
            ->size(["width" => 100, "height" => 100])
            ->labels(['Pades', 'Non pades'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                    ],
                    "data" => [$dataView['percentualePartiMinoreMille'], $dataView['percentualePartiComplMinoreMille']]
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



        $dataView['chartPartiCesareiMinoriMilleLine'] = Chartjs::build()
            ->name("chartPartiCesareiMinoriMilleLine")
            ->type("line")
            ->size(["width" => 50, "height" => 15])
            ->labels($labelsMinoreMille)
            ->datasets([
                [
                    "label" => $dataView['nome_struttura'],
                    "backgroundColor" => "rgba(60, 179, 113, 0.2)",
                    "borderColor" => "rgb(60, 179, 113)",
                    "data" => $dataMinoreMille
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Parti di donne non precesarizzate8cesarei primari) / totale parti di donne con nessun pregresso cesareo.'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);



        /************************* Maggiori di mille ******************************** */

        $dataView['numeratoreMaxPartiMaggioreMille'] = $flowsMeseMassimo->ob2_2_maggiore_mille_numeratore;
        $dataView['denominatoreMaxPartiMaggioreMille'] = $flowsMeseMassimo->ob2_2_maggiore_mille_denominatore;


        $labelsMaggioreMille = [];
        $dataMaggioreMille = [];


        foreach ($flowsSdo as $item) {

            $labelsMaggioreMille[] = Carbon::createFromFormat('m', $item->month)->translatedFormat('F');

            $dataMaggioreMille[] = $item->ob2_2_maggiore_mille_denominatore > 0
                ? ($item->ob2_2_maggiore_mille_numeratore / $item->ob2_2_maggiore_mille_denominatore) * 100 : 0;
        }

        if ($flowsMeseMassimo->ob2_2_maggiore_mille_denominatore > 0) {
            $dataView['percentualePartiMaggioreMille'] = round(($flowsMeseMassimo->ob2_2_maggiore_mille_numeratore / $flowsMeseMassimo->ob2_2_maggiore_mille_denominatore) * 100, 2);
            $dataView['percentualePartiComplMaggioreMille'] = 100 - $dataView['percentualePartiMaggioreMille'];
        } else {
            $dataView['percentualePartiMaggioreMille'] = 0;
            $dataView['percentualePartiComplMaggioreMille'] = 100;
        }


        $dataView['chartPartiCesareiMaggioriMille'] = Chartjs::build()
            ->name("chartPartiCesareiMaggioriMille")
            ->type("doughnut")
            ->size(["width" => 100, "height" => 100])
            ->labels(['Pades', 'Non pades'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                    ],
                    "data" => [$dataView['percentualePartiMaggioreMille'], $dataView['percentualePartiComplMaggioreMille']]
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


        $dataView['chartPartiCesareiMaggioriMilleLine'] = Chartjs::build()
            ->name("chartPartiCesareiMaggioriMilleLine")
            ->type("line")
            ->size(["width" => 50, "height" => 15])
            ->labels($labelsMaggioreMille)
            ->datasets([
                [
                    "label" => $dataView['nome_struttura'],
                    "backgroundColor" => "rgba(60, 179, 113, 0.2)",
                    "borderColor" => "rgb(60, 179, 113)",
                    "data" => $dataMaggioreMille
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Parti di donne non precesarizzate8cesarei primari) / totale parti di donne con nessun pregresso cesareo.'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);




        /*********************** sub 3 *************************************/

        $dataView['numeratoreMaxIma'] = $flowsMeseMassimo->ob2_3_numeratore;
        $dataView['denominatoreMaxIma'] = $flowsMeseMassimo->ob2_3_denominatore;

        $labelsIma = [];
        $dataIma = [];


        foreach ($flowsSdo as $item) {

            $labelsIma[] = Carbon::createFromFormat('m', $item->month)->translatedFormat('F');

            $dataIma[] = $item->ob2_3_denominatore > 0
                ? ($item->ob2_3_numeratore / $item->ob2_3_denominatore) * 100 : 0;
        }

        if ($flowsMeseMassimo->ob2_3_denominatore > 0) {
            $dataView['percentualeIma'] = round(($flowsMeseMassimo->ob2_3_numeratore / $flowsMeseMassimo->ob2_3_denominatore) * 100, 2);
            $dataView['percentualeImaCompl'] = 100 - $dataView['percentualeIma'];
        } else {
            $dataView['percentualeIma'] = 0;
            $dataView['percentualeImaCompl'] = 100;
        }


        $dataView['chartIma'] = Chartjs::build()
            ->name("chartIma")
            ->type("doughnut")
            ->size(["width" => 100, "height" => 100])
            ->labels(['PTCA entro 90 minuti', 'PTCA oltre 90 minuti'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                    ],
                    "data" => [$dataView['percentualeIma'], $dataView['percentualeImaCompl']]
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'PTCA effettuate entro 90 minuti / numero totale di di I.M.A. SISTEMI diagnosticati'
                    ]
                ]
            ]);


        $dataView['chartImaLine'] = Chartjs::build()
            ->name("chartImaLine")
            ->type("line")
            ->size(["width" => 50, "height" => 15])
            ->labels($labelsIma)
            ->datasets([
                [
                    "label" => $dataView['nome_struttura'],
                    "backgroundColor" => "rgba(60, 179, 113, 0.2)",
                    "borderColor" => "rgb(60, 179, 113)",
                    "data" => $dataIma
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'PTCA effettuate entro 90 minuti / numero totale di di I.M.A. SISTEMI diagnosticati'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);


        /************************sub 4 *************************** */

        $dataView['numeratoreMaxCole'] = $flowsMeseMassimo->ob2_4_numeratore;
        $dataView['denominatoreMaxCole'] = $flowsMeseMassimo->ob2_4_denominatore;

        $labelsCole = [];
        $dataCole = [];

        foreach ($flowsSdo as $item) {

            $labelsCole[] = Carbon::createFromFormat('m', $item->month)->translatedFormat('F');
            $dataCole[] = $item->ob2_3_denominatore > 0
                ? ($item->ob2_4_numeratore / $item->ob2_4_denominatore) * 100 : 0;
        }

        if ($flowsMeseMassimo->ob2_4_denominatore > 0) {
            $dataView['percentualeCole'] = round(($flowsMeseMassimo->ob2_4_numeratore / $flowsMeseMassimo->ob2_4_denominatore) * 100, 2);
            $dataView['percentualeColeCompl'] = 100 - $dataView['percentualeCole'];
        } else {
            $dataView['percentualeCole'] = 0;
            $dataView['percentualeColeCompl'] = 100;
        }

        $dataView['chartColecistectomia'] = Chartjs::build()
            ->name("chartColecistectomia")
            ->type("doughnut")
            ->size(["width" => 100, "height" => 100])
            ->labels(['Degenza post-operatoria > 3 Giorni', 'Degenza post-operatoria <= 3 Giorni'])
            ->datasets([
                [
                    "label" => "Percentuali MMG",
                    "backgroundColor" => [
                        "rgb(60, 179, 113)",
                        "rgb(255, 0, 0)",
                    ],
                    "data" => [$dataView['percentualeCole'], $dataView['percentualeColeCompl']]
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


        $dataView['chartColecistectomiaLine'] = Chartjs::build()
            ->name("chartColecistectomiaLine")
            ->type("line")
            ->size(["width" => 50, "height" => 15])
            ->labels($labelsCole)
            ->datasets([
                [
                    "label" => $dataView['nome_struttura'],
                    "backgroundColor" => "rgba(60, 179, 113, 0.2)",
                    "borderColor" => "rgb(60, 179, 113)",
                    "data" => $dataCole
                ]
            ])
            ->options([
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Numero di ricoveri con intervento con degenza post-operatoria inferiore a 3 gironi / numero totale di ricoveri conintervento.'
                    ]
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Mesi'
                        ]
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Percentuale'
                        ],
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            ]);


        return view("esisti")->with("dataView", $dataView);
    }

    /*
        public function uploadDatiVeterinaria(Request $request)
        {

            $structure_id = $request->structure_id;



            Target10_data::updateOrCreate([
                'anno' => $request->anno,
                'structure_id' => $structure_id
            ], [
                'num_aziende_bovine_controllate' => $request->num_aziende_bovine_controllate,
                'num_aziende_bovine_totali' => $request->num_aziende_bovine_totali,
                'num_aziende_ovicaprine_controllate' => $request->num_aziende_ovicaprine_controllate,
                'num_aziende_ovicaprine_totali' => $request->num_aziende_ovicaprine_totali,
                'num_capi_ovicaprini_controllati' => $request->num_capi_ovicaprini_controllati,
                'num_capi_ovicaprini_totali' => $request->num_capi_ovicaprini_totali,
                'num_aziende_suine_controllate' => $request->num_aziende_suine_controllate,
                'num_aziende_suine_totali' => $request->num_aziende_suine_totali,
                'num_aziende_equine_controllate' => $request->num_aziende_equine_controllate,
                'num_aziende_equine_totali' => $request->num_aziende_equine_totali,
                'num_allevamenti_apistici_controllati' => $request->num_allevamenti_apistici_controllati,
                'num_allevamenti_apistici_totali' => $request->num_allevamenti_apistici_totali,
                'pnaa7_esecuzione' => $request->pnaa7_esecuzione,
                'pnaa7_esecuzione_totali' => $request->pnaa7_esecuzione_totali,
                'controlli_farmacosorveglianza_veterinaria' => $request->controlli_farmacosorveglianza_veterinaria,
                'controlli_farmacosorveglianza_veterinaria_totali' => $request->controlli_farmacosorveglianza_veterinaria_totali,
                'structure_id' => $structure_id,
                'anno' => $request->anno,
            ]);


            return redirect()->route('caricamentoGaranziaLea', ['obiettivo' => $request->obiettivo]);

        }
    */
    public function uploadDatiCombinati(Request $request)
    {

        $structure_id = $request->structure_id;
        $anno = $request->anno;
        $mese = $request->mese;

        $data = $request->only([
            'ob10_1_numeratore',
            'ob10_1_denominatore',
            'ob10_2_numeratore',
            'ob10_2_denominatore',
            'num_aziende_bovine_controllate',
            'num_aziende_bovine_totali',
            'num_aziende_ovicaprine_controllate',
            'num_aziende_ovicaprine_totali',
            'num_capi_ovicaprini_controllati',
            'num_capi_ovicaprini_totali',
            'num_aziende_suine_controllate',
            'num_aziende_suine_totali',
            'num_aziende_equine_controllate',
            'num_aziende_equine_totali',
            'num_allevamenti_apistici_controllati',
            'num_allevamenti_apistici_totali',
            'pnaa7_esecuzione',
            'pnaa7_esecuzione_totali',
            'controlli_farmacosorveglianza_veterinaria',
            'controlli_farmacosorveglianza_veterinaria_totali',
            'copertura_pnr_num',
            'copertura_pnr_den',
            'copertura_fitofarmaci_num',
            'copertura_fitofarmaci_den',
            'copertura_additivi_num',
            'copertura_additivi_den',
            'ob10_at_1_den',
            'ob10_at_2_den',
            'cia_1_num',
            'cia_1_den',
            'cia_2_num',
            'cia_2_den',
            'cia_3_num',
            'cia_3_den',
            'ob10_ao_4_num',
            'ob10_ao_4_den',

        ]);

        $data['structure_id'] = $structure_id;
        $data['anno'] = $anno;
        $data['mese'] = $mese;


        Target10_data::updateOrCreate([
            'anno' => $anno,
            'mese' => $mese,
            'structure_id' => $structure_id,
        ], $data);

        return redirect()->route('caricamentoGaranziaLea', ['obiettivo' => $request->obiettivo]);
    }

}
