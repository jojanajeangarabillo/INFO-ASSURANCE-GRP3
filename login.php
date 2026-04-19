<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>

<script src="https://cdn.tailwindcss.com"></script>

<!-- CUSTOM COLORS -->
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#fdf2f6',
          100: '#f9dbe5',
          500: '#a61b4a',
          900: '#610C27'
        },
        custombg: '#EFECE9' // YOUR BACKGROUND
      }
    }
  }
}
</script>

</head>

<body class="bg-custombg">

<div class="min-h-[80vh] flex items-center justify-center">
  <div class="w-full max-w-md">

    <!-- Header -->
    <div class="text-center mb-8">
      <div class="w-24 h-24 rounded-full overflow-hidden mx-auto mb-6 shadow-lg border-4 border-white">
         <img src="JERS-LOGO.png" alt="Logo" class="w-full h-full object-cover">
      </div>

      <h1 class="text-3xl font-bold text-brand-900 mb-2">
        Welcome back
      </h1>

      <p class="text-brand-500">
        Please enter your details to sign in.
      </p>

      <?php
      if (isset($_SESSION['success_message'])) {
          echo "<div class='mt-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm text-center'>" . $_SESSION['success_message'] . "</div>";
          unset($_SESSION['success_message']);
      }
      ?>
    </div>

    <!-- Card -->
    <div class="p-8 shadow-xl rounded-2xl bg-white">

      <form class="space-y-5" onsubmit="handleLogin(event)">

        <div>
          <label class="block text-sm font-medium mb-1">Username</label>
          <input type="text"
            class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500"
            placeholder="Enter your username" required>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Password</label>
          <input type="password"
            class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500"
            placeholder="••••••••" required>

          <div class="flex justify-between items-center pt-2 text-sm">
            <label><input type="checkbox"> Remember me</label>

            <button type="button"
              class="font-medium text-brand-900 hover:text-brand-500">
              Forgot password?
            </button>
          </div>
        </div>

        <button type="submit"
          class="w-full py-3 mt-2 text-base bg-brand-900 text-white rounded-xl hover:bg-brand-500 transition">
          Sign in
        </button>

      </form>
    </div>

    <p class="text-center mt-8 text-brand-500">
      Don't have an account?
      <a href="register.php" class="font-medium text-brand-900 hover:text-brand-500">
        Sign up
      </a>
    </p>

  </div>
</div>

<script>
function handleLogin(e){
  e.preventDefault();
  alert("Login success (demo)");
}
</script>

</body>
</html>