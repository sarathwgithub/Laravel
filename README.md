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
