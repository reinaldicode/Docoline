<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocLine - Menu Utama</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        /* CSS untuk mengatur seluruh halaman */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            /* Latar belakang gradasi biru cerah yang lembut */
            background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        /* Kontainer utama untuk semua elemen */
        .main-container {
            background-color: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            text-align: center;
        }

        /* Styling untuk judul utama */
        .main-container h1 {
            color: #0056b3; /* Biru gelap untuk kontras */
            margin-bottom: 30px;
            font-weight: 600;
        }

        /* Grid untuk menata menu-menu */
        .menu-grid {
            display: grid;
            /* Membuat grid responsif, akan menyesuaikan jumlah kolom berdasarkan lebar layar */
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        /* Styling untuk setiap kartu menu */
        .menu-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: #f0faff; /* Biru sangat terang */
            border: 1px solid #b3e5fc;
            border-radius: 12px;
            text-decoration: none;
            color: #0056b3;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .menu-item:hover {
            transform: translateY(-5px); /* Efek mengangkat saat disentuh cursor */
            box-shadow: 0 8px 20px rgba(0, 123, 255, 0.15);
            background-color: #e3f2fd;
        }

        .menu-item img {
            width: 50px;
            height: 50px;
            margin-bottom: 15px;
        }
        
        /* Kontainer untuk tombol di bawah */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        /* Styling dasar untuk tombol */
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        /* Tombol utama (Login) */
        .btn-primary {
            background-color: #007bff; /* Biru cerah */
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3; /* Biru lebih gelap saat hover */
        }
        
        /* Tombol sekunder (Kembali) */
        .btn-secondary {
            background-color: #6c757d; /* Abu-abu netral */
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }

    </style>
</head>
<body>

    <div class="main-container">
        <h1>Document Control Online</h1>

        <div class="menu-grid">
            <a href="procedure_awal.php" class="menu-item">
                <img src="images/document.png" alt="Procedure Icon">
                <span>Procedure</span>
            </a>
            <a href="wi_awal.php" class="menu-item">
                <img src="images/doc2.png" alt="WI Icon">
                <span>WI</span>
            </a>
            <a href="form_awal.php" class="menu-item">
                <img src="images/doc3.png" alt="Form Icon">
                <span>Form</span>
            </a>
            <!-- <a href="mon_awal.php" class="menu-item">
                <img src="images/report.png" alt="Monitor Sample Icon">
                <span>Monitor Sample</span>
            </a>
            <a href="search_awal.php" class="menu-item">
                <img src="images/search3.png" alt="Search Icon">
                <span>Search</span>
            </a> -->
            </div>

        <div class="action-buttons">
            <a href="index.php" class="btn btn-secondary">Kembali</a>
            <a href="login.php" class="btn btn-primary" img src="images/login.png">Login</a>
        </div>
    </div>

</body>
</html>