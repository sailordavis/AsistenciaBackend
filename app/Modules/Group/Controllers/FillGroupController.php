<?php
namespace App\Modules\Group\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Group\Services\FillGroupService;

class FillGroupController extends Controller
{
     protected $FillGroupService;
    //  Inyección del service
    public function __construct(FillGroupService $FillGroupService)
    {
        $this->FillGroupService = $FillGroupService;
    }

    public function cargarAlumnos(Request $request){
     try {

            //  Validar request
            $request->validate([
                'archivo' => 'required|file|mimes:csv,txt'
            ]);

            $file = $request->file('archivo');

            //  Llamar al service
            $resultado = $this->FillGroupService->LlenarGrupos($file);

            return response()->json([
                'success' => true,
                'data' => $resultado
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}