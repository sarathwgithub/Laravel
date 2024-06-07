<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\Product;
use Illuminate\Http\Request;

class GrnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $grns = Grn::with('products')->get();
        return view('grns.index', compact('grns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get the last GRN
        $lastGrn = Grn::orderBy('id', 'desc')->first();
        
        // Generate the new GRN number
        $nextGrnNo = $lastGrn ? 'GRN' . sprintf('%05d', (int)substr($lastGrn->grn_no, 3) + 1) : 'GRN00001';
        
        return view('grns.create', compact('nextGrnNo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_no' => 'required|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'product_name.*' => 'required|string|max:255',
            'product_qty.*' => 'required|integer',
            'remark' => 'nullable|string',
        ]);

        $grn = Grn::create([
            'grn_no' => $request->grn_no,
            'invoice_no' => $request->invoice_no,
            'supplier_name' => $request->supplier_name,
            'remark' => $request->remark,
        ]);

        foreach ($request->product_name as $index => $productName) {
            Product::create([
                'grn_id' => $grn->id,
                'product_name' => $productName,
                'product_qty' => $request->product_qty[$index],
            ]);
        }

        return redirect()->route('grns.index')
            ->with('success', 'GRN created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Grn $grn)
    {
        $items = $grn->products; // Assuming you have a 'products' relationship in Grn model

        return view('grns.items', compact('grn', 'items'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Grn $grn)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Grn $grn)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grn $grn)
    {
        //
    }
}
