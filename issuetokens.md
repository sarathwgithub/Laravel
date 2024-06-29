-----------------------------------------------------------------------------------------------------------------------------
**implement a feature for the receptionist to issue tokens to patients according to doctor availability and specialization**
-----------------------------------------------------------------------------------------------------------------------------

1. Create Migration for Tokens Table
    ```php
        php artisan make:migration create_tokens_table
2. Update the migration file
    ```php
        public function up(): void
        {
            Schema::create('tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('patient_id');
                $table->unsignedBigInteger('doctor_id');
                $table->dateTime('issue_time');
                $table->integer('appointment_number');
                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
                $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
                $table->timestamps();
            });
        }
    
        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('tokens');
        }
3. Run the migration
    ```php
        php artisan migrate
4. Create Models for Token
    ```php
        php artisan make:model Token  

5. Update the model
    ```php
        class Token extends Model
        {
            use HasFactory;
            protected $fillable = [
                'patient_id',
                'doctor_id',
                'issue_time',
                'appointment_number',
            ];
        
            public function patient()
            {
                return $this->belongsTo(Patient::class);
            }
        
            public function doctor()
            {
                return $this->belongsTo(Doctor::class);
            }
        }
6. Update the ReceptionistController 
    Add methods to handle token issuing
     ```php
            use Illuminate\Http\Request;
            use App\Models\Patient;
            use App\Models\Doctor;
            use App\Models\Specialization;
            use App\Models\Token;
            use RealRashid\SweetAlert\Facades\Alert;
            use Illuminate\Support\Facades\Log;
            use Carbon\Carbon;
            public function issueToken()
            {
                $patients = Patient::all();
                $specializations = Specialization::all();
                return view('receptionist.issue_token', compact('patients', 'specializations'));
            }
        
            public function getDoctorsBySpecializationAndDate($specializationId, $date)
            {
                $doctors = Doctor::where('specialization_id', $specializationId)->get();
                $availableDoctors = $doctors->filter(function ($doctor) use ($date) {
                    $tokenCount = $doctor->tokens()->whereDate('issue_time', $date)->count();
                    Log::info('Checking availability for doctor:', [
                        'doctor_id' => $doctor->id,
                        'name' => $doctor->name,
                        'token_count' => $tokenCount
                    ]);
                    return $tokenCount == 0; // Assuming we want doctors with no tokens for that date
                });
        
                $availableDoctors = $availableDoctors->values(); // Re-index the collection
                Log::info('Available doctors:', $availableDoctors->toArray());
        
                return response()->json($availableDoctors);
            }
        
        
            public function getNextAppointmentNumber($doctorId)
            {
                $nextAppointmentNumber = Token::where('doctor_id', $doctorId)->max('appointment_number') + 1;
                return response()->json(['next_appointment_number' => $nextAppointmentNumber]);
            }
        
            public function storeToken(Request $request)
            {
                Log::info('Request data: ', $request->all());
        
                try {
                    $validated = $request->validate([
                        'patient_id' => 'required|exists:patients,id',
                        'doctor_id' => 'required|exists:doctors,id',
                        'appointment_number' => 'required|integer',
                    ]);
                    // Get the last appointment for the same doctor and patient
                    $lastAppointment = Token::where('doctor_id', $validated['doctor_id'])
                        ->where('patient_id', $validated['patient_id'])
                        ->latest('issue_time')
                        ->first();
        
                    // Calculate issue_time as 15 minutes after the last appointment's issue_time
                    if ($lastAppointment) {
                        $issue_time = Carbon::parse($lastAppointment->issue_time)->addMinutes(15);
                    } else {
                        // If no previous appointment, start from current time
                        $issue_time = Carbon::now('Asia/Colombo')->addMinutes(15);
                    }
                    $validated['issue_time'] = $issue_time->toDateTimeString();
                    Log::info('Validated data: ', $validated);
        
                    Token::create([
                        'patient_id' => $validated['patient_id'],
                        'doctor_id' => $validated['doctor_id'],
                        'issue_time' => $validated['issue_time'],
                        'appointment_number' => $validated['appointment_number'],
                    ]);
        
                    Alert::success('Token issued successfully', 'Token issued successfully');
                } catch (\Exception $e) {
                    Log::error('Error inserting token: ' . $e->getMessage());
                    Alert::error('Token issuance failed', 'An error occurred while issuing the token');
                }
        
                return redirect()->back();
            }
             
8. Update the doctor model
    ```php
          public function tokens()
          {
              return $this->hasMany(Token::class);
          }
9. Create Blade View for Issuing Token
      ```php
           php artisan make:view receptionist/issue_token
10. Update the view

    ```php
                <!-- resources/views/receptionist/issue_token.blade.php -->
                  <x-app-layout>
                      <x-slot name="header">
                          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                              {{ __('Issue Token') }}
                          </h2>
                      </x-slot>
            
                      <div class="py-12">
                          <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                              <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                                  <div class="max-w-full">
                                      @if(session('success'))
                                      <div>
                                          <span class="block sm:inline">{{ session('success') }}</span>
                                      </div>
                                      @endif
                                      <form method="POST" action="{{ route('receptionist.store_token') }}">
                                          @csrf
            
                                          <!-- Specialization Selection -->
                                          <div class="mb-4">
                                              <label for="specialization_id" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Specialization') }}</label>
                                              <select id="specialization_id" class="form-select rounded-md shadow-sm mt-1 block w-full" name="specialization_id" required>
                                                  <option value="">--</option>
                                                  @foreach($specializations as $specialization)
                                                  <option value="{{ $specialization->id }}">{{ $specialization->name }}</option>
                                                  @endforeach
                                              </select>
                                              @error('specialization_id')
                                              <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                              @enderror
                                          </div>
            
                                          <!-- Date Selection -->
                                          <div class="mb-4">
                                              <label for="date" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Date') }}</label>
                                              <input id="date" type="date" class="form-input rounded-md shadow-sm mt-1 block w-full" name="date" required>
                                              @error('date')
                                              <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                              @enderror
                                          </div>
            
                                          <!-- Doctor Selection -->
                                          <div class="mb-4">
                                              <label for="doctor_id" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Doctor') }}</label>
                                              <select id="doctor_id" class="form-select rounded-md shadow-sm mt-1 block w-full" name="doctor_id" required>
            
                                                  <!-- Options will be populated by JavaScript -->
                                              </select>
                                              @error('doctor_id')
                                              <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                              @enderror
                                          </div>
                                          <div class="mb-4">
                                              <label for="patient_id" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Patient') }}</label>
                                              <select id="patient_id" class="form-select rounded-md shadow-sm mt-1 block w-full" name="patient_id" required>
                                                  <option value="">Select Patient</option>
                                                  @foreach($patients as $patient)
                                                  <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                                  @endforeach
                                              </select>
                                          </div>
                                          <!-- Next Appointment Number -->
                                          <div class="mb-4">
                                              <label for="appointment_number" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Next Appointment Number') }}</label>
                                              <input id="appointment_number" type="number" class="form-input rounded-md shadow-sm mt-1 block w-full" name="appointment_number" required readonly>
                                              @error('appointment_number')
                                              <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                              @enderror
                                          </div>
            
                                          <x-primary-button class="ms-4">
                                              {{ __('Confirm') }}
                                          </x-primary-button>
                                      </form>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </x-app-layout>
            
                  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                  <script>
                      $(document).ready(function() {
                          $('#specialization_id, #date').on('change', loadDoctors);
            
                          function loadDoctors() {
                              const specializationId = $('#specialization_id').val();
                              const date = $('#date').val();
                              const doctorSelect = $('#doctor_id');
            
                              if (specializationId && date) {
                                  $.ajax({
                                      url: `/receptionist/get-doctors/${specializationId}/${date}`,
                                      method: 'GET',
                                      success: function(doctors) {
                                          console.log('Fetched doctors:', doctors); // Debugging line
                                          doctorSelect.empty(); // Clear previous options
            
                                          doctorSelect.append(
                                              $('<option>', {
                                                  value: '',
                                                  text: '--'
                                              })
                                          );
            
                                          if (doctors.length > 0) {
                                              $.each(doctors, function(index, doctor) {
                                                  doctorSelect.append(
                                                      $('<option>', {
                                                          value: doctor.id,
                                                          text: doctor.name
                                                      })
                                                  );
                                              });
                                          } else {
                                              doctorSelect.append(
                                                  $('<option>', {
                                                      text: 'No doctors available'
                                                  })
                                              );
                                          }
                                      },
                                      error: function(error) {
                                          console.error('Error fetching doctors:', error); // Debugging line
                                      }
                                  });
                              }
                          }
            
                          $('#doctor_id').on('change', function() {
                              const doctorId = $(this).val();
            
                              $.ajax({
                                  url: `/receptionist/get-next-appointment-number/${doctorId}`,
                                  method: 'GET',
                                  success: function(data) {
                                      console.log('Next appointment number:', data); // Debugging line
                                      $('#appointment_number').val(data.next_appointment_number);
                                  },
                                  error: function(error) {
                                      console.error('Error fetching next appointment number:', error); // Debugging line
                                  }
                              });
                          });
                      });
                  </script>
11. Add a route for fetching doctors by specialization
    ```php
        Route::get('/receptionist/issue-token', [ReceptionistController::class, 'issueToken'])->name('receptionist.issue_token');
        Route::post('/receptionist/issue-token', [ReceptionistController::class, 'storeToken'])->name('receptionist.store_token');
        Route::get('/receptionist/get-doctors/{specialization}', [ReceptionistController::class, 'getDoctorsBySpecialization']);
        Route::get('/receptionist/get-doctors/{specialization}/{date}', [ReceptionistController::class, 'getDoctorsBySpecializationAndDate']);
        Route::get('/receptionist/get-next-appointment-number/{doctorId}', [ReceptionistController::class, 'getNextAppointmentNumber']);
12. 
