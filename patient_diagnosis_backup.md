------------------------------------------------------------------------------------------------------------------------------------------------------------
***To implement a system where a doctor can view their appointments, add patient diagnosis details, and prescribe medications or lab reports, you'll typically need to build a system with several components:***
------------------------------------------------------------------------------------------------------------------------------------------------------------

1. Define Relationships in Models
   
   Ensure the User model has a doctor relationship
      ```php
          public function doctor()
          {
              return $this->hasOne(Doctor::class);
          }
      ```
   Ensure the Doctor model has the correct relationship

      ```php
          public function user()
          {
              return $this->belongsTo(User::class);
          }
      ```
   
2. Update the Controller Method
   Ensure the controller method fetches the logged-in user and checks if they are associated with a doctor:
      ```php
          public function index()
          {
              // Get the logged-in user
              $user = Auth::user();
              // Check if the user is actually a doctor
              if ($user && $user->doctor) {
                  // Retrieve the doctor ID through the relationship
                  $doctorId = $user->doctor->id;
                  // Fetch tokens for the doctor and load patient details
                  $tokens = Token::where('doctor_id', $doctorId)
                      ->where('issue_time', '>=', now()) // Filter future tokens
                      ->with('patient') // Eager load patient details
                      ->orderBy('issue_time', 'asc')
                      ->get();
      
                  return view('doctor.index', compact('tokens'));
              } else {
                  // Handle cases where the logged-in user is not a doctor
                  abort(403, 'Unauthorized access');
              }
          }
      ```
4. Create index blade
   Make sure your Blade template correctly displays the patient details and includes the required JavaScript
      ```php
         <!-- resources/views/doctor/index.blade.php -->
         <x-app-layout>
             <x-slot name="header">
                 <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                     {{ __('Doctor') }}
                 </h2>
             </x-slot>
         
             <div class="py-12">
                 <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                     <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                         <table>
                             <thead>
                                 <tr>
                                     <th>Patient Name</th>
                                     <th>Token No</th>
                                     <th></th>
                                 </tr>
                             </thead>
                             <tbody>
                                 @foreach ($tokens as $appointment)
                                 <tr>
                                     <td>{{ $appointment->patient->name }}</td>
                                     <td>{{ $appointment->appointment_number }}</td>
                                     <td>
                                         <a href="{{ route('doctor.showDiagnosisForm', ['appointmentId' => $appointment->id]) }}" class="btn btn-primary">Add Diagnosis</a>
                                     </td>
                                     @endforeach
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>
         
         </x-app-layout>
      ```
5. Add a route to serve the diagnosis form. Add this to your web.php
      ```php
          Route::get('/doctor/diagnosis-form/{appointmentId}', [DoctorController::class, 'showDiagnosisForm'])->name('doctor.showDiagnosisForm');
      ```
6.  Add a method to the DoctorController to return the view with the form
      ```php
          public function showDiagnosisForm($appointmentId)
          {
              $appointment = Token::with('patient')->findOrFail($appointmentId);
              return view('doctor.diagnosis_form', compact('appointment'));
          }
      ```
7.  Create Drug and LabReport Models
      ```
         php artisan make:model Drug -m
         php artisan make:model LabReport -m
      ```
8.  Migration for Drug
      ```php
          $table->foreignId('token_id')->constrained()->onDelete('cascade');
          $table->string('drug_name');
          $table->string('dosage');
      ```
9. Migration for LabReport
      ```php
          $table->foreignId('token_id')->constrained()->onDelete('cascade');
          $table->string('report_name');
      ```
10.  Run migrations
      ```php
         php artisan migrate
      ```
11.  Update Drug Model
      ```php
          protected $fillable = [
              'token_id',
              'drug_name',
              'dosage',
          ];
      
          public function token()
          {
              return $this->belongsTo(Token::class);
          }
      ```
12.  Update LabReport Model
      ```php
          protected $fillable = [
              'token_id',
              'report_name',
          ];
      
          public function token()
          {
              return $this->belongsTo(Token::class);
          }
      ```
13.  Update Token model to include relationships for drugs and lab reports
      ```php
          public function drugs()
          {
              return $this->hasMany(Drug::class);
          }
      
          public function labReports()
          {
              return $this->hasMany(LabReport::class);
          }
      ```
14.  Create Models and Migrations for Health Details
      ```
         php artisan make:model HealthDetail -m
      ```
15.  Create migration for HealthDetail
      ```php
            $table->foreignId('token_id')->constrained()->onDelete('cascade');
            $table->float('height');
            $table->float('weight');
            $table->float('bmi');
            $table->integer('pressure_level_1');
            $table->integer('pressure_level_2');
            $table->integer('heart_rate');
      ```
16.  Update HealthDetail Model
      ```php
         protected $fillable = [
           'token_id',
           'height',
           'weight',
           'bmi',
           'pressure_level_1',
           'pressure_level_2',
           'heart_rate',
       ];
   
       public function token()
       {
           return $this->belongsTo(Token::class);
       }
      ```
17.  Migrate the database
      ```
         php artisan migrate
      ```
18.  Add the relationship in the Token model
      ```php
          public function healthDetails()
          {
              return $this->hasOne(HealthDetail::class);
          }
      ```
19.  Update the DoctorController to handle storing data in the new tables
      ```php
            use App\Models\HealthDetail;
            use App\Models\Drug;
            use App\Models\LabReport;

             public function showDiagnosisForm($appointmentId)
             {
                 $appointment = Token::with(['patient', 'healthDetails', 'drugs', 'labReports'])->findOrFail($appointmentId);
                 return view('doctor.diagnosis_form', compact('appointment'));
             }

             public function addBasicDetails(Request $request, $appointmentId)
             {
                 $appointment = Token::findOrFail($appointmentId);
         
                 $validatedData = $request->validate([
                     'height' => 'required|numeric',
                     'weight' => 'required|numeric',
                     'bmi' => 'required|numeric',
                     'pressure_level_1' => 'required|numeric',
                     'pressure_level_2' => 'required|numeric',
                     'heart_rate' => 'required|numeric',
                 ]);
         
                 HealthDetail::updateOrCreate(
                     ['token_id' => $appointmentId],
                     $validatedData
                 );
         
                 return response()->json(['success' => 'Basic health details saved successfully.']);
             }
         
             public function addDrugDetails(Request $request, $appointmentId)
             {
                 $appointment = Token::findOrFail($appointmentId);
         
                 $validatedData = $request->validate([
                     'drug_name' => 'required|string',
                     'dosage' => 'required|string',
                 ]);
         
                 $appointment->drugs()->create($validatedData);
         
                 return response()->json(['success' => 'Drug details saved successfully.']);
             }
         
             public function addLabReport(Request $request, $appointmentId)
             {
                 $appointment = Token::findOrFail($appointmentId);
         
                 $validatedData = $request->validate([
                     'report_name' => 'required|string',
                 ]);
         
                 $appointment->labReports()->create($validatedData);
         
                 return response()->json(['success' => 'Lab report saved successfully.']);
             }
      ```
20.  Update resources/views/doctor/diagnosis_form.blade.php to handle displaying and submitting data using Ajax
      ```html
         <!-- resources/views/doctor/diagnosis_form.blade.php -->
         <x-app-layout>
             <x-slot name="header">
                 <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                     {{ __('Diagnosis Form for ') . $appointment->patient->name }}
                 </h2>
             </x-slot>
         
             <div class="py-12">
                 <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                     <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                         <h3>Basic Health Details</h3>
                         <form id="basic-details-form">
                             @csrf
                             <label>Height:</label>
                             <input type="text" name="height" value="{{ $appointment->healthDetails->height ?? '' }}"><br>
                             <label>Weight:</label>
                             <input type="text" name="weight" value="{{ $appointment->healthDetails->weight ?? '' }}"><br>
                             <label>BMI:</label>
                             <input type="text" name="bmi" value="{{ $appointment->healthDetails->bmi ?? '' }}"><br>
                             <label>Pressure Level 1:</label>
                             <input type="text" name="pressure_level_1" value="{{ $appointment->healthDetails->pressure_level_1 ?? '' }}"><br>
                             <label>Pressure Level 2:</label>
                             <input type="text" name="pressure_level_2" value="{{ $appointment->healthDetails->pressure_level_2 ?? '' }}"><br>
                             <label>Heart Rate:</label>
                             <input type="text" name="heart_rate" value="{{ $appointment->healthDetails->heart_rate ?? '' }}"><br>
                             <button type="button" id="save-basic-details">Save</button>
                         </form>
                         <table id="basic-details-table">
                             <!-- Basic details will be loaded here -->
                             @if($appointment->healthDetails)
                             <tr>
                                 <td>{{ $appointment->healthDetails->height }}</td>
                                 <td>{{ $appointment->healthDetails->weight }}</td>
                                 <td>{{ $appointment->healthDetails->bmi }}</td>
                                 <td>{{ $appointment->healthDetails->pressure_level_1 }}</td>
                                 <td>{{ $appointment->healthDetails->pressure_level_2 }}</td>
                                 <td>{{ $appointment->healthDetails->heart_rate }}</td>
                                 <td>{{ $appointment->healthDetails->created_at }}</td>
                             </tr>
                             @endif
                         </table>
         
                         <h3>Drug Details</h3>
                         <form id="drug-details-form">
                             @csrf
                             <label>Drug Name:</label>
                             <input type="text" name="drug_name"><br>
                             <label>Dosage:</label>
                             <input type="text" name="dosage"><br>
                             <button type="button" id="save-drug-details">Save</button>
                         </form>
                         <table id="drug-details-table">
                             <!-- Drug details will be loaded here -->
                             @foreach($appointment->drugs as $drug)
                             <tr>
                                 <td>{{ $drug->drug_name }}</td>
                                 <td>{{ $drug->dosage }}</td>
                                 <td>{{ $drug->created_at }}</td>
                             </tr>
                             @endforeach
                         </table>
         
                         <h3>Lab Report Details</h3>
                         <form id="lab-report-form">
                             @csrf
                             <label>Report Name:</label>
                             <input type="text" name="report_name"><br>
                             <button type="button" id="save-lab-report">Save</button>
                         </form>
                         <table id="lab-report-table">
                             <!-- Lab reports will be loaded here -->
                             @foreach($appointment->labReports as $report)
                             <tr>
                                 <td>{{ $report->report_name }}</td>
                                 <td>{{ $report->created_at }}</td>
                             </tr>
                             @endforeach
                         </table>
                     </div>
                 </div>
             </div>
             <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
             <script>
                 $(document).ready(function() {
                     $('#save-basic-details').click(function() {
                         $.ajax({
                             url: '{{ route("doctor.addBasicDetails", $appointment->id) }}',
                             type: 'POST',
                             data: $('#basic-details-form').serialize(),
                             success: function(response) {
                                 alert(response.success);
                                 // Reload basic details table
                                 location.reload();
                             }
                         });
                     });
         
                     $('#save-drug-details').click(function() {
                         $.ajax({
                             url: '{{ route("doctor.addDrugDetails", $appointment->id) }}',
                             type: 'POST',
                             data: $('#drug-details-form').serialize(),
                             success: function(response) {
                                 alert(response.success);
                                 // Reload drug details table
                                 location.reload();
                             }
                         });
                     });
         
                     $('#save-lab-report').click(function() {
                         $.ajax({
                             url: '{{ route("doctor.addLabReport", $appointment->id) }}',
                             type: 'POST',
                             data: $('#lab-report-form').serialize(),
                             success: function(response) {
                                 alert(response.success);
                                 // Reload lab report table
                                 location.reload();
                             }
                         });
                     });
                 });
             </script>
         </x-app-layout>

      ```
21.  Ensure the routes for handling the form submissions are defined in your web.php
      ```php
          Route::post('/doctor/diagnosis/{appointmentId}/basic', [DoctorController::class, 'addBasicDetails'])->name('doctor.addBasicDetails');
          Route::post('/doctor/diagnosis/{appointmentId}/drug', [DoctorController::class, 'addDrugDetails'])->name('doctor.addDrugDetails');
          Route::post('/doctor/diagnosis/{appointmentId}/lab', [DoctorController::class, 'addLabReport'])->name('doctor.addLabReport');
      ```
22.  
