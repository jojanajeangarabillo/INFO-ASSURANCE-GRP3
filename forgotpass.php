<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#fdf2f6',
              100: '#f9dbe5',
              500: '#a61b4a',
              900: '#610C27',
            },
            custombg: '#EFECE9'
          }
        }
      }
    }
  </script>
</head>

<body class="bg-custombg flex items-center justify-center min-h-screen">

  <div class="w-full max-w-md">
    <div class="bg-white p-8 rounded-2xl shadow-xl">

      <!-- FORM STATE -->
      <div id="formState">
        <div class="text-center mb-8">
          <div class="w-24 h-24 rounded-full overflow-hidden mx-auto mb-6 shadow-lg border-4 border-white">
          <img src="JERS-LOGO.png" alt="Logo" class="w-full h-full object-cover">
          </div>
          <h1 class="text-2xl font-bold text-brand-900 mb-2">
            Forgot password?
          </h1>
          <p class="text-brand-500">
            No worries, we'll send you reset instructions.
          </p>
        </div>

        <form id="resetForm" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-brand-900 mb-1">
              Email
            </label>
            <input
              type="email"
              placeholder="Enter your email"
              class="w-full px-4 py-3 border border-brand-100 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
              required
            />
          </div>

          <button
            type="submit"
            class="w-full py-3 bg-brand-900 text-white rounded-lg hover:bg-brand-500 transition"
          >
            Reset password
          </button>
        </form>

        <div class="mt-8 text-center">
          <button class="text-sm font-medium text-brand-900 hover:text-brand-500">
            ← Back to log in
          </button>
        </div>
      </div>

      <!-- SUCCESS STATE -->
      <div id="successState" class="hidden text-center py-6">
        <div class="w-16 h-16 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-6">
          ✅
        </div>
        <h2 class="text-2xl font-bold text-brand-900 mb-3">
          Check your email
        </h2>
        <p class="text-brand-500 mb-8">
          We've sent a password reset link to your email address.
        </p>
        <button class="w-full py-3 bg-brand-900 text-white rounded-lg hover:bg-brand-500 transition">
          Back to login
        </button>
      </div>

    </div>
  </div>

  <script>
    const form = document.getElementById("resetForm");
    const formState = document.getElementById("formState");
    const successState = document.getElementById("successState");

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      formState.classList.add("hidden");
      successState.classList.remove("hidden");
    });
  </script>

</body>
</html>