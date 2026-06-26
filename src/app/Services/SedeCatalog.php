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
                'parsed_items' => $parsedItems,
            ];
        }

        $selectedCode = $this->operationCenterForSede($selectedSede);

        if ($operationCenters->count() > 1) {
            return $this->resolveMultipleOperationCenters($parsedItems, $selectedSede, $selectedCode);
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
                'filtered' => false,
            ],
            'parsed_items' => $parsedItems,
        ];
    }

    private function resolveMultipleOperationCenters(Collection $parsedItems, string $selectedSede, string $selectedCode): array
    {
        $sede = self::OPERATION_CENTERS[$selectedCode];
        $filteredItems = $parsedItems
            ->filter(fn (array $item) => $this->normalizeOperationCenter($item['co'] ?? '') === $selectedCode)
            ->values();

        if ($filteredItems->isEmpty()) {
            throw ValidationException::withMessages([
                'archivo' => "El archivo no contiene ítems para la sede seleccionada {$sede} (C.O. {$selectedCode}).",
            ]);
        }

        return [
            'sede' => $sede,
            'operation_center' => [
                'code' => $selectedCode,
                'sede' => $sede,
                'applied' => $this->normalizeSede($sede) !== $this->normalizeSede($selectedSede),
                'filtered' => true,
                'original_items_count' => $parsedItems->count(),
                'filtered_items_count' => $filteredItems->count(),
            ],
            'parsed_items' => $filteredItems,
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
        return $this->operationCenterForSede($sede) !== null;
    }

    private function operationCenterForSede(string $sede): ?string
    {
        $normalizedSede = $this->normalizeSede($sede);

        foreach (self::OPERATION_CENTERS as $code => $mappedSede) {
            if ($this->normalizeSede($mappedSede) === $normalizedSede) {
                return $code;
            }
        }

        return null;
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
