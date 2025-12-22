document.getElementById("loginForm").addEventListener("submit", function(e) {
    const nim = document.getElementById("nim").value;
    const password = document.getElementById("password").value;

    if (nim.trim() === "" || password.trim() === "") {
        alert("Harap isi semua field!");
        e.preventDefault();
    }
});
