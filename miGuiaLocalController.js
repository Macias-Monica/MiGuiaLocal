// miGuiaLocalController.js

new Vue({
    el: '#app', // Monta la instancia de Vue en el elemento con id "app"
    data: {
        negocios: [],
        categorias: [],
        valoraciones: [],
        isDropdownVisible: false,
        isCompact: false,
        UsuarioLogin: '',
        isLoggedIn: false,
        pefil: '',
        modalIsOpen: false,
        esPremium: false,
        nombresCategorias: [],                                                                                                                                                                                                                                    // Controla la visibilidad del cuadro desplegable
        user: {
            nombreCompleto: '',
            usuario: '',
            contrasena: '',
            correo: '',
            telefono: ''
        },
        business: {
            nombre: '',
            nombreNegocio: '',
            categoria: '',
            servicioDomicilio: '',
            domicilio: '',
            ubicacionMaps: '',
            rutaImagenNegocio: '',
            horario: '',
            usuario: '',
            contrasena: '',
            correo: '',
            telefono: ''
        },
        login: {
            username: '',
            password: ''
        },
        categoriaSeleccionada: '',
        busqueda: '',
        comentariosFiltrados: [],
        nuevoComentario: '',
        paymentData: {
            cardName: '',
            cardNumber: '',
            expiryDate: '',
            cvv: ''
        },
        mostrarNegocios: true,
        promos: [],
        newPost: '',
        imagePreview: null,
        calificacion: 0,
        nombreNegocio2:'',
    },
    methods: {
        buscar() { //método que obtiene toda la informacion de la BD (Negocios y categorias)
            var t = this
            t.showLoading()
            fetch(
                "miGuiaLocalModel.php?Cache=", {
                method: "POST",
                body: JSON.stringify({ tipo: 'buscar' })
            }
            ).then(function (response) {
                response
                    .json()
                    .then(function (json) {
                        console.log(json);
                        if (json.status == "OK") {
                            Swal.close();
                            t.negocios = json.negocios
                            t.categorias = json.categorias
                            t.Valoraciones = json.Valoraciones
                        }
                    })
                    .catch(function (err) {
                        console.log(err);
                    });
            });
        },
        toggleDropdown() {
            var t = this
            t.isDropdownVisible = !t.isDropdownVisible; // Alterna la visibilidad del cuadro desplegable
            // console.log(t.isDropdownVisible)
        },
        handleScroll() {
            this.isCompact = window.scrollY > 0; // Cambia el número según lo que necesites
        },
        register() {
            var t = this
            // Funcionalidad para registrarse
            data = {
                tipo: 'registraUsuario',
                info: t.user
            }
            console.log(data)
            t.showLoading()
            fetch(
                "miGuiaLocalModel.php?Cache=", {
                method: "POST",
                body: JSON.stringify(data)
            }
            ).then(function (response) {
                response
                    .json()
                    .then(function (json) {
                        console.log(json);

                        if (json.status == "OK") {
                            //console.log(json.message)
                            swal.close();
                            Swal.fire({
                                title: '¡Feliciades!',
                                text: json.message,
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    var modalElement = document.getElementById('registroUserModal');
                                    t.cerrarModal(modalElement)
                                }
                            });
                        } else {
                            console.log(json.message)
                            Swal.fire({
                                title: '¡Oops!',
                                text: json.message,
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            })
                        }
                    })
                    .catch(function (err) {
                        console.log(err);
                    });
            });
        },
        iniciarSesion() {
            var t = this;
            var data = {
                tipo: 'iniciarSesion',
                info: t.login
            };
            t.showLoading()
            fetch("miGuiaLocalModel.php?Cache=", {
                method: "POST",
                body: JSON.stringify(data)
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (json) {
                    //console.log(json);
                    if (json.status == "OK") {
                        t.pefil = json.perfil
                        t.UsuarioLogin = json.session;
                        t.isLoggedIn = true; // Cambia el estado a iniciado sesión
                        t.esPremium = json.esPremium == 'Y' ? true : false; //Valida si es miembro premium,
                        t.nombreNegocio2 = json.nombreNegocio
                        Swal.close();
                        Swal.fire({
                            title: '¡Éxito!',
                            text: json.message,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var modalElement = document.getElementById('loginModal');
                                t.cerrarModal(modalElement)
                                if (t.modalIsOpen) {
                                    t.openModal(4)
                                }
                            }
                        });
                    } else {
                        Swal.fire({
                            title: '¡Oops!',
                            text: json.message,
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                t.login.password = ''
                                t.pefil = ''
                            }
                        });
                    }
                })
                .catch(function (err) {
                    console.log(err);
                });
        },
        addNegocio() {
            var t = this
            console.log(t.business)
            // Funcionalidad para registrarse
            data = {
                tipo: 'registraNegocio',
                info: t.business
            }
            console.log(data)
            t.showLoading();
            fetch(
                "miGuiaLocalModel.php?Cache=", {
                method: "POST",
                body: JSON.stringify(data)
            }
            ).then(function (response) {
                response
                    .json()
                    .then(function (json) {
                        //console.log(json);
                        if (json.status == "OK") {
                            swal.close();
                            Swal.fire({
                                title: '¡Éxito!',
                                text: json.message,
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    var modalElement = document.getElementById('registroNegocioModal');
                                    t.cerrarModal(modalElement);
                                }
                            });
                            //console.log(json.message)
                        } else {
                            Swal.fire({
                                title: '¡Oops!',
                                text: json.message,
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            })
                        }
                    })
                    .catch(function (err) {
                        console.log(err);
                    });
            });
        },
        openModal(opc, aux) {
            var t = this
            var modalElement;
            switch (opc) {
                case 1: //Abrir Modal del registro como usuario
                    modalElement = document.getElementById('registroUserModal');
                    break;
                case 2: //Abrir Modal de Registrar el negocio
                    modalElement = document.getElementById('registroNegocioModal');
                    break;
                case 3: //Abrir Modal de de comentarios
                    modalElement = document.getElementById('valoracionesModal');
                    t.comentariosFiltrados = t.Valoraciones.filter(c => c.idNegocio === aux);
                    break;
                case 4: //Abrir Modal de ser miembro premium
                    if (t.isLoggedIn) { // Si ya está loggeado
                        modalElement = document.getElementById('premiumModal');
                    } else {
                        t.modalIsOpen = true
                        Swal.fire({
                            title: '¡Inicia Sesión!',
                            text: 'Primero debes iniciar sesión',
                            icon: 'info',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                modalElement = document.getElementById('loginModal');
                                if (modalElement) {
                                    var modal = new bootstrap.Modal(modalElement);
                                    modal.show();
                                }
                            }
                        });
                        return; // Salir de la función para evitar que continúe ejecutando el resto del código
                    }
                    break;
                case 5: //Abrir Modal para iniciar sesion
                    modalElement = document.getElementById('loginModal');
                    break;
            }
            if (modalElement) {
                var modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        },
        scrollLeft() {
            this.$refs.carousel.scrollBy({ left: -200, behavior: 'smooth' });
        },
        scrollRight() {
            this.$refs.carousel.scrollBy({ left: 200, behavior: 'smooth' });
        },
        elegirCategoria(cat) {
            var t = this
            t.categoriaSeleccionada = cat
        },
        enviarValoracion() {
            var t = this
            // Validar que haya un comentario y una calificación
            if (this.nuevoComentario.trim() === '' || this.calificacion === 0) {
                Swal.fire({
                    title: '¡Oops!',
                    text: 'Agrega un comentario y su puntaje',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                })
                return;
            }
            const negocioActual = t.comentariosFiltrados[0]
            // Crear un nuevo comentario
            const nuevoComentario = {
                idNegocio: negocioActual.idNegocio,
                usuario: t.UsuarioLogin,
                comentarios: t.nuevoComentario,
                categoria: negocioActual.categoria,
                nombreNegocio:negocioActual.nombreNegocio,
                calificacion: t.calificacion,
            };
            console.log(nuevoComentario)
           

               //Guardamos esta info en el back
               data = {
                tipo: 'newComment',
                info: nuevoComentario
            }
            console.log(data)
            // t.showLoading()
            fetch(
                "miGuiaLocalModel.php?Cache=", {
                method: "POST",
                body: JSON.stringify(data)
            }
            ).then(function (response) {
                response
                    .json()
                    .then(function (json) {
                        console.log(json);
                        if (json.status == "OK") {
                            //console.log(json.message)
                             // Agregar el nuevo comentario a la lista
            t.comentariosFiltrados.push(nuevoComentario);
                            nuevoComentario =''
                        } else {
                            //console.log(json.message)
                            Swal.fire({
                                title: '¡Oops!',
                                text: json.message,
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            })
                        }
                    })
                    .catch(function (err) {
                        console.log(err);
                    });
            });
        },
        onImageChange(event) {
            var t = this
            const file = event.target.files[0]; // Obtener el archivo seleccionado
            const reader = new FileReader(); // Crear un nuevo FileReader
            reader.onload = (e) => {
                t.business.rutaImagenNegocio = e.target.result; // Guardar la imagen en formato base64
            };
            reader.readAsDataURL(file); // Leer el archivo como URL de datos
            if (file) {
                t.imagePreview = URL.createObjectURL(file); // Crea una URL para la vista previa
            } else {
                t.imagePreview = null; // Resetea la vista previa si no hay archivo
            }
        },
        checkLoginStatus() {
            var t = this;
            fetch("miGuiaLocalModel.php", {
                method: "POST",
                body: JSON.stringify({ tipo: 'verificaSession' })
            }).then(response => response.json())
                .then(json => {
                    if (json.status === "OK") {
                        t.isLoggedIn = true; // Cambia el estado si la sesión es válida
                        t.UsuarioLogin = json.session; // Actualiza la información del usuario
                        t.esPremium = json.esPremium; // Actualiza la información del usuario
                        t.nombreNegocio2 = json.nombreNegocio
                    } else {
                        t.isLoggedIn = false; // Cambia el estado si la sesión no es válida
                    }
                })
                .catch(err => {
                    console.log(err);
                });
        },
        logout() {
            var t = this;
            t.showLoading()
            fetch("miGuiaLocalModel.php", {
                method: "POST",
                body: JSON.stringify({ tipo: 'cerrarSesion' })
            })
                .then(response => response.json())
                .then(json => {
                    if (json.status === "OK") {
                        Swal.fire({
                            title: '!Adiós!',
                            text: json.message,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();

                            }
                        });
                        t.isLoggedIn = false; // Cambia el estado a no iniciado sesión
                        t.UsuarioLogin = ''; // Limpia la información del usuario
                    }
                })
                .catch(err => {
                    console.log(err);
                });
        },
        showLoading() {
            Swal.fire({
                title: 'Cargando...',
                html: 'Por favor, espera un momento...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        cerrarModal(modalElement) {
            var modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide(); // Cerrar el modal si existe
            }
        },
        registerPremium() {
            var t = this
            data = {
                tipo: 'actualizaPremium',
                info: t.paymentData,
                user: t.username
            }
            t.showLoading()
            setTimeout(() => {//TIEMPO DE CARGA PARA SIMULAR EL PAGO
                fetch(
                    "miGuiaLocalModel.php?Cache=", {
                    method: "POST",
                    body: JSON.stringify(data)
                }
                ).then(function (response) {
                    response
                        .json()
                        .then(function (json) {
                            console.log(json);                            
                            if (json.status == "OK") {
                                swal.close();
                                t.esPremium = true
                                Swal.fire({
                                    title: '¡Feliciades!',
                                    text: json.message,
                                    icon: 'success',
                                    confirmButtonText: 'Aceptar'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        var modalElement = document.getElementById('premiumModal');
                                        t.cerrarModal(modalElement)
                                    }
                                });
                            } else {
                                console.log(json.message)
                                Swal.fire({
                                    title: '¡Oops!',
                                    text: json.message,
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar'
                                })
                            }
                        })
                        .catch(function (err) {
                            console.log(err);
                        });
                });
            }, 5000);   
        },
        cambiarpantalla(opc) {
            var t = this
            switch (opc) {
                case 'verPromos':
                    t.mostrarNegocios = false
                    t.showLoading()
                    fetch(
                        "miGuiaLocalModel.php?Cache=", {
                        method: "POST",
                        body: JSON.stringify({ tipo: 'promos' })
                    }
                    ).then(function (response) {
                        response
                            .json()
                            .then(function (json) {
                                // console.log(json);
                                if (json.status == "OK") {
                                    Swal.close();
                                    t.promos = json.promos
                                }
                            })
                            .catch(function (err) {
                                console.log(err);
                            });
                    });
                    break;
                case 'verNegocios':
                    console.log('verNegocios')
                    t.mostrarNegocios = true
                    break;
            }
        },
        addPost() {
            var t = this
            //buscamos la categoría a la que pertenece el negocio
            const negocio = t.negocios.find(n => n.nombreNegocio === t.nombreNegocio2);
            if (t.newPost.trim() !== '') {
                const categoria = negocio ? negocio.categoria : 'Categoría no encontrada';
                t.promos.push({ //se inserta en la lista para que se vea la publicacon de inmediato
                    contenido: t.newPost,
                    negocio: t.nombreNegocio2,
                    categoria: categoria,
                    FechaCreacion: new Date(), // Fecha de creación
                    rutaImagen: t.business.rutaImagenNegocio // Adjuntar la imagen a la publicación
                });
                //Guardamos esta info en el back
                data = {
                    tipo: 'newPost',
                    info: {
                        contenido: t.newPost,
                        negocio: t.nombreNegocio2,
                        categoria: categoria,
                        FechaCreacion: new Date(), // Fecha de creación
                        rutaImagen: t.business.rutaImagenNegocio // Adjuntar la imagen a la publicación
                    }
                }
                // t.showLoading()
                fetch(
                    "miGuiaLocalModel.php?Cache=", {
                    method: "POST",
                    body: JSON.stringify(data)
                }
                ).then(function (response) {
                    response
                        .json()
                        .then(function (json) {
                            console.log(json);
                            if (json.status == "OK") {
                                //console.log(json.message)
                                t.imagePreview = null
                                t.newPost = ''; // Limpiar el campo de entrada
                                t.selectedImage = null; // Limpiar la imagen seleccionada
                            } else {
                                console.log(json.message)
                                Swal.fire({
                                    title: '¡Oops!',
                                    text: json.message,
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar'
                                })
                            }
                        })
                        .catch(function (err) {
                            console.log(err);
                        });
                });
            } else {
                Swal.fire({
                    title: '¡Oops!',
                    text: 'No has escrito nada',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                })
            }
        },
        setRating(value) {
            this.calificacion = value; // Establecer la calificación cuando se hace clic en la estrella
        },

    },
    computed: {
        negociosFiltrados() {
            var t = this;
            return t.negocios.filter(negocio => {
                // Verifica que las propiedades existan y no sean nulas
                const coincideCategoria = t.categoriaSeleccionada === '' ||
                    (negocio.categoria && negocio.categoria === t.categoriaSeleccionada);
                const coincideBusqueda = negocio.nombreNegocio ?
                    negocio.nombreNegocio.toLowerCase().includes(t.busqueda.toLowerCase()) : true;
                return coincideCategoria && coincideBusqueda;
            });

        }
    },


    mounted() {// cuando termina de cargar la página
        var t = this
        t.checkLoginStatus()
        t.buscar()
        window.addEventListener('scroll', this.handleScroll);
    },
    created() { //mientras cargando la página

    },
    beforeDestroy() {
        window.removeEventListener('scroll', this.handleScroll);
    },

});
