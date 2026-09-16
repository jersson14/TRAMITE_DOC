var tbl_bitacora;

// Cada acción se pinta con el color que ya usa el sistema: ámbar para lo que
// está en curso, rojo para lo que salió mal o borra, verde para lo que avanza.
function insigniaAccion(accion) {
  var mapa = {
    INGRESO: ["badge-success", "Ingreso"],
    INGRESO_FALLIDO: ["badge-danger", "Intento fallido"],
    SALIDA: ["badge-secondary", "Cierre de sesión"],
    REGISTRO_TRAMITE: ["badge-primary", "Registro de trámite"],
    DERIVO_TRAMITE: ["badge-primary", "Derivación"],
    CAMBIO_ESTADO: ["badge-warning", "Cambio de estado"],
    ELIMINO_TRAMITE: ["badge-danger", "Eliminación"],
    REGISTRO: ["badge-primary", "Registro"],
    MODIFICO: ["badge-warning", "Modificación"],
    ELIMINO: ["badge-danger", "Eliminación"],
  };
  var datos = mapa[accion] || ["badge-light", accion];
  return '<span class="badge ' + datos[0] + '">' + datos[1] + "</span>";
}

function Listar_Bitacora() {
  var desde = document.getElementById("txt_desde").value;
  var hasta = document.getElementById("txt_hasta").value;
  var accion = document.getElementById("cbo_accion").value;

  tbl_bitacora = $("#tabla_bitacora").DataTable({
    ordering: false,
    bLengthChange: true,
    lengthMenu: [
      [25, 50, 100, -1],
      [25, 50, 100, "Todos"],
    ],
    pageLength: 25,
    destroy: true,
    pagingType: "full_numbers",
    responsive: true,
    processing: true,
    ajax: {
      url: "../controller/bitacora/controlador_listar_bitacora.php",
      type: "POST",
      data: { desde: desde, hasta: hasta, accion: accion },
    },
    columns: [
      { data: "fecha" },
      { data: "usuario" },
      { data: "rol" },
      {
        data: "bit_accion",
        render: function (data) {
          return insigniaAccion(data);
        },
      },
      {
        // A qué se refiere el registro: un expediente o un usuario. Antes
        // se mostraba solo el id, que en los ingresos era el del usuario y
        // se leía como si fuera un número de expediente.
        data: "entidad_id",
        render: function (data, type, fila) {
          if (!data) return '<span class="text-muted">—</span>';
          if (fila.entidad === "documento") {
            return '<i class="fas fa-folder-open text-muted mr-1"></i>' + data;
          }
          if (fila.entidad === "usuario") {
            return '<span class="text-muted">usuario #' + data + "</span>";
          }
          return data;
        },
      },
      {
        data: "detalle",
        render: function (data) {
          return data ? data : '<span class="text-muted">—</span>';
        },
      },
      { data: "ip" },
    ],
    language: {
      processing: "Procesando...",
      lengthMenu: "Mostrar _MENU_ registros",
      zeroRecords: "No hay movimientos registrados en ese rango",
      info: "Registros del (_START_ al _END_) total de _TOTAL_ registros",
      infoEmpty: "Sin registros",
      infoFiltered: "(filtrado de _MAX_ registros)",
      search: "Buscar:",
      loadingRecords: "Cargando...",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior",
      },
    },
  });
}

$(document).ready(function () {
  Listar_Bitacora();
});
