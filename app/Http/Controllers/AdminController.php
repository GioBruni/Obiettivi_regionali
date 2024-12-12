<?php

namespace App\Http\Controllers;

use App\Mail\controllerEmail;
use App\Models\Gare;
use App\Models\LocationsUsers;
use App\Models\PCT;
use App\Models\Structure;
use App\Models\UploatedFile;
use App\Models\User;
use App\Models\UserLog;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;
use Storage;
use App\ChartTrait;
use Illuminate\Support\Facades\Mail;


class AdminController extends Controller
{
    use ChartTrait;

    protected function coloreSfondo($value, $base) {
        $startColor = [139, 0, 0]; // Rosso scuro
        $endColor = [255, 204, 204]; // Rosso chiaro
        
        $ratio = $value / $base;
        $r = (int)($startColor[0] + ($endColor[0] - $startColor[0]) * $ratio);
        $g = (int)($startColor[1] + ($endColor[1] - $startColor[1]) * $ratio);
        $b = (int)($startColor[2] + ($endColor[2] - $startColor[2]) * $ratio);
        
        $hex = "#" . str_pad(dechex($r), 2, "0", STR_PAD_LEFT) .
            str_pad(dechex($g), 2, "0", STR_PAD_LEFT) .
            str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
    
        return $hex;
    }

    /**
     ************************* A D M I N  ************************
     */

    /**
     * Recupera la lista degli utenti
     * @return void
     */
    public function usersList() {

        $dataView['users'] = User::leftJoin("users_structures", "users_structures.user_id", "=", "users.id")
            ->leftJoin("structures",  "structures.id", "=", "users_structures.user_id")
            ->select("users.id", "users.name", "users.email", "users.enable", DB::raw("group_concat(structures.name) as strutture"))
            ->groupBy("users.id")
            ->get();

        return view("admin.users_list")->with("dataView", $dataView);
    }

    /**
     * Summary of showUser
     * @param mixed $id
     * @return mixed|\Illuminate\Contracts\View\View
     */
    public function showUser($id = null) {
        if ($id != null) {
            $dataView['user'] = User::where("users.id", $id)->first();
            $dataView['userStructures'] = User::find( $id)->structures();
            $dataView['structures'] = Structure::orderBy("company_code")->orderBy("structure_code")->orderBy("name")->get();
        } else {
            $dataView['user'] = null;
            $dataView['userStructures'] = null;
            $dataView['structures'] = null;
        }

        return view("admin.user")->with("dataView", $dataView);
    }

    public function registerUser(Request $request) {

        $userId = null;
        if (isset($request->user_id) && $request->user_id != null) {
            $userId = $request->user_id;

            User::find($userId)->update([
                'name' => $request->name,
                'email' => $request->email,
                'enable' => $request->enable,
            ]);

            UserLog::create([
                'user_id' => Auth::user()->id,
                'super_action' => config("constants.SUPER_ACTION.UPDATE"),
                'action' => "Utente aggiornato: " . $userId
            ]);
    
        } else {
            $userId = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'enable' => $request->enable,
                'password' => Hash::make(Str::random(8)),
            ])->id;

            UserLog::create([
                'user_id' => Auth::user()->id,
                'super_action' => config("constants.SUPER_ACTION.INSERT"),
                'action' => "Utente inserito: " . $userId
            ]);

        }
        
        return $this->showUser($userId);
    }


    public function structureDelete(Request $request) {
        $location = LocationsUsers::find($request->structure_id);
        $location->delete();
        UserLog::create([
            'user_id' => Auth::user()->id,
            'super_action' => config("constants.SUPER_ACTION.DELETE"),
            'action' => "Cancellata a user id: " . $request->user_id . " asp: " . $location->asp . ", struttura: " . $location->structure
        ]);

        return $this->showUser($request->user_id);
    }

    public function structureInsert(Request $request) {
        LocationsUsers::create([
            "user_id" => $request->user_id,
            "structure_id" => $request->structure_id,
        ]);
        UserLog::create(attributes: [
            'user_id' => Auth::user()->id,
            'super_action' => config("constants.SUPER_ACTION.INSERT"),
            'action' => "Associato a user id: " . $request->user_id . " asp: " . $request->asp . ", struttura: " . $request->structure
        ]);

        return $this->showUser($request->user_id);
    }

    public function enableUser(Request $request) {
        $user = User::where("id", $request->user_id);
        if ($user) {
            $enable = $user->first()->enable == config("constants.ACTIVE") ? config("constants.NO_ACTIVE") : config("constants.ACTIVE");
            $user->update([
                'enable' => $enable
            ]);
            UserLog::create([
                'user_id' => Auth::user()->id,
                'super_action' => config("constants.SUPER_ACTION.UPDATE"),
                'action' => (($enable == 1) ? "Abilitato" : "Disabilitato"). " l'utente " . $request->user_id . " all'accesso."
            ]);
        }
        return $this->usersList();
    }




    /**
     ************************* C O N T R O L L E R  ************************
     */

    public function indexController() {
        
    }

    protected function updateUploatedFiles(Request $request, bool $validate) {

        //verifico che il file sia associato realmente all'obiettivo
        $uploatedFile = UploatedFile::where("target_number", $request->t)
            ->where("id", $request->id)->first();
    
        if($uploatedFile) {
            $uploatedFile->update([
                "notes" => $request->input("notes" . $request->id),
                "approved" => $validate,
                "validator_user_id" => Auth::user()->id,
            ]);
        }

  
        
        $emailData = [
            'file' => $uploatedFile,
            'validator' => Auth::user(),
            'status' => $validate ? 'Approvato' : 'Non Approvato',
        ];

        Mail::to("sebastiano.ortisi.ext@asp.sr.it")->send(new ControllerEmail($emailData));
        

       
        return response()->json(['redirect' => route("controller.obiettivo", ["obiettivo" => $request->t])]);
    }


    public function showObiettivo($obiettivo) {
        $dataView['obiettivo'] = $obiettivo;
        switch($obiettivo) {
            case 1: $dataView['titolo'] = config("constants.OBIETTIVO.1.text");
            break;
            case 3: $dataView['titolo'] = config("constants.OBIETTIVO.3.text");
            break;
            case 5: $dataView['titolo'] = config("constants.OBIETTIVO.5.text");
            break;
            case 6: $dataView['titolo'] = config("constants.OBIETTIVO.6.text");
            break;
            case 8: $dataView['titolo'] = config("constants.OBIETTIVO.8.text");
            break;
            case 9: $dataView['titolo'] = config("constants.OBIETTIVO.9.text");
            break;
        }

        $dataView['categorie'] = DB::table("target_categories")
            ->where("target_number", $obiettivo)
            ->orderBy("order")
            ->get();

        $dataView['filesCaricati'] = UploatedFile::where('uploated_files.target_number', $obiettivo)
        ->leftjoin("structures", "structures.id", "=", "uploated_files.structure_id")
        ->leftjoin("users", "users.id", "=", "uploated_files.user_id")
        ->leftjoin('target_categories as tc', 'uploated_files.target_category_id', '=', 'tc.id')
        ->orderByRaw('approved IS NULL DESC') // prima quelli con approved a NULL
        ->orderBy('created_at')
        ->select("uploated_files.*", "users.name as utente", "structures.name as struttura", "tc.category")
        ->get();

        
        return view("controller.showObiettivo".$obiettivo)->with("dataView", $dataView);

    }

    public function approvaObiettivo(Request $request)
    {

        $file = UploatedFile::findOrFail($request->fileId);
        $file->approved = $request->esito;
        $file->validator_user_id = Auth::user()->id;
        $file->notes = $request->notes;
        $file->save();

        DB::table("target3_data")->insert([
            'numerator' => $request->numeratore ?? null,
            'denominator' => $request->denominatore ?? null,
            'uploated_file_id' => $request->fileId,
        ]);

        $emailData = [
           
            'validator' => Auth::user(),
       
        ];
        Mail::to("sebastiano.ortisi.ext@asp.sr.it")->send(new ControllerEmail(emailData: $emailData));

        return $this->showObiettivo($file->target_number);
    }

    

    public function valide(Request $request) {

        return $this->updateUploatedFiles($request, true);
    }

    public function notValide(Request $request) {
   
        return $this->updateUploatedFiles($request, false);
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


    
    protected function initView(int $obiettivo)
    {
        $dataView['titolo'] = config("constants.OBIETTIVO." . $obiettivo . ".text");
        $dataView['icona'] = config("constants.OBIETTIVO." . $obiettivo . ".icon");
        $dataView['tooltip'] = config("constants.OBIETTIVO." . $obiettivo . ".tooltip");
        $dataView['obiettivo'] = $obiettivo;
        $dataView['categorie'] = DB::table(table: 'target_categories as tc')
            ->where("target_number", $obiettivo)->get();

        return $dataView;
    }


    public function index()
    {
        $dataView['saluteEFunzionamento'] = config("constants.OBIETTIVO");

        return view('admin.home')->with("dataView", $dataView);
    }
    
    public function tempiListeAttesa(Request $request)
    {
        $tmpAnno = isset($request->anno) ? $request->anno : date('Y');
        $tmpMeseInizio = isset($request->mese_inizio) ? $request->mese_inizio : 1;
        $tmpMeseFine = isset($request->mese_fine) ? $request->mese_fine : date("m");

        $dataView = $this->initView(1);
        $dataView['anno'] = $tmpAnno;
        $dataView['meseInizio'] = $tmpMeseInizio;
        $dataView['meseFine'] = $tmpMeseFine;
        
        $dataView['strutture'] = Structure::all();
        $numeratori = DB::table('cup_model_target1')
            ->select(
                'structure_id',
                DB::raw('SUM(amount) as totale_quantita'),
                'structures.name'
            )
            ->join("structures", "structures.id", "=", "cup_model_target1.structure_id")
            ->whereYear( 'cup_model_target1.provision_date', $tmpAnno)
            ->whereMonth('cup_model_target1.provision_date', '>=', $tmpMeseInizio)
            ->whereMonth('cup_model_target1.provision_date', '<=', $tmpMeseFine)
            ->groupBy(groups: 'cup_model_target1.structure_id')
            ->get()->keyBy('structure_id')->toArray();

        $denominatoriC = DB::table('flows_c')
            ->select(
                'structure_id',
                DB::raw('COALESCE(SUM(ob1_1), 0) as tot'),
                'structures.name',
            )
            ->join("structures", "structures.id", "=", "flows_c.structure_id")
            ->where( 'year', $tmpAnno)
            ->whereBetween('month', [$tmpMeseInizio, $tmpMeseFine])
            ->groupBy( 'structure_id')
            ->get()->keyBy('structure_id')->toArray();
        $denominatoriM = DB::table('flows_m')
            ->select(
                'structure_id',
                DB::raw('COALESCE(SUM(ob1_1), 0) as tot'),
                'structures.name',
            )
            ->join("structures", "structures.id", "=", "flows_m.structure_id")
            ->where( 'year', $tmpAnno)
            ->whereBetween('month', [$tmpMeseInizio, $tmpMeseFine])
            ->groupBy( 'structure_id')
            ->get()->keyBy('structure_id')->toArray();

        $allStructureIds = array_unique(array_merge(
            array_keys($numeratori),
            array_keys($denominatoriC),
            array_keys($denominatoriM)
        ));

        // Crea un array finale con tutte le strutture e i dati corrispondenti
        $dataView['dati'] = [];
        foreach ($allStructureIds as $structureId) {
            $denominatore = (isset($denominatoriC[$structureId]) ? $denominatoriC[$structureId]->tot : 0) + (isset($denominatoriM[$structureId]) ? $denominatoriM[$structureId]->tot : 0);
            $percentuale = round($numeratori[$structureId]->totale_quantita / $denominatore * 100, 2);
            $dataView['dati'][$structureId] = [
                'structure_id' => $structureId,
                'numeratore' => isset($numeratori[$structureId]) ? $numeratori[$structureId]->totale_quantita : 0,
                'denominatore' => $denominatore,
                'percentuale' => $percentuale,
                'backgroundPerc' => $percentuale >= 100 ? "green" : $this->coloreSfondo($percentuale, 99), 
                'name' => isset($denominatoriC[$structureId]) ? $denominatoriC[$structureId]->name : (isset($denominatoriM[$structureId]) ? $denominatoriM[$structureId]->name : ''),
            ];
        }

        $dataView['tempiListeAttesa'] = $this->showChart("bar", "tempiListeAttesa",
            array_column($dataView['dati'], "name"),
             [
                [
                    "label" => "Numeratore",
                    "backgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "data" => array_column($dataView['dati'], "numeratore")
                ],
                [
                    "label" => "Denominatore",
                    "backgroundColor" => "rgba(255, 99, 132, 0.7)",
                    "data" => array_column($dataView['dati'], "denominatore")
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
            ]);

        $tmpIndicatori2 = DB::table("target1_data")
            ->select("target1_data.*", "structures.name")
            ->join("uploated_files", "uploated_files.id", "=", "target1_data.uploated_file_id")
            ->join("structures", "structures.id", "=", "target1_data.structure_id")
            ->where("target1_data.year", $tmpAnno)
            ->where("uploated_files.approved", 1)
            ->orderBy("structure_id")
            ->get();

        $dataView['indicatori2'] = [];
        foreach($tmpIndicatori2 as $indicatore) {
            $percSpecialista = round(($indicatore->prestazioni_specialista_riferimento - $indicatore->prestazioni_specialista_precedente) / $indicatore->prestazioni_specialista_precedente * 100, 2);
            $percMMG = round(($indicatore->prestazioni_MMG_riferimento - $indicatore->prestazioni_MMG_precedente) / $indicatore->prestazioni_MMG_precedente * 100, 2);
            $dataView['indicatori2'][] = [
                "name" => $indicatore->name, 
                "numero_agende" => $indicatore->numero_agende, 
                "backgroundAgende" => $indicatore->numero_agende > 10 ? "green" : $this->coloreSfondo($indicatore->numero_agende, 11), 
                "prestazioni_specialista_riferimento" => $indicatore->prestazioni_specialista_riferimento, 
                "prestazioni_specialista_precedente" => $indicatore->prestazioni_specialista_precedente, 
                "percentualeSpecialista" => $percSpecialista, 
                "backgroundSpecialista" => $percSpecialista > 10 ? "green" : $this->coloreSfondo($percSpecialista, 11), 
                "prestazioni_MMG_riferimento" => $indicatore->prestazioni_MMG_riferimento, 
                "prestazioni_MMG_precedente" => $indicatore->prestazioni_MMG_precedente, 
                "percentualeMMG" => $percMMG, 
                "backgroundMMG" => $percMMG < 20 ? "green" : "red", 
            ];
        }
               
        return view("admin.tempiListeAttesa")->with("dataView", $dataView);
    }


    public function puntiNascita(Request $request)
    {
        $dataView = $this->initView(obiettivo: 3);
        $dataView['categorie'] = DB::table("target_categories")
            ->where("target_number", 3)
            ->orderBy("order")
            ->get();


        $livelli = DB::table('uploated_files as uf')
            ->join(
                DB::raw('(SELECT structure_id, target_category_id, target_number, MAX(created_at) AS max_created_at
                          FROM uploated_files
                          WHERE approved = 1 AND target_number = 3 
                          GROUP BY structure_id, target_category_id, target_number) as latest_files'),
                function($join) {
                    $join->on('uf.structure_id', '=', 'latest_files.structure_id')
                         ->on('uf.target_category_id', '=', 'latest_files.target_category_id')
                         ->on('uf.target_number', '=', 'latest_files.target_number')
                         ->on('uf.created_at', '=', 'latest_files.max_created_at');
                }
            )
            ->join("target3_data", "target3_data.uploated_file_id", "=", "uf.id")
            ->join("structures", "structures.id", "=", "uf.structure_id")
            ->join("target_categories", "target_categories.id", "=", "uf.target_category_id")
            ->where('uf.approved', 1)
            ->where('uf.target_number', 3)
            ->select('uf.structure_id', DB::raw("sum(target3_data.numerator) as numerator"), DB::raw("sum(target3_data.denominator) as denominator"), "structures.name")
            ->groupby("uf.structure_id")
            ->get();

        $dataView['livelli'] = [];
        foreach($livelli as $livello) {
            $perc = round($livello->numerator / $livello->denominator * 100, 2);
            if ($perc >= 100) {
                $color = "Green";
            } elseif ($perc >= 90 && $perc < 100) {
                $color = "#59721C";
            } elseif ($perc >= 75 && $perc < 90) {
                $color = "#ffd700"; // giallo
            } else {
                $color = "red";
            }
            $dataView['livelli'][] = [
                "structure_id" => $livello->structure_id,
                "numerator" => $livello->numerator,
                "denominator" => $livello->denominator,
                "name" => $livello->name,
                "percentage" => $perc,
                "backgroundLiv" => $color
            ];
        }



        $dataView['obiettivo'] = 3;

        return view("admin.showFormObiettivo")->with("dataView", $dataView);
    }


    public function prontoSoccorso(Request $request)
    {
        $dataView = $this->initView(obiettivo: 4);

        $dataView['anno'] = isset($request->year) ? $request->year : date('Y');

        $flowEmur = DB::table('flows_emur as fe')
            ->join('structures as s', 'fe.structure_id', '=', 's.id')
            ->select(DB::raw('avg(fe.tmp) as tmp'), DB::raw('avg(fe.boarding) as boarding'), "s.name")            
            ->where("fe.year", $dataView['anno'])
            ->groupby("fe.structure_id")
            ->get();

        $dataView['flowEmur'] = [];
        foreach($flowEmur as $row) {
            if($row->tmp >= 85)
                $tmpColor = "green";
            elseif($row->tmp >= 75 && $row->tmp < 85)
                $tmpColor = "#59721C";
            else
                $tmpColor = "red";
            if($row->boarding <= 2)
                $boardingColor = "green";
            elseif($row->boarding >= 2 && $row->boarding < 4)
                $boardingColor = "#59721C";
            else
                $boardingColor = "red";
            $dataView['flowEmur'][] = [
                "tmp" => round($row->tmp, 2),
                "boarding" => round($row->boarding, 2),
                "name" => $row->name,
                "tmpColor" => $tmpColor,
                "boardingColor" => $boardingColor,
            ];
        }

        $flowMonths = DB::table('flows_emur as fe')
            ->join('structures as s', 'fe.structure_id', '=', 's.id')
            ->select('fe.tmp', 'fe.boarding', "fe.month", "s.name", 'fe.structure_id', "fe.year")            
            ->where("fe.year", $dataView['anno'])
            //->where("fe.structure_id", 8)
            ->orderby("fe.month")
            ->orderby("fe.structure_id")
            ->get();
//dd($flowMonths);
        $months = [];
        for($i = 1; $i <= 12; $i++)
            $months[$i-1] = str_pad($i, 2, '0', STR_PAD_LEFT). "/" . $dataView['anno'];
        $structureTmp = [];
        $structureBoarding = [];
        foreach ($flowMonths as $row) {
            if (!isset($structureTmp[$row->structure_id])) {
                $structureTmp[$row->structure_id] = [
                    'label' => $row->name,
                    'data' => array_fill(0, 12, 0),
                ];
            }
            if (!isset($structureBoarding[$row->structure_id])) {
                $structureBoarding[$row->structure_id] = [
                    'label' => $row->name,
                    'data' => array_fill(0, 12, 0),
                ];
            }
       
            $structureTmp[$row->structure_id]['data'][$row->month-1] = $row->tmp;
            $structureBoarding[$row->structure_id]['data'][$row->month-1] = $row->boarding;
        }
        $dataView['lineChartTmp'] = $this->showChart("line", "SovraffollamentoPS_Tmp"
        , $months //$flowMonths->pluck("month")->toArray() // labels
        , array_values($structureTmp)
        , [
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
                            'text' => 'TMP'
                        ]
                    ],
                ],

            ]
        ] // options
        );
        $dataView['lineChartBoarding'] = $this->showChart("line", "SovraffollamentoPS_Boarding"
        , $months 
        , array_values($structureBoarding)
        , [
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
                            'text' => 'Boarding'
                        ]
                    ],
                ],

            ]
        ] // options
        );
        return view("admin/prontoSoccorso", ['dataView' => $dataView]);

    }


    public function screening(Request $request)
    {
        $dataView = $this->initView(obiettivo: 5);

        $dataView['anno'] = isset($request->year) ? $request->year : date('Y');

        $idR = DB::table(table: "target5_data")
            ->join("structures as s", "s.id", "=", "target5_data.structure_id")
            ->select("month", "structure_id", "s.name", "mammografico", "cercocarcinoma as cervicocarcinoma", "colonretto")
            ->where("year", $dataView['anno'])
            ->orderby("month")
            ->get();

        $structureData = [];
        $months = [];
        for($i = 1; $i <= 12; $i++)
            $months[$i-1] = str_pad($i, 2, '0', STR_PAD_LEFT). "/" . $dataView['anno'];

        foreach ($idR as $row) {

            if (!isset($structureData[$row->structure_id])) {
                $structureData[$row->structure_id] = [
                    'name' => $row->name,
                    'mammografico' => array_fill(0, 12, 0),
                    'cervicocarcinoma' => array_fill(0, 12, 0),
                    'colonretto' => array_fill(0, 12, 0),
                ];
                $dataView["colori"][$row->structure_id] = [
                    'mammografico' => array_fill(0, 12, "red"),
                    'cervicocarcinoma' => array_fill(0, 12, "red"),
                    'colonretto' => array_fill(0, 12, "red"),
                ];
            }    
            $structureData[$row->structure_id]['mammografico'][$row->month-1] = $row->mammografico;
            $dataView["colori"][$row->structure_id]['mammografico'][$row->month-1] = ($row->mammografico >= 60) ? "green" : (($row->mammografico > 35) ? $this->coloreSfondo( $row->mammografico, 100) : "red");
            $structureData[$row->structure_id]['cervicocarcinoma'][$row->month-1] = $row->cervicocarcinoma;
            $dataView["colori"][$row->structure_id]['cervicocarcinoma'][$row->month-1] = ($row->cervicocarcinoma >= 50) ? "green" : (($row->cervicocarcinoma > 25) ? $this->coloreSfondo( $row->cervicocarcinoma, 100) : "red");
            $structureData[$row->structure_id]['colonretto'][$row->month-1] = $row->colonretto;
            $dataView["colori"][$row->structure_id]['colonretto'][$row->month-1] = ($row->colonretto >= 50) ? "green" : (($row->colonretto > 25) ? $this->coloreSfondo( $row->colonretto, 100) : "red");
        }    

        $datasets = [
            'mammografico' => [],
            'cervicocarcinoma' => [],
            'colonretto' => []
        ];
        foreach ($structureData as $data) {
            // Aggiungi i dati per ogni serie (mammografico, cervicocarcinoma, colonretto)
            $datasets['mammografico'][] = [
                'label' => $data['name'],
                'data' => $data['mammografico'],
            ];
        
            $datasets['cervicocarcinoma'][] = [
                'label' => $data['name'],
                'data' => $data['cervicocarcinoma'],
            ];
        
            $datasets['colonretto'][] = [
                'label' => $data['name'],
                'data' => $data['colonretto'],
            ];
        }

        $dataView['lineChartMammografico'] = $this->showChart("line", "IndicatoreMammograficoTarget5"
        , $months
        , $datasets['mammografico']
        , [
            'responsive' => true,
            'plugins' => [
                'title'=> [
                    'display' => true,
                    'text' => 'Mammografico'
                ],
                'scales' => [
                    "x" => [
                        "stacked" => true,
                        "title" => [
                            'display' => true,
                            'text' => 'Mese'
                        ]
                    ],
                    "y" => [
                        "stacked" => true,
                        "title" => [
                            'display' => true,
                            'text' => 'Indicatore LEA %'
                        ]
                    ],
                ],
            ],
        ]);
        $dataView['lineChartCervicocarcinoma'] = $this->showChart("line", "IndicatoreCervicocarcinomaTarget5"
        , $months
        , $datasets['cervicocarcinoma']
        , [
            'responsive' => true,
            'plugins' => [
                'title'=> [
                    'display' => true,
                    'text' => 'Cervicocarcinoma'
                ],
                'scales' => [
                    "x" => [
                        "stacked" => true,
                        "title" => [
                            'display' => true,
                            'text' => 'Mese'
                        ]
                    ],
                    "y" => [
                        "stacked" => true,
                        "title" => [
                            'display' => true,
                            'text' => 'Indicatore LEA %'
                        ]
                    ],
                ],
            ],
        ]);
        $dataView['lineChartColonretto'] = $this->showChart("line", "IndicatoreColonrettoTarget5"
        , $months
        , $datasets['colonretto']
        , [
            'responsive' => true,
            'plugins' => [
                'title'=> [
                    'display' => true,
                    'text' => 'Colonretto'
                ],
                'scales' => [
                    "x" => [
                        "stacked" => true,
                        "title" => [
                            'display' => true,
                            'text' => 'Mese'
                        ]
                    ],
                    "y" => [
                        "stacked" => true,
                        "title" => [
                            'display' => true,
                            'text' => 'Indicatore LEA %'
                        ]
                    ],
                ],
            ],
        ]);
        $dataView['indicatoriRisultato'] = $structureData;

        /***************************MMG Coinvolti ****************************/
        $tableData = DB::table('insert_mmg as mmg')
            ->join('structures as s', 'mmg.structure_id', '=', second: 's.id')
            ->select('mmg.mmg_totale', 'mmg.mmg_coinvolti', 'mmg.year', 'mmg.structure_id', 's.name as nome_struttura')
            ->where("year", $dataView['anno'])
            ->get();
        $dataView['mmg'] = [];
        foreach($tableData as $row) {
            $perc = ($row->mmg_totale != 0) ? round($row->mmg_coinvolti / $row->mmg_totale * 100, 2) : 0;
            $dataView['mmg'][$row->structure_id] = [
                "mmg_totale" => $row->mmg_totale,
                "mmg_coinvolti" => $row->mmg_coinvolti,
                "nome_struttura" => $row->nome_struttura,
                "percentuale" => $perc,
                'backgroundMMG' => ($perc >= 60) ? "green" : (($perc > 20) ?  $this->coloreSfondo($perc, 100) : "red")
            ];            
        }
        $dataView['mmgCoinvolti'] = $this->showChart("bar", "MMGCoinvolti"
        , array_column($dataView['mmg'], "nome_struttura")
        , [
            [
                "label" => "Numeratore",
                "data" => array_column($dataView['mmg'], "percentuale")
            ]
        ]
        , [
            'responsive' => true,
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Percentuale MMG coinvolti'
                ]
            ]
        ]);


        /***************************Prestazioni inappropriate ****************************/
        $datiFlussoM = DB::table('flows_m')
            ->join('structures as s', 'flows_m.structure_id', '=', second: 's.id')
            ->select(DB::raw('sum(ob5_num) as numeratore_m'), DB::raw('sum(ob5_den) as denominatore_m'), 's.name', 'flows_m.structure_id')
            ->where("year", $dataView['anno'])
            ->groupBy("flows_m.structure_id")
            ->get();
        $dataView['prestazioniInappropriate'] = [];
        foreach($datiFlussoM as $m) {
            if(!isset($dataView['prestazioniInappropriate'][$m->structure_id])) {
                $perc = ($m->denominatore_m != 0) ? round($m->numeratore_m / $m->denominatore_m * 100, 2) : 0;
                $dataView['prestazioniInappropriate'][$m->structure_id] = [
                    'nome_struttura' => $m->name,
                    'numeratore_totale' => $m->numeratore_m,
                    'denominatore_totale' => $m->denominatore_m,
                    'percentuale' => $perc,
                    'backgroundInappropriate' => ($perc <= 10) ? "green" : $this->coloreSfondo($perc, 100),

                ];
            } else {
                $totNumeratore = $dataView['prestazioniInappropriate'][$m->structure_id]['numeratore_totale'] + $m->numeratore_m;
                $totDenominatore = $dataView['prestazioniInappropriate'][$m->structure_id]['denominatore_totale'] + $m->denominatore_m;
                $perc = ($totDenominatore != 0) ? round($totNumeratore / $totDenominatore * 100, 2) : 0;

                $dataView['prestazioniInappropriate'][$m->structure_id] = [
                    'nome_struttura' => $m->name,
                    'numeratore_totale' => $totNumeratore,
                    'denominatore_totale' => $totDenominatore,
                    'percentuale' => $perc,
                    'backgroundInappropriate' => ($perc <= 10) ? "green" : $this->coloreSfondo($perc, 100),    

                ];
            }
        }
        $datiFlussoC = DB::table('flows_c')
            ->join('structures as s', 'flows_c.structure_id', '=', second: 's.id')
            ->select(DB::raw('sum(ob5_num) as numeratore_c'), DB::raw('sum(ob5_den) as denominatore_c'), 's.name', 'flows_c.structure_id')
            ->where("year", $dataView['anno'])
            ->groupBy("flows_c.structure_id")
            ->get();
        foreach($datiFlussoC as $c) {
            if(!isset($dataView['prestazioniInappropriate'][$c->structure_id])) {
                $perc = ($c->denominatore_c != 0) ? round($c->numeratore_c / $c->denominatore_c * 100, 2) : 0;
                $dataView['prestazioniInappropriate'][$c->structure_id] = [
                    'nome_struttura' => $c->name,
                    'numeratore_totale' => $c->numeratore_c,
                    'denominatore_totale' => $c->denominatore_c,
                    'percentuale' => $perc,
                    'backgroundInappropriate' => ($perc <= 10) ? "green" : $this->coloreSfondo($perc, 100),    

                ];
            } else {
                $totNumeratore = $dataView['prestazioniInappropriate'][$c->structure_id]['numeratore_totale'] + $c->numeratore_c;
                $totDenominatore = $dataView['prestazioniInappropriate'][$c->structure_id]['denominatore_totale'] + $c->denominatore_c;
                $perc = ($totDenominatore != 0) ? round($totNumeratore / $totDenominatore * 100, 2) : 0;

                $dataView['prestazioniInappropriate'][$c->structure_id] = [
                    'nome_struttura' => $c->name,
                    'numeratore_totale' => $totNumeratore,
                    'denominatore_totale' => $totDenominatore,
                    'percentuale' => $perc,
                    'backgroundInappropriate' => ($perc <= 10) ? "green" : $this->coloreSfondo($perc, 100),    

                ];
            }
        }
        $dataView['prestazioniInappropriateChart'] = $this->showChart("bar", "prestazioniInappropriateChart"
        , array_column($dataView['prestazioniInappropriate'], "nome_struttura")
        , [
            [
                "label" => "Inappropriatezza",
                "data" => array_column($dataView['prestazioniInappropriate'], "percentuale")
            ]
        ]
        , [
            'responsive' => true,
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Percentuale MMG coinvolti'
                ]
            ]
        ]);


        /***************************Formazione del personale ****************************/
        $categorieCaricate = DB::table('uploated_files as uf')
        ->join(
            DB::raw('(SELECT structure_id, target_category_id, target_number, MAX(created_at) AS max_created_at
                      FROM uploated_files
                      WHERE approved = 1 AND target_number = 5 AND year=' . $dataView['anno'] . '
                      GROUP BY structure_id, target_category_id, target_number) as latest_files'),
            function($join) {
                $join->on('uf.structure_id', '=', 'latest_files.structure_id')
                     ->on('uf.target_category_id', '=', 'latest_files.target_category_id')
                     ->on('uf.target_number', '=', 'latest_files.target_number')
                     ->on('uf.created_at', '=', 'latest_files.max_created_at');
            }
        )
        ->join('structures as s', 'uf.structure_id', '=', 's.id')
        ->join("target_categories", "target_categories.id", "=", "uf.target_category_id")
        ->select(
            
            'uf.structure_id',
            'uf.target_category_id',
            's.name as structure_name'
        )
        ->groupBy('uf.structure_id', 'uf.target_category_id') 
        ->orderBy('uf.created_at', 'desc') // Ordina per la data più recente
        ->get();
        
        $dataView['files'] = [];
        $sottoCategorie = DB::table("target_categories")->where("target_number", 5)->pluck("id");
        foreach(DB::table("structures")->select("id", "name")->get() as $struttura)
            foreach($sottoCategorie as $subCategoria)
            $dataView['files'][$struttura->name][$subCategoria] = 0;
        
        foreach($categorieCaricate as $row) {
            $dataView['files'][$row->structure_name][$row->target_category_id] = 1;
        }
        
        return view("admin.screening")->with("dataView", $dataView);
    }

    public function donazioni(Request $request) {
        $dataView = $this->initView(6);

        $dataView['annoSelezionato'] = $request->annoSelezionato ?? date('Y');

        $categorieCaricate =  DB::table('uploated_files as uf')
        ->join(
            DB::raw('(SELECT structure_id, target_category_id, target_number, MAX(created_at) AS max_created_at
                      FROM uploated_files
                      WHERE approved = 1 AND target_number = 6 AND year=' . $dataView['annoSelezionato'] . '
                      GROUP BY structure_id, target_category_id, target_number) as latest_files'),
            function($join) {
                $join->on('uf.structure_id', '=', 'latest_files.structure_id')
                     ->on('uf.target_category_id', '=', 'latest_files.target_category_id')
                     ->on('uf.target_number', '=', 'latest_files.target_number')
                     ->on('uf.created_at', '=', 'latest_files.max_created_at');
            }
        )
        ->join('structures as s', 'uf.structure_id', '=', 's.id')
        ->join("target_categories", "target_categories.id", "=", "uf.target_category_id")
        ->select(            
            'uf.structure_id',
            'uf.target_category_id',
            's.name as structure_name'
        )
        ->groupBy('uf.structure_id', 'uf.target_category_id') 
        ->orderBy('uf.created_at', 'desc')
        ->get();

        $dataView['files'] = [];
        $sottoCategorie = DB::table("target_categories")->where("target_number", 6)->pluck("id");
        foreach(DB::table("structures")->select("id", "name")->get() as $struttura)
            foreach($sottoCategorie as $subCategoria)
                $dataView['files'][$struttura->name][$subCategoria] = 0;
        
        foreach($categorieCaricate as $row) {
            $dataView['files'][$row->structure_name][$row->target_category_id] = 1;
        }


        $dataView['tableData'] = DB::table('target6_data')
        ->select('totale_accertamenti', 'numero_opposti','totale_cornee', 'anno', 'structure_id', 's.name')
        ->join('structures as s', 'target6_data.structure_id', '=', 's.id')
        ->orderBy("name")
        ->orderBy("anno")
        ->get();

        //Denominatore preso dal flusso
        $denominatore = DB::table('flows_sdo as f')
        ->join(
            DB::raw('(SELECT structure_id, year, MAX(created_at) AS latest_created_at FROM flows_sdo GROUP BY structure_id, year) as latest'),
            function ($join) {
                $join->on('f.structure_id', '=', 'latest.structure_id')
                     ->on('f.year', '=', 'latest.year')
                     ->on('f.created_at', '=', 'latest.latest_created_at');
            })
        ->select('f.structure_id', 'f.year', 'f.ob6', 'f.created_at')
        ->get();

        $dataView['result'] = [];
        $incrementi = [];
        $percentualiAnnoSelezionato = [];
        $percentualiCorneeAnnoSelezionato = [];
        // Calcolo la percentuale per ogni anno
        foreach ($dataView['tableData'] as $target) {
            
            $denominatoreTmp = $denominatore->firstWhere('year',  $target->anno);
            if($target->anno == 2023) {
                $accertamenti2023 = $target->totale_accertamenti;
                $cornee2023 = $target->totale_cornee;
            }
            $percentualeAccertamenti = ($denominatoreTmp->ob6 != 0) ? round(($target->totale_accertamenti / $denominatoreTmp->ob6) * 100, 2) : 0;
            $percentualeCornee = ($denominatoreTmp->ob6 != 0) ? round(($target->totale_cornee / $denominatoreTmp->ob6) * 100, 2) : 0;
            $percentualeOpposizioni = ($target->totale_accertamenti != 0) ? round(($target->numero_opposti / $target->totale_accertamenti) * 100, 2) : 0;
            $incrAccertamenti = ($target->anno > 2023 && $accertamenti2023 != 0) ? round((($target->totale_accertamenti - $accertamenti2023) / $accertamenti2023) * 100, 2): 0;
            $incrementoCornee = ($target->anno > 2023 && $cornee2023 != 0) ? round((($target->totale_cornee - $cornee2023) / $cornee2023) * 100, 2): 0;
            $dataView['result'][] = [
                'nome_struttura' => $target->name,
                'anno' => $target->anno,
                'percentualeAccertamenti' => $percentualeAccertamenti,
                'percentualeCornee' => $percentualeCornee,
                'percentualeOpposizioni' => $percentualeOpposizioni,
                'totale_accertamenti' => $target->totale_accertamenti,
                'numero_opposti' => $target->numero_opposti,
                'totale_cornee' => $target->totale_cornee,
                'denominatore' => $denominatoreTmp->ob6,
                'incrementoAccertamenti' => $incrAccertamenti,
                'incrementoCornee' => $incrementoCornee,
            ];
            $incrementi[$target->name] = [
                $target->anno => $incrAccertamenti
            ];
            if($target->anno == $dataView['annoSelezionato']) {
                $percentualiAnnoSelezionato[] = [
                    'nome_struttura' => $target->name,
                    'percentuale' => $percentualeOpposizioni
                ];
                $percentualiCorneeAnnoSelezionato[] = [
                    'nome_struttura' => $target->name,
                    'percentuale' => $incrementoCornee
                ];

            }
        }
        $dataset = [];
        foreach ($incrementi as $nome_struttura => $data) {
            $dataset[] = [
                'label' => $nome_struttura,
                'data' => [
                    isset($data['2023']) ? $data['2023'] : 0,  
                    isset($data['2024']) ? $data['2024'] : 0,
                ],
            ];
        }
        //grafico Sub.2
        $dataView['chartDonazioni'] = $this->showChart("line", "chartDonazioni",
        [2023, 2024],
            $dataset
        ,[]);

        /***************************Chart sub. 3************************************ */


        //grafico sub.3
        $dataView['chartSubObiettivo3'] = $this->showChart("bar", "chartSubObiettivo3",
        array_column( $percentualiAnnoSelezionato, 'nome_struttura'),
        [
                [
                    "label" => "Percentuale",
                    "data" => array_column( $percentualiAnnoSelezionato, 'percentuale')
                ]
            ],
            [
                
            ]
            );


        /******************************Sub.ob 4************************************ */

        //grafico sub.4
        $dataView['chartSubObiettivo4'] = $this->showChart("bar", "chartSubObiettivo4",
        array_column( $percentualiCorneeAnnoSelezionato, 'nome_struttura'),
        [
            [
                "label" => "Percentuale di incremento",
                "data" => array_column( $percentualiCorneeAnnoSelezionato, 'percentuale')
            ]
        ],
            [ ]);


        return view("admin.donazioni")->with('dataView', $dataView);

    }

    protected function chartFSE($name, $labels, $data, $title) {
        return $this->showChart("bar", $name,
            $labels,
            [
                [
                    "label" => $title,
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

    public function fse(Request $request)
    {
        $dataSelezionata = $request->annoSelezionato ?? date('Y');

        $dataView = $this->initView(7);

        /*****************************Dimissioni Ospedaliere**********************************/
        $prevenzioneTre = DB::table('target7_data')
            ->where('anno', "=",  $dataSelezionata)
            ->join('structures as s', 'target7_data.structure_id', '=', 's.id')
            ->get();


        $percentualePS = [];
        $percentualeDimissioniOspedaliere = [];
        $percentualePrestazioneLab = [];
        $percentualeRefRadiologia = [];
        $percentualeAmbulatoriale = [];
        $percentualeVaccinati = [];
        $percentualePerstazioneErogate = [];
        $percentualeDocumentiCDA2 = [];
        $percentualeDocumentiPades = [];
        foreach($prevenzioneTre as $row) {
            // Estrai i dati del denominatore della struttura e dell'anno
            $denominatore = DB::table('flows_sdo')
                ->join('structures as s', 'flows_sdo.structure_id', '=', 's.id')
                ->where('year',  $dataSelezionata)
                ->where('structure_id',  $row->structure_id) 
                ->select('ob7_1')
                ->orderByDesc('month')
                ->first();
            $ob7PS = DB::table('flows_emur')
                ->join('structures as s', 'flows_emur.structure_id', '=', 's.id')
                ->where('structure_id',  $row->structure_id) 
                ->where('year', "=",  $dataSelezionata)
                ->sum('ia1_2');
            $denFlussoC = DB::table('flows_c')
                ->select(DB::raw('sum(ia1_3) as ia1_3'), DB::raw('sum(ia1_4) as ia1_4'), DB::raw('sum(ia1_5) as ia1_5'), DB::raw('sum(ia1_6) as ia1_6'))
                ->where('year', "=", $dataSelezionata)
                ->where('structure_id',  $row->structure_id) 
                ->join('structures as s', 'flows_c.structure_id', '=', 's.id')
                ->first();


            $documentiCDA2 = isset($row->documenti_cda2) ? $row->documenti_cda2 : 0;
            $percentualePSVal = $ob7PS != 0 ? round($row->dimissioni_ps / $ob7PS  * 100, 2) : 0;
            $percDimizzioniOspedaliere = ($denominatore->ob7_1 != 0) ? round(($row->dimissioni_ospedaliere / $denominatore->ob7_1) * 100, 2) : 0;
            $percPrestLab = $denFlussoC->ia1_3 != 0 ? round($row->prestazioni_laboratorio / $denFlussoC->ia1_3  * 100, 2) : 0;
            $percRefRadiologia = $denFlussoC->ia1_4 != 0 ? round($row->prestazioni_radiologia / $denFlussoC->ia1_4 * 100, 2) : 0;
            $percAmbulatoriale = $denFlussoC->ia1_5 != 0 ? round($row->prestazioni_ambulatoriali / $denFlussoC->ia1_5 * 100, 2) : 0;
            $percVaccinati = $row->vaccinati != 0 ? round($row->certificati_indicizzati / $row->vaccinati * 100, 2) : 0;
            $percPrestErogate = $denFlussoC->ia1_6 != 0 ? round($row->documenti_indicizzati / $denFlussoC->ia1_6 * 100, 2) : 0;
            $percDocumentiCDA2 = $row->documenti_indicizzati_cda2 != 0 ? round($documentiCDA2 / $row->documenti_indicizzati_cda2 * 100, 2) : 0;
            $percDocumentiPades = $row->documenti_indicizzati_pades != 0 ? round($row->documenti_pades / $row->documenti_indicizzati_pades * 100, 2) : 0;

            //numeratori
            $dataView['strutture'][$row->structure_id] = [
                'nome_struttura' => $row->name,
                'dimissioniOspedaliere' => isset($row->dimissioni_ospedaliere) ? $row->dimissioni_ospedaliere : 0,
                'dimissioniPS' => isset($row->dimissioni_ps) ? $row->dimissioni_ps : 0,
                'prestazioniLab' => isset($row->prestazioni_laboratorio) ? $row->prestazioni_laboratorio : 0,
                'prestazioniRadiologia' => isset($row->prestazioni_radiologia) ? $row->prestazioni_radiologia : 0,
                'specialisticaAmbulatoriale' => isset($row->prestazioni_ambulatoriali) ? $row->prestazioni_ambulatoriali : 0,
                'vaccinati' => isset($row->vaccinati) ? $row->vaccinati : 0,
                'certificatiIndicizzati' => isset($row->certificati_indicizzati) ? $row->certificati_indicizzati : 0,
                'documentiIndicizzati' => isset($row->documenti_indicizzati) ? $row->documenti_indicizzati : 0,
                'documentiIndicizzatiCDA2' => isset($row->documenti_indicizzati_cda2) ? $row->documenti_indicizzati_cda2 : 0,
                'documentiCDA2' => $documentiCDA2,
                'documentiPades' => isset($row->documenti_pades) ? $row->documenti_pades : 0,
                'documentiIndicizzatiPades' => isset($row->documenti_indicizzati_pades) ? $row->documenti_indicizzati_pades : 0,

                'ob7' => $denominatore->ob7_1,
                'ob7PS' => $ob7PS,
                'ia13' => $denFlussoC->ia1_3,
                'ia14' => $denFlussoC->ia1_4,
                'ia15' => $denFlussoC->ia1_5,                
                'ia16' => $denFlussoC->ia1_6,                
                'percentualePS' => $percentualePSVal,
                'percentualeDimissioniOspedaliere' => $percDimizzioniOspedaliere,
                'percentualePrestLab' => $percPrestLab,
                'percentualeRefRadiologia' => $percRefRadiologia,
                'percentualeAmbulatoriale' => $percAmbulatoriale,
                'percentualeVaccinati' => $percVaccinati,
                'percentualePrestErogate' => $percPrestErogate,
                'percentualeDocumentiCDA2' => $percDocumentiCDA2,
                'percentualeDocumentiPades' => $percDocumentiPades,
            ];
            $percentualePS[] = [
                'nome_struttura' => $row->name,
                'percentuale' => $percentualePSVal
            ];
            $percentualeDimissioniOspedaliere[] = [
                'nome_struttura' => $row->name,
                'percentuale' => $percDimizzioniOspedaliere
            ];
            $percentualePrestazioneLab[] = [
                'nome_struttura' => $row->name,
                'percentuale' => $percPrestLab
            ];
            $percentualeRefRadiologia[] = [
                'nome_struttura' => $row->name,
                'percentuale' => $percRefRadiologia
            ];
            $percentualeAmbulatoriale[] = [
                'nome_struttura' => $row->name,
                'percentuale' => $percAmbulatoriale
            ];
            $percentualeVaccinati[] = [
                'nome_struttura' => $row->name,
                'percentuale' => $percVaccinati
            ];
            $percentualePerstazioneErogate[] = [
                'nome_struttura' => $row->name,
                'percentuale' => $percPrestErogate
            ];
            $percentualeDocumentiCDA2[] = [
                'nome_struttura' => $row->name,
                'percentuale' => $percDocumentiCDA2
            ];
            $percentualeDocumentiPades[] = [
                'nome_struttura' => $row->name,
                'percentuale' => $percDocumentiPades
            ];

        }


        $dataView['chartDimissioniOspedaliere'] = $this->chartFSE("chartDimissioniOspedaliere",
        array_column($percentualeDimissioniOspedaliere, "nome_struttura"), array_column($percentualeDimissioniOspedaliere, "percentuale"),"% LDO Indicizzate"
        );

        /*****************************Dimissioni Pronto Soccorso****************************************************/
        $dataView['chartProntoSoccorso'] = $this->chartFSE( "chartProntoSoccorso",
        array_column($percentualePS, "nome_struttura"), array_column($percentualePS, "percentuale"),"% Verbali Indicizzati"
        );

        /*********************Prestazioni di Laboratorio****************************** */
        $dataView['chartRefertiLaboratorio'] = $this->chartFSE( "chartRefertiLaboratorio",
            array_column($percentualePrestazioneLab, "nome_struttura"), array_column($percentualePrestazioneLab, "percentuale"),"% Referti Indicizzati"
        );

        /*********************Ref radiologia*********************************************************** */
        $dataView['chartRefertiRadiologia'] = $this->chartFSE("chartRefertiRadiologia",
        array_column($percentualeRefRadiologia, "nome_struttura"), array_column($percentualeRefRadiologia, "percentuale"),"% Referti Indicizzati"
        );

        /**********************Specialistica Ambulatoriale**********************************************************/
        $dataView['chartSpecialisticaAmbulatoriale'] = $this->chartFSE( "chartSpecialisticaAmbulatoriale",        
        array_column($percentualeAmbulatoriale, "nome_struttura"), array_column($percentualeAmbulatoriale, "percentuale"),"% Referti Indicizzati"
        );

        /****************************Vaccinati****************************************************** */
        $dataView['chartCertificatiVaccinali'] = $this->chartFSE( "chartCertificatiVaccinali",
        array_column($percentualeVaccinati, "nome_struttura"), array_column($percentualeVaccinati, "percentuale"),"% Certificati Indicizzati"
        );

        /**************************Documentazione FSE************************************************************ */
        $dataView['chartDocumentiFSE'] = $this->chartFSE( "chartDocumentiFSE",
        array_column($percentualePerstazioneErogate, "nome_struttura"), array_column($percentualePerstazioneErogate, "percentuale"),"% Documenti Indicizzati"
        );

        /***************************Documenti in CDA2************************************************************* */
        $dataView['chartDocumentiCDA2'] = $this->chartFSE( "chartDocumentiCDA2",
        array_column($percentualeDocumentiCDA2, "nome_struttura"), array_column($percentualeDocumentiCDA2, "percentuale"),"% Verbali Indicizzati"
        );

        /***************************Documenti Pades************************************************************ */
        $dataView['chartDocumentiPades'] = $this->chartFSE( "chartDocumentiPades",
        array_column($percentualeDocumentiPades, "nome_struttura"), array_column($percentualeDocumentiPades, "percentuale"),"% Verbali Indicizzati"
        );

        return view("admin.fse")->with("dataView", $dataView);
    }

    public function certificabilita(Request $request) {
        $dataView = $this->initView(obiettivo: 8);
        $dataView['files'] = [];
        $sottoCategorie = DB::table("target_categories")->where("target_number", 8)->pluck("id");
        foreach(DB::table("structures")->select("id", "name")->get() as $struttura)
            foreach($sottoCategorie as $subCategoria)
                $dataView['files'][$struttura->name][$subCategoria] = 0;
        
        $files = DB::table('uploated_files as uf')
            ->join(
                DB::raw('(SELECT structure_id, target_category_id, target_number, MAX(created_at) AS max_created_at
                          FROM uploated_files
                          WHERE approved = 1 AND target_number = 8 
                          GROUP BY structure_id, target_category_id, target_number) as latest_files'),
                function($join) {
                    $join->on('uf.structure_id', '=', 'latest_files.structure_id')
                         ->on('uf.target_category_id', '=', 'latest_files.target_category_id')
                         ->on('uf.target_number', '=', 'latest_files.target_number')
                         ->on('uf.created_at', '=', 'latest_files.max_created_at');
                }
            )
            ->join("structures", "structures.id", "=", "uf.structure_id")
            ->join("target_categories", "target_categories.id", "=", "uf.target_category_id")
            ->select('uf.structure_id', "structures.name", "uf.target_category_id")
            ->get();

        foreach($files as $row) {
            $dataView['files'][$row->name][$row->target_category_id] = 1;
        }

        return view("admin.certificabilita")->with("dataView", $dataView);
    }

    public function farmaci(Request $request) {
        $dataView = $this->initView( 9);

        $anno = $request->has("year") ? $request->year : date('Y');
        $dataView['strutture'] = Auth::user()->structures();
        $dataView['annoSelezionato'] = $anno;

        $dataView['anni'] = DB::table('flows_sdo')
            ->distinct()
            ->pluck("year");

        $gare = DB::table('target9_gare as t')
            ->join("structures", "structures.id", "=", "t.structure_id")
            ->leftJoin('uploated_files as uf1', 't.uploated_file_gara_id', '=', 'uf1.id')
            ->leftJoin('uploated_files as uf2', 't.uploated_file_delibera_id', '=', 'uf2.id')
            ->select(
                'structures.name',
                DB::raw('COUNT(DISTINCT CASE WHEN uf1.approved = 1 THEN uf1.id END) AS gare_approvate'),
                DB::raw('COUNT(DISTINCT CASE WHEN uf2.approved = 1 THEN uf2.id END) AS delibere_approvate')
            )
            ->where('t.year', $anno)
            ->groupBy('structures.name')
            ->get();

        $dataView['gare'] = [];
        $dataset = [];
        foreach($gare as $struttura) {
            $rapporto = $struttura->gare_approvate > 0 ? ($struttura->delibere_approvate / $struttura->gare_approvate * 100) : 0;

            $dataView['gare'][$struttura->name] = [
                'gare_approvate' => $struttura->gare_approvate,
                'delibere_approvate' => $struttura->delibere_approvate,
                'rapporto' => $rapporto,
            ];

            $dataset[] = [
                "label" => $struttura->name,
                "data" => [$rapporto]
            ];
        }

        $pct = DB::table('target9_PCT as t')
            ->join('uploated_files as uf', 't.uploated_file_id', '=', 'uf.id')
            ->join("structures", "structures.id", "=", "uf.structure_id")
            ->select('structures.name', 'uf.structure_id', 't.numerator')
            ->where('uf.approved', 1)
            ->where('t.year', $anno)
            ->whereIn('t.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('target9_PCT')
                    ->groupBy('structure_id');
            })
            ->get();

        $denominatori = DB::table("flows_sdo")
            ->join("structures", "structures.id", "=", "flows_sdo.structure_id")
            ->where("flows_sdo.year", $anno)
            ->select('flows_sdo.structure_id', DB::raw("sum(flows_sdo.ob9_2) as ob9_2"))
            ->groupBy('flows_sdo.structure_id')
            ->get()->pluck('ob9_2', 'structure_id')->toArray();        

        $dataView['pct'] = [];
        $dataset2 = [];
        foreach($pct as $row) {
            if(isset($denominatori[$row->structure_id])) {
                $rapporto = $denominatori[$row->structure_id] != 0 ? round($row->numerator / $denominatori[$row->structure_id] * 100, 2) : 0;
                $dataView['pct'][$row->name] = [
                    'numeratore' => $row->numerator,
                    'denominatore' => $denominatori[$row->structure_id],
                    'rapporto' => $rapporto
                ];
            }
            $dataset2[] = [
                "label" => $row->name,
                "data" => [$rapporto]
            ];
        }

        $dataView['chart91'] = $this->showChart("bar", "chart91",
            ["Percentuali"],
            $dataset,
            [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        //'text' => ''
                    ]
                ]
            ]
        );
        $dataView['chart92'] = $this->showChart("bar", "chart92",
            ["Percentuali"],
            $dataset2,
            [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        //'text' => ''
                    ]
                ]
            ]
        );
        return view("admin.farmaci")->with("dataView", $dataView);
    }
}
