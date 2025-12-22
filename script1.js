document.getElementById("formDaftar").addEventListener("submit", function (e) {
    e.preventDefault();

    const nama = document.getElementById("nama").value;
    const role = document.querySelector('input[name="role"]:checked');

    if (!role) {
        alert("Pilih Dosen atau Mahasiswa!");
        return;
    }

    alert(
        "Pendaftaran berhasil!\n" +
        "Nama: " + nama + "\n" +
        "Sebagai: " + role.value
    );
});


