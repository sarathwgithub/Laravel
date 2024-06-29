------------------------------------------------------------------------------------------------------------------------------------------------------------
***To implement a system where a doctor can view their appointments, add patient diagnosis details, and prescribe medications or lab reports, you'll typically need to build a system with several components:***
------------------------------------------------------------------------------------------------------------------------------------------------------------

1. Create a dashboard where the doctor can view their upcoming appointments.
   In doctor controller update the below code
   ```php
         use App\Models\Token;
         use Illuminate\Support\Facades\Auth;
         public function index()
        {
            // Get the logged-in user
            $user = Auth::user();
            // Check if the user is actually a doctor
            if ($user->doctor) {
                // Retrieve the doctor ID through the relationship
                $doctorId = $user->doctor->id;
                // Fetch tokens for the doctor
                $tokens = Token::where('doctor_id', $doctorId)
                    ->where('issue_time', '>=', now()) // Filter future tokens
                    ->orderBy('issue_time', 'asc')
                    ->get();
    
                return view('doctor.index', compact('tokens'));
            } else {
                // Handle cases where the logged-in user is not a doctor
                abort(403, 'Unauthorized access');
            }
        }
3. Ensure a Correct Relationship between User and Doctor
   ```php
               //In user model update Relationship
                public function doctor()
                {
                    return $this->hasOne(Doctor::class);
                }
              //In doctor model update Relationship
           
              public function user()
              {
                  return $this->belongsTo(User::class);
              }
5. Create Migration for Missing Columns
    ```php
        php artisan make:migration add_diagnosis_and_prescription_to_tokens_table --table=tokens

6. In the generated migration file add the columns
    ```php
          public function up(): void
          {
              Schema::table('tokens', function (Blueprint $table) {
                  $table->text('diagnosis')->nullable();
                  $table->text('prescription')->nullable();
              });
          }
      
          /**
           * Reverse the migrations.
           */
          public function down(): void
          {
              Schema::table('tokens', function (Blueprint $table) {
                  $table->dropColumn('diagnosis');
                  $table->dropColumn('prescription');
              });
          }
7. Run the migration to apply these changes to your database
    ```php
          php artisan migrate
8. Display Appointments and Add Diagnosis(doctor/index.blade.php)
    ```php
          <x-app-layout>
          <x-slot name="header">
              <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                  {{ __('Doctor') }}
              </h2>
          </x-slot>
      
          <div class="py-12">
              <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                  <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                      Test
                      <div class="max-w-full">
                          @foreach ($tokens as $appointment)
                          <div>
                              <h3>{{ $appointment->patient->name }} - {{ $appointment->appointment_date }}</h3>
                              <p>{{ $appointment->description }}</p>
      
                              @if ($errors->any())
                              <div class="alert alert-danger">
                                  <ul>
                                      @foreach ($errors->all() as $error)
                                      <li>{{ $error }}</li>
                                      @endforeach
                                  </ul>
                              </div>
                              @endif
                              <!-- Form for diagnosis and prescription -->
                              <form action="{{ route('doctor.addDiagnosis', ['appointmentId' => $appointment->id]) }}" method="POST">
                                  @csrf
                                  <textarea name="diagnosis" placeholder="Enter diagnosis"></textarea><br>
                                  <textarea name="prescription" placeholder="Enter prescription (medications, lab tests, etc.)"></textarea><br>
                                  <button type="submit">Save Diagnosis & Prescription</button>
                              </form>
                          </div>
                          @endforeach
      
                      </div>
                  </div>
          </x-app-layout>
9. Define the Route
    ```php
          Route::post('/doctor/add-diagnosis/{appointmentId}', [DoctorController::class, 'addDiagnosis'])->name('doctor.addDiagnosis');
10. Ensure your controller method addDiagnosis accepts the correct parameter:
      ```php
            public function addDiagnosis(Request $request, $appointmentId)
             {
                 $appointment = Token::findOrFail($appointmentId);
         
                 // Validate and store diagnosis and prescription
                 $validatedData = $request->validate([
                     'diagnosis' => 'required|string',
                     'prescription' => 'required|string',
                 ]);
         
                 // Update appointment with diagnosis and prescription details
                 $appointment->diagnosis = $validatedData['diagnosis'];
                 $appointment->prescription = $validatedData['prescription'];
                 $appointment->save();
         
                 return redirect()->back()->with('success', 'Diagnosis and prescription saved successfully.');
             }
11. 
