<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::query()
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('q'), fn ($q) => $q->where('description', 'like', '%' . $request->string('q') . '%'))
            ->latest('date')
            ->paginate(15)
            ->withQueryString();

        $yearTotals = [
            'amount' => Expense::whereBetween('date', [now()->startOfYear(), now()->endOfYear()])->sum('amount'),
            'iva' => Expense::where('deductible', true)->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])->sum('iva_amount'),
        ];

        return view('expenses.index', compact('expenses', 'yearTotals'));
    }

    public function create()
    {
        return view('expenses.create');
    }

    public function store(Request $request)
    {
        Expense::create($this->validated($request));

        return redirect()->route('expenses.index')->with('success', 'Gasto registrado.');
    }

    public function edit(Expense $expense)
    {
        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $this->validated($request, $expense);

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Gasto actualizado.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Gasto eliminado.');
    }

    private function validated(Request $request, ?Expense $expense = null): array
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'iva_amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'category' => ['required', Rule::in(Expense::CATEGORIES)],
            'deductible' => ['nullable', 'boolean'],
            'receipt' => ['nullable', 'image', 'max:4096'],
        ], [], [
            'description' => 'descripción',
            'amount' => 'importe',
            'iva_amount' => 'IVA soportado',
            'date' => 'fecha',
            'category' => 'categoría',
            'receipt' => 'ticket',
        ]);

        $data['deductible'] = $request->boolean('deductible');

        if ($request->hasFile('receipt')) {
            if ($expense?->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        unset($data['receipt']);

        return $data;
    }
}
