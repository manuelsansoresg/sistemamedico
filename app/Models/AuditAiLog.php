<?php

namespace App\Models;

class AuditAiLog extends AuditLog
{
    protected $table = 'audit_ai_logs';

    public function auditSection(): string
    {
        return 'ia';
    }
}
