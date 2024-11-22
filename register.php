<?php include_once("header.php")?>

<div class="container">
<h2 class="my-3">Register new account</h2>

<!-- Create auction form -->
<form method="POST" action="process_registration.php">
  <div class="form-group row">
    <label for="accountType" class="col-sm-2 col-form-label text-right">Registering as a:</label>
	<div class="col-sm-10">
	  <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="accountType" id="accountBuyer" value="buyer" checked>
        <label class="form-check-label" for="accountBuyer">Buyer</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="accountType" id="accountSeller" value="seller">
        <label class="form-check-label" for="accountSeller">Seller</label>
      </div>
      <!-- <small id="accountTypeHelp" class="form-text-inline text-muted"><span class="text-danger">* Required.</span></small> -->
	</div>
  </div>
  <div class="form-group row">
    <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
	<div class="col-sm-10">
      <!-- add name for email-->
      <input type="text" class="form-control" id="email" name="email" placeholder="Email">
      <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  <!-- add username -->
  </div>
  <div class="form-group row">
    <label for="username" class="col-sm-2 col-form-label text-right">Username</label>
	<div class="col-sm-10">
      <input type="text" class="form-control" id="username" name="username" placeholder="username">
      <small id="usernameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  </div>
  <div class="form-group row">
    <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
    <div class="col-sm-10">
      <!-- add name for password -->
      <input type="password" class="form-control" id="password" name="password" placeholder="Password">
      <small id="passwordHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <div class="form-group row">
    <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
    <div class="col-sm-10">
      <!-- add name for passwordConfirmation -->
      <input type="password" class="form-control" id="passwordConfirmation" name="passwordConfirmation" placeholder="Enter password again">
      <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <div class="form-group row">
    <button type="submit" class="btn btn-primary form-control">Register</button>
  </div>
</form>

<div class="text-center">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a>

</div>


<script>
const form = document.querySelector('form');
const email = document.getElementById('email');
const username = document.getElementById('username');
const password = document.getElementById('password');
const passwordConfirmation = document.getElementById('passwordConfirmation');

// 确认元素是否正确获取到
console.log('Password Input:', password);
console.log('Password Confirmation:', passwordConfirmation);

password.addEventListener('input', function() {
    const minLength = 8;
    const passwordHelp = document.getElementById('passwordHelp');
    
    if (this.value.length < minLength) {
        passwordHelp.innerHTML = '<span class="text-danger">Password must be at least 8 characters long</span>';
        this.setCustomValidity('Password must be at least 8 characters long');
    } else {
        passwordHelp.innerHTML = '<span class="text-success">Password length is good</span>';
        this.setCustomValidity('');
    }
});

passwordConfirmation.addEventListener('input', function() {
    const passwordConfirmationHelp = document.getElementById('passwordConfirmationHelp');
    
      // 调试用
    console.log('Password:', password.value);
    console.log('Confirmation:', this.value);

    if (this.value !== password.value) {
        passwordConfirmationHelp.innerHTML = '<span class="text-danger">Passwords do not match</span>';
        this.setCustomValidity('Passwords do not match');
    } else {
        passwordConfirmationHelp.innerHTML = '<span class="text-success">Passwords match</span>';
        this.setCustomValidity('');
    }
});

email.addEventListener('blur', async function() {
    if (!this.value) return;
    const emailHelp = document.getElementById('emailHelp');
    
    try {
        const response = await fetch('check_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(this.value)}`
        });
        const data = await response.json();
        
        if (data.exists) {
            emailHelp.innerHTML = '<span class="text-danger">Email already exists</span>';
            this.setCustomValidity('Email already exists');
        } else {
            emailHelp.innerHTML = '<span class="text-success">Email is available</span>';
            this.setCustomValidity('');
        }
    } catch (error) {
        console.error('Error:', error);
    }
});

username.addEventListener('blur', async function() {
    if (!this.value) return;
    const usernameHelp = document.getElementById('usernameHelp');
    
    try {
        const response = await fetch('check_username.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${encodeURIComponent(this.value)}`
        });
        const data = await response.json();
        
        if (data.exists) {
            usernameHelp.innerHTML = '<span class="text-danger">Username already exists</span>';
            this.setCustomValidity('Username already exists');
        } else {
            usernameHelp.innerHTML = '<span class="text-success">Username is available</span>';
            this.setCustomValidity('');
        }
    } catch (error) {
        console.error('Error:', error);
    }
});
</script>

<?php include_once("footer.php")?>