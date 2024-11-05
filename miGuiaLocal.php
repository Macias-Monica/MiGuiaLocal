<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mi Guia Local</title>
  <link rel="stylesheet" href="src/paquetes/bootstrap-5.3.3-dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="src/styles.css" />
  <link rel="stylesheet" href="src/fontawesome/css/all.min.css" />
  <link rel="stylesheet" href="src/paquetes/sweetalert2/dist/sweetalert2.min.css" />
</head>

<body>
  <div id="app" class="container my-5">
    <header :class="{ compact: isCompact }">
      <div class="header-container">
        <div class="header-logo" @click="elegirCategoria('')">
          <img src="src/images/Logotipo.jpeg" alt="Logo" />
          <span @click="openModal(2)"><b>Publica tu negocio</b></span>
        </div>
        <div class="header-title">
          <h1>Mi Guia Local</h1>
        </div>
        <div class="header-user">
          <span v-if="isLoggedIn" style="padding-right: 1rem;">
            <b>
              <i v-if="esPremium" class="fas fa-gem" style="color: goldenrod; margin-right: 1px;"></i> Bienvenido,
              {{UsuarioLogin}} </b>
          </span>
          <button class="name-user" @click="toggleDropdown">
            <img src="src/images/user.png" alt="Usuario" />
          </button>
          <div class="dropdown-container" v-if="isDropdownVisible">
            <div v-if="!isLoggedIn">
              <div class="dropdown-item" @click="openModal(1)">Registrarse</div>
              <div class="dropdown-item" @click="openModal(5)">Iniciar sesión</div>
            </div>
            <div v-else>
              <div class="dropdown-item" @click="logout">Cerrar sesión</div>
            </div>
          </div>
        </div>
      </div>
      <div class="premium-container" v-if="!esPremium">
        <span></span> ¡Conviertete en <a href="#" @click.prevent="openModal(4)">
          <b><i class="fas fa-gem"></i> miembro premium </b>
        </a> para recibir increibles ofertas exclusivas! </span>
      </div>
      <div class="search-container">
        <div class="form-group row">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Busca tu negocio..." aria-label="buscar"
              v-model="busqueda" />
            <span class="input-group-text">
              <i class="fas fa-search"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="divider"></div>
      <!-- SE VEN TODAS LAS CATEGORIAS-->
      <div class="content-container categorias">
        <button @click="scrollLeft" class="nav-button left"><i class="fas fa-chevron-left"></i></button>
        <div class="categories-carousel" ref="carousel">
          <div v-for="(categoria, index) in categorias" :key="index" class="category-item"
            @click="elegirCategoria(categoria.categoria)">
            <i :class="categoria.icono"></i>
            <p>{{ categoria.categoria }}</p>
          </div>
        </div>
        <button @click="scrollRight" class="nav-button right">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </header>
    <div class="divider" v-if="esPremium"></div>
    <div class="container-opcPremium" v-if="esPremium || (nombreNegocio2 !== '' && nombreNegocio2 !== null)">
      <div class="row justify-content-center">
        <div class="col-auto d-flex gap-2">
          <button type="button" class="btn btn-secondary" :class="{'btn-selected': !mostrarNegocios}"
            @click="cambiarpantalla('verPromos')">Ver mis promociones</button>
          <button type="button" class="btn btn-secondary" :class="{'btn-selected': mostrarNegocios}"
            @click="cambiarpantalla('verNegocios')">Ver los negocios</button>
        </div>
      </div>
    </div>
    <div class="divider"></div>
    <!-- SE VEN TODOS LOS NEGOCIOS-->
    <div class="row" v-if="mostrarNegocios">
      <div class="col-md-4" v-for="negocio in negociosFiltrados" :key="negocio.nombreNegocio">
        <div class="card mb-4 text-center">
          <div class="card-body">
            <h5 class="card-title"> {{ negocio.nombreNegocio }} <span
                class="badge bg-primary ms-2">{{ negocio.categoria }}</span>
            </h5>
            <div class="rating" @click="openModal(3, negocio.id)">
              <span v-for="star in 5" :key="star" class="star"
                :class="{ 'filled': star <= negocio.calificacion }">★</span>
            </div>
            <div>
              <img v-if="negocio.rutaImagenNegocio" :src="negocio.rutaImagenNegocio" class="card-img-top"
                :alt="negocio.nombreNegocio" />
              <img v-else class="card-img-top" src="src\images\Negocios\default.jpg"></img>
            </div>
            <h6 class="card-text">{{ negocio.domicilio }}</h6>
            <h6 class="card-text">{{ negocio.telefono }}</h6>
            <a :href="negocio.ubicacionMaps" target="_blank" class="btn btn-primary">
              <i class="fas fa-map-marker-alt"></i> Ver en Maps </a>
          </div>
        </div>
      </div>
    </div>
    <div class="container" v-else>
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">Crear una publicación</h5>
          <div class="input-group mb-3">
            <input type="file" id="image-upload" @change="onImageChange" class="d-none" accept="image/*" />
            <label for="image-upload" class="btn btn-outline-primary rounded-left">
              <i class="fas fa-image"></i> <!-- Ícono de imagen -->
            </label>
            <textarea v-model="newPost" class="form-control" rows="3" placeholder="Escribe algo..."></textarea>
            <button class="btn btn-primary rounded-right" @click="addPost">Publicar</button>
          </div>
          <div v-if="imagePreview" class="image-preview mt-2">
            <img :src="imagePreview" alt="Preview" class="img-thumbnail" />
          </div>
        </div>
      </div>
      <div>
        <h2>Publicaciones</h2>
        <div class="row">
          <div class="col-md-6" v-for="(post, index) in promos" :key="index">
            <div class="card mb-3">
              <div class="card-body">
                <h5 class="card-title">{{ post.negocio }} <span
                    class="badge bg-primary ms-2">{{ post.categoria }}</span></h5>
                <h4>{{post.contenido}}</h4>
                <div>
                  <img v-if="post.rutaImagen" :src="post.rutaImagen" class="img-custom mb-2"
                    style="width: 100%; height: auto; object-fit: cover;" />
                </div>
                <p class="card-text"><small class="text-muted">Creado el: {{ post.FechaCreacion }}</small></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="divider"></div>
    <footer class="bg-light text-dark text-center text-lg-start">
      <div class="container p-4">
        <div class="row">
          <div class="col-lg-6 col-md-12 mb-4">
            <div class="accordion" id="accordionExample">
              <!-- Sección Misión -->
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingMision">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseVision" aria-expanded="false" aria-controls="collapseMision"> Misión
                  </button>
                </h2>
                <div id="collapseMision" class="accordion-collapse collapse" aria-labelledby="headingMision"
                  data-bs-parent="#accordionExample">
                  <div class="accordion-body"> Nuestra misión es brindar productos y servicios de alta calidad para
                    satisfacer las necesidades de nuestros clientes, fomentando un ambiente de respeto y
                    profesionalismo. </div>
                </div>
              </div>
              <!-- Sección Visión -->
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingVision">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseVision" aria-expanded="false" aria-controls="collapseVision"> Visión
                  </button>
                </h2>
                <div id="collapseVision" class="accordion-collapse collapse" aria-labelledby="headingVision"
                  data-bs-parent="#accordionExample">
                  <div class="accordion-body"> Nuestra visión es ser líderes en el mercado, reconocidos por nuestra
                    innovación, calidad y compromiso con la satisfacción del cliente. </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-6 mb-4"></div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="text-uppercase">Contacto</h5>
            <p> Email: info@tuguialocal.com <br /> Teléfono: +123 456 7890 </p>
            <div>
              <a href="#!" class="text-black me-4">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="#!" class="text-black me-4">
                <i class="fab fa-twitter"></i>
              </a>
              <a href="#!" class="text-black me-4">
                <i class="fab fa-instagram"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </footer>
    <!-- Inicio Modal Registro Usuario-->
    <div class="modal fade" id="registroUserModal" tabindex="-1" aria-labelledby="registroUserModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="registroUserModalLabel"> Registrarse </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label for="nombreCompletoUser" class="form-label"> Nombre Completo </label>
                <input type="text" class="form-control" v-model="user.nombreCompleto" id="nombreCompletoUser"
                  placeholder="Ingresa tu nombre completo" required />
              </div>
              <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" v-model="user.usuario" id="usuario"
                  placeholder="Ingresa tu usuario" required />
              </div>
              <div class="mb-3">
                <label for="contrasenaUser" class="form-label"> Contraseña </label>
                <input type="password" class="form-control" v-model="user.contrasena" id="contrasenaUser"
                  placeholder="Ingresa tu contraseña" required />
              </div>
              <div class="mb-3">
                <label for="correoUser" class="form-label"> Correo Electrónico </label>
                <input type="email" class="form-control" v-model="user.correo" id="correoUser"
                  placeholder="Ingresa tu correo electrónico" required />
              </div>
              <div class="mb-3">
                <label for="telefonoUser" class="form-label">Teléfono</label>
                <input type="tel" class="form-control" v-model="user.telefono" id="telefonoUser"
                  placeholder="Ingresa tu número de teléfono" required />
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Cerrar </button>
            <button type="button" class="btn btn-primary" @click="register"> Guardar cambios </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin Modal Registro Usuario-->
    <!-- Inicio Modal Registro Negocio-->
    <div class="modal fade" id="registroNegocioModal" tabindex="-1" aria-labelledby="registroNegocioModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="registroNegocioModalLabel"> Registrar tu Negocio </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label for="nombreCompletoNegocio" class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" v-model="business.nombre" id="nombreCompletoNegocio"
                  placeholder="Ingresa tu nombre completo" required />
              </div>
              <div class="mb-3">
                <label for="nombreNegocio" class="form-label">Nombre del Negocio</label>
                <input type="text" class="form-control" v-model="business.nombreNegocio" id="nombreNegocio"
                  placeholder="Ingresa el nombre de tu negocio" required />
              </div>
              <div class="mb-3">
                <label for="categoria" class="form-label">Categoría</label>
                <select class="form-select" v-model="business.categoria" id="categoria" required>
                  <option value disabled selected>Selecciona una categoría</option>
                  <option v-for="cat in categorias" :key="cat.categoria" :value="cat.categoria"> {{cat.categoria}}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label for="servicioDomicilio" class="form-label">¿Ofrece servicio a domicilio?</label>
                <select class="form-select" v-model="business.servicioDomicilio" id="servicioDomicilio" required>
                  <option value disabled selected>Selecciona una opción</option>
                  <option value="S">Sí</option>
                  <option value="N">No</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="domicilio" class="form-label">Domicilio</label>
                <input type="text" class="form-control" v-model="business.domicilio" id="domicilio"
                  placeholder="Ingresa el domicilio de tu negocio" required />
              </div>
              <div class="mb-3">
                <label for="ubicacionMaps" class="form-label">Ubicación en Google Maps</label>
                <input type="url" class="form-control" v-model="business.ubicacionMaps" id="ubicacionMaps"
                  placeholder="URL de Google Maps" required />
              </div>
              <div class="mb-3">
                <label for="rutaImagenNegocio" class="form-label">Imagen del Negocio</label>
                <input type="file" class="form-control" id="rutaImagenNegocio" @change="onImageChange" accept="image/*"
                  required />
              </div>
              <div class="mb-3">
                <label for="horario" class="form-label">Horario de Atención</label>
                <input type="text" class="form-control" v-model="business.horario" id="horario"
                  placeholder="Ejemplo: Lunes a Viernes de 9:00 a 18:00" required />
              </div>
              <div class="mb-3">
                <label for="usuarioNegocio" class="form-label">Usuario</label>
                <input type="text" class="form-control" v-model="business.usuario" id="usuarioNegocio"
                  placeholder="Ingresa tu usuario" required />
              </div>
              <div class="mb-3">
                <label for="contrasenaNegocio" class="form-label">Contraseña</label>
                <input type="password" class="form-control" v-model="business.contrasena" id="contrasenaNegocio"
                  placeholder="Ingresa tu contraseña" required />
              </div>
              <div class="mb-3">
                <label for="correoNegocio" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" v-model="business.correo" id="correoNegocio"
                  placeholder="Ingresa tu correo electrónico" required />
              </div>
              <div class="mb-3">
                <label for="telefonoNegocio" class="form-label">Teléfono</label>
                <input type="tel" class="form-control" v-model="business.telefono" id="telefonoNegocio"
                  placeholder="Ingresa tu número de teléfono" required />
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Cerrar </button>
            <button type="button" class="btn btn-primary" @click="addNegocio"> Guardar cambios </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fin Modal Registro Negocio-->
    <!--Modal de valoraciones, comentarios y/u opiniones-->
    <div class="modal fade" id="valoracionesModal" tabindex="-1" aria-labelledby="valoracionesModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="valoracionesModalLabel"> Comentarios </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div>
              <div class="rating">
                <span v-for="star in 5" :key="star" class="star" @click="setRating(star)"
                  :class="{ filled: star <= calificacion }"> ★ </span>
              </div>
              <label for="comentario" class="form-label">Tu comentario</label>
              <textarea class="form-control" v-model="nuevoComentario" rows="1"
                @keyup.enter="enviarValoracion"></textarea>
            </div>
            <div class="divider"></div>
            <div v-for="comentario in comentariosFiltrados" :key="comentario.id">
              <div class="row align-items-center mb-2">
                <div class="col-auto">
                  <strong>              
                    <span v-if="comentario.usuario" class="comment-username">
                      <i class="fas fa-user-circle"></i>
                      {{ comentario.usuario }}
                    </span>
                    <span v-else>
                      <i class="fas fa-user-circle"></i>
                    </span>
                  </strong>
                </div>
                <div class="col-auto">
                  <span v-for="star in 5" :key="star" class="star"
                    :class="{ 'filled': star <= comentario.calificacion }"> ★ </span>
                </div>
              </div>
              <p>{{ comentario.comentarios}}</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Cerrar </button>
            <button type="button" class="btn btn-primary" @click="enviarValoracion">Comentar</button>
          </div>
        </div>
      </div>
    </div>
    <!--FIN Modal de valoraciones, comentarios y/u opiniones-->
    <!--Modal para iniciar sesion-->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="loginModalLabel"> Iniciar Sesión </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label for="username" class="form-label">Usuario</label>
                <input type="text" class="form-control" v-model="login.username" id="username"
                  placeholder="Ingresa tu usuario" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" v-model="login.password" id="password"
                  placeholder="Ingresa tu contraseña" required>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Cerrar </button>
            <button type="button" class="btn btn-primary" @click="iniciarSesion">Iniciar Sesión </button>
          </div>
        </div>
      </div>
    </div>
    <!--FIN Modal para iniciar sesion-->
    <!--Modal para registrarse como miembro premium-->
    <div class="modal fade" id="premiumModal" tabindex="-1" aria-labelledby="premiumModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="premiumModalLabel">Miembro Premium</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-info text-center" role="alert">
              <strong>¡Hazte Premium y disfruta de beneficios exclusivos!</strong><br> Accede a ofertas exclusivas en
              negocios asociados por solo <strong>$200 MXN mensuales</strong>. </div>
            <!-- Formulario de pago -->
            <form @submit.prevent="registerPremium">
              <div class="mb-3">
                <label for="cardName" class="form-label">Nombre en la tarjeta</label>
                <input type="text" v-model="paymentData.cardName" class="form-control" id="cardName"
                  placeholder="Como aparece en la tarjeta" required>
              </div>
              <div class="mb-3">
                <label for="cardNumber" class="form-label">Número de tarjeta</label>
                <input type="text" v-model="paymentData.cardNumber" class="form-control" id="cardNumber"
                  placeholder="XXXX XXXX XXXX XXXX" maxlength="16" required>
              </div>
              <div class="row mb-3">
                <div class="col">
                  <label for="expiryDate" class="form-label">Fecha de expiración</label>
                  <input type="text" v-model="paymentData.expiryDate" class="form-control" id="expiryDate"
                    placeholder="MM/AA" maxlength="5" required>
                </div>
                <div class="col">
                  <label for="cvv" class="form-label">CVV</label>
                  <input type="password" v-model="paymentData.cvv" class="form-control" id="cvv" placeholder="XXX"
                    maxlength="3" required>
                </div>
              </div>
              <div class="mb-3">
                <small class="text-muted">Al hacer clic en "Pagar", aceptas el cobro de $200 MXN mensuales para mantener
                  tu suscripción premium.</small>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" @click="registerPremium">Pagar</button>
          </div>
        </div>
      </div>
    </div>
    <!--FIN Modal para registrarse como miembro premium-->
  </div>
  <!-- Cierra el div con id="app" -->
  <script src="src/paquetes/vue.js"></script>
  <script src="src/paquetes/bootstrap-5.3.3-dist/js/bootstrap.min.js"></script>
  <script src="src/paquetes/sweetalert2/dist/sweetalert2.all.min.js"></script>
  <script src="miGuiaLocalController.js"></script>
</body>

</html>