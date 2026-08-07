<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\StockInstrument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StockInstrumentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'symbol' => ['required', 'string', 'max:15', 'unique:stock_instruments,symbol'],
            'name' => ['required', 'string', 'max:150'],
            'exchange' => ['required', 'string', 'max:30'],
            'currency' => ['required', 'string', 'max:8'],
            'last_price' => ['required', 'numeric', 'min:0'],
        ]);
        $data['symbol'] = strtoupper($data['symbol']);

        $instrument = StockInstrument::create($data);
        AuditLog::record(auth()->user(), 'stock_instrument.created', StockInstrument::class, $instrument->id);

        return back()->with('success', "Stock {$instrument->symbol} added.");
    }

    public function update(Request $request, StockInstrument $instrument)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'exchange' => ['required', 'string', 'max:30'],
            'currency' => ['required', 'string', 'max:8'],
            'last_price' => ['required', 'numeric', 'min:0'],
            'change_pct' => ['required', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $instrument->update($data);
        AuditLog::record(auth()->user(), 'stock_instrument.updated', StockInstrument::class, $instrument->id);

        return back()->with('success', "Stock {$instrument->symbol} updated.");
    }

    public function destroy(StockInstrument $instrument)
    {
        if ($instrument->positions()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'Cannot delete an instrument with open user positions — deactivate it instead.');
        }

        AuditLog::record(auth()->user(), 'stock_instrument.deleted', StockInstrument::class, $instrument->id);
        $instrument->delete();

        return back()->with('success', 'Stock instrument deleted.');
    }

    public function importCsv(Request $request)
    {
        $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        $path = $request->file('csv')->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        $imported = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowData = array_combine($header, $row);
            if (empty($rowData['symbol'])) {
                continue;
            }

            StockInstrument::updateOrCreate(
                ['symbol' => strtoupper(trim($rowData['symbol']))],
                [
                    'name' => $rowData['name'] ?? $rowData['symbol'],
                    'exchange' => $rowData['exchange'] ?? 'NASDAQ',
                    'currency' => $rowData['currency'] ?? 'USD',
                    'last_price' => (float) ($rowData['last_price'] ?? $rowData['price'] ?? 0),
                    'change_pct' => (float) ($rowData['change_pct'] ?? 0),
                    'is_active' => true,
                ]
            );
            $imported++;
        }
        fclose($handle);

        AuditLog::record(auth()->user(), 'stock_instrument.csv_imported', null, null, ['count' => $imported]);

        return back()->with('success', "Imported/updated {$imported} stock instruments from CSV.");
    }
}
