<?php

namespace App\Http\Controllers;

use App\Models\LocationsUsers;
use App\Models\Structure;
use App\Models\UploatedFile;
use App\Models\User;
use App\Models\UserLog;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class AdminController extends Controller
{


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
        
        return response()->json(['redirect' => route("controller.obiettivo", ["obiettivo" => $request->t])]);
    }


    public function showObiettivo($obiettivo) {
        $dataView['obiettivo'] = $obiettivo;
        switch($obiettivo) {
            case 3: $dataView['titolo'] = config("constants.OBIETTIVO.3.text");
            break;
            case 8: $dataView['titolo'] = config("constants.OBIETTIVO.8.text");
            break;
            case 9: $dataView['titolo'] = "Ottimizzazione della gest del I ciclo di terapia";
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

        DB::table("result_target3")->insert([
            'numerator' => $request->numeratore ?? null,
            'denominator' => $request->denominatore ?? null,
            'uploated_file_id' => $request->fileId,
        ]);

        return $this->showObiettivo($file->target_number);
    }

    

    public function valide(Request $request) {
        return $this->updateUploatedFiles($request, true);
    }

    public function notValide(Request $request) {
        return $this->updateUploatedFiles($request, false);
    }

    
    
}
