<?php

namespace App\Modules\Management\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Management\Services\ManagementService;

class ManagementController extends Controller
{
    protected $ManagementService;

    // Inyección del service
    public function __construct(ManagementService $ManagementService)
    {
        $this->ManagementService = $ManagementService;
    }

    //  IMPORTAR DOCENTES
    public function importarDocentes(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt'
        ]);

        try {

            $resultado = $this->ManagementService->procesarCSVDocentes(
                $request->file('archivo')
            );

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar docentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //  IMPORTAR ALUMNOS
    public function importarAlumnos(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt'
        ]);

        try {

            $resultado = $this->ManagementService->procesarCSVAlumnos(
                $request->file('archivo')
            );

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar alumnos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

