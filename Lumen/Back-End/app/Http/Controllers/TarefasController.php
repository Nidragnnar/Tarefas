<?php

namespace App\Http\Controllers;

use App\Models\SubTarefas;
use App\Models\Tarefas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class TarefasController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Tarefas $tarefas)
    {
        $this->tarefas = $tarefas;
    }

    public function list(Request $request, $id)
    {
        try {
            $tarefas = Tarefas::find($id);
            $tarefas->subs;
        } catch (\Throwable $th) {
            return response()->json(["message" => "Tarefa não encontrada"]);
        }
        return $tarefas;
    }



    public function index(Request $request)
    {
        $tarefas = Tarefas::all();
        $json = array();

        $jsonTarefas = array();

        foreach($tarefas as $tarefa){

           $subtarefas = SubTarefas::where("id_tarefa", $tarefa->id)->get();
        
           $jsonTarefas[] = array(
                "id"=> $tarefa->id,
                "titulo"=> $tarefa->titulo,
                "descricao"=> $tarefa->descricao,
                "data_vencimento"=> $tarefa->data_vencimento,
                "status"=> $tarefa->status,
                "subtarefas"=> $subtarefas,
            );
        
        }
    

     return response()->json($jsonTarefas);
    }

    public function get(Request $request, $id)
    {
        return response()->json(Tarefas::findOrFail($id)->fill($request->all()));
    }


    public function create(Request $request)
    {
        $this->validate($request, [
            'titulo' => 'required|string',
            'data_vencimento' => 'required',
            'descricao' => 'string',
            'status' => 'string'
        ]);

        $tarefas = $this->tarefas->create([
            'titulo' => $request->input('titulo'),
            'data_vencimento' => $request->input('data_vencimento'),
            'descricao' => $request->input('descricao'),
            'status' => $request->input('status'),
        ]);

        return response()->json(['message' => 'Tarefa criada com sucesso!', 'tarefas' => $tarefas]);
    }

    public function update(Request $request, $id)
    {

        try {
            $tarefas = $this->tarefas->findOrFail($id);
            $tarefas->fill($request->all())->update();


            return response()->json(['message' => 'Tarefa atualizada!', 'tarefas' => $tarefas]);
        } catch (\Exception $th) {
            Log::error(['th' => $th]);
            return response()->json(['message' => 'Erro ao atualizar a tarefa']);
        }
    }

    public function delete($id)
    {

        try {
            $this->tarefas->findOrFail($id)->delete();
            return response()->json(['message' => 'Tarefa Deletada']);
        } catch (\Exception $th) {
            Log::error(['th' => $th]);
            return response()->json(['message' => 'Erro ao Deletar']);
        }
    }




    protected $tarefas;
}
