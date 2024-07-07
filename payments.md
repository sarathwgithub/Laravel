**To implement a payment feature for a cashier, you need to create a form for the cashier to input payment details and then process those details in your Laravel application**
1. Create Payment Model and Migration
    ```
        php artisan make:model Payment -m
    ```
2. Edit the migration file to include the necessary fields
    ```php
            $table->foreignId('token_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 8, 2);
            $table->string('payment_method');
    ```
3. Run the migration to create the table
    ```
      php artisan migrate
    ```
4. Define Relationships: Define the relationships between the Payment and other relevant models
    ```php
        protected $fillable = ['token_id', 'amount', 'payment_method'];

        public function token()
        {
            return $this->belongsTo(Token::class);
        }
    ```
5.  Implement Methods in CashierController
      ```php
          use App\Models\Token;
          use App\Models\Payment;

          public function index()
          {
              // Fetch tokens where diagnoses are finished
              $tokens = Token::whereNotNull('diagnosis')
                  ->with('patient') // Eager load patient details
                  ->orderBy('issue_time', 'asc')
                  ->get();
      
              return view('cashier.index', compact('tokens'));
          }
      
          public function showPaymentForm($tokenId)
          {
              $token = Token::with('patient')->findOrFail($tokenId);
              return view('cashier.payment_form', compact('token'));
          }
      
          public function processPayment(Request $request, $tokenId)
          {
              $token = Token::findOrFail($tokenId);
      
              $validatedData = $request->validate([
                  'amount' => 'required|numeric|min:0',
                  'payment_method' => 'required|string',
              ]);
      
              Payment::create([
                  'token_id' => $tokenId,
                  'amount' => $validatedData['amount'],
                  'payment_method' => $validatedData['payment_method'],
              ]);
      
              return redirect()->route('cashier.paymentSuccess', $tokenId)
                  ->with('success', 'Payment processed successfully.');
          }
      
          public function paymentSuccess($tokenId)
          {
              $token = Token::findOrFail($tokenId);
              return view('cashier.payment_success', compact('token'));
          }
      ```
6.   Create Views for Cashier
     View for Finished Diagnoses
     
       ```php
                <!-- resources/views/cashier/finished_diagnoses.blade.php -->
                <x-app-layout>
                    <x-slot name="header">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            {{ __('Finished Diagnoses') }}
                        </h2>
                    </x-slot>
                
                    <div class="py-12">
                        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                                <table class="min-w-full leading-normal">
                                    <thead>
                                        <tr>
                                            <th>Patient Name</th>
                                            <th>Token No</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tokens as $token)
                                        <tr>
                                            <td>{{ $token->patient->name }}</td>
                                            <td>{{ $token->id }}</td>
                                            <td>
                                                <a href="{{ route('cashier.makePayment', $token->id) }}" class="btn btn-primary">Make Payment</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </x-app-layout>
       ```
   View for Payment Form
   
        ```php
            <!-- resources/views/cashier/payment_form.blade.php -->
            <x-app-layout>
                <x-slot name="header">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('Process Payment') }}
                    </h2>
                </x-slot>
            
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                            <form action="{{ route('cashier.processPayment', $token->id) }}" method="POST">
                                @csrf
                                <div>
                                    <label>Patient Name:</label>
                                    <input type="text" value="{{ $token->patient->name }}" readonly>
                                </div>
                                <div>
                                    <label>Token No:</label>
                                    <input type="text" value="{{ $token->id }}" readonly>
                                </div>
                                <div>
                                    <label>Amount:</label>
                                    <input type="text" name="amount" required>
                                </div>
                                <div>
                                    <label>Payment Method:</label>
                                    <select name="payment_method" required>
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="submit">Process Payment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </x-app-layout>
        ```
Create Payment Success View
     
            ```php
                <!-- resources/views/cashier/payment_success.blade.php -->
                <x-app-layout>
                    <x-slot name="header">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            {{ __('Payment Success for Token ') . $token->id }}
                        </h2>
                    </x-slot>
                
                    <div class="py-12">
                        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                                <h3>Payment processed successfully!</h3>
                                <p>Token ID: {{ $token->id }}</p>
                                <p>Patient Name: {{ $token->patient->name }}</p>
                                <!-- Add more details as needed -->
                            </div>
                        </div>
                    </div>
                </x-app-layout>
            ```

11.   Update Routes
      ```php
          Route::get('/cashier/make-payment/{token}', [CashierController::class, 'showPaymentForm'])->name('cashier.makePayment');
          Route::post('/cashier/make-payment/{token}', [CashierController::class, 'processPayment'])->name('cashier.processPayment');
          Route::get('/cashier/payment/success/{tokenId}', [CashierController::class, 'paymentSuccess'])->name('cashier.paymentSuccess');
      ```
12.   
