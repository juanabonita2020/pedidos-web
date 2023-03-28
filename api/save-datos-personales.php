<?php require 'lib/kernel.php';

// guarda los datos personales
class API extends JBSYSWEBAPI
{

protected $_input = array(
'nombre' => array(
	'required' => true
),
'apellido' => array(
	'required' => true
),
'telefonoArea1' => array(
	'required' => true
),
'telefonoPrefijo1' => array(
	'required' => true
),
'telefonoSufijo1' => array(
	'required' => true
),
'telefonoArea2' => array(
	'required' => true
),
'telefonoPrefijo2' => array(
	'required' => true
),
'telefonoSufijo2' => array(
	'required' => true
),
'celularArea' => array(
	'required' => true
),
'celularPrefijo' => array(
	'required' => true
),
'celularSufijo' => array(
	'required' => true
),
'direccion' => array(
	'required' => true
),
'codigoPostal' => array(
	'required' => true
),
'localidad' => array(
	'required' => true
),
'fechaNacimiento' => array(
	'required' => true
),
'provincia' => array(
	'required' => true
),
'alertaCerrar' => array(
	'required' => true
),
'id_web_paises' => array(
	'required' => true
),
'tiene_instagram' => array(
	'required' => true
),
'tiene_facebook' => array(
	'required' => true
),
'id_sistema_operativo' => array(
	'required' => true
),
'altura' => array(
	'required' => false
),
'piso' => array(
	'required' => true
),
'departamento' => array(
	'required' => true
),
'barrio' => array(
	'required' => false
),
'usuario_instagram' => array(
	'required' => true
),
'usuario_facebook' => array(
	'required' => true
)
);

protected $_logReq = true;

protected function _process($input)		
{
	$this->_updateMyData(array(
	'nombre' => $input['nombre'],
	'apellido' => $input['apellido'],
	'tel_area1' => $input['telefonoArea1'],
	'tel_prefijo1' => $input['telefonoPrefijo1'],
	'tel_sufijo1' => $input['telefonoSufijo1'],
	'tel_area2' => $input['telefonoArea2'],
	'tel_prefijo2' => $input['telefonoPrefijo2'],
	'tel_sufijo2' => $input['telefonoSufijo2'],
	'cel_area' => $input['celularArea'],
	'cel_prefijo' => $input['celularPrefijo'],
	'cel_sufijo' => $input['celularSufijo'],
	'direccion' => $input['direccion'],
	'codigopostal' => $input['codigoPostal'],
	'localidad' => $input['localidad'],
	'fecha_nacimiento' => $this->_toDate($input['fechaNacimiento']),
	'id_web_provincias' => $input['provincia'],
	'alerta_cerrarpedido' => $input['alertaCerrar'],
	'altura' => $input['altura'],
	'piso' => $input['piso'],
	'departamento' => $input['departamento'],
	'barrio' => $input['barrio'],
	'id_web_paises' => $input['id_web_paises'],
	'tiene_instagram' => $input['tiene_instagram'],
	'usuario_instagram' => $input['usuario_instagram'],
	'tiene_facebook' => $input['tiene_facebook'],
	'usuario_facebook' => $input['usuario_facebook'],
	'id_sistema_operativo' => $input['id_sistema_operativo'],
	'fecha_datos_personales' => date('Y-m-d')
	));
	
	$this->_saveSessionVar('closeAlert', ($input['alertaCerrar'] == 1));
}

}

require 'lib/exe.php';
