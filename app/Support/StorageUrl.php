<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class StorageUrl
{
    /**
     * Gera a URL pública de um arquivo do disco padrão.
     *
     * Em desenvolvimento usamos o link simbólico public/storage (disco "public").
     * Em produção (Cloudflare R2, S3, etc.) usamos a URL configurada no próprio disco.
     */
    public static function public(string $path): string
    {
        $disk = (string) config('filesystems.default');

        return Storage::disk($disk === 'local' ? 'public' : $disk)->url($path);
    }
}
