document.getElementById("forgotForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value;

    if (email === "") {
        alert("Email harus diisi!");
        return;
    }

    alert(
        "Link reset kata sandi telah dikirim ke:\n" + email +
        "\n\n(Simulasi)"
    );

    document.getElementById("forgotForm").reset();
});
