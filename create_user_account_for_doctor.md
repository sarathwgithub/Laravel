--------------------------------------------------------------------------------------------------------------
**To create a user account for a doctor when registering a doctor, you need to perform the following steps:**
--------------------------------------------------------------------------------------------------------------
1. Update the Doctor Registration Form
   ```php
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Email') }}</label>
                            <input id="email" type="email" class="form-input rounded-md shadow-sm mt-1 block w-full" name="email" value="{{ old('email') }}" required>
                            @error('email')
                            <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-input rounded-md shadow-sm mt-1 block w-full" name="password" required>
                            @error('password')
                            <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Confirm Password') }}</label>
                            <input id="password_confirmation" type="password" class="form-input rounded-md shadow-sm mt-1 block w-full" name="password_confirmation" required>
                            @error('password_confirmation')
                            <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>
   ```
2. Add user_id column as a foreign key in the doctors table
      Create the Migration
      ```html
      php artisan make:migration add_user_id_to_doctors_table --table=doctors
4. Define the Migration:
   ```php
   public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id'); // Add the user_id column
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Add the foreign key constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['user_id']); // Drop the foreign key constraint
            $table->dropColumn('user_id'); // Drop the user_id column
        });
    }
6. Run Migration
   ```html
   php artisan migrate
8. Updating the Controller to Create a User Account for Each Doctor
   ```php
   use App\Models\User;
   use App\Models\Doctor;
   use App\Models\Specialization;
   use Illuminate\Support\Facades\Hash;
   use RealRashid\SweetAlert\Facades\Alert;
   public function storeDoctor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'specialization_id' => 'required|exists:specializations,id',
            'contact_information' => 'required|string|max:255',
            'dob' => 'required|date',
            'nic_number' => 'required|string|max:255|unique:doctors,nic_number',
            'gender' => 'required|in:Male,Female,Other',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'doctor',
        ]);

        Doctor::create([
            'name' => $request->name,
            'specialization_id' => $request->specialization_id,
            'contact_information' => $request->contact_information,
            'dob' => $request->dob,
            'nic_number' => $request->nic_number,
            'gender' => $request->gender,
            'user_id' => $user->id,
        ]);

        Alert::success('Doctor registered successfully', 'Doctor registered successfully');
        return redirect()->back();
    }
10. Ensure User and Doctor Models Are Related
    *Add the necessary relationship in the Doctor model to link it with the User model.*
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Doctor extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'specialization_id',
        'contact_information',
        'dob',
        'nic_number',
        'gender',
        'user_id', // Ensure this matches your database column
    ];

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

