<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container">
    <h1>Create GRN</h1>
    <form action="{{ route('grns.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="grn_no">GRN No</label>
            <input type="text" class="form-control" id="grn_no" name="grn_no" value="{{ $nextGrnNo }}" readonly>
        </div>
        <div class="form-group">
            <label for="invoice_no">Invoice No</label>
            <input type="text" class="form-control" id="invoice_no" name="invoice_no" required>
        </div>
        <div class="form-group">
            <label for="supplier_name">Supplier Name</label>
            <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
        </div>
        
        <div class="form-group">
            <label>Products</label>
            <table class="table" id="product-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Product Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="text" class="form-control" name="product_name[]" required></td>
                        <td><input type="number" class="form-control" name="product_qty[]" required></td>
                        <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-success" id="add-row">Add Product</button>
        </div>

        <div class="form-group">
            <label for="remark">Remark</label>
            <textarea class="form-control" id="remark" name="remark"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addRowButton = document.getElementById('add-row');
        const productTable = document.getElementById('product-table').getElementsByTagName('tbody')[0];

        addRowButton.addEventListener('click', function () {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td><input type="text" class="form-control" name="product_name[]" required></td>
                <td><input type="number" class="form-control" name="product_qty[]" required></td>
                <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
            `;
            productTable.appendChild(newRow);

            newRow.querySelector('.remove-row').addEventListener('click', function () {
                newRow.remove();
            });
        });

        // Attach event listener for removing rows to existing row's remove button
        document.querySelectorAll('.remove-row').forEach(function(button) {
            button.addEventListener('click', function () {
                this.closest('tr').remove();
            });
        });
    });
</script>
</body>
</html>
