<?php require 'version.php'; ?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Expires" content="0">
<meta http-equiv="Last-Modified" content="0">
<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<link rel="icon" href="images/juana_favicon.png">
<title>Juana Bonita</title>
<link href="contrib/bootstrap.css" rel="stylesheet" />
<link href="contrib/custom_styles.css" rel="stylesheet">
<link href="contrib/bootstrapValidator.min.css" rel="stylesheet" />
<link href="contrib/bootstrap-dialog.min.css" rel="stylesheet" />
<link href="contrib/owl-carousel/owl.carousel.css" rel="stylesheet" />
<link href="contrib/owl-carousel/owl.theme.css" rel="stylesheet" />
<link href="core.css?v=<?php echo JBSYSWEBVERSION;?>" rel="stylesheet" />
<link href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" rel="stylesheet" />
<script src="contrib/jquery.min.js" type="text/javascript"></script>
<script src="contrib/bootstrap.min.js" type="text/javascript"></script>
<script src="contrib/bootstrapValidator.js" type="text/javascript"></script>
<script src="contrib/modernizr.min.js" type="text/javascript"></script>
<script src="contrib/bootstrap-dialog.min.js" type="text/javascript"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js" type="text/javascript"></script>
<script src="contrib/jquery.inputmask/jquery.inputmask.bundle.min.js"></script>
<script src="contrib/owl-carousel/owl.carousel.js"></script>
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<script src="core.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/panelEmpresaria.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/pedidosCarga.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/pedidosGestion.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/clientesGestion.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/campaniaChange.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/panelRevendedora.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/passwordChange.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/login.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/catalogo.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/catalogoItem.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/personalInfo.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/panelAdministrador.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/passwordReset.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/passwordRecover.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/campaigns.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/magazine.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/signup.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/signupManual.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/newPass.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/panelCoordinadora.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/gestionCierres.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/historial.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/gestionRegionales.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/panelRegional.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/faq.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/relevamiento.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/banners.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/claveMaestra.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/avancesZonas.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/regionalUsuarios.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/avanceMigracion.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/gestionRegManual.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/userRecover.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/restorePedido.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/flyers.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/enviosBloqueados.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/cms.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/ficheros.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/capacitacion.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/amigas.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/control.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/testSendmail.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/capacitacionCat.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/capacitaciones.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/canje.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/comunidad.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/stats.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/misc.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/dataErrors.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/encuesta.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
<script src="pages/mensajes.js?v=<?php echo JBSYSWEBVERSION;?>" type="text/javascript"></script>
</head>
<body onload="SISWEB.init('<?php echo JBSYSWEBVERSION;?>');">

<div id="waitenv" class="waitenv"></div>

<div id="progenv" class="waitenv"><div id="progcnt">
	<p id="progmsg"></p>
	<div id="progressbar"><div class="progress-label" id="progressbarval"></div></div>
	<p id="progcancelenv"><button id="progcancel" type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-cancel"></span> Cancelar</button></p>
</div></div>

<div id="body">

<div id="header" class="container">
	<div class="header">
		<ul class="nav nav-pills pull-right ">
			<li class="active"><a class="colorjb" href="#">Pedidos Web</a></li>
			<li><a href="http://www.juanabonita.com" target="_blank">JuanaBonita.com</a></li>
			<li><a href="http://www.juanabonita.com.ar/?s=contactanos target="_blank">Contacto</a></li>
			<li><a href="#faq">Ayuda</a></li>
			<li id="cerrarSesion_env"><a id="cerrarSesion" href="#logout" ><span class="glyphicon glyphicon-off"></span> Cerrar</a></li>
		</ul>
		<h3><a href="#home"><img src="images/logo1.png" /></a></h3>
	</div>
</div>

<div class="container"><div class="row"><div class="container">

<div id="userHeader" class="row">
	<div class="col-md-8 dashEmpresariaHeader box1">
		<div class="dashEmpresariaTitle">Bienvenida</div>
		<div class="dashEmpresariaValue"><span id="username">--</span></div>
	</div>
	<div class="col-md-2 dashEmpresariaHeader box2 ">
		<div class="dashEmpresariaTitle">Zona</div>
		<div class="dashEmpresariaValue"><span id="nrozona">--</span></div>
	</div>
	<div class="col-md-2 dashEmpresariaHeader box3 ">
		<div class="dashEmpresariaTitle">Clienta</div>
		<div class="dashEmpresariaValue"><span id="nroclienta">--</span></div>
	</div>
</div>

<div id="userHeaderNotice" class="row"><div class="col-md-12"></div></div>

<a href="Javascript:;" id="goBack"><span class="glyphicon glyphicon-circle-arrow-left"></span> Regresar</a>

<div id="alert" class="alert alert-danger" role="alert"></div>

<div id="maincnt"></div>

</div></div></div>

<div id="footer" class="footer">
	<div class="container">
		<p><address class="text-center"><strong>Juana Bonita</strong><br>Av. Juan Domingo Perón 3929 (1617), Gral. Pacheco, Bs.As.<br>© 2019 Juana Bonita. Todos los derechos reservados</address></p>
		<p id="version">ver. <?php echo JBSYSWEBVERSION;?></p>
	</div>
</div>

</div>

<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
        <h4 class="modal-title" id="modalTitle"></h4>
      </div>
      <div class="modal-body" id="modalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <!--<button type="button" class="btn btn-primary">Save changes</button>-->
      </div>
    </div>
  </div>
</div>

<iframe id="backiframe" name="backiframe" style="display:none"></iframe>

</body>
</html>
