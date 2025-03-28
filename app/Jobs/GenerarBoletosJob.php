<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Boleto;
use App\Models\Rifa;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class GenerarBoletosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rifa;
    protected $cantidadBoletos;

    /**
     * Create a new job instance.
     */
    public function __construct(Rifa $rifa, int $cantidadBoletos)
    {
        $this->rifa = $rifa;
        $this->cantidadBoletos = $cantidadBoletos;
    }

    /**
     * Execute the job.
     */


     public function handle(): void
     {
         $boletos = []; 
         
         Log::info("Iniciando generación de boletos para la rifa: {$this->rifa->id}");
     
         // 1. Generar números del 00001 al 99999 como strings de 5 dígitos
         $numerosDisponibles = array_map(
             fn($i) => str_pad($i, 5, '0', STR_PAD_LEFT),
             range(1, 99999)
         );
         
         Log::info("Total de números generados: " . count($numerosDisponibles));
         Log::debug("Ejemplo de números generados: " . json_encode(array_slice($numerosDisponibles, 0, 5))); // Muestra los primeros 5
     
         shuffle($numerosDisponibles); // Mezclar aleatoriamente
     
         DB::beginTransaction();
     
         try {
             for ($i = 0; $i < $this->cantidadBoletos; $i++) {
                 // 2. Extraer 20 números (ya están en formato 00000-99999)
                 $numerosBoleto = array_slice($numerosDisponibles, $i * 20, 20);
     
                 Log::info("Generando boleto #{$i} con los siguientes números:", $numerosBoleto);
     
                 // 3. Validar que todos tengan 5 dígitos
                 foreach ($numerosBoleto as $num) {
                     if (strlen($num) !== 5) {
                         Log::error("¡Número inválido! Debe tener 5 dígitos: {$num}");
                         throw new \Exception("Número inválido: {$num} (no tiene 5 dígitos)");
                     }
                 }
     
                 // 4. Guardar en la base de datos
                 $boletos[] = [
                     'rifa_id' => $this->rifa->id,
                     'codigo' => strtoupper(bin2hex(random_bytes(4))),
                     'numeros' => json_encode($numerosBoleto),
                 ];
             }
     
             Boleto::insert($boletos);
             $this->rifa->update(['estado' => 'completado']);
     
             DB::commit();
             Log::info("Boletos generados exitosamente. Total: " . count($boletos));
         } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Error generando boletos: " . $e->getMessage());
             throw $e;
         }
     }

}