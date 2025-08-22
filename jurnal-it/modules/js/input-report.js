 // Fungsi untuk memfokuskan dan membuka date/time picker
    function focusDateInput(inputId) {
        const input = document.getElementById(inputId);
        input.focus();
        input.showPicker();
    }

    // Set nilai default tanggal ke hari ini
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const formattedDate = today.toISOString().substr(0, 10);
        
        // Set nilai default untuk tanggal
        document.getElementById('inputTanggal').value = formattedDate;
        document.getElementById('inputCatatanTanggal').value = formattedDate;
        
        // Set waktu default (jam dan menit sekarang)
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const currentTime = `${hours}:${minutes}`;
        
        document.getElementById('inputStartTime').value = currentTime;
        document.getElementById('inputEndTime').value = currentTime;
        
        // Tambahkan event listener untuk form submission
        document.getElementById('laporanForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Laporan berhasil dikirim!');
        });
        
        document.getElementById('catatanForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Catatan berhasil dikirim!');
        });
    });