<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rifa;
use App\Models\Boleto;
use App\Jobs\GenerarBoletosJob;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB; // Importar la fachada DB
use Illuminate\Support\Str;         // Importar Str para generar códigos



class RifaController extends Controller
{
    public function index()
    {
        $rifas = Rifa::latest()->get();

        return view('stisla.rifas.index', [
            'rifas'       => $rifas, // Aquí pasamos la variable correcta a la vista
            'title'       => __('Rifas'),
            'countUnRead' => 0,
        ]);
    }

    public function create()
    {
        return view('stisla.rifas.create');
    }

    public function store2(Request $request)
    {
        // Obtener el usuario logueado
        $user = auth()->user(); // Esto te da el usuario autenticado

        // Crear la rifa con el usuario logueado como 'generado_por'
        $rifa = Rifa::create([
            'lote' => $request->lote,
            'fecha' => $request->fecha,
            'generado_por' => $user->id, // Asignar el nombre del usuario logueado
            'cantidad_boletos' => $request->cantidad_boletos
        ]);

        // Generar boletos aleatorios
        for ($i = 0; $i < $request->cantidad_boletos; $i++) {
            $boleto = Boleto::create([
                'rifa_id' => $rifa->id,
                'codigo' => strtoupper(uniqid('B')),
                'numeros' => json_encode(array_map(function () {
                    // Generar un número aleatorio y asegurarse de que tenga 5 dígitos
                    return str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT); // Completar con ceros a la izquierda hasta 5 dígitos
                }, range(1, 20)))
            ]);
        }

        return redirect()->route('rifas.index')->with('successMessage', 'Rifa creada correctamente.');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $rifa = Rifa::create([
            'lote' => $request->lote,
            'fecha' => $request->fecha,
            'generado_por' => $user->id,
            'cantidad_boletos' => $request->cantidad_boletos,
            'estado' => 'en_proceso'

        ]);

        // Despachar el Job para ejecución en segundo plano
        GenerarBoletosJob::dispatch($rifa, (int) $request->cantidad_boletos)->onQueue('boletos');

        return redirect()->route('rifas.index')->with('successMessage', 'Rifa creada correctamente. Los boletos se están generando en segundo plano.');
    }


    // Método auxiliar para generar números únicos de forma más eficiente
    protected function generateUniqueNumbers(int $count): array
    {
        $numbers = [];
        $maxAttempts = $count * 2; // Prevenir bucles infinitos

        while (count($numbers) < $count && $maxAttempts-- > 0) {
            $num = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            if (!in_array($num, $numbers)) {
                $numbers[] = $num;
            }
        }

        if (count($numbers) < $count) {
            throw new \RuntimeException('No se pudieron generar suficientes números únicos');
        }

        return $numbers;
    }



    public function show(Rifa $rifa)
    {
        // Pagina los boletos, mostrando 6 por página, puedes ajustar este número
        $boletos = $rifa->boletos()->paginate(6);

        return view('stisla.rifas.show', compact('rifa', 'boletos'));
    }

    public function print(Rifa $rifa)
    {
        return view('rifas.print', compact('rifa'));
    }


    public function imprimir(Rifa $rifa)
    {
        $pdf = Pdf::loadView('stisla.rifas.rifas_boletos', compact('rifa'))
            ->setPaper('letter', 'landscape'); // Tamaño carta horizontal

        return $pdf->download("boletos_rifa_{$rifa->lote}.pdf");
    }

    // Vista previa en Stisla
    public function imprimirVista(Rifa $rifa)
    {
        return view('stisla.rifas.boletos_preview', compact('rifa'));
    }

    // Generar y descargar PDF
    public function imprimirPDF(Rifa $rifa)
    {
        $pdf = Pdf::loadView('stisla.rifas.boletos_pdf', compact('rifa'))
            ->setPaper('letter', 'portrait'); // Cambiar a orientación vertical

            $pdf->getDomPDF()->set_option('enable_html5_parser', true);
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

        return $pdf->download("boletos_rifa_{$rifa->lote}.pdf");
    }
}
