<?php
include "./php/conexion.php";
if (!isset($_GET['id_venta'])) {
    header("Location: ./");
}
$datos = $conexion->query("select 
        ventas.*,
        usuario.nombre, usuario.telefono, usuario.email
        from ventas 
        inner join usuario on ventas.id_usuario = usuario.id
        where ventas.id= ".$_GET['id_venta'])or die($conexion->error);
$datosUsuario = mysqli_fetch_row($datos);
$datos2 = $conexion->query("select * from envios where id_venta=".$_GET['id_venta'])or die($conexion->error);
$datosEnvio= mysqli_fetch_row($datos2);

$datos3= $conexion->query("select productos_venta.*,
                    productos.nombre as nombre_producto, productos.imagen
                    from productos_venta inner join productos on productos_venta.id_producto = productos.id
                    where id_venta =".$_GET['id_venta'])or die($conexion->error);

$total = $datosUsuario[2];
$descuento = "0";
$banderadescuento = false;

/* if($datosUsuario[6] != 0){
  $banderadescuento = true;
  $cupon = $conexion->query("select * from cupones where cupones.id = "."'$datosUsuario[6]'");
  $filaCupon = mysqli_fetch_row($cupon);
  if($filaCupon[3] == "moneda"){
  $total = $total - $filaCupon[4];
  $descuento = $filaCupon[4]."MXN";
  }else{
    $total = $total -  ($total * ( $filaCupon[4] / 100));
    $descuento = $filaCupon[4]."%";
  }
} */

// SDK de Mercado Pago
require __DIR__ .  '/vendor/autoload.php';

// Agrega credenciales
MercadoPago\SDK::setAccessToken('TEST-4399896456088113-112820-071e1df97c72129689afc2deca1358be-679528360');

// Crea un objeto de preferencia
$preference = new MercadoPago\Preference();

$preference->back_urls = array(
    "success" => "https://localhost/carrito/thankyou.php?id_venta=".$_GET['id_venta']."&metodo=mercado_pago",
    "failure" => "http://localhost/carrito/errorpago.php?error=failure",
    "pending" => "http://localhost/carrito/errorpago.php?error=pending"
);
$preference->auto_return = "approved";

$datos=array();
if ($banderadescuento) {
    $item = new MercadoPago\Item();
    $item->title = "Productos de mi tienda menos el descuento";
    $item->quantity = 1;
    $item->unit_price = $total;
    $datos[]=$item;
}else{
  while ($f = mysqli_fetch_array($datos3)) {
    // Crea un ítem en la preferencia
    $item = new MercadoPago\Item();
    $item->title = $f['nombre_producto'];;
    $item->quantity = $f['cantidad'];;
    $item->unit_price = $f['precio'];;
    $datos[]=$item;
  }
}


$preference->items = $datos;
$preference->save();
/*Petición curl en cmd
curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer TEST-6526071902378835-112819-499513fc22eac7d23efffe60a447cf48-677949789" "https://api.mercadopago.com/users/test_user" -d "{'site_id':'MLM'}"
Respuesta Json usar vendedor y comprador
{"id":679528360,
"nickname":"TESTREUWKAAT",
"password":"qatest7844",
"site_status":"active",
"email":"test_user_15168783@testuser.com"}
{"id":679532775,
"nickname":"TESTTQQFSAD3",
"password":"qatest2068",
"site_status":"active",
"email":"test_user_32419459@testuser.com"}
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <title>Elije método de pago</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Mukta:300,400,700"> 
    <link rel="stylesheet" href="fonts/icomoon/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="./dashboard/plugins/fontawesome-free/css/all.min.css">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/jquery-ui.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">


    <link rel="stylesheet" href="css/aos.css">

    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <script 
      src="https://www.paypal.com/sdk/js?client-id=Afb3WgwWg4GPWq8xhNlcPCOZdt51lAOT-HL3Mgnj0jbOe5RKYOt3_7_dllj6Chi-25JO_koQKVdXGvPM&currency=MXN"> // Replace YOUR_SB_CLIENT_ID with your sandbox client ID
    </script>

    <div class="site-wrap">
  <?php include("./layouts/header.php"); ?> 

    <div class="site-section">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <h2 class="h3 mb-3 text-black">Elige método de pago</h2>
          </div>
          <div class="col-md-7">

            <form action="#" method="post">
              
              <div class="p-3 p-lg-5 border">

                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Venta #<?php echo $_GET['id_venta']; ?></label>                   
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Nombre <?php echo $datosUsuario[4]; ?></label>                   
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Email <?php echo $datosUsuario[6]; ?></label>                   
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Teléfono <?php echo $datosUsuario[5]; ?></label>                   
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Company <?php echo $datosEnvio[2]; ?></label>                   
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Dirección <?php echo $datosEnvio[3]; ?></label>                   
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Estado <?php echo $datosEnvio[4]; ?></label>                   
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">CP <?php echo $datosEnvio[5]; ?></label>                   
                  </div>
                </div>
                
              </div>
            </form>
          </div>
          <div class="col-md-5 ml-auto">
              
            <h4 class="h1">Total: <?php echo number_format($datosUsuario[2],2,'.','');?></h4>
            <h5>Descuento: <?php echo $descuento; ?></h5>
            <h5>Total Final: <?php echo $total; ?></h5>
            <form action="http://localhost/carrito/thankyou.php?id_venta=<?php echo $_GET['id_venta'] ?>&metodo=mercado_pago" method="$_POST">
                <h2>Mercado Pago</h2>
                <img style="-webkit-user-select: none;margin: auto;" src="https://www.mercadopago.com/org-img/Manual/ManualMP/imgs/isologoHorizontal.png"><br><br>
                <script
                    src="https://www.mercadopago.com.mx/integrations/v1/web-payment-checkout.js"
                    data-preference-id="<?php echo $preference->id; ?>">
                </script><br><br>

            </form>
            <div id="paypal-button-container"></div>
          </div>
        </div>
      </div>
    </div>

    <?php include("./layouts/footer.php"); ?> 
  </div>

  <script src="js/jquery-3.3.1.min.js"></script>
  <script src="js/jquery-ui.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/jquery.magnific-popup.min.js"></script>
  <script src="js/aos.js"></script>

  <script src="js/main.js"></script>
  <!-- Add the checkout buttons, set up the order and approve the order -->
  <script>
      paypal.Buttons({
        createOrder: function(data, actions) {
          return actions.order.create({
            purchase_units: [{
              amount: {
                value: '<?php echo $total; ?>'
                // value: '< ?php echo number_format($datosUsuario[2],2,'.','');?>'
              }
            }]
          });
        },
        onApprove: function(data, actions) {
          return actions.order.capture().then(function(details) {
              console.log(details);
            if(details.status == 'COMPLETED'){
                location.href="./thankyou.php?id_venta=<?php echo $_GET['id_venta'];?>&metodo=paypal";
            }
            
          });
        }
      }).render('#paypal-button-container'); // Display payment options on your web page
    </script>

</body>
</html>