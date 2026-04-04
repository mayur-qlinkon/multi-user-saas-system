<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hrm\QrToken;

class PruneQrTokensCommand extends Command
{
    protected $signature = 'qr-tokens:prune';

    protected $description = 'Delete expired QR tokens older than 24 hours';

    public function handle(): int
    {
        $count = QrToken::where('expires_at', '<', now()->subDay())->delete();

        $this->info("Pruned {$count} expired QR token(s).");

        return self::SUCCESS;
    }
}
