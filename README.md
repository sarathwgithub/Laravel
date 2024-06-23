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
8. 
