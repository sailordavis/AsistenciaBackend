<?php
namespace App\Modules\Group\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Group\Services\GroupService;

class GroupController extends Controller
{
    protected $groupService;

    //  Inyección del service
    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    //  Endpoint para subir CSV
    public function importarGrupos(Request $request)
    {
        try {

            //  Validar request
            $request->validate([
                'archivo' => 'required|file|mimes:csv,txt'
            ]);

            $file = $request->file('archivo');

            //  Llamar al service
            $resultado = $this->groupService->procesarCSV($file);

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