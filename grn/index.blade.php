<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container">
    <h1>Goods Received Notes</h1>
    <a href="{{ route('grns.create') }}" class="btn btn-primary">Create GRN</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>GRN No</th>
                <th>Invoice No</th>
                <th>Supplier Name</th>
                <th>Product Name</th>
                <th>Product Qty</th>
                <th>Remark</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grns as $grn)
            <tr>
                <td>{{ $grn->grn_no }}</td>
                <td>{{ $grn->invoice_no }}</td>
                <td>{{ $grn->supplier_name }}</td>
                <td>{{ $grn->product_name }}</td>
                <td>{{ $grn->product_qty }}</td>
                <td>{{ $grn->remark }}</td>
                <td>
                    <a href="{{ route('grns.show', $grn->id) }}" class="btn btn-info">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</body>
</html>
