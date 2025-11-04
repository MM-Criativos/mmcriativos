<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'subject',
        'body',
        'footer',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Helper simples para renderizar placeholders {{var}}
    public function render(array $vars): string
    {
        $map = [];
        foreach ($vars as $k => $v) {
            $map['{{' . $k . '}}'] = is_scalar($v) ? (string) $v : json_encode($v);
        }

        $content = strtr($this->body ?? '', $map);
        if (!empty($this->footer)) {
            $content .= "\n\n" . strtr($this->footer, $map);
        }
        return $content;
    }

    public function renderSubject(array $vars): string
    {
        $map = [];
        foreach ($vars as $k => $v) {
            $map['{{' . $k . '}}'] = is_scalar($v) ? (string) $v : json_encode($v);
        }
        return strtr($this->subject ?? '', $map);
    }
}

