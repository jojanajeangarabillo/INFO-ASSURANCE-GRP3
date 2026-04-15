<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Signup</title>

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
          900: '#610C27'
        },
        custombg: '#EFECE9'
      }
    }
  }
}
</script>

</head>

<body class="bg-custombg">

<div class="min-h-[80vh] flex items-center justify-center py-12">
  <div class="w-full max-w-lg">

    <!-- HEADER -->
    <div class="text-center mb-8">
      <div class="w-24 h-24 rounded-full overflow-hidden mx-auto mb-6 shadow-lg border-4 border-white">
         <img src="logo.jpg" alt="Logo" class="w-full h-full object-cover">
      </div>

      <h1 class="text-3xl font-bold text-brand-900 mb-2">
        Create an account
      </h1>

      <p class="text-brand-500">
        Join J3RS to start shopping today.
      </p>
    </div>

    <!-- CARD -->
    <div class="p-8 bg-white rounded-2xl shadow-xl">

      <form class="space-y-5" onsubmit="handleRegister(event)">

        <!-- USERNAME -->
        <div>
          <label class="block mb-1 font-medium">Username</label>
          <input type="text" class="w-full p-3 border rounded-lg" required>
        </div>

        <!-- EMAIL -->
        <div>
          <label class="block mb-1 font-medium">Email</label>
          <input type="email" class="w-full p-3 border rounded-lg" required>
        </div>

        <!-- PASSWORD -->
        <div>
          <label class="block mb-1 font-medium">Password</label>
          <input type="password" id="password"
            class="w-full p-3 border rounded-lg"
            oninput="checkPassword()" required>

          <!-- RULES -->
          <div class="bg-brand-50 p-4 rounded-xl mt-3 border">
            <p class="text-xs font-semibold text-brand-900 mb-2">
              Password requirements:
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">

              <div id="rule-length">❌ At least 12 characters</div>
              <div id="rule-upper">❌ Uppercase letter</div>
              <div id="rule-lower">❌ Lowercase letter</div>
              <div id="rule-number">❌ Number</div>
              <div id="rule-special">❌ Special character</div>

            </div>
          </div>
        </div>

        <!-- CONFIRM PASSWORD -->
        <div>
          <label class="block mb-1 font-medium">Confirm Password</label>
          <input type="password" class="w-full p-3 border rounded-lg" required>
        </div>

        <!-- CAPTCHA -->
        <div class="bg-brand-50 border rounded-xl p-4 flex justify-between items-center">
          <label>
            <input type="checkbox"> I am not a robot
          </label>
          <span class="text-xs text-brand-500">CAPTCHA</span>
        </div>

        <!-- TERMS -->
        <div class="flex items-start gap-2">
          <input type="checkbox" id="terms" onchange="toggleButton()">
          <span class="text-sm">
            I agree to the
            <button type="button" onclick="openTerms()" class="text-brand-500 font-medium">
              Terms and Agreements
            </button>
          </span>
        </div>

        <!-- BUTTON -->
        <button id="submitBtn"
          class="w-full py-3 bg-brand-900 text-white rounded-xl mt-4 opacity-50 cursor-not-allowed"
          disabled>
          Create Account
        </button>

      </form>
    </div>

    <!-- LOGIN -->
    <p class="text-center mt-8 text-brand-500">
      Already have an account?
      <button class="text-brand-900 font-medium">
        Log in
      </button>
    </p>

  </div>
</div>

<!-- TERMS MODAL -->
<div id="termsModal" class="hidden fixed inset-0 flex items-center justify-center bg-black/40">

  <div class="bg-white p-6 rounded-xl max-w-lg w-full">
    <h2 class="text-xl font-bold text-brand-900 mb-3">Terms and Agreements</h2>

    <p class="text-sm text-brand-500 mb-4">
      Sample terms... You agree to follow the rules.
    </p>

    <div class="flex justify-end gap-3">
      <button onclick="closeTerms()">Decline</button>
      <button onclick="acceptTerms()" class="bg-brand-900 text-white px-4 py-2 rounded">
        Accept
      </button>
    </div>
  </div>

</div>

<script>
function checkPassword(){
  let p = document.getElementById("password").value;

  updateRule("rule-length", p.length >= 12);
  updateRule("rule-upper", /[A-Z]/.test(p));
  updateRule("rule-lower", /[a-z]/.test(p));
  updateRule("rule-number", /[0-9]/.test(p));
  updateRule("rule-special", /[^A-Za-z0-9]/.test(p));
}

function updateRule(id, valid){
  let el = document.getElementById(id);
  el.innerHTML = (valid ? "✅ " : "❌ ") + el.innerText.slice(2);
  el.className = valid ? "text-green-600" : "text-gray-500";
}

function toggleButton(){
  let checkbox = document.getElementById("terms");
  let btn = document.getElementById("submitBtn");

  btn.disabled = !checkbox.checked;
  btn.classList.toggle("opacity-50");
  btn.classList.toggle("cursor-not-allowed");
}

function handleRegister(e){
  e.preventDefault();
  alert("Registered Successfully!");
}

function openTerms(){
  document.getElementById("termsModal").classList.remove("hidden");
}

function closeTerms(){
  document.getElementById("termsModal").classList.add("hidden");
}

function acceptTerms(){
  document.getElementById("terms").checked = true;
  toggleButton();
  closeTerms();
}
</script>

</body>
</html>