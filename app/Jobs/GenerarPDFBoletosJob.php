<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Boleto;
use App\Models\User;
use App\Models\Rifa;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarPDFBoletosJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Rifa $rifa,public User $user){

    } 

    /**
     * Execute the job.
     */
    public function handle()
    {
        $path = "pdf/boletos_rifa_{$this->rifa->id}_".now()->timestamp.'.pdf';
        
        Pdf::loadView('stisla.rifas.boletos_pdf', [
            'rifa' => $this->rifa,
            'boletos' => $this->rifa->boletos()->cursor()
        ])->setPaper('letter', 'landscape')
          ->save(storage_path("app/public/$path"));

        // Envía email con el PDF o notificación
       // $this->user->notify(new PdfListoNotification($path));
    }
}
