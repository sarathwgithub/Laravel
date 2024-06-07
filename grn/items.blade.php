<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container">
    <h1>Items for GRN: {{ $grn->grn_no }}</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->product_qty }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</body>
</html>

