<?php
session_start();
require_once 'conexionSQL.php';

// headers to tell that result is JSON
header('Content-Type: application/json; charset=utf-8');

$datos = json_decode(file_get_contents("php://input"), true);
$opc = $datos["tipo"];
$fechaActual = date('Y-m-d');
switch ($opc) {
    case 'buscar':
        try {
            // Consulta SQL a los Negocios existentes
            // $sqlNegocios = "SELECT N.id, N.categoria, N.nombreNegocio, N.servicioDomicilio, N.domicilio, N.telefono, N.ubicacionMaps, N.rutaImagenNegocio, V.calificacion FROM mglNegocios N
            //inner join mglValoraciones v  on v.idNegocio = n.id";
            $sqlNegocios = "WITH UniqueBusinesses AS (
                            SELECT *,
                                ROW_NUMBER() OVER(PARTITION BY nombreNegocio ORDER BY id) AS row_num
                            FROM mglNegocios
                        )
                        SELECT n.id, n.categoria, n.nombreNegocio, n.servicioDomicilio, n.domicilio, n.telefono, n.ubicacionMaps, 
                        (CASE WHEN n.rutaImagenNegocio = 'src\images\Negocios\' THEN '' 
                        WHEN n.rutaImagenNegocio = 'src\images\Negocios\*' THEN '' 
                        ELSE n.rutaImagenNegocio END)rutaImagenNegocio, n.Horario, AVG(v.calificacion) calificacion                       
                        FROM UniqueBusinesses n
                        LEFT JOIN mglValoraciones v ON v.idNegocio = n.id
                        WHERE n.row_num = 1
                        group by n.id, n.categoria, n.nombreNegocio, n.servicioDomicilio, n.domicilio, n.telefono, n.ubicacionMaps,n.rutaImagenNegocio,n.Horario,v.calificacion";
            $stmt = $conn->prepare($sqlNegocios);
            $stmt->execute();
            $negocios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Consulta SQL a las categorías existentes
            $sqlCategorias = "SELECT id, categoria,icono FROM mglCategorias";
            $stmt2 = $conn->prepare($sqlCategorias);
            $stmt2->execute();
            $categorias = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            //Consulta de los comentarios, opionios y/o valoraciones
            $sqlValoraciones = "SELECT idNegocio, categoria, nombreNegocio, calificacion, comentarios, fecha, usuario FROM mglValoraciones";
            $stmt3 = $conn->prepare($sqlValoraciones);
            $stmt3->execute();
            $Valoraciones = $stmt3->fetchAll(PDO::FETCH_ASSOC);

            // Enviar la respuesta en formato JSON
            echo json_encode([
                "status" => "OK",
                "negocios" => $negocios,
                "categorias" => $categorias,
                "Valoraciones" => $Valoraciones
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "status" => "ERROR",
                "message" => $e->getMessage()
            ]);
        }
        break;
    case 'registraUsuario': // Insert a la BD para guardar al usuario registrado
        // Extraer datos del JSON
        $info = $datos['info'];
        try {
            // Hash de la contraseña usando password_hash
            $pswSeguro = password_hash($info['contrasena'], PASSWORD_DEFAULT);

            // Preparar la consulta SQL
            $sql = "INSERT INTO mglUsuarios (nombre, usuario, contrasena, correo, telefono, fechaRegistro,perfil) 
                    VALUES (:nombreCompleto, :usuario, :contrasena, :correo, :telefono, :fechaRegistro,:perfil)";
            $stmt = $conn->prepare($sql);

            // Vincular los parámetros
            $stmt->bindParam(':nombreCompleto', $info['nombreCompleto']);
            $stmt->bindParam(':usuario', $info['usuario']);
            $stmt->bindParam(':contrasena', $pswSeguro);
            $stmt->bindParam(':correo', $info['correo']);
            $stmt->bindParam(':telefono', $info['telefono']);
            $stmt->bindParam(':fechaRegistro', $fechaActual);
            $stmt->bindParam(':perfil', 'Lector');

            // Ejecutar la consulta
            if ($stmt->execute()) {
                echo json_encode(["status" => "OK", "message" => "Usuario registrado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al registrar usuario, Intente mas tarde."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Error de conexión: " . $e->getMessage()]);
        }
        break;
    case 'registraNegocio':
        $info = $datos['info'];
        try {

            // Encriptar la contraseña
            $pswSeguro = password_hash($info['contrasena'], PASSWORD_DEFAULT);

            // Procesar la imagen
            $imagenBase64 = $info['rutaImagenNegocio'];

            // Extraer el tipo y el contenido de la imagen
            list($tipo, $base64String) = explode(';', $imagenBase64);
            list(, $base64String) = explode(',', $base64String);

            // Determinar la extensión del archivo
            $tipoImagen = explode(':', $tipo)[1];
            $extension = '';

            if (strpos($tipoImagen, 'image/png') !== false) {
                $extension = '.png';
            } elseif (strpos($tipoImagen, 'image/jpeg') !== false) {
                $extension = '.jpg'; // Puedes usar .jpeg si prefieres
            }

            // Generar el nombre de la imagen
            $nombreImagen = str_replace(' ', '_', $info['nombreNegocio']) . $extension; // Reemplazar espacios por _
            $rutaImagen = 'src\\images\\Negocios\\' . $nombreImagen;

            // Guardar la imagen en la ruta
            file_put_contents($rutaImagen, base64_decode($base64String));
            // Primera consulta: Insertar en mglNegocios
            $sql1 = "INSERT INTO mglNegocios (nombreNegocio, categoria, servicioDomicilio, domicilio, telefono, ubicacionMaps, rutaImagenNegocio, Horario) 
                         VALUES (:nombreNegocio, :categoria, :servicioDomicilio, :domicilio, :telefono, :ubicacionMaps, :rutaImagenNegocio, :Horario)";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bindParam(':nombreNegocio', $info['nombreNegocio']);
            $stmt1->bindParam(':categoria', $info['categoria']);
            $stmt1->bindParam(':servicioDomicilio', $info['servicioDomicilio']);
            $stmt1->bindParam(':domicilio', $info['domicilio']);
            $stmt1->bindParam(':telefono', $info['telefono']);
            $stmt1->bindParam(':ubicacionMaps', $info['ubicacionMaps']);
            $stmt1->bindParam(':rutaImagenNegocio', $rutaImagen);
            $stmt1->bindParam(':Horario', $info['horario']);
            // Ejecutar la primera consulta
            $stmt1->execute();

            // Segunda consulta: Insertar en mglUsuarios
            $sql2 = "INSERT INTO mglUsuarios (nombre, usuario, contrasena, correo, telefono, fechaRegistro, perfil, nombreNegocio) 
                         VALUES (:nombre, :usuario, :contrasena, :correo, :telefono, :fechaRegistro, 'Negocio', :nombreNegocio)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':nombre', $info['nombre']);
            $stmt2->bindParam(':usuario', $info['usuario']);
            $stmt2->bindParam(':contrasena', $pswSeguro); // Usar la contraseña cifrada
            $stmt2->bindParam(':correo', $info['correo']);
            $stmt2->bindParam(':telefono', $info['telefono']);
            $stmt2->bindParam(':fechaRegistro', $fechaActual);
            $stmt2->bindParam(':nombreNegocio', $info['nombreNegocio']);
            // Ejecutar la segunda consulta
            $stmt2->execute();
            echo json_encode(["status" => "OK", "message" => "Negocio registrado correctamente."]);
        } catch (PDOException $e) {
            // Si ocurre un error, revertir la transacción y mostrar el mensaje de error
            $conn->rollBack();
            echo json_encode(["status" => "error", "message" => "Error de conexión: " . $e->getMessage()]);
        }
        break;
    case 'iniciarSesion':
        $info = $datos['info'];
        $sql = "SELECT id,nombre,usuario,contrasena,correo,telefono,fechaRegistro,perfil,nombreNegocio,versionPremium,fechainicioVersionPremium,fechaFinalVersionPremium
                FROM mglUsuarios where usuario = :usuario";
        try {
            // Preparar la consulta
            $stmt = $conn->prepare($sql);

            // Vincular el parámetro
            $stmt->bindParam(':usuario', $info['username']);

            // Ejecutar la consulta
            $stmt->execute();

            // Obtener el resultado
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si el usuario existe
            if ($resultado) {
                // Comparar la contraseña directamente
                if (password_verify($info['password'], $resultado['contrasena'])) {
                    //guardamos la sesion iniciada en la variable de session
                    $_SESSION['usuario'] = $resultado['usuario'];
                    $_SESSION['nombre'] = $resultado['nombre'];
                    $_SESSION['perfil'] = $resultado['perfil'];
                    $_SESSION['iduser'] = $resultado['id'];
                    $_SESSION['esPremium'] = $resultado['versionPremium'];
                    $_SESSION['nombreNegocio'] = $resultado['nombreNegocio'];
                    echo json_encode([
                        "status" => "OK",
                        "message" => "Bienvenido " . $resultado['nombre'] . ", nos encanta tenerte de visita",
                        "session" => $_SESSION['usuario'],
                        "perfil" => $_SESSION['perfil'],
                        "esPremium" => $_SESSION['esPremium'],
                        "nombreNegocio" => $_SESSION['nombreNegocio']
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error con su usuario o contraseña"
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error con su usuario o contraseña"
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Error de conexión: " . $e->getMessage()
            ]);
        }

        break;
    case 'verificaSession':
        if (isset($_SESSION['usuario'])) {
            echo json_encode([
                'status' => 'OK',
                'session' => $_SESSION['usuario'],
                "esPremium" => $_SESSION['esPremium'],
                "nombreNegocio" => $_SESSION['nombreNegocio']
            ]);
        } else {
            echo json_encode(['status' => 'NOT_LOGGED_IN']);
        }
        break;
    case 'cerrarSesion':
        session_unset(); // Elimina todas las variables de sesión
        session_destroy(); // Destruye la sesión
        echo json_encode([
            "message" => "¡Nos vemos pronto!",
            'status' => 'OK'
        ]);
        break;
    case 'actualizaPremium':   //Proceso para el pago de la membresía  
        $info = $datos['info'];

        // Sumar 30 días a la fecha actual
        $fechaFin = date('Y-m-d', strtotime($fechaActual . ' + 30 days'));

        //obtenemos los datos de la tarjeta agregada
        $cardName = $info['cardName'];
        $cardNumber = $info['cardNumber'];
        $expiryDate = $info['expiryDate'];
        $cvv = $info['cvv'];
        // Datos de tarjeta de prueba
        $validCardNumber = "4111111111111111";
        $validExpiryDate = "12/25";
        $validCvv = "123";
        if ($cardNumber === $validCardNumber && $expiryDate === $validExpiryDate && $cvv === $validCvv) {

            //Hacemos UPDATE a la cuenta del usuario
            $sqlupdate = "UPDATE mglUsuarios SET versionPremium = 'Y', fechainicioVersionPremium = :fechaInicio, fechaFinalVersionPremium = :fechaFin 
            WHERE id = :id";
            $stmt = $conn->prepare($sqlupdate);

            // Vincular los parámetros
            $stmt->bindParam(':fechaInicio', $fechaActual);
            $stmt->bindParam(':fechaFin', $fechaFin);
            // $stmt->bindParam(':usuario', $_SESSION['user']);
            $stmt->bindParam(':id', $_SESSION['iduser']);

            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "OK",
                    "message" => "Ya eres miembro premium. Disfruta de tus ofertas exclusivas."
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al procesar el pago. Intente más tarde."]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Datos de tarjeta inválidos.']);
        }
        break;
    case 'promos': //obtenemos todas las promos existentes
        try {
            $sqlPromos = "SELECT Id,contenido,negocio,categoria,FechaCreacion,rutaImagen FROM mglPublicaciones";
            $stmt = $conn->prepare($sqlPromos);
            $stmt->execute();
            $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Enviar la respuesta en formato JSON
            echo json_encode([
                "status" => "OK",
                "promos" => $promos,
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "status" => "ERROR",
                "message" => $e->getMessage()
            ]);
        }
        break;
    case 'newPost'://guardamos la info de la publicacion
            $info = $datos['info'];
        
            try {              
                // Procesar la imagen
                $imagenBase64 = $info['rutaImagen'];   
                // Extraer el tipo y el contenido de la imagen
                list($tipo, $base64String) = explode(';', $imagenBase64);
                list(, $base64String) = explode(',', $base64String); 
                // Determinar la extensión del archivo
                $tipoImagen = explode(':', $tipo)[1];
                $extension = '';
                if (strpos($tipoImagen, 'image/png') !== false) {
                    $extension = '.png';
                } elseif (strpos($tipoImagen, 'image/jpeg') !== false) {
                    $extension = '.jpg'; // Puedes usar .jpeg si prefieres
                }

                // Generar el nombre de la imagen
                $nombreImagen = str_replace(' ', '_', $info['negocio']) . $extension; // Reemplazar espacios por _
                $rutaImagen = 'src\\images\\publicaciones\\' . $nombreImagen;
    
                // Guardar la imagen en la ruta
                file_put_contents($rutaImagen, base64_decode($base64String));
                // Insertar en mglPublicaciones
                $sqlInsert = "INSERT INTO mglPublicaciones (contenido, negocio, categoria, fechaCreacion, rutaImagen) 
                                             VALUES (:contenido, :negocio, :categoria, :fechaCreacion,:rutaImagen)";
                $stmt1 = $conn->prepare($sqlInsert);
                $stmt1->bindParam(':contenido', $info['contenido']);
                $stmt1->bindParam(':negocio', $info['negocio']);
                $stmt1->bindParam(':categoria', $info['categoria']);
                $stmt1->bindParam(':fechaCreacion', $fechaActual);
                $stmt1->bindParam(':rutaImagen', $rutaImagen);
                // Ejecutar la primera consulta

                if ($stmt1->execute()) {
                    echo json_encode(["status" => "OK"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error al realizar la publicación, Intente mas tarde."]);
                }
            } catch (PDOException $e) {
                // Si ocurre un error, revertir la transacción y mostrar el mensaje de error
                $conn->rollBack();
                echo json_encode(["status" => "error", "message" => "Error de conexión: " . $e->getMessage()]);
            }

        break;
    case 'newComment'://guardamos la info de la publicacion
            $info = $datos['info'];        
            try {                 
                //Insertar en mglNegocios
                $sqlInsert = "INSERT INTO mglValoraciones (idNegocio, categoria, nombreNegocio, calificacion, comentarios, fecha, usuario) 
                                             VALUES (:idNegocio, :categoria, :nombreNegocio, :calificacion,:comentarios,:fecha,:usuario)";
                $stmt1 = $conn->prepare($sqlInsert);
                $stmt1->bindParam(':idNegocio', $info['idNegocio']);
                $stmt1->bindParam(':categoria', $info['categoria']);
                $stmt1->bindParam(':nombreNegocio', $info['nombreNegocio']);
                $stmt1->bindParam(':calificacion', $info['calificacion']);
                $stmt1->bindParam(':comentarios', $info['comentarios']);
                $stmt1->bindParam(':fecha', $fechaActual);
                $stmt1->bindParam(':usuario', $info['usuario']);
                // Ejecutar la primera consulta

                if ($stmt1->execute()) {
                    echo json_encode(["status" => "OK"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error al realizar la publicación, Intente mas tarde."]);
                }
            } catch (PDOException $e) {
                // Si ocurre un error, revertir la transacción y mostrar el mensaje de error
                $conn->rollBack();
                echo json_encode(["status" => "error", "message" => "Error de conexión: " . $e->getMessage()]);
            }

        break;
};
