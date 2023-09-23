<?php

namespace App\Http\Controllers;

use App\Models\SubTarefas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SubTarefasController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SubTarefas $subtarefas)
    {
        $this->subtarefas = $subtarefas;
    }

    public function list(Request $request, $id)
    {
        try {
            $subtarefas = SubTarefas::find($id);
        } catch (\Throwable $th) {
            return response()->json(["message" => "SubTarefa não encontrada"]);
        }
        return $subtarefas;
    }



    public function index(Request $request)
    {
        $subtarefas = subTarefas::all();
        return $subtarefas;
    }

    public function get(Request $request, $id)
    {
        return response()->json(SubTarefas::findOrFail($id)->fill($request->all()));
    }


    public function create(Request $request){
        $create = SubTarefas::create($request->all());

        if($create){
            $callback['msg'] = 'A subtarefa foi criada com sucesso!';
        }

        return response()->json($callback);

    }

    public function update(Request $request, $id){

        try{
            $subtarefas = $this->subtarefas->findOrFail($id)->fill($request->all());
            $subtarefas->fill($request->all())->save();
            return response()->json(['message' => 'SubTarefa atualizada!', 'subtarefas' => $subtarefas]);
        }catch(\Exception $th){
            Log::error(['th' => $th]);
            return response()->json(['message' => 'Erro ao atualizar a subtarefa']);

        }
    }

    public function delete($id){

        try{
            $this->subtarefas->findOrFail($id)->delete();
            return response()->json(['message' => 'SubTarefa Deletada']);
        }catch(\Exception $th){
            Log::error(['th' => $th]);
            return response()->json(['message' => 'Erro ao Deletar']);

        }
    }


    

    protected $subtarefas;
}