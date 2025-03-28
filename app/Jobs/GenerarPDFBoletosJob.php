<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;
use App\Models\Rifa;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarPDFBoletosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 10800; // 3 horas
    public $maxExceptions = 3;

    public function __construct(
        public Rifa $rifa,
        public User $user
    ) {}

    public function handle()
    {
        // Aumentar memoria y tiempo de ejecución
        ini_set('memory_limit', '2048M'); // 1GB
        set_time_limit(4600); // 60 minutos

        $baseDir = "pdf/boletos/{$this->rifa->id}/";
        $zipFilename = "boletos_rifa_{$this->rifa->id}_".now()->format('Ymd_His').'.zip';
        $pdfFiles = [];

        try {
            Storage::disk('public')->makeDirectory($baseDir);

            // Reducir el tamaño del chunk
            $this->rifa->boletos()->chunk(100, function($boletos) use ($baseDir, &$pdfFiles) {
                $chunkId = Str::random(6);
                $filename = "boletos_{$this->rifa->id}_part_{$chunkId}.pdf";
                $fullPath = $baseDir . $filename;
                
                // Liberar memoria después de cada generación
                $pdf = Pdf::loadView('stisla.rifas.boletos_pdf', [
                    'rifa' => $this->rifa,
                    'boletos' => $boletos
                ])->setPaper('letter', 'landscape');
                
                $pdf->save(storage_path("app/public/{$fullPath}"));
                
                // Liberar recursos explícitamente
                unset($pdf);
                gc_collect_cycles();
                
                $pdfFiles[] = $fullPath;
            });

            // Crear archivo ZIP
            $zipPath = $this->createZip($pdfFiles, $baseDir.$zipFilename);

            // Notificar al usuario
            //$this->user->notify(new \App\Notifications\BoletosGeneradosNotification(
              //  Storage::disk('public')->url($zipPath)
           // ));

            // Limpiar PDFs individuales
            foreach ($pdfFiles as $pdfFile) {
                Storage::disk('public')->delete($pdfFile);
            }

        } catch (\Throwable $e) {
            // Limpieza en caso de error
            foreach ($pdfFiles as $pdfFile) {
                Storage::disk('public')->delete($pdfFile);
            }
            Log::error("Error generando PDFs: " . $e->getMessage());
            throw $e;
        }
    }

    protected function createZip(array $files, string $zipPath): string
    {
        $fullZipPath = storage_path("app/public/{$zipPath}");
        $zip = new ZipArchive();

        if ($zip->open($fullZipPath, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("No se pudo crear el archivo ZIP");
        }

        foreach ($files as $file) {
            $zip->addFile(
                storage_path("app/public/{$file}"),
                basename($file)
            );
        }

        $zip->close();
        return $zipPath;
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Error generando PDFs para rifa {$this->rifa->id}: " . $exception->getMessage());
        // Opcional: Notificar al administrador del fallo
    }
}