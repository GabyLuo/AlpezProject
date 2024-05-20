<?php
/**
 * Created by PhpStorm.
 * User: Falza
 * Date: 21/11/2017
 * Time: 03:05 PM
 */


//use \DOMXPath;
//use \DOMDocument;
//use FPDF;
use Endroid\QrCode\QrCode;

header('Content-Type: text/html; charset=utf-8');

class invoiceProducts
{
    public $cantidad;
    public $descripcion;
    public $costoUni;
    public $total;
    public $clavProdServ;
    public $clavUnidad;
    public $descuento;
    public $impuesto;
    public $traslado;
    public $retencion;
    public $peso;
}

class impuestoTraslado
{
    public $tasa;
    public $importe;
    public $impuesto;
}

class impuestoRetencion
{
    public $importe;
    public $impuesto;
}

class AntPDF extends \FPDF
{
    function TextWithDirection($x, $y, $txt, $direction = 'R')
    {
        if ($direction == 'R') {
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 1, 0, 0, 1, $x * $this->k,
                ($this->h - $y) * $this->k, $this->_escape($txt));
        } elseif ($direction == 'L') {
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', -1, 0, 0, -1, $x * $this->k,
                ($this->h - $y) * $this->k, $this->_escape($txt));
        } elseif ($direction == 'U') {
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 0, 1, -1, 0, $x * $this->k,
                ($this->h - $y) * $this->k, $this->_escape($txt));
        } elseif ($direction == 'D') {
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 0, -1, 1, 0, $x * $this->k,
                ($this->h - $y) * $this->k, $this->_escape($txt));
        } else {
            $s = sprintf('BT %.2F %.2F Td (%s) Tj ET', $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        }
        if ($this->ColorFlag) {
            $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        }
        $this->_out($s);
    }

    function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle = 0)
    {
        $font_angle += 90 + $txt_angle;
        $txt_angle *= M_PI / 180;
        $font_angle *= M_PI / 180;

        $txt_dx = cos($txt_angle);
        $txt_dy = sin($txt_angle);
        $font_dx = cos($font_angle);
        $font_dy = sin($font_angle);

        $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', $txt_dx, $txt_dy, $font_dx, $font_dy,
            $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        if ($this->ColorFlag) {
            $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        }
        $this->_out($s);
    }
}


class InvoicePDF
{
    public $pdf;
    public $type;
    public $folio_current;
    public $moneda;

    public function nomina_pdf (
        // String $cliente, // 80
        String $cfdi,
        bool $cancelada,
        array $clientAddress,
        String $emisor_address = '',
        String $receptor_address = '',
        String $type_save = '',
        String $url_save = ''
    ) {
        $cliente = '80';
        $logo = '';
        $QR = '';

        $logo = $_SERVER['DOCUMENT_ROOT'] . '/public/images/logo2.png'; ///
        $color_r = 128;
        $color_g = 179;
        $color_b = 240;
        $color_fr = 200;
        $color_fg = 220;
        $color_fb = 240;
        
        $color_tmr = 255;
        $color_tmg = 255;
        $color_tmb = 255;

        $color_tsr = 0;
        $color_tsg = 0;
        $color_tsb = 0;

        $folio = "";
        $serie = "";
        $folio = "";
        $tipoPago = "";
        $cuenta = "";
        $condPago = "";
        $subtotal = "0";
        $total = "0";
        $folio = "";
        $Emisor = "";
        $EmisorRFC = "";
        $receptorCliente = "";
        $receptorRFCCliente ="";
        $EmisorCalle = "";
        $EmisorNumExt = "";
        $EmisorNumInt = "";
        $EmisorColonia = "";
        $EmisorCity = "";
        $EmisorEstado = "";
        $EmisorPais = "";
        $EmisorZIP = "";
        $folioFiscal = "";
        $CSTSat = "";
        $CSTEmisor = "";
        $fechaEmision = "";
        $fechaCert = "";
        $co = "";
        $selloEmisor = "";
        $selloSAT = "";
        $Receptor = "";
        $ReceptorRFC = "";
        $ReceptorCalle = "";
        $ReceptorNumExt = "";
        $ReceptorNumInt = "";
        $ReceptorColonia = "";
        $ReceptorCity = "";
        $ReceptorEstado = "";
        $ReceptorPais = "";
        $ReceptorZIP = "";
        $productos = array();
        $traslados = array();
        $retenciones = array();
        $productosTraslado = array();
        $aduanal = false;
        $predial = false;
        $regimen = "";
        $RegimenReceptor = "";
        $leyenda = "";
        $norma = "";
        $disposicionFiscal = "";
        $factura = "";
        $Efectos_fiscales = "EFECTOS FISCALES AL PAGO";
        $carta_porte = false;


        $telefono = $clientAddress['branchoffice'] == '12'? '6188102521' : ($clientAddress['branchoffice'] == '13' || $clientAddress['branchoffice'] == '14' ? '6188147148' : '6188170585');
        $calle = $clientAddress['branchoffice'] == '12'? 'FRANCISCA ESCARZAGA #500' : ($clientAddress['branchoffice'] == '13' || $clientAddress['branchoffice'] == '14' ? 'ALUMINIO SN' : '20 DE NOVIEMBRE # 515 OTE.'); 
        $colonia = $clientAddress['branchoffice'] == '12'? 'SANTA FE' : ($clientAddress['branchoffice'] == '13' || $clientAddress['branchoffice'] == '14' ? 'FIDEICOMISO CIUDAD INDUSTRIAL' : 'ZONA CENTRO'); 
        $municipio = $clientAddress['branchoffice'] == '12'? 'DURANGO' : ($clientAddress['branchoffice'] == '13' || $clientAddress['branchoffice'] == '14' ? 'DURANDO' : 'DURANGO'); 
        $ciudad = $clientAddress['branchoffice'] == '12'? 'VICTORIA DE DURANGO' : ($clientAddress['branchoffice'] == '13' || $clientAddress['branchoffice'] == '14' ? '' : 'DURANGO'); 
        $cp = $clientAddress['branchoffice'] == '12'? '34240' : ($clientAddress['branchoffice'] == '13' || $clientAddress['branchoffice'] == '14' ? '34229' : '34000'); 

        $doc = new \DOMDocument();
        $doc->loadXML($cfdi);
        $xpath = new \DOMXpath($doc);

        $xpath->registerNamespace("leyendasFisc", "http://www.sat.gob.mx/leyendasFiscales");
        $xpath->registerNamespace("tfd", "http://www.sat.gob.mx/TimbreFiscalDigital");
        error_reporting(0);
        $elements = $xpath->query("//cfdi:Comprobante");
        foreach ($elements as $element) {
            $folio = $element->getAttribute('Folio');
           // $this->folio_current = $folio;
            $serie = $element->getAttribute('Serie');
            $folio = $folio;
            $fechaEmision = $element->getAttribute('Fecha');
            $condPago = $element->getAttribute('MetodoPago');
            $lugar_exp = $element->getAttribute('LugarExpedicion');
            if ($condPago == "PUE") {
                $condPago = "PUE - Pago en una sola exhibicion";
            } else {
                $condPago = "PPD - Pago en parcialidades o diferido";
            }
            $tipoPago = $element->getAttribute('FormaPago');
            $old_tipoPago = $tipoPago;
            $moneda = $element->getAttribute('Moneda');
            if ($moneda == 'EUR') {
                $signo_peso = chr(128);
            }else{ 
                $signo_peso = "$";
            }
            if ($tipoPago == '01') {
                $tipoPago = "01 - Efectivo";
            } elseif ($tipoPago == '02') {
                $tipoPago = "02 - Cheque nominativo";
            } elseif ($tipoPago == '03') {
                $tipoPago = "03 - Trasferencia electronica de fondos";
            } elseif ($tipoPago == '04') {
                $tipoPago = "04 - Tarjeta de Credito";
            } elseif ($tipoPago == '05') {
                $tipoPago = "05 - Monedero Electrónico";
            } elseif ($tipoPago == '06') {
                $tipoPago = "06 - Dinero Electrónico";
            } elseif ($tipoPago == '08') {
                $tipoPago = "08 - Vales de despensa";
            } elseif ($tipoPago == '28') {
                $tipoPago = "28 - Tarjeta de Debito";
            } elseif ($tipoPago == '29') {
                $tipoPago = "29 - Tarjeta de Servicio";
            } elseif ($tipoPago == '99') {
                $tipoPago = "99 - Por definir";
            }
            $cuenta = $element->getAttribute('NumCtaPago');
            $Efectos_fiscales = $element->getAttribute('condicionesDePago');

            $subtotal = $element->getAttribute('SubTotal');
            $descuento_total = $element->getAttribute('Descuento')==null?0:$element->getAttribute('Descuento');
            $total = $element->getAttribute('Total');
            $CSTEmisor = $element->getAttribute('NoCertificado');
            $selloEmisor = $fechaCert = $element->getAttribute('Sello');
            $versionCO = $element->getAttribute('Version');
            $tipo_comprobante = $element->getAttribute('TipoDeComprobante');
        }

        $factura = "Ingreso";

        $elements = $xpath->query("//cfdi:Comprobante/cfdi:CfdiRelacionados");
        foreach ($elements as $element) {
            $clave_relacion = $element->getAttribute('TipoRelacion');
            if($clave_relacion == '01'){
                $tipo_relacion = '01 - Nota de credito de los documentos relacionados';
            }else if ($clave_relacion == '02'){
                $tipo_relacion = '02 - Nota de credito de los documentos relacionados';
            }else if ($clave_relacion == '07'){
                $tipo_relacion = '07 - CFDI por aplicación de anticipo';
            }else if ($clave_relacion == '04'){
                $tipo_relacion = '04 - Sustitución de CFDI previos';
            }
        }
        $elements = $xpath->query("//cfdi:Comprobante/cfdi:CfdiRelacionados/cfdi:CfdiRelacionado");
        foreach ($elements as $element) {
            $uuid_relacionado = $element->getAttribute('UUID');
        }
            
        if ($tipo_comprobante == 'E') {
            $factura = "Egreso";
        }

        if ($tipo_comprobante == 'T') {
            $factura = "T - Traslado";
        }

        if ($tipo_comprobante == 'P') {
            $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/pago10:Pagos/pago10:Pago");
            if ($elements == null) {
                $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago");
            }
            foreach ($elements as $element) {
                $tipoPago = $element->getAttribute('FormaDePagoP');
                if ($tipoPago == '01') {
                    $tipoPago = "01 - Efectivo";
                } elseif ($tipoPago == '02') {
                    $tipoPago = "02 - Cheque nominativo";
                } elseif ($tipoPago == '03') {
                    $tipoPago = "03 - Trasferencia electronica de fondos";
                } elseif ($tipoPago == '04') {
                    $tipoPago = "04 - Tarjeta de Credito";
                } elseif ($tipoPago == '05') {
                    $tipoPago = "05 - Monedero Electrónico";
                } elseif ($tipoPago == '06') {
                    $tipoPago = "06 - Dinero Electrónico";
                } elseif ($tipoPago == '08') {
                    $tipoPago = "08 - Vales de despensa";
                } elseif ($tipoPago == '28') {
                    $tipoPago = "28 - Tarjeta de Debito";
                } elseif ($tipoPago == '29') {
                    $tipoPago = "29 - Tarjeta de Servicio";
                } elseif ($tipoPago == '99') {
                    $tipoPago = "99 - Otros";
                }
                $monto_complemento = $element->getAttribute('Monto');
                $fecha_pago = $element->getAttribute('FechaPago');
                $moneda = $element->getAttribute('MonedaP');
                $rfc_cta_ord = $element->getAttribute('RfcEmisorCtaOrd');
                $cta_ord = $element->getAttribute('CtaOrdenante');
                $banco_ord = $element->getAttribute('NomBancoOrdExt');
                $num_operacion = $element->getAttribute('NumOperacion');
                $rfc_cta_ben = $element->getAttribute('RfcEmisorCtaBen');
                $cta_ben = $element->getAttribute('CtaBeneficiario');
            }
            $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/pago10:Pagos/pago10:Pago/pago10:DoctoRelacionado");
            if($elements == null){
                $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/pago20:DoctoRelacionado");
            }
            foreach ($elements as $element) {
                $uuid_relacionado = $element->getAttribute('IdDocumento');
                $num_parcialidad = $element->getAttribute('NumParcialidad');
                $saldo_anterior = $element->getAttribute('ImpSaldoAnt');
                $saldo_insoluto = $element->getAttribute('ImpSaldoInsoluto');
                $condPago = $element->getAttribute('MetodoPagoDR');
                if ($condPago == "PUE") {
                    $condPago = "PUE - Pago en una sola exhibicion";
                } else {
                    $condPago = "PPD - Pago en parcialidades o diferido";
                }
            }
            if($cliente == '333'){
                $factura = "Recibo E. de pago";
            }else{
                $factura = "Complemento pago";
            }
        }

        if ($tipo_comprobante == 'T') {
            $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/cartaporte20:Mercancias");
            foreach ($elements as $element) {
                $pesoTotal = $element->getAttribute('PesoBrutoTotal');
            }
            $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/cartaporte20:Mercancias/cartaporte20:Mercancia");
            foreach ($elements as $element) {
                $prod = new invoiceProducts();
                $prod->cantidad = $element->getAttribute('Cantidad');
                $prod->clavUnidad = $element->getAttribute('ClaveUnidad');
                $prod->descripcion = $element->getAttribute('Descripcion');
                $prod->peso = $element->getAttribute('PesoEnKg');
                $prod->clavProdServ = $element->getAttribute('BienesTransp');
                $prod->costoUni = $element->getAttribute('ValorMercancia');
                array_push($productosTraslado, $prod);
            }
        }

        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Emisor");
        foreach ($elements as $element) {
            $Emisor = $element->getAttribute('Nombre');
            $EmisorRFC = $element->getAttribute('Rfc');
            $regimen = $element->getAttribute('RegimenFiscal');
            if ($regimen == '601') {
                $regimen = "General de Ley Personas Morales";
            } else {
                if ($regimen == '612') {
                    $regimen = "Personas Fisicas con Actividades Empresariales y Profesionales";
                } else {
                    if ($regimen == '621') {
                        $regimen = "Incorporacion Fiscal";
                    }
                }
            }
        }

        $elementsReceptor = $xpath->query("//cfdi:Comprobante/cfdi:Receptor");
        foreach ($elementsReceptor as $element) {
            $receptorCliente = $element->getAttribute('Nombre');
            $receptorRFCCliente = $element->getAttribute('Rfc');
            $RegimenReceptor = $element->getAttribute('RegimenFiscalReceptor');
            if ($RegimenReceptor == '601') {
                $RegimenReceptor = "General de Ley Personas Morales";
            } else if ($RegimenReceptor == '603') {
                $RegimenReceptor = "Personas Morales con Fines no Lucrativos";
            } else if ($RegimenReceptor == '605') {
                $RegimenReceptor = "Sueldos y Salarios e Ingresos Asimilados a Salarios";
            }else if ($RegimenReceptor == '606'){
                $RegimenReceptor = "Arrendamiento";
            }else if ($RegimenReceptor == '608'){
                $RegimenReceptor = "Demás ingresos";
            }else if ($RegimenReceptor == '610'){
                $RegimenReceptor = "Residentes en el Extranjero sin Establecimiento Permanente en México";
            }else if ($RegimenReceptor == '611'){
                $RegimenReceptor = "Ingresos por Dividendos (socios y accionistas)";
            }else if ($RegimenReceptor == '614'){
                $RegimenReceptor = "Ingresos por intereses";
            }else if ($RegimenReceptor == '616'){
                $RegimenReceptor = "Sin obligaciones fiscales";
            }else if ($RegimenReceptor == '620'){
                $RegimenReceptor = "Sociedades Cooperativas de Producción que optan por diferir sus ingresos";
            }else if ($RegimenReceptor == '621'){
                $RegimenReceptor = "Incorporación Fiscal";
            }else if ($RegimenReceptor == '622'){
                $RegimenReceptor = "Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras";
            }else if ($RegimenReceptor == '623'){
                $RegimenReceptor = "Opcional para Grupos de Sociedades";
            }else if ($RegimenReceptor == '624'){
                $RegimenReceptor = "Coordinados";
            }else if ($RegimenReceptor == '628'){
                $RegimenReceptor = "Hidrocarburos";
            }else if ($RegimenReceptor == '607'){
                $RegimenReceptor = "Régimen de Enajenación o Adquisición de Bienes";
            }else if ($RegimenReceptor == '629'){
                $RegimenReceptor = "De los Regímenes Fiscales Preferentes y de las Empresas Multinacionales";
            }else if ($RegimenReceptor == '630'){
                $RegimenReceptor = "Enajenación de acciones en bolsa de valores";
            }else if ($RegimenReceptor == '615'){
                $RegimenReceptor = "Régimen de los ingresos por obtención de premios";
            }else if ($RegimenReceptor == '625'){
                $RegimenReceptor = "Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas";
            }else if ($RegimenReceptor == '626'){
                $RegimenReceptor = "Régimen Simplificado de Confianza";
            } else {
                if ($RegimenReceptor == '612') {
                    $RegimenReceptor = "Personas Fisicas con Actividades Empresariales y Profesionales";
                } else {
                    if ($RegimenReceptor == '621') {
                        $RegimenReceptor = "Incorporacion Fiscal";
                    }
                }
            }
        }

        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Emisor/cfdi:DomicilioFiscal");
        foreach ($elements as $element) {
            $EmisorCalle = $element->getAttribute('calle');
            $EmisorNumExt = $element->getAttribute('noExterior');
            $EmisorNumInt = $element->getAttribute('noInterior');
            $EmisorColonia = $element->getAttribute('colonia');

            $auxlocalidad = ucwords(strtolower(trim($element->getAttribute('localidad'))));
            $auxmunicipio = ucwords(strtolower(trim($element->getAttribute('municipio'))));
            if ($auxlocalidad == $auxmunicipio) {
                $EmisorCity = $auxmunicipio;
            } else {
                $EmisorCity = $auxlocalidad . ', ' . $auxmunicipio;
            }
            $EmisorEstado = $element->getAttribute('estado');
            $EmisorPais = $element->getAttribute('pais');
            $EmisorZIP = $element->getAttribute('codigoPostal');
        }

        /*$elements = $xpath->query("//cfdi:Comprobante/cfdi:Emisor/cfdi:RegimenFiscal");
        foreach( $elements as $element )
        {
            $regimen = $element->getAttribute('Regimen');
        }*/
        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Receptor");
        foreach ($elements as $element) {
            $Receptor = str_replace("Ü", "Ü", $element->getAttribute('Nombre'));
            $ReceptorRFC = $element->getAttribute('Rfc');
            $ReceptorusoCFDI = $element->getAttribute('UsoCFDI');

            if ($ReceptorusoCFDI == 'G01') {
                $ReceptorusoCFDI = "G01 - Adquisicion de mercancias";
            } elseif ($ReceptorusoCFDI == 'G02') {
                $ReceptorusoCFDI = "G02 - Devoluciones, descuentos o bonificaciones";
            } elseif ($ReceptorusoCFDI == 'G03') {
                $ReceptorusoCFDI = "G03 - Gastos en general";
            } elseif ($ReceptorusoCFDI == 'I01') {
                $ReceptorusoCFDI = "I01 - Construcciones";
            } elseif ($ReceptorusoCFDI == 'I02') {
                $ReceptorusoCFDI = "I02 - Mobilario y equipo de oficina por inversiones";
            } elseif ($ReceptorusoCFDI == 'I03') {
                $ReceptorusoCFDI = "I03 - Equipo de transporte";
            } elseif ($ReceptorusoCFDI == 'I04') {
                $ReceptorusoCFDI = "I04 - Equipo de computo y accesorios";
            } elseif ($ReceptorusoCFDI == 'I05') {
                $ReceptorusoCFDI = "I05 - Dados, troqueles, moldes, matrices y herramental";
            } elseif ($ReceptorusoCFDI == 'I06') {
                $ReceptorusoCFDI = "I06 - Comunicaciones telefonicas";
            } elseif ($ReceptorusoCFDI == 'I07') {
                $ReceptorusoCFDI = "I07 - Comunicaciones satelitales";
            } elseif ($ReceptorusoCFDI == 'I08') {
                $ReceptorusoCFDI = "I08 - Otra maquinaria y equipo";
            } elseif ($ReceptorusoCFDI == 'D01') {
                $ReceptorusoCFDI = "D01 - Honorarios medicos, dentales y gastos hospitalarios.";
            } elseif ($ReceptorusoCFDI == 'D02') {
                $ReceptorusoCFDI = "D02 - Gastos medicos por incapacidad o discapacidad";
            } elseif ($ReceptorusoCFDI == 'D03') {
                $ReceptorusoCFDI = "D03 - Gastos funerales.";
            } elseif ($ReceptorusoCFDI == 'D04') {
                $ReceptorusoCFDI = "D04 - Donativos";
            } elseif ($ReceptorusoCFDI == 'D05') {
                $ReceptorusoCFDI = "D05 - Intereses reales efectivamente pagados por creditos hipotecarios(casa habitacion)";
            } elseif ($ReceptorusoCFDI == 'D06') {
                $ReceptorusoCFDI = "D06 - Aportaciones voluntarias al SAR";
            } elseif ($ReceptorusoCFDI == 'D07') {
                $ReceptorusoCFDI = "D07 - Primas por seguros de gastos medicos";
            } elseif ($ReceptorusoCFDI == 'D08') {
                $ReceptorusoCFDI = "D08 - Gastos de transportacion escolar obligatoria";
            } elseif ($ReceptorusoCFDI == 'D09') {
                $ReceptorusoCFDI = "D09 - Depositos en cuentas para el ahorro, primas que tengan como base planes de pensiones";
            } elseif ($ReceptorusoCFDI == 'D10') {
                $ReceptorusoCFDI = "D10 - Pagos por servicios educativos(colegiaturas)";
            } elseif ($ReceptorusoCFDI == 'P01') {
                $ReceptorusoCFDI = "P01 - Por definir";
            }
        }

        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Receptor/cfdi:Domicilio");
        foreach ($elements as $element) {
            $ReceptorCalle = $element->getAttribute('calle');
            $ReceptorNumExt = $element->getAttribute('noExterior');
            $ReceptorNumInt = $element->getAttribute('noInterior');
            $ReceptorColonia = $element->getAttribute('colonia');
            $ReceptorCity = $element->getAttribute('municipio');
            //$ReceptorCity = $element->getAttribute('localidad').", ".$element->getAttribute('municipio');
            $ReceptorEstado = $element->getAttribute('estado');
            $ReceptorPais = $element->getAttribute('pais');
            $ReceptorZIP = $element->getAttribute('codigoPostal');
        }

        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto");
        foreach ($elements as $key => $element) {
            $prod = new invoiceProducts();
            $prod->cantidad = $element->getAttribute('Cantidad');
            $prod->descripcion = $element->getAttribute('Descripcion');
            $prod->costoUni = $element->getAttribute('ValorUnitario');
            $prod->total = $element->getAttribute('Importe');
            $prod->clavUnidad = $element->getAttribute('ClaveUnidad');
            $prod->clavProdServ = $element->getAttribute('ClaveProdServ');
            if (is_null($element->getAttribute('Descuento')) || $element->getAttribute('Descuento') == '') {
                $descuento_null = true;
                $prod->descuento = '0.00';
            } else {
                $descuento_null = false;
                $prod->descuento = $element->getAttribute('Descuento');
            }
            if ($prod->clavUnidad == 'E48') {
                $prod->clavUnidad = 'E48 - Unidad de servicio';
            } else {
                if ($prod->clavUnidad == 'ACT') {
                    $prod->clavUnidad = 'ACT - Actividad';
                }
                if ($prod->clavUnidad == 'C62') {
                    $prod->clavUnidad = 'C62 - Uno';
                }
                if ($prod->clavUnidad == 'H87') {
                    $prod->clavUnidad = 'H87 - Pieza';
                }
                if ($prod->clavUnidad == 'XKI') {
                    $prod->clavUnidad = 'XKI - Kit (Conjunto de piezas)';
                }
                if ($prod->clavUnidad == 'XBX') {
                    $prod->clavUnidad = 'XKI - Caja';
                }
            }
            $impuesto_null = true;
            $traslados_total = 0;
            $retenidos_total = 0;        
            $prod->traslado = 0;       
            $prod->retencion = 0;
            $imp_elements = $xpath->query("//cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado");
            foreach ($imp_elements as $key2 => $element) {
                $impuesto_null = false;
                if($key2 == $key){
                    $traslado_importe = $element->getAttribute('Importe');
                    $traslados_total += $traslado_importe;
                    $prod->traslado += $traslado_importe;
                }
            }
            $imp_elements = $xpath->query("//cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto/cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion");
            foreach ($imp_elements as $key2 => $element) {
                $impuesto_null = false;
                if($key2 == $key){
                    $retenido_importe = $element->getAttribute('Importe');
                    $retenidos_total += $retenido_importe;
                    $prod->retencion += $retenido_importe;
                }
            }
            $prod->impuesto = $traslados_total - $retenidos_total;
            array_push($productos, $prod);

        }

        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto/cfdi:InformacionAduanera");
        foreach ($elements as $element) {
            $aduanal = true;
            $aduanalNumero = $element->getAttribute('numero');
            $aduanalFecha = $element->getAttribute('fecha');
        }

        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto/cfdi:CuentaPredial");
        foreach ($elements as $element) {
            $predial = true;
            $predialNumero = $element->getAttribute('numero');
        }

        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion");
        foreach ($elements as $element) {
            $retencion = new impuestoRetencion();
            $retencion->impuesto = $element->getAttribute('Impuesto');
            $retencion->importe = $element->getAttribute('Importe');
            array_push($retenciones, $retencion);
        }

        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado");
        foreach ($elements as $element) {
            $traslado = new impuestoTraslado();
            $traslado->impuesto = $element->getAttribute('Impuesto');
            $traslado->importe = $element->getAttribute('Importe');
            $traslado->tasa = $element->getAttribute('Tasa');
            array_push($traslados, $traslado);

        }
        $totalImpLocales = 0;
        
        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/implocal:ImpuestosLocales");
        foreach ($elements as $element) {
            $totalRetencionesLocales = $element->getAttribute('TotaldeRetenciones');
            $totalTrasladosLocales = $element->getAttribute('TotaldeTraslados');
            $totalImpLocales = $totalTrasladosLocales - $totalRetencionesLocales;
            if ($totalImpLocales < 0) {
                $imp_local_reduction = true;
                $totalImpLocales = $totalImpLocales * -1;
            } else {
                $imp_local_reduction = false;
            }
        }
        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/tfd:TimbreFiscalDigital");
        foreach ($elements as $element) {
            $folioFiscal = $element->getAttribute('UUID');
            $CSTSat = $element->getAttribute('NoCertificadoSAT');
            $fechaCert = $element->getAttribute('FechaTimbrado');
            $selloSAT = $element->getAttribute('SelloSAT');
            $version = $element->getAttribute('Version');
            $selloCFD = $element->getAttribute('SelloCFD');

            $co = "||" . $versionCO . "|" . $folioFiscal . "|" . $fechaCert . "|" . $selloCFD . "|" . $CSTSat . "||";
        }

        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/leyendasFisc:LeyendasFiscales/leyendasFisc:Leyenda");
        foreach ($elements as $element) {
            $leyenda = $element->getAttribute('textoLeyenda');
            $norma = $element->getAttribute('norma');
            $disposicionFiscal = $element->getAttribute('disposicionFiscal');

        }

        $comercio_exterior = false;
        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cce11:ComercioExterior");
        if ($elements) {
            foreach ($elements as $element) {
                $comercio_exterior = true;
                $tipo_operacion = $element->getAttribute('TipoOperacion');
                $clave_pedimento = $element->getAttribute('ClaveDePedimento');
                $certiicado_origen = $element->getAttribute('CertificadoOrigen');
                $incoterm = $element->getAttribute('Incoterm');
                if ($incoterm == 'EXW') {
                    $incoterm_text = 'En fábrica, lugar convenido';
                }
                $subdivision = $element->getAttribute('Subdivision');
                $tipo_cambioUSD = $element->getAttribute('TipoCambioUSD');
                $total_dolares = $element->getAttribute('TotalUSD');
                $emisor_query = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cce11:ComercioExterior/cce11:Emisor");
                foreach ($emisor_query as $e) {
                    $curp_ext = $e->getAttribute('Curp');
                    $emisor_query_d = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cce11:ComercioExterior/cce11:Emisor/cce11:Domicilio");
                    foreach ($emisor_query_d as $e) {
                        $calle_emisor_ext = $e->getAttribute('Calle');
                        $num_emisor_ext = $e->getAttribute('NumeroExterior');
                        $estado_emisor_ext = $e->getAttribute('Estado');
                        $pais_emisor_ext = $e->getAttribute('Pais');
                        $cp_emisor_ext = $e->getAttribute('CodigoPostal');
                    }
                }
                $receptor_query_d = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cce11:ComercioExterior/cce11:Receptor/cce11:Domicilio");
                foreach ($receptor_query_d as $e) {
                    $calle_receptor_ext = $e->getAttribute('Calle');
                    $localidad_receptor_ext = $e->getAttribute('Localidad');
                    $estado_receptor_ext = $e->getAttribute('Estado');
                    $pais_receptor_ext = $e->getAttribute('Pais');
                    $cp_receptor_ext = $e->getAttribute('CodigoPostal');
                }
                $mercancias_array = array();
                $mercancias_query = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cce11:ComercioExterior/cce11:Mercancias/cce11:Mercancia");
                foreach ($mercancias_query as $m) {
                    $new_merch = array();
                    $new_merch['cantidad_aduana'] = $m->getAttribute('CantidadAduana');
                    $new_merch['unidad_aduana'] = $m->getAttribute('UnidadAduana');
                    $new_merch['valor_unitario'] = $m->getAttribute('ValorUnitarioAduana');
                    $new_merch['no_identificacion'] = $m->getAttribute('NoIdentificacion');
                    $new_merch['fraccion_arancelaria'] = $m->getAttribute('FraccionArancelaria');
                    $new_merch['valor_dolares'] = $m->getAttribute('ValorDolares');
                    $mercancias_array[] = $new_merch;
                }
                $receptor_main = $xpath->query("//cfdi:Comprobante/cfdi:Receptor");
                foreach ($receptor_main as $r) {
                    $residencia_fiscal = $r->getAttribute('ResidenciaFiscal');
                    $numregidtrib = $r->getAttribute('NumRegIdTrib');
                }
            }
        }

        $folio = str_replace(' ', '', $folio);
        $serie = str_replace(' ', '', $serie);

        /*******************************/

        $pdf = new AntPDF('P', 'mm', "Letter");
        $leftMargin = 10;
        $pdf->SetMargins(10, 10, 10);
        //$pdf->SetFooter(NULL);
        $pdf->AddPage();
        //Logo -> Obtener de ANTAccount
        if ($cliente == 43) {
            //Define tipo de letra a usar, Arial, Negrita, 15
            $pdf->SetX(50);

            $pdf->SetFont('Arial', 'B', 15);

            $pdf->Cell(111, 25, 'SERVICIO TECNICO A EQUIPOS DE COCINA', 0, 0, 'C');

            $pdf->Ln(25);
            $pdf->SetX(35);
            $pdf->Image($logo, 15, 15, null, 15);
        } else {
            if ($cliente == 47) {
                $pdf->Image($logo, 42.5, 5, null, 15);
            } else {
                if ($cliente == 41) {
                    $pdf->Image($logo, 8, 8, null, 10);
                } else {
                    if ($cliente == 6) {
                        $pdf->Image($logo, 43, 8, 13, 13);
                    } else if ($cliente == 26 || $cliente == 537) {
                        $pdf->Image($logo, 8, 0, null, 25);
                    } else if ($cliente >= 101 && $cliente <= 150) {
                        $pdf->Image($logo, 8, 2, null, 15);
                    } else {
                        $pdf->Image($logo, 8, 2, null, 20);
                    }
                }
            }
        }
        $pdf->SetX(95);
        $pdf->SetFont("Arial", "", 8);
        $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
        $pdf->SetFillColor($color_r, $color_g, $color_b);


        //Folio Fiscal CDFI
        $pdf->SetX(95);
        $pdf->SetFillColor($color_r, $color_g, $color_b);
        $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
        $pdf->Cell(110, 4, "FOLIO FISCAL", 0, 1, "C", 1);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $ikufont = 7;
        $clientfont = 8;
        $clientlabelfont = 8;
        $pdf->SetX(57);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $pdf->SetFont("Arial", "", $clientfont);
        $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $pdf->Cell(38, 9, "TEL: ".$telefono, 0, 0, "L", 0);
        $pdf->Cell(110, 5, $folioFiscal, 0, 1, "C", 1);
        $pdf->Ln(3);


        $pdf->SetFont("Arial", "", 8);
        $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
        $pdf->SetFillColor($color_r, $color_g, $color_b);
        $pdf->Cell(38, 4, "FECHA EMISION", 0, 0, "C", 1);
        $pdf->Cell(5, 5);
        $pdf->Cell(38, 4, "FECHA CERTIFICACION", 0, 0, "C", 1);
        $pdf->Cell(7, 5);
        $pdf->Cell(38, 4, "FACTURA", 0, 0, "C", 1);
        $pdf->Cell(50, 7);

        //No. de Serie del CSD del SAT
        $pdf->SetX(95);
        $pdf->SetFillColor($color_r, $color_g, $color_b);
        $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
        $pdf->Cell(110, 4, "NO. SERIE DEL CSD DEL SAT", 0, 1, "C", 1);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $ikufont = 7;
        $clientfont = 8;
        $clientlabelfont = 8;

        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
        $pdf->Cell(38, 5, $fechaEmision, 0, 0, "C", 1);
        $pdf->Cell(5, 5);

        //Fecha de Expedicion
        $pdf->Cell(38, 5, $fechaCert, 0, 0, "C", 1);
        $pdf->Cell(7, 5);

        $pdf->SetX(95);
        $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $pdf->SetFont("Arial", "", $clientfont);
        $pdf->Cell(110, 5, $CSTSat, 0, 1, "C", 1);
        $pdf->Ln(3);

        $pdf->SetFont("Arial", "", 8);
        $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
        $pdf->SetFillColor($color_r, $color_g, $color_b);
        $pdf->Cell(25, 4, "FOLIO", 0, 0, "C", 1);

        $pdf->Cell(5, 5);
        $pdf->Cell(25, 4, "TIPO", 0, 0, "C", 1);

        $pdf->Cell(5, 5);
        if ($tipo_comprobante == 'T') {
            $pdf->Cell(21, 4);
        }else{
            $pdf->Cell(21, 4, "SERIE", 0, 0, "C", 1);
        }
        $pdf->Cell(7, 5);

        $pdf->SetX(95);
        $pdf->SetFillColor($color_r, $color_g, $color_b);
        $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
        $pdf->Cell(110, 4, "NO. SERIE DEL CSD DEL Emisor", 0, 1, "C", 1);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);


        $ikufont = 7;
        $clientfont = 8;
        $clientlabelfont = 8;


        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
        $pdf->Cell(25, 5, $folio, 0, 0, "C", 1);


        //Agregar condicion para que sea Recibo de honorarios o Factura
        $pdf->Cell(5, 5);
        if ($cliente == 38 && ($folio == 06 || $folio == 07 || $folio == 8 || $folio == 9 || $folio == 11)) {
            $pdf->Cell(38, 4, "Recibo de Honorarios", 0, 0, "C", 1);
        } else {
            $pdf->Cell(25, 4, $factura, 0, 0, "C", 1);
        }
        $pdf->Cell(5, 5);
        if ($tipo_comprobante == 'T') {
            $pdf->Cell(21, 4);
        }else{
            $pdf->Cell(21, 5, $serie, 0, 0, "C", 1);
        }
        $pdf->Cell(7, 5);

        $pdf->SetX(95);
        $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $pdf->SetFont("Arial", "", 7.5);
        $pdf->Cell(110, 5, $CSTEmisor, 0, 1, "C", 1);
        $pdf->Ln(3);

        $pdf->SetX(95);
        $pdf->SetFillColor($color_r, $color_g, $color_b);
        $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
        $pdf->Cell(110, 4, "CLIENTE", 0, 1, "C", 1);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);

        //Nombre Emisor
        $split_emisor = explode(' ',htmlspecialchars_decode($Emisor));
        $antes_emisor = $pdf->GetY()-4;
        $pdf->Cell(60, 5);
        if(count($split_emisor) == 8){
            $pdf->SetXY(10, $antes_emisor);
            $pdf->MultiCell(60, 5, $split_emisor[0].' '.$split_emisor[1], 0, "L");
        }else{
            //$pdf->Cell(60, 5, htmlspecialchars_decode($Emisor), 0, 0, "L");
            $pdf->SetXY(10, $antes_emisor);
            $pdf->MultiCell(60, 5, htmlspecialchars_decode($Emisor), 0, "L");
        }

        $ikufont = 7;
        $clientfont = 8;
        $clientlabelfont = 8;
        $pdf->SetXY(70, $antes_emisor+4);
        $pdf->SetFont("Arial", "", $clientlabelfont);
        $pdf->SetTextColor($color_r, $color_g, $color_b);
        $pdf->Cell(25, 5, "RAZON SOCIAL: ", 0, 0, "R");

        $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $pdf->SetFont("Arial", "", $clientfont);

        if ($Receptor == '') {
            $Receptor = 'Publico general';
        }
        //Nombre Receptor
        //$pdf->Cell(110,5,$Receptor,0,1,"L",1);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $address_client = $clientAddress['client_street'].($clientAddress['outdoor_number'] == ''?'':' # '.$clientAddress['outdoor_number']).($clientAddress['int_number'] == ''?'':' Int: '.$clientAddress['int_number']);
        $pdf->MultiCell(50, 5, htmlspecialchars_decode(utf8_decode($tipo_comprobante == 'T'?$clientAddress['razon_social']:$Receptor)).(strlen($address_client)>=30?"\n ":''), 0, "L",1);
        $yAfter = $pdf->GetY();
        //$pdf->Cell(50, 5, utf8_decode($Receptor), 0, 0, "L",1);
        $pdf->SetXY($x + 50, $y);
        $address_client .= strlen($address_client)<=31 && $yAfter > $y+5? "\n ":"";
        $pdf->MultiCell(60, 5, utf8_decode('CALLE: '.$address_client), 0, "L", 1);
        $pdf->SetFont("Arial", "", $ikufont);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);

        //RFC Emisor
        if(count($split_emisor) == 8){
            $pdf->Cell(60, 5, $split_emisor[2].' '.$split_emisor[3].' '.$split_emisor[4].' '.$split_emisor[5].' '.$split_emisor[6].' '.$split_emisor[7], 0, 0, "L");
        }else{
            $pdf->Cell(60, 5, "RFC:" . utf8_decode($EmisorRFC), 0, 0, "L");
        }

        $pdf->SetFont("Arial", "", $clientlabelfont);
        $pdf->SetTextColor($color_r, $color_g, $color_b);
        $pdf->Cell(25, 5, "RFC: ", 0, 0, "R");

        $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $pdf->SetFont("Arial", "", $clientfont);

        //RFC Receptor
        $pdf->Cell(50, 5, $tipo_comprobante == 'T'?$clientAddress['rfc']:$ReceptorRFC, 0, 0, "L", 1);
        $pdf->Cell(60, 5, utf8_decode('COL: '.$clientAddress['client_colonia']), 0, 1, "L", 1);
        $pdf->SetFont("Arial", "", 7);
        $pdf->Cell(60, 5, "CALLE: $calle" , 0, 0, "L");
        if ($cliente == 41) {
            $pdf->SetFont("Arial", "", $ikufont);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->Cell(60, 5, 'Lugar Expedicion: ' . $emisor_address, 0, 0, "L");
        }else{
            if(count($split_emisor) == 8){
                $pdf->Cell(60, 5, "RFC:" . utf8_decode($EmisorRFC), 0, 0, "L");
            }else{
                $pdf->SetFont("Arial", "", $ikufont);
                $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
                //$pdf->Cell(60, 5, '', 0, 0, "L");
            }
        }

        $ikufont = 7;
        $clientfont = 8;
        $clientlabelfont = 8;

        $pdf->SetFont("Arial", "", $clientlabelfont);
        $pdf->SetTextColor($color_r, $color_g, $color_b);
        if ($cliente == 41) {
            $pdf->Cell(25, 5, "Direccion: ", 0, 0, "R");
        } else {
            $pdf->Cell(25, 5, utf8_decode("Código Postal: "), 0, 0, "R");
        }

        $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $pdf->SetFont("Arial", "", $clientfont);

        //Nombre Receptor
        //$pdf->Cell(110,5,$Receptor,0,1,"L",1);
        if ($cliente == 41) {
            $pdf->MultiCell(110, 5, $receptor_address, 0, "L", 1);
        } else {
            $pdf->Cell(50, 5, ($tipo_comprobante == 'T'?$clientAddress['lugar_expedicion']:$clientAddress['zip_code']), 0, 0, "L", 1);
            $pdf->MultiCell(60, 5, utf8_decode('MUNICIPIO: '.$clientAddress['client_municipio']), 0, "L", 1);
            
        }
        $pdf->SetFont("Arial", "", $ikufont);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);

        $pdf->SetFont("Arial", "", 7);
        $pdf->Cell(60, 5, "COL: $colonia" , 0, 0, "L");

        $ikufont = 7;
        $clientfont = 8;
        $clientlabelfont = 8;

        $pdf->SetFont("Arial", "", $clientlabelfont);
        $pdf->SetTextColor($color_r, $color_g, $color_b);
        $pdf->Cell(25, 5, "usoCFDI: ", 0, 0, "R");

        $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
        $pdf->SetFont("Arial", "", $clientfont);

        //Nombre Receptor
        //$pdf->Cell(110,5,$Receptor,0,1,"L",1);
        $pdf->Cell(50, 5, $ReceptorusoCFDI, 0, 0, "L", 1);
        $pdf->Cell(60, 5, utf8_decode('CIUDAD: '.$clientAddress['client_ciudad']), 0, 1, "L", 1);
        $pdf->SetFont("Arial", "", $ikufont);
        $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);

        $pdf->SetFont("Arial", "", 7);
        $pdf->Cell(85, 5, "MUNICIPIO: $municipio" , 0, 0, "L");
        $pdf->Cell(60, 5, $clientAddress['sucursal'] != 'MATRIZ' ? $clientAddress['sucursal'] : '', 0, 1, "L");
        $pdf->Cell(50, 5, "CIUDAD: $ciudad" , 0, 0, "L");
        $pdf->Cell(40, 5, "CP: $cp" , 0, 1, "L");
        // $pdf->Ln(5);
        $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
        $pdf->SetFillColor($color_r, $color_g, $color_b);
        $pdf->SetDrawColor($color_r, $color_g, $color_b);
        $pdf->SetFont("Arial", "", 7);
        $pdf->Cell(10, 4, "CANT", 0, 0, "C", 1);
        $x_desc = 42;
        $tipo_comp_x = 0;
        if ($impuesto_null) {
            $x_desc += 25;
            $tipo_comp_x += 25;
        } else {
            if ($traslados_total == 0) {
                $x_desc += 20;
            }
            if ($retenidos_total == 0) {
                $x_desc += 20;
            }
        }
        if ($descuento_null) {
            $x_desc += 25;
            $tipo_comp_x += 25;
        }
        $pdf->Cell($x_desc, 4, "DESCRIPCION", 0, 0, "C", 1);
        $pdf->Cell(20, 4, "CLAVE P. S.", 0, 0, "C", 1);
        $pdf->Cell(20, 4, "CLAVE U.", 0, 0, "C", 1);
        if ($tipo_comprobante == 'P') {
            $pdf->Cell(85 - $tipo_comp_x, 4, '', 0, 0, "C", 1);
        } else {
            $pdf->Cell(20, 4, "COSTO U.", 0, 0, "C", 1);
            if (!$descuento_null) {
                $pdf->Cell(25, 4, "DESCUENTO", 0, 0, "C", 1);
            }
            if (!$impuesto_null) {
                if ($traslados_total > 0) {
                    $pdf->Cell(20, 4, "IVA", 0, 0, "C", 1);
                }
                if ($retenidos_total > 0) {
                    $pdf->Cell(20, 4, "RETENCION", 0, 0, "C", 1);
                }
            } else if($tipo_comprobante == 'T'){
                $pdf->Cell(15, 4, "MAT. PELIGROSO", 0, 0, "C", 1);
            }else {
                $pdf->Cell(15, 4, "", 0, 0, "C", 1);
            }
        }
        if($tipo_comprobante == 'T'){
            $pdf->Cell(18, 4, "PESO KG", 0, 1, "C", 1);
        }else {
            $pdf->Cell(18, 4, "IMPORTE", 0, 1, "C", 1);
        }

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont("Arial", "", 8);
        $pdf->SetFillColor(255);

        if($tipo_comprobante == 'T'){
            $productos = $productosTraslado;
        }
        $numProductos = count($productos);
        for ($i = 1; $i <= $numProductos; $i++) {
            $product = array_shift($productos);
            $desc = $product->descripcion;
            //$desc = preg_replace('/[^A-Za-z0-9\ -]/', ' ', $desc);
            $qty = $product->cantidad;
            $precioUni = $product->costoUni;
            $importe = $product->total;
            $clavProdServ = $product->clavProdServ;
            $clavUnidad = $product->clavUnidad;
            $descuento = $product->descuento;
            $impuesto = $product->impuesto;
            $traslado = $product->traslado;
            $retencion = $product->retencion;
            $peso = $product->peso;

            $yValue = strlen($desc) / 58;
            $yValue = ceil($yValue);
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell(10, 5,/*$x." ".$y."-".*/
                $qty, 0, "C");
            // if ($y > 250) {
            //     $y = $y - 250;
            // }
            $pdf->SetXY($x + 10, $y);
            if ($cliente == 23 && $folio == 3001 &&
                $desc == 'Ponencia "resistencia bacterianas y manejo de infecciones intrabdominales en el paciente pediatrico"') {
                $desc = 'Ponencia Sepsis Abdominal en el paciente cirtico en la UCI';
            }
            if ($cliente == 48 && $folio == 8) {
                $desc = 'Renta de espacio publicitario VM-2B Blvd. Federico Benitez Col. La Cienega, Periodo 15 de Mayo al 14 de Julio del 2014';
            }
            if ($cliente == 48 && $folio == 10) {
                $desc = 'Renta de espacio publicitario VM-2B Blvd. Federico Benitez Col. La Cienega, Periodo 15 de Mayo al 14 de Julio del 2014';
            }
            $push = 0;
            $x_desc = 47;
            $ret_push = 0;
            $tras_push = 0;
            if ($impuesto_null) {
                $x_desc += 25;
                $push += 25;
            } else {
                if ($traslados_total == 0) {
                    $push += 20;
                    $x_desc += 20;
                    $tras_push = 20;
                }
                if ($retenidos_total == 0) {
                    $push += 20;
                    $x_desc += 20;
                    $ret_push = 20;
                }
            }
            if ($descuento_null) {
                $push += 25;
                $x_desc += 25;
            }
            $pdf->MultiCell($x_desc, 5, utf8_decode($desc), 0, "L");
            $pdf->SetXY($x + 55 + $push, $y);
            $pdf->MultiCell(20, 5, $clavProdServ, 0, "L");
            $pdf->SetXY($x + 72 + $push, $y);
            $pdf->MultiCell(27, 5, $clavUnidad, 0, "L");
            $pdf->SetXY($x + 92 + $push, $y);
            if ($tipo_comprobante == 'P') {
                $pdf->MultiCell(20, 5, "" . '', 0, "C");
                $pdf->SetXY($x + 178, $y);
                $pdf->MultiCell(20, 5, $signo_peso . $monto_complemento, 0, "C");
            } else if($tipo_comprobante == 'T'){
                $pdf->MultiCell(20, 5, $signo_peso . $precioUni, 0, "C");
                $pdf->SetXY($x + 154, $y);
                $pdf->MultiCell(25, 5, 'No', 0, "C");
                $pdf->SetXY($x + 173, $y);
                $pdf->MultiCell(25, 5, $peso, 0, "C");
            }else {
                $pdf->MultiCell(20, 5, $signo_peso . $precioUni, 0, "C");
                $pdf->SetXY($x + 122 + $push, $y);
                if ($descuento != '0.00') {
                    $pdf->MultiCell(25, 5, $signo_peso . $descuento, 0, "C");
                }
                $pdf->SetXY($x + 134 + $ret_push + $tras_push, $y);
                if ($impuesto != '') {
                    $impuesto = $signo_peso . $impuesto;
                    if ($traslados_total > 0) {
                        $pdf->MultiCell(25, 5, $signo_peso . number_format($traslado, 2, ".", ""), 0, "C");
                        $pdf->SetXY($x + 154, $y);
                    }
                    if ($retenidos_total > 0) {
                        $pdf->MultiCell(25, 5, $signo_peso . number_format($retencion, 2, ".", ""), 0, "C");
                        $pdf->SetXY($x + 173, $y);
                    }
                }
                $pdf->SetXY($x + 173, $y);
                $pdf->MultiCell(25, 5, $signo_peso . number_format($importe, 2, ".", ""), 0, "C");
            }
            if($y < 244){
                $pdf->SetXY($x, $y + ($yValue * 4));
            }else{
                $pdf->AddPage();
                $pdf->SetXY($x, 5);
            }
            $pdf->Ln();
            if ((!$impuesto_null || !$descuento_null) && $cliente == 62) {
                $pdf->Ln();
                $pdf->Ln();
            }
        }

        if ($aduanal) {
            $pdf->MultiCell(115, 10, "INFORMACION ADUANERA: " . $aduanalNumero . " FECHA: " . $aduanalFecha, "1", "L",
                1);
        }
        if ($predial) {
            $pdf->MultiCell(115, 10, "CUENTA PREDIAL: " . $predialNumero, "1", "L", 1);
        }
        //Total CFDI
        if ($tipo_comprobante == 'P') {
            $monto_complemento = number_format($monto_complemento, 2, ".", "");
            if($moneda == 'EUR') {
                $pdf->MultiCell(115, 10, "*** " . $this->convertNumberToWord($monto_complemento) . " ***", "T", "C", 1);
            }else{
                $pdf->MultiCell(115, 10, "*** " . InvoicePDF::num2letras($monto_complemento,$moneda) . " ***", "T", "C", 1);
            }
        } else if($tipo_comprobante == 'T'){
            $pdf->MultiCell(115, 10,'');
        }else {
            $total = number_format($total, 2, ".", "");
            if($moneda == 'EUR') {
                $pdf->MultiCell(115, 10, "*** " . $this->convertNumberToWord($total) . " ***", "T", "C", 1);
            }else{
                $pdf->MultiCell(115, 10, "*** " . InvoicePDF::num2letras($total,$moneda) . " ***", "T", "C", 1);
            }
        }
        //$pdf->MultiCell(115,10,"*** Mil trescientos noventa y dos  00/100 USD.  ***","T","C",1);

        $pdf->SetFillColor($color_r, $color_g, $color_b);
        $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
        $pdf->SetXY(125, $pdf->GetY() - 10);
        if($tipo_comprobante == 'T'){
            $pdf->SetX(145);
            $pdf->Cell(40, 5, "SUBTOTAL", 0, 0, "C", 1);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetDrawColor($color_r, $color_g, $color_b);
            $pdf->Cell(20, 5, $signo_peso . number_format($subtotal + 0, 2) . "   ", "B", 1, "R", 1);

            $pdf->SetX(145);
            $pdf->SetFillColor($color_r, $color_g, $color_b);
            $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
            $pdf->Cell(40, 5, "TOTAL", 0, 0, "C", 1);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetDrawColor($color_r, $color_g, $color_b);
            $pdf->Cell(20, 5, $signo_peso . number_format($total + 0, 2) . "   ", "B", 1, "R", 1);

            $pdf->SetX(145);
            $pdf->SetFillColor($color_r, $color_g, $color_b);
            $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
            $pdf->Cell(40, 5, "PESO TOTAL", 0, 0, "C", 1);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetDrawColor($color_r, $color_g, $color_b);
            $pdf->Cell(20, 5, $pesoTotal . "   ", "B", 1, "R", 1);
        }else {

            if ($tipo_comprobante == 'P') {
                $pdf->Cell(40, 5, "", 0, 0, "C", 1);
            } else {
                $pdf->Cell(40, 5, "SUBTOTAL", 0, 0, "C", 1);

            }
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetDrawColor($color_r, $color_g, $color_b);
            if ($tipo_comprobante == 'P') {
                $pdf->Cell(40, 5, "", "T", 1, "R", 1);
                $pdf->SetX(125);
                $pdf->SetFillColor($color_r, $color_g, $color_b);
                $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
                $pdf->SetFillColor($color_r, $color_g, $color_b);
                $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
                $pdf->Cell(40, 5, 'MONTO', 0, 0, "C", 1);
                $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
                $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
                $pdf->SetDrawColor($color_r, $color_g, $color_b);
                $pdf->Cell(40, 5, $signo_peso . number_format($monto_complemento + 0, 2) . "   ", 0, 1, "R", 1);
            } else {
                $pdf->Cell(40, 5, $signo_peso . number_format($subtotal + 0, 2) . "   ", "T", 1, "R", 1);
            }

            $numTraslados = count($traslados);
            $numRetenciones = count($retenciones);

            if ($numTraslados > 0 || $numRetenciones > 0) {
                /*$pdf->SetX(125);
                $pdf->SetFillColor(0,127,0);
                $pdf->SetTextColor(255,255,255);
                $pdf->Cell(40,5,"",0,0,"C",1);
                $pdf->SetTextColor(0,0,0);
                $pdf->SetFillColor(240,255,230);
                $pdf->SetDrawColor(0,127,0);
                $pdf->Cell(40,5," ",0,1,"R",1);*/

                for ($i = 1; $i <= $numTraslados; $i++) {
                    $traslado = array_shift($traslados);
                    if ($traslado->impuesto == '002') {
                        $traslado->impuesto = 'IVA';
                        $traslado->tasa = '16';
                    }
                    $pdf->SetX(125);
                    $pdf->SetFillColor($color_r, $color_g, $color_b);
                    $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
                    $pdf->Cell(40, 5, 'IVA', 0, 0, "C", 1);
                    $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
                    $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
                    $pdf->SetDrawColor($color_r, $color_g, $color_b);
                    $pdf->Cell(40, 5, $signo_peso . number_format($traslado->importe + 0, 2) . "   ", 0, 1, "R", 1);
                }
                if($numRetenciones > 0){
                    $pdf->SetX(125);
                    $pdf->SetFillColor($color_r, $color_g, $color_b);
                    $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
                    $pdf->Cell(40, 5, 'IMPUESTOS RETENIDOS', 0, 0, "R", 1);
                    $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
                    $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
                    $pdf->SetDrawColor($color_r, $color_g, $color_b);
                    $pdf->Cell(40, 5, "", 0, 1, "R", 1);
                }
                for ($i = 1; $i <= $numRetenciones; $i++) {
                    $retencion = array_shift($retenciones);
                    if($retencion->impuesto == '001'){
                        $retimp = "ISR";
                    }else if($retencion->impuesto == '002'){
                        $retimp = "IVA";
                    }else if($retencion->impuesto == '003'){
                        $retimp = "IEPS";
                    }
                    $pdf->SetX(125);
                    $pdf->SetFillColor($color_r, $color_g, $color_b);
                    $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
                    $pdf->Cell(40, 5, $retimp, 0, 0, "R", 1);
                    $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
                    $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
                    $pdf->SetDrawColor($color_r, $color_g, $color_b);
                    if((int)$cliente >= 1000){
                        $pdf->Cell(40, 5, "$" . number_format($retencion->importe + 0, 2) . "   ", 0, 1, "R", 1);
                    }else{
                        $pdf->Cell(40, 5, "$" . number_format($retencion->importe + 0, 2) . "   ", 0, 1, "R", 1);
                    }
                }
                if ($totalImpLocales != 0) {
                    $pdf->SetX(125);
                    $pdf->SetFillColor($color_r, $color_g, $color_b);
                    $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
                    $pdf->Cell(40, 5, 'IMP. LOCALES', 0, 0, "R", 1);
                    $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
                    $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
                    $pdf->SetDrawColor($color_r, $color_g, $color_b);
                    if ($imp_local_reduction) {
                        $pdf->Cell(40, 5, $signo_peso . number_format($totalImpLocales + 0, 2) . "   ", 0, 1, "R", 1);
                    } else {
                        $pdf->Cell(40, 5, $signo_peso . number_format($totalImpLocales + 0, 2) . "   ", 0, 1, "R", 1);
                    }
                }
                $pdf->SetX(125);
                $pdf->SetFillColor($color_r, $color_g, $color_b);
                $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
                if(number_format($descuento_total + 0, 2) <= 0){
                    $pdf->Cell(40, 5, "", 0, 0, "R", 1);
                }else{
                    $pdf->Cell(40, 5, "DESCUENTO", 0, 0, "R", 1);
                }
                $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
                $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
                $pdf->SetDrawColor($color_r, $color_g, $color_b);
                if(number_format($descuento_total + 0, 2) <= 0){
                    $pdf->Cell(40, 5, "", 0, 1, "R", 1);
                }else{
                    $pdf->Cell(40, 5, $signo_peso . number_format($descuento_total + 0, 2) . "   ", 0, 1, "R", 1);
                }
            } else {
                if ($totalImpLocales != 0) {
                    $pdf->SetX(125);
                    $pdf->SetFillColor($color_r, $color_g, $color_b);
                    $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
                    $pdf->Cell(40, 5, 'IMP. LOCALES', 0, 0, "R", 1);
                    $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
                    $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
                    $pdf->SetDrawColor($color_r, $color_g, $color_b);
                    if ($imp_local_reduction) {
                        $pdf->Cell(40, 5, $signo_peso . number_format($totalImpLocales + 0, 2) . "   ", 0, 1, "R", 1);
                    } else {
                        $pdf->Cell(40, 5, $signo_peso . number_format($totalImpLocales + 0, 2) . "   ", 0, 1, "R", 1);
                    }
                }
                if ($tipo_comprobante != 'P') {
                    $pdf->SetX(125);
                    $pdf->SetFillColor($color_r, $color_g, $color_b);
                    $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
                    $pdf->Cell(40, 5, "", 0, 0, "C", 1);
                    $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
                    $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
                    $pdf->SetDrawColor($color_r, $color_g, $color_b);
                    $pdf->Cell(40, 5, " ", 0, 1, "R", 1);
                }
            }
        
            $pdf->SetTextColor($color_r, $color_g, $color_b);
            $pdf->SetFont("Arial", "", 7);
            $pdf->Cell(115, 5, "IMPORTE TOTAL CON LETRA", "B", 0, "L", 1);
            $pdf->SetFont("Arial", "", 9);
            $pdf->SetFillColor($color_r, $color_g, $color_b);
            $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
            if ($tipo_comprobante == 'P') {
                $pdf->Cell(40, 5, "", 0, 0, "C", 1);
            } else {
                $pdf->Cell(40, 5, "TOTAL", 0, 0, "C", 1);
            }
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetDrawColor($color_r, $color_g, $color_b);

            //Total de CFDI
            if ($tipo_comprobante == 'P') {
                $pdf->Cell(40, 5, "", "B", 1, "R", 1);
            } else {
                $pdf->Cell(40, 5, $signo_peso . number_format($total + 0, 2) . "   ", "B", 1, "R", 1);
            }
        }

        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/pago10:Pagos/pago10:Pago/pago10:DoctoRelacionado");
        if ($elements == null) {
            $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/pago20:Pagos/pago20:Pago/pago20:DoctoRelacionado");
        }
        foreach ($elements as $key => $element) {
            $uuid_relacionado = $element->getAttribute('IdDocumento');
            $folio_relacionado = $element->getAttribute('Folio');
            $moneda_relacionado = $element->getAttribute('MonedaDR');
            $num_parcialidad = $element->getAttribute('NumParcialidad');
            $saldo_anterior = $element->getAttribute('ImpSaldoAnt');
            $saldo_insoluto = $element->getAttribute('ImpSaldoInsoluto');
            $imp_pagado = $element->getAttribute('ImpPagado');
            $condPago = $element->getAttribute('MetodoDePagoDR');
            $tipo_cambio = $element->getAttribute('TipoCambioDR');
            $condPago = "PPD";
            $pdf->Ln(3);

            $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
            $pdf->SetFillColor($color_r, $color_g, $color_b);
            $pdf->SetDrawColor($color_r, $color_g, $color_b);
            $pdf->SetFont("Arial", "", 9);
            $pdf->Cell(195, 4, "Documento relacionado #".($key+1), 0, 0, "L", 1);
            $pdf->Ln(4);
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(10, 5, "Folio:", 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(10, 5, $folio_relacionado, 0, "L", 1);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(10, 5, "UUID:", 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(65, 5, $uuid_relacionado, 0, "L", 1);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(15, 5, "Moneda:", 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(10, 5, $moneda_relacionado, 0, "L", 1);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(25, 5, "Metodo de pago:", 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(12, 5, $condPago, 0, "L", 1);
            if(!empty($tipo_cambio)){
                $pdf->SetFont("Arial", "B", $clientfont);
                $pdf->Cell(25, 5, "Tipo de cambio:", 0, "L", 1);
                $pdf->SetFont("Arial", "", $clientfont);
                $pdf->Cell(20, 5, $tipo_cambio, 0, "L", 1);
            }
            $pdf->Ln(6);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(20, 5, "# Parcialidad:", 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(13, 5, $num_parcialidad, 0, "L", 1);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(22, 5, "Saldo anterior:", 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(20, 5, $signo_peso.$saldo_anterior, 0, "L", 1);
            if(!empty($imp_pagado)){
                $pdf->SetFont("Arial", "B", $clientfont);
                $pdf->Cell(25, 5, "Importe pagado:", 0, "L", 1);
                $pdf->SetFont("Arial", "", $clientfont);
                $pdf->Cell(20, 5, $signo_peso.$imp_pagado, 0, "L", 1);
            }
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(25, 5, "Saldo Insoluto:", 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(25, 5, $signo_peso.$saldo_insoluto, 0, "L", 1);
            $pdf->Ln(8);
        }

        if($tipo_comprobante == 'T'){
            $pdf->Ln(3);
            $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
            $pdf->SetFillColor($color_r, $color_g, $color_b);
            $pdf->SetDrawColor($color_r, $color_g, $color_b);
            $pdf->SetFont("Arial", "", $ikufont);
            $pdf->Cell(30, 4, "ORIGEN / DESTINO", 0, 0, "L", 1);
            $pdf->Cell(30, 4, "RFC", 0, 0, "L", 1);
            $pdf->Cell(35, 4, "SALIDA / LLEGADA", 0, 0, "L", 1);
            $pdf->Cell(30, 4, "DISTANCIA (KM)", 0, 0, "L", 1);
            $pdf->Cell(70, 4, "DIRECCION", 0, 0, "L", 1);
            $pdf->Ln(4);
        }
        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/cartaporte20:Ubicaciones/cartaporte20:Ubicacion");
        $domicilios = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/cartaporte20:Ubicaciones/cartaporte20:Ubicacion/cartaporte20:Domicilio");
        foreach ($elements as $key => $element) {
            $TipoUbicacion = $element->getAttribute('TipoUbicacion');
            $RFCRemitenteDestinatario = $element->getAttribute('RFCRemitenteDestinatario');
            $FechaHoraSalidaLlegada = $element->getAttribute('FechaHoraSalidaLlegada');
            $DistanciaRecorrida = $element->getAttribute('DistanciaRecorrida');
            
            $Calle = $domicilios[$key]->getAttribute('Calle');
            $NumeroExterior = $domicilios[$key]->getAttribute('NumeroExterior');
            $NumeroInterior = $domicilios[$key]->getAttribute('NumeroInterior');
            $Colonia = $domicilios[$key]->getAttribute('Colonia');
            $CodigoPostal = $domicilios[$key]->getAttribute('CodigoPostal');
            $Estado = $domicilios[$key]->getAttribute('Estado');
            $Municipio = $domicilios[$key]->getAttribute('Municipio');
            $Pais = $domicilios[$key]->getAttribute('Pais');
            $Pais = $Pais == 'MEX' ? 'México' : $Pais;

            $suburb = Suburb::findFirst("suburb_code = '$Colonia' and postal_code = '$CodigoPostal'");
            $suburb = $suburb->suburb;
            $municipality = Municipality::findFirst("municipality_code = '$Municipio' and state_code = '$Estado'");
            $municipality = $municipality->municipality;
            $state = State::findFirst("state_code = '$Estado'");
            $state = $state->state;

            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFont("Arial", "", 8);
            $pdf->Cell(30, 5, $TipoUbicacion, 0, "L", 1);
            $pdf->Cell(30, 5,$RFCRemitenteDestinatario, 0, "L", 1);
            $pdf->Cell(35, 5, $FechaHoraSalidaLlegada, 0, "L", 1);
            $pdf->Cell(30, 5, $DistanciaRecorrida==''?0:$DistanciaRecorrida, 0,0, "R");
            $pdf->MultiCell(70, 5, utf8_decode("$Calle, $NumeroExterior,".($NumeroInterior==''?'':$NumeroInterior.',')." $suburb, $municipality, $state, $Pais, $CodigoPostal"), 0, "L");
            $pdf->Ln(2);
        }

        if($tipo_comprobante == 'T'){
            $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
            $pdf->SetFillColor($color_r, $color_g, $color_b);
            $pdf->SetDrawColor($color_r, $color_g, $color_b);
            $pdf->SetFont("Arial", "", $ikufont);
            $pdf->Cell(30, 4, "TIPO", 0, 0, "L", 1);
            $pdf->Cell(40, 4, "RFC", 0, 0, "L", 1);
            $pdf->Cell(40, 4, "LICENCIA", 0, 0, "L", 1);
            $pdf->Cell(85, 4, "NOMBRE", 0, 0, "L", 1);
            $pdf->Ln(4);
        }
        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/cartaporte20:FiguraTransporte/cartaporte20:TiposFigura");
        foreach ($elements as $key => $element) {
            $TipoFigura = $element->getAttribute('TipoFigura');
            $RFCFigura = $element->getAttribute('RFCFigura');
            $NumLicencia = $element->getAttribute('NumLicencia');
            $NombreFigura = $element->getAttribute('NombreFigura');

            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFont("Arial", "", 8);
            $pdf->Cell(30, 5, $TipoFigura.' - Operador', 0, "L", 1);
            $pdf->Cell(40, 5,$RFCFigura, 0, "L", 1);
            $pdf->Cell(40, 5, $NumLicencia, 0, "L", 1);
            $pdf->Cell(85, 5, $NombreFigura, 0,0, "L");
            $pdf->Ln(5);
        }
        if($tipo_comprobante == 'T'){
            $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
            $pdf->SetFillColor($color_r, $color_g, $color_b);
            $pdf->SetDrawColor($color_r, $color_g, $color_b);
            $pdf->SetFont("Arial", "", $ikufont);
            $pdf->Cell(195, 4, "TRANSPORTE", 0, 0, "C", 1);
            $pdf->Ln(4);
        }
        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/cartaporte20:Mercancias/cartaporte20:Autotransporte");
        foreach ($elements as $key => $element) {
            $PermSCT = $element->getAttribute('PermSCT');
            $NumPermisoSCT = $element->getAttribute('NumPermisoSCT');
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFont("Arial", "", 8);
            $pdf->Cell(80, 5,'Permiso SCT: '.$PermSCT, 0, "L", 1);
            $pdf->Cell(80, 5,'No. Permiso SCT: '.$NumPermisoSCT, 0, "L", 1);
            $pdf->Ln(5);
        }
        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/cartaporte20:Mercancias/cartaporte20:Autotransporte/cartaporte20:IdentificacionVehicular");
        foreach ($elements as $key => $element) {
            $ConfigVehicular = $element->getAttribute('ConfigVehicular');
            $PlacaVM = $element->getAttribute('PlacaVM');
            $AnioModeloVM = $element->getAttribute('AnioModeloVM');
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFont("Arial", "", 8);
            $pdf->Cell(80, 5,utf8_decode('Configuración Vehicular: ').$ConfigVehicular, 0, "L", 1);
            $pdf->Cell(80, 5,'Placa: '.$PlacaVM, 0, "L", 1);
            $pdf->Cell(20, 5,utf8_decode('Año: ').$AnioModeloVM, 0, "L", 1);
            $pdf->Ln(5);
        }
        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/cartaporte20:Mercancias/cartaporte20:Autotransporte/cartaporte20:Seguros");
        foreach ($elements as $key => $element) {
            $AseguraRespCivil = $element->getAttribute('AseguraRespCivil');
            $PolizaRespCivil = $element->getAttribute('PolizaRespCivil');
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFont("Arial", "", 8);
            $pdf->Cell(80, 5,'Aseguradora Resp. Civil: '.$AseguraRespCivil, 0, "L", 1);
            $pdf->Cell(80, 5,utf8_decode('Póliza Resp. Civil: ').$PolizaRespCivil, 0, "L", 1);
            $pdf->Ln(5);
        }
        $elements = $xpath->query("//cfdi:Comprobante/cfdi:Complemento/cartaporte20:CartaPorte/cartaporte20:Mercancias/cartaporte20:Autotransporte/cartaporte20:Remolques/cartaporte20:Remolque");
        foreach ($elements as $key => $element) {
            $SubTipoRem = $element->getAttribute('SubTipoRem');
            $Placa = $element->getAttribute('Placa');
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFont("Arial", "", 8);
            $pdf->Cell(80, 5,'Subtipo de remolque: '.$SubTipoRem, 0, "L", 1);
            $pdf->Cell(80, 5,utf8_decode('Placa remolque: ').$Placa, 0, "L", 1);
            $pdf->Ln(5);
        }

        $datos_bancos = false;
        if($datos_bancos){
            $pdf->Ln(4);
        }
        if($datos_bancos){
            $pdf->Ln(8);
        }
        $pdf->SetFont("Arial", "", 5);

        $pdf->SetDrawColor(0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0);
        $pdf->Cell(0, 3, "CADENA ORIGINAL", 1, 0, "L", 1);
        $pdf->Ln();

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0);
        $pdf->MultiCell(0, 3, $co, 1, "L", 1);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0);
        $pdf->Cell(0, 3, "SELLO DIGITAL DEL EMISOR", 1, 0, "L", 1);
        $pdf->Ln();

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0);
        $pdf->MultiCell(0, 3, $selloEmisor, 1, "L", 1);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0);
        $pdf->Cell(0, 3, "SELLO DIGITAL DEL SAT", 1, 0, "L", 1);
        $pdf->Ln();

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0);
        $pdf->MultiCell(0, 3, $selloSAT, 1, "L", 1);

        $pdf->Cell(40, 5);


        //$pdf->MultiCell(155,6,"   FORMA DE PAGO: $condPago. "      FORMA DE PAGO: ".$tipoPago."      CUENTA: No Identificado ".$cuenta,0,"J");

        if (($folio == 3 || $folio == 4 || $folio == 5 || $folio == 6 || $folio == 7 || $folio == 8) && $cliente == 47) {
            $Efectos_fiscales = "EFECTOS FISCALES AL PAGO";
        }
        if ($folio == 2528 && $cliente == 43) {
            $tipoPago = 'EFECTIVO';
            $condPago = 'EN UNA SOLA EXHIBICION';
        }
        /*if($folio == 3 && $cliente == 47){
            $condPago = 'EN UNA SOLA EXHIBICION';
        }*/
        $forTotal = number_format($total, 6, '.', '');
        $lengTotal = strlen($forTotal);
        $lengTotalT = 17 - $lengTotal;
        $lengTotal = $lengTotalT + $lengTotal;
        $last_sello = substr($selloEmisor, -8);
        $totalLeft = str_pad($forTotal, $lengTotal, '0', STR_PAD_LEFT);
        $fileName = '../jobs/files/qrfactura/' . $cliente . '_' . date("YmdHis") . '.png';
        $urlQr = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?re=' . $EmisorRFC . '&rr=' . $ReceptorRFC . '&tt=' . $totalLeft . '&id=' . $folioFiscal . '&fe=' . $last_sello;


        //QRcode::png($urlQr, $fileName);
        $qrCode = new QrCode($urlQr);
        $qrCode->setWriterByName('png');
        $fileName = $qrCode->writeDataUri();

        $x = $pdf->GetX() / 5;
        $y = $pdf->GetY();
        if($y >= 240){
            $pdf->AddPage();
            $pdf->SetXY($x, 5);
            $y = $pdf->GetY();
        }
        //$pdf->Image($uriData,x,y,2.5,0,'PNG');
        $pdf->Image($fileName, $x, $y, null, 40, 'PNG');

        /*if($cliente == 40 && ($folio == 11 || $folio == 14 || $folio == 15 || $folio == 16)){
            $pdf->MultiCell(155,6,"CONDICIONES DE PAGO: ".$Efectos_fiscales,0,"J");
        }else{*/
        if ($Efectos_fiscales == "" || $Efectos_fiscales == null) {
            $Efectos_fiscales = "EFECTOS FISCALES AL PAGO";
        }
        $pdf->SetFont("Arial", "", 6);
        $pdf->MultiCell(155, 3, "CONDICIONES DE PAGO: " . $Efectos_fiscales, 0, "J");
        //}
        $pdf->Cell(40, 2);
        if ($leyenda != "") {
            $pdf->Ln(1);
            $pdf->Cell(40, 2);
            $pdf->MultiCell(155, 2, $leyenda, 0, "J");
        }
        if ($norma != "") {
            $pdf->Ln(1);
            $pdf->Cell(40, 5);
            $pdf->MultiCell(155, 2, $norma, 0, "J");
        }
        if ($disposicionFiscal != "") {
            $pdf->Ln(1);
            $pdf->Cell(40, 5);
            $pdf->MultiCell(155, 2, $disposicionFiscal, 0, "J");
        }
        if($tipo_comprobante != 'T'){
            $pdf->Ln(1);
            $pdf->Cell(40, 2);
            $pdf->MultiCell(155, 2, "FORMA DE PAGO: " . $tipoPago, 0, "J");
            if ($tipo_comprobante != 'P') {
                $pdf->Ln(1);
                $pdf->Cell(40, 2);
                $pdf->MultiCell(155, 2, "METODO DE PAGO: " . $condPago, 0, "J");
            }else if(!empty($num_operacion)){
                $pdf->Ln(1);
                $pdf->Cell(40, 2);
                $pdf->MultiCell(155, 2, "NUM. OPERACION: " . $num_operacion, 0, "J");
            }
        }
        
        $pdf->Ln(1);
        $pdf->Cell(40, 4);
        $pdf->Cell(90, 4, "REGIMEN EMISOR: " . utf8_decode($regimen), 0, "J");
        $pdf->Ln(4);
        $pdf->Cell(40, 4);
        $pdf->Cell(90, 4, "REGIMEN RECEPTOR: " . utf8_decode($RegimenReceptor), 0, "J");
        if (!empty($uuid_relacionado) && !empty($tipo_relacion)) {
            $pdf->Ln(2);
            $pdf->Cell(40, 4);
            $pdf->Cell(90, 6, 'TIPO DE RELACION: ' . utf8_decode($tipo_relacion), 0, "J");
            $pdf->Ln(3);
            $pdf->Cell(40, 4);
            $pdf->Cell(90, 6, 'UUID RELACIONADO: ' . $uuid_relacionado, 0, "J");
            $pdf->Ln(2);
        }
        if ($tipo_comprobante == 'P') {
            $pdf->Ln(4);
            $pdf->Cell(40, 4);
            $pdf->Cell(90, 4, 'FECHA DEL PAGO: ' . $fecha_pago, 0, "J");
        }
        if (!empty($rfc_cta_ord) || !empty($cta_ord) || !empty($num_operacion)) {
            $pdf->Ln(3);
            $pdf->Cell(40, 5);
            $pdf->Cell(35, 4, utf8_decode('RFC Banco Origen: '.$rfc_cta_ord), 0, 1, "L", 1);
            $pdf->Cell(40, 5);
            $pdf->Cell(35, 4, utf8_decode('Banco Origen: '.$cta_ord), 0, 1, "L", 1);
            $pdf->Cell(40, 5);
            $pdf->Cell(35, 4, utf8_decode('Cuenta Origen: '.$banco_ord), 0, 1, "L", 1);
        }
        if (!empty($rfc_cta_ben) || !empty($cta_ben)) {
            $pdf->Ln(1);
            $pdf->Cell(40, 5);
            $pdf->Cell(35, 4, utf8_decode('RFC Banco Destino: '.$rfc_cta_ben), 0, 1, "L", 1);
            $pdf->Cell(40, 5);
            $pdf->Cell(35, 4, utf8_decode('Cuenta Destino: '.$cta_ben), 0, 1, "L", 1);
        }

        //$pdf->Cell(130, 4);
        if($tipo_comprobante != 'P' && $tipo_comprobante != 'T'){
            $pdf->Ln(3);
            $pdf->Cell(40, 5);
            $pdf->Cell(50, 4, utf8_decode('ORDEN DE COMPRA # '.$clientAddress['orden']), 0, 0, "L", 1);
            $pdf->Cell(35, 4, utf8_decode('AGENTE: '.$clientAddress['agente']), 0, 1, "L", 1);
            $pdf->Cell(40, 5);
            $pdf->Cell(50, 4, utf8_decode('FECHA VENCIMIENTO: '.$clientAddress['expired_date']), 0, 0, "L", 1);
            $pdf->Cell(35, 4, utf8_decode('CONDICIONES: '.$clientAddress['condiciones']), 0, 1, "L", 1);        
            $pdf->Cell(40, 5);
            $pdf->Cell(35, 4, utf8_decode('ventas@empresa.mx'), 0, 0, "L", 1);
            $pdf->Cell(35, 4, utf8_decode('credito@empresa.mx'), 0, 1, "L", 1);
            $pdf->Cell(40, 5);
            $pdf->Cell(35, 4, utf8_decode('credito@empresa.mx'), 0, 0, "L", 1);
            $pdf->Cell(50, 4, utf8_decode('www.empresa.mx'), 0, 0, "L", 1);

            $pdf->Cell(50,4, 'FIRMA CLIENTE CREDITO Y PAGARE','T',1,'C');

            $pdf->Ln(1);
            $pdf->Cell(40, 4);
            $pdf->MultiCell(155, 4,
                "LA REPRODUCCION APOCRIFA DE ESTE COMPROBANTE CONSTITUYE UN DELITO EN LOS TERMINOS DE LAS DISPOSICIONES FISCALES. ESTE DOCUMENTO ES UNA REPRESENTACION IMPRESA DE UN CFDI",
                0, "J");

            if($clientAddress['immex']){
                $pdf->SetFillColor($color_r, $color_g, $color_b);
                $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
                $pdf->SetDrawColor($color_r, $color_g, $color_b);
                $pdf->Cell(195, 5," SOLD TO:  ", "B", 1, "L", 1);

                $pdf->Cell(135, 5," TYCO FIRE & SECURITY GMBH VICTOR VON BRUNS-STRASSE 21, 8212 NEUHAUSEN AM RHEINFALL, SUIZA", "B", 0, "L", 0);
                $pdf->Cell(30, 5," RFC: XAXX010101000", "B", 0, "L", 0);
                $pdf->Cell(30, 5," TAX ID: CHE.469.016.289", "B", 1, "L", 0);

                $pdf->Cell(195, 5," SHIP TO / CONSIGNEE TO:  ", "B", 1, "L", 1);
                $pdf->Cell(75, 5," JOHNSON CONTROLS BE OPERATIONS MEXICO S DE RL DE CV", "B", 0, "L", 0);
                $pdf->Cell(60, 5," CARR MEXICO KM 945, DURANGO, DGO. C.P. 34080", "B", 0, "L", 0);
                $pdf->Cell(30, 5," RFC: JCB100702TQ1", "B", 0, "L", 0);
                $pdf->Cell(30, 5," IMMEX: 397-2015", "B", 1, "L", 0);
                
                $pdf->Cell(95, 5,utf8_decode(" Operación de conformidad con el Art 29 LIVA, RGCE 4.3.21, 5.2.6, 5.2.5. Fracción II"), "B", 0, "L", 0);

                $pdf->Cell(50, 5,utf8_decode(" Pedimento Importación V1: ".$clientAddress['import']), "B", 0, "L", 0);
                $pdf->Cell(50, 5,utf8_decode(" Pedimento Exportación V1: ".$clientAddress['export']), "B", 1, "L", 0);

            }
        }

        if ($cliente == 38 && ($folio == 9 || $folio == 11)) {
            $folio_sust = '';
            if ($folio == 9) {
                $folio_sust = "05";
            }
            if ($folio == 11) {
                $folio_sust = "08";
            }
            $pdf->Ln(1);
            $pdf->Cell(40, 4);
            $pdf->MultiCell(155, 6, "SUSTITUYE AL FOLIO " . $folio_sust, 0, "J");
        }


        $pdf->SetY(65);

        if ($cancelada) {
            $pdf->SetFont('Arial', 'B', 120);
            $pdf->SetTextColor(255, 152, 163);
            $pdf->TextWithRotation(35, 220, ' Cancelada ', 45, -45);
        }
        if ($comercio_exterior) {
            $pdf->AddPage();
            $pdf->SetFillColor($color_r, $color_g, $color_b);
            $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
            $pdf->Cell(195, 4, "COMPLEMENTO COMERCIO EXTERIOR", 0, 1, "C", 1);
            $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
            $pdf->SetFillColor($color_r, $color_g, $color_b);
            $pdf->SetDrawColor($color_r, $color_g, $color_b);
            $pdf->SetFont("Arial", "", 9);
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(28, 5, utf8_decode("Tipo de operación:"), 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(22, 5, $tipo_operacion.utf8_decode(' - Exportación'), 0, "L", 1);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(30, 5, "Clave de Pedimento:", 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(70, 5, $clave_pedimento.' - IMPORTACION O EXPORTACION DEFINITIVA', 0, "L", 1);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(32, 5, "Certificado de Origen:", 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(13, 5, ($certiicado_origen=='0'?'No funge':'Funge'), 0, "L", 1);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Ln(6);
            $pdf->Cell(15, 5, "Incoterm:", 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(38, 5, utf8_decode($incoterm_text), 0, "L", 1);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(20, 5, "Subdivision:", 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(5, 5, $subdivision, 0, "L", 1);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(39, 5, utf8_decode("Tipo de cambio en dólares:"), 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(15, 5, $signo_peso.$tipo_cambioUSD, 0, "L", 1);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(25, 5, utf8_decode("Total en dólares:"), 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(15, 5, $signo_peso.$total_dolares, 0, "L", 1);
            $pdf->Ln(8);
            $pdf->SetFillColor($color_r, $color_g, $color_b);
            $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
            $pdf->Cell(98, 4, "Emisor", 1, 0, "C", 1);
            $pdf->Cell(97, 4, "Receptor", 1, 1, "C", 1);
            $pdf->SetFont("Arial", "", 9);
            $pdf->SetFillColor($color_fr, $color_fg, $color_fb);
            $pdf->SetTextColor($color_tsr, $color_tsg, $color_tsb);
            $pdf->Ln(2);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->SetX(20);
            $pdf->Cell(12, 5, utf8_decode("CURP:"), 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(32, 5, $curp_ext, 0, "L", 1);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->SetX(108);
            $pdf->Cell(45, 5, utf8_decode("Número de identificación fiscal:"), 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(15, 5, $numregidtrib, 0, "L", 1);
            $pdf->Ln(5);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->SetX(20);
            $pdf->Cell(16, 5, utf8_decode("Domicilio:"), 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(65, 5, $calle_emisor_ext.' #'.$num_emisor_ext.' CP. '.$cp_emisor_ext, 0, "L", 1);
            $pdf->SetX(108);
            $pdf->SetFont("Arial", "B", $clientfont);
            $pdf->Cell(16, 5, utf8_decode("Domicilio:"), 0, "L", 1);
            $pdf->SetFont("Arial", "", $clientfont);
            $pdf->Cell(65, 5, $calle_receptor_ext.' CP. '.$cp_receptor_ext.', '.$localidad_receptor_ext.', '.$estado_receptor_ext.', '.$pais_receptor_ext, 0, "L", 1);
            $pdf->Ln(5);
            $pdf->SetX(36);
            $pdf->Cell(65, 5, 'GUADALAJARA '.$estado_emisor_ext.', '.$pais_emisor_ext, 0, "L", 1);
            $pdf->Ln(10);
            $pdf->SetTextColor($color_tmr, $color_tmg, $color_tmb);
            $pdf->SetFillColor($color_r, $color_g, $color_b);
            $pdf->SetDrawColor($color_r, $color_g, $color_b);
            $pdf->SetFont("Arial", "", 7);
            $pdf->SetX(25);
            $pdf->Cell(30, 4, "NO. IDENTIFICACION", 0, 0, "C", 1);
            $pdf->Cell(35, 4, "FRACCION ARANCELARIA", 0, 0, "C", 1);
            $pdf->Cell(15, 4, "CANTIDAD", 0, 0, "C", 1);
            $pdf->Cell(35, 4, "UNIDAD", 0, 0, "C", 1);
            $pdf->Cell(25, 4, "VALOR UNITARIO", 0, 0, "C", 1);
            $pdf->Cell(25, 4, "VALOR DOLARES", 0, 0, "C", 1);

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont("Arial", "", 8);

            $numProductos = count($mercancias_array);
            for ($i = 1; $i <= $numProductos; $i++) {
                $pdf->Ln();
                $pdf->SetX(25);
                $product = array_shift($mercancias_array);
                $yValue = ceil($yValue);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->MultiCell(30, 5, $product['no_identificacion'], 0, "C");
                $pdf->SetXY($x + 32, $y);
                $pdf->MultiCell(35, 5, utf8_decode($product['fraccion_arancelaria'] . ' - Las demás.'), 0, "L");
                $pdf->SetXY($x + 70, $y);
                $pdf->MultiCell(15, 5, $product['cantidad_aduana'], 0, "L");
                $pdf->SetXY($x + 88, $y);
                if ($product['unidad_aduana'] == '02') {
                    $pdf->MultiCell(35, 5, $product['unidad_aduana'] . ' - GRAMO', 0, "L");
                }
                $pdf->SetXY($x + 125, $y);
                $pdf->MultiCell(25, 5, '$'.$product['valor_unitario'], 0, "L");
                $pdf->SetXY($x + 148, $y);
                $pdf->MultiCell(25, 5, '$'.$product['valor_dolares'], 0, "L");
                $pdf->SetXY($x + 175, $y);
                if($y < 240){
                    $pdf->SetXY($x, $y + ($yValue * 4));
                }else{
                    $pdf->AddPage();
                    $pdf->SetXY($x, 5);
                }
            }

        }

        // $pdf->Output('I',$folio."_".$EmisorRFC."_factura.pdf",true);
        return $pdf;
    }

    private function num2letras($num, $moneda, $fem = false, $dec = true)
    {
        $matuni[2] = "dos";
        $matuni[3] = "tres";
        $matuni[4] = "cuatro";
        $matuni[5] = "cinco";
        $matuni[6] = "seis";
        $matuni[7] = "siete";
        $matuni[8] = "ocho";
        $matuni[9] = "nueve";
        $matuni[10] = "diez";
        $matuni[11] = "once";
        $matuni[12] = "doce";
        $matuni[13] = "trece";
        $matuni[14] = "catorce";
        $matuni[15] = "quince";
        $matuni[16] = "dieciseis";
        $matuni[17] = "diecisiete";
        $matuni[18] = "dieciocho";
        $matuni[19] = "diecinueve";
        $matuni[20] = "veinte";
        $matunisub[2] = "dos";
        $matunisub[3] = "tres";
        $matunisub[4] = "cuatro";
        $matunisub[5] = "quin";
        $matunisub[6] = "seis";
        $matunisub[7] = "sete";
        $matunisub[8] = "ocho";
        $matunisub[9] = "nove";

        $matdec[2] = "veint";
        $matdec[3] = "treinta";
        $matdec[4] = "cuarenta";
        $matdec[5] = "cincuenta";
        $matdec[6] = "sesenta";
        $matdec[7] = "setenta";
        $matdec[8] = "ochenta";
        $matdec[9] = "noventa";
        $matsub[3] = 'mill';
        $matsub[5] = 'bill';
        $matsub[7] = 'mill';
        $matsub[9] = 'trill';
        $matsub[11] = 'mill';
        $matsub[13] = 'bill';
        $matsub[15] = 'mill';
        $matmil[4] = 'millones';
        $matmil[6] = 'billones';
        $matmil[7] = 'de billones';
        $matmil[8] = 'millones de billones';
        $matmil[10] = 'trillones';
        $matmil[11] = 'de trillones';
        $matmil[12] = 'millones de trillones';
        $matmil[13] = 'de trillones';
        $matmil[14] = 'billones de trillones';
        $matmil[15] = 'de billones de trillones';
        $matmil[16] = 'millones de billones de trillones';

        //Zi hack
        $float = explode('.', $num);
        $num = $float[0];

        $num = trim((string)@$num);
        if ($num[0] == '-') {
            $neg = 'menos ';
            $num = substr($num, 1);
        } else {
            $neg = '';
        }
        while ($num[0] == '0') {
            $num = substr($num, 1);
        }
        if ($num[0] < '1' or $num[0] > 9) {
            $num = '0' . $num;
        }
        $zeros = true;
        $punt = false;
        $ent = '';
        $fra = '';
        for ($c = 0; $c < strlen($num); $c++) {
            $n = $num[$c];
            if (!(strpos(".,'''", $n) === false)) {
                if ($punt) {
                    break;
                } else {
                    $punt = true;
                    continue;
                }

            } elseif (!(strpos('0123456789', $n) === false)) {
                if ($punt) {
                    if ($n != '0') {
                        $zeros = false;
                    }
                    $fra .= $n;
                } else {
                    $ent .= $n;
                }
            } else {
                break;
            }

        }
        $ent = '     ' . $ent;
        if ($dec and $fra and !$zeros) {
            $fin = ' coma';
            for ($n = 0; $n < strlen($fra); $n++) {
                if (($s = $fra[$n]) == '0') {
                    $fin .= ' cero';
                } elseif ($s == '1') {
                    $fin .= $fem ? ' una' : ' un';
                } else {
                    $fin .= ' ' . $matuni[$s];
                }
            }
        } else {
            $fin = '';
        }
        if ((int)$ent === 0) {
            return 'Cero ' . $fin;
        }
        $tex = '';
        $sub = 0;
        $mils = 0;
        $neutro = false;
        while (($num = substr($ent, -3)) != '   ') {
            $ent = substr($ent, 0, -3);
            if (++$sub < 3 and $fem) {
                $matuni[1] = 'una';
                $subcent = 'as';
            } else {
                $matuni[1] = $neutro ? 'un' : 'uno';
                $subcent = 'os';
            }
            $t = '';
            $n2 = substr($num, 1);
            if ($n2 == '00') {
            } elseif ($n2 < 21) {
                $t = ' ' . $matuni[(int)$n2];
            } elseif ($n2 < 30) {
                $n3 = $num[2];
                if ($n3 != 0) {
                    $t = 'i' . $matuni[$n3];
                }
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            } else {
                $n3 = $num[2];
                if ($n3 != 0) {
                    $t = ' y ' . $matuni[$n3];
                }
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            }
            $n = $num[0];
            if ($n == 1) {
                $t = ' ciento' . $t;
            } elseif ($n == 5) {
                $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
            } elseif ($n != 0) {
                $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
            }
            if ($sub == 1) {
            } elseif (!isset($matsub[$sub])) {
                if ($num == 1) {
                    $t = ' mil';
                } elseif ($num > 1) {
                    $t .= ' mil';
                }
            } elseif ($num == 1) {
                $t .= ' ' . $matsub[$sub] . 'on';
            } elseif ($num > 1) {
                $t .= ' ' . $matsub[$sub] . 'ones';
            }
            if ($num == '000') {
                $mils++;
            } elseif ($mils != 0) {
                if (isset($matmil[$sub])) {
                    $t .= ' ' . $matmil[$sub];
                }
                $mils = 0;
            }
            $neutro = true;
            $tex = $t . $tex;
        }
        $tex = $neg . substr($tex, 1) . $fin;
        //Zi hack --> return ucfirst($tex);

        if ($moneda == 'usd' || $moneda == 'USD') {
            $end_num = ucfirst($tex) . ' dolares ' . $float[1] . '/100 ';
        } else {
            $end_num = ucfirst($tex) . ' pesos ' . $float[1] . '/100 M.N.';
        }

        return $end_num;
    }

    private function convertNumberToWord($num = false)
    {
        $float = explode('.', $num);
        $num = str_replace(array(',', ' '), '' , trim($num));
        if(! $num) {
            return false;
        }
        $num = (int) $num;
        $words = array();
        $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
            'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
        );
        $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
        $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
            'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
            'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
        );
        $num_length = strlen($num);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $num = substr('00' . $num, -$max_length);
        $num_levels = str_split($num, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ( $tens < 20 ) {
                $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
            } else {
                $tens = (int)($tens / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }
            $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
        } //end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        return implode('', $words).'euros ' . $float[1] . '/100 ';
    }
}
