<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rifa;
use App\Models\Boleto;
use Barryvdh\DomPDF\Facade\Pdf;


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

        return redirect()->route('rifas.index')->with('success', 'Rifa creada correctamente.');
    }

    public function store(Request $request)
    {
        // Validar los datos de la rifa
        //dd($request);
        // Obtener el usuario logueado
        $user = auth()->user(); // Esto te da el usuario autenticado
        // Crear la rifa
       // Crear la rifa con el usuario logueado como 'generado_por'
       $rifa = Rifa::create([
            'lote' => $request->lote,
            'fecha' => $request->fecha,
            'generado_por' => $user->id, // Asignar el nombre del usuario logueado
            'cantidad_boletos' => $request->cantidad_boletos
        ]);

        $numerosGenerados = []; // Array para almacenar los números generados

        // Generar boletos aleatorios
        for ($i = 0; $i < $request->cantidad_boletos; $i++) {
            $numerosBoleto = [];

            // Generar 20 números únicos para este boleto
            while (count($numerosBoleto) < 20) {
                // Generar un número aleatorio de 5 dígitos
                $numero = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

                // Si el número no ha sido generado antes, agregarlo
                if (!in_array($numero, $numerosGenerados)) {
                    $numerosBoleto[] = $numero;
                    $numerosGenerados[] = $numero; // Añadir a la lista global
                }
            }

            // Crear boleto con los números generados
            $boleto = Boleto::create([
                'rifa_id' => $rifa->id,
                'codigo' => strtoupper(uniqid('B')),
                'generado_por' => $user->id, // Asignar el nombre del usuario logueado
                'numeros' => json_encode($numerosBoleto) // Convertir el array de números a JSON
            ]);
        }

        return redirect()->route('rifas.index')->with('success', 'Rifa creada correctamente.');
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

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'isSvgImagesEnabled' => true, // Para imágenes SVG, si es necesario
            'isCurlEnabled' => true // Para habilitar la carga de imágenes externas
        ]);

        return $pdf->download("boletos_rifa_{$rifa->lote}.pdf");
    }

}
