<!----- VISTA DESPUES DEL LOGIN ... EN CASO DE QUE NO, MANDA A CREAR UNA CUENTA----->
<?php 
session_start();

if (isset($_SESSION['id']) && isset($_SESSION['fname'])) {

    include "db_conn.php";

    
    if (isset($_FILES['imagen'])) {
        $file = $_FILES['imagen'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];

        // Imagen válida (opcional)
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = array("jpg", "jpeg", "png");
        if (in_array($fileExt, $allowedExtensions)) {
            if ($fileError === 0) {
                if ($fileSize < 5000000) { // Tamaño máximo permitido (5MB)
                    $fileContent = file_get_contents($fileTmpName);

                    // Actualizar la tabla de imagen
                    $userId = $_SESSION['id'];
                    $sql = "UPDATE users SET profile_img = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$fileContent, $userId]);

                    
                    header("Location: home.php");
                    exit();
                } else {
                    $error = "El tamaño del archivo es demasiado grande. Por favor, elige una imagen más pequeña.";
                }
            } else {
                $error = "Ha ocurrido un error al cargar el archivo.";
            }
        } else {
            $error = "El tipo de archivo no está permitido. Por favor, elige una imagen en formato JPG, JPEG o PNG.";
        }
    }

    // Obtener la imagen desde la BD
    $userId = $_SESSION['id'];
    $sql = "SELECT profile_img FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $profileImage = $user['profile_img'];

    // Mostrar la imagen
    if ($profileImage) {
        $imageData = base64_encode($profileImage);
        $imageSrc = "data:image/jpeg;base64," . $imageData;
    } else {
        $imageSrc = "default_profile_image.jpg"; // imagen predeterminada 
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        
        <div class="shadow w-450 p-3 text-center border border-warning rounded-4">
            <h3 class="display-4 mb-2 text-warning fw-semibold">Hello, <?php echo $_SESSION['fname']; ?></h3>
            
            <!-- Mostrar  imagen -->
            <img src="<?php echo $imageSrc; ?>" alt="Profile Image" width="300">
            
            <!-- Cargar la imagen -->
            <form action="home.php" method="post" enctype="multipart/form-data">
                <input type="file" class="mb-2 mt-2" name="imagen">
                <button type="submit" class="btn btn-outline-info mt-1 mb-2 text-start">Load Imagen</button>
            </form>
            <div class="">
              <a href="logout.php" class="btn btn-warning mt-3 mb-2 fw-bold text-white">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>

<?php 
} else {
    header("Location: login.php");
    exit;
}
?>
