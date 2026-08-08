<?php
session_start();
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Regjistrohu</title>
    <link rel="stylesheet" href="assets/auth.css">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

<div class="auth-page">
    <div class="auth-box">
        <h2 class="auth-title">Regjistrohu</h2>
        <p class="auth-subtitle">Krijo llogarinë tënde</p>

        <form action="signup_process.php" method="POST">
            <div class="form-group">
                <label>Emri</label>
                <input type="text" name="emri" required>
            </div>

            <div class="form-group">
                <label>Mbiemri</label>
                <input type="text" name="mbiemri" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
    <label>Password</label>
    <input type="password" name="password" id="password" required>
    <small id="passwordError" style="color:red;"></small>
</div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_pass" required>
            </div>

            <div class="form-group">
                <label>Roli</label>
                <select name="roli" id="roli" required onchange="TregoFushaFemije()">
                    <option value="">Zgjidh rolin</option>
                    <option value="prind">Prind</option>
                    <option value="femije">Femije</option>
                </select>
            </div>

            <div id="femije_div" style="display:none;">
                <div class="form-group">
                    <label>ID e prindit</label>
                    <input type="number" name="prind_id">
                </div>

                <div class="form-group">
                    <label>Datëlindja</label>
                    <input type="date" name="ditelindja" id="ditelindja">
                </div>
            </div>

            <button type="submit" name="signup" class="auth-btn">
                Regjistrohu
            </button>
        </form>

        <div class="auth-link">
            Ke llogari?
            <a href="login.php">Logohu këtu</a>
        </div>
    </div>
</div>

<script>
function TregoFushaFemije() {
    let roli = document.getElementById('roli').value;
    let femijeDiv = document.getElementById('femije_div');
    let ditelindja = document.getElementById('ditelindja');

    if (roli === 'femije') {
        femijeDiv.style.display = 'block';
        ditelindja.required = true;
    } else {
        femijeDiv.style.display = 'none';
        ditelindja.required = false;
    }
}

const password = document.getElementById("password");
const passwordError = document.getElementById("passwordError");

password.addEventListener("input", function () {

    if (password.value.length < 8) {
        passwordError.innerHTML = "Fjalëkalimi duhet të ketë të paktën 8 karaktere.";
    } else {
        passwordError.innerHTML = "";
    }

});
</script>

</body>
</html>