<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SedeCatalog
{
    private const OPERATION_CENTERS = [
        '001' => 'CIUDAD JARDIN',
        '002' => 'UNICENTRO',
        '004' => 'JARDIN PLAZA',
        '008' => 'PANCE',
        '011' => 'BOCHALEMA PLAZA',
    ];

    public function options(): array
    {
        $configured = collect(self::OPERATION_CENTERS)
            ->map(fn (string $sede, string $code) => $this->formatOption($sede, $code));

        $existing = Order::query()
            ->select('sede')
            ->distinct()
            ->orderBy('sede')
            ->pluck('sede')
            ->filter()
            ->reject(fn (string $sede) => $this->hasOperationCenter($sede))
            ->map(fn (string $sede) => $this->formatOption($sede));

        return $configured->concat($existing)->values()->all();
    }

    public function names(): array
    {
        return collect($this->options())->pluck('name')->all();
    }

    public function resolveForParsedItems(Collection $parsedItems, string $selectedSede): array
    {
        $operationCenters = $parsedItems
            ->pluck('co')
            ->filter()
            ->map(fn (string $code) => $this->normalizeOperationCenter($code))
            ->filter()
            ->unique()
            ->values();

        if (! $this->hasOperationCenter($selectedSede) || $operationCenters->isEmpty()) {
            return [
                'sede' => $selectedSede,
                'operation_center' => null,
            ];
        }

        if ($operationCenters->count() > 1) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo contiene varios C.O. No se puede crear un único pedido para varias sedes.',
            ]);
        }

        $code = $operationCenters->first();
        $sede = self::OPERATION_CENTERS[$code] ?? null;

        if (! $sede) {
            throw ValidationException::withMessages([
                'archivo' => "El C.O. {$code} no está configurado para una sede.",
            ]);
        }

        return [
            'sede' => $sede,
            'operation_center' => [
                'code' => $code,
                'sede' => $sede,
                'applied' => $this->normalizeSede($sede) !== $this->normalizeSede($selectedSede),
            ],
        ];
    }

    private function formatOption(string $sede, ?string $code = null): array
    {
        return [
            'name' => trim($sede),
            'operation_center' => $code,
            'has_operation_center' => $code !== null,
        ];
    }

    private function hasOperationCenter(string $sede): bool
    {
        return collect(self::OPERATION_CENTERS)
            ->contains(fn (string $mappedSede) => $this->normalizeSede($mappedSede) === $this->normalizeSede($sede));
    }

    private function normalizeSede(string $sede): string
    {
        return Str::of($sede)->ascii()->upper()->squish()->toString();
    }

    private function normalizeOperationCenter(string $code): ?string
    {
        $digits = preg_replace('/\D+/', '', $code);

        if ($digits === '') {
            return null;
        }

        return str_pad($digits, 3, '0', STR_PAD_LEFT);
    }
}
