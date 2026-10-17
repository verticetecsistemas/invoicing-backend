<?php

namespace App\Console\Commands;

use App\Services\IaintegrationProcessor;
use Illuminate\Console\Command;

class ProcessIaintegration extends Command
{
    protected $signature = 'iaintegration:process';

    protected $description = 'Efetiva as integrações de IA pendentes como guias e consultas.';

    public function handle(IaintegrationProcessor $processor): int
    {
        $result = $processor->processPending();

        $this->info("Total: {$result['total']}; efetivadas: {$result['processed']}; falhas: {$result['failed']}.");

        foreach ($result['errors'] as $error) {
            $this->error("Integração {$error['iaintegration_id']}: {$error['message']}");
        }

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
