<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\EanNormalizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class EanNormalizerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Forzamos largoEan = 13 sin pegarle a la DB real.
        Cache::shouldReceive('remember')
            ->andReturn(13);
    }

    /** @dataProvider casos */
    public function test_normaliza_correctamente(string $entrada, string $esperado): void
    {
        $this->assertSame($esperado, EanNormalizer::normalizar($entrada));
    }

    public static function casos(): array
    {
        return [
            'corto 45'                 => ['45', '0000000000045'],
            'corto 015'                => ['015', '0000000000015'],
            'completo 13'              => ['0000000000045', '0000000000045'],
            'alfanumerico intacto'     => ['ABC123', 'ABC123'],
            'vacio'                    => ['', ''],
            'con espacios'             => ['  45  ', '0000000000045'],
        ];
    }
}