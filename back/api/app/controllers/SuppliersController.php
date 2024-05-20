<?php

use Phalcon\Mvc\Controller;

class SuppliersController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getAll ()
    {
        $sql = "SELECT p_s.*, w_bo.name as branch_name
                FROM pur_suppliers AS p_s
                INNER JOIN wms_branch_offices AS w_bo
                ON p_s.branch_id = w_bo.id
                ORDER BY p_s.id DESC";
        $suppliers = $this->db->query($sql)->fetchAll();

        $this->content['suppliers'] = $suppliers;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }
    public function getSuppliersToReportSales () {
        if ($this->userHasPermission()) {
            $sql = "SELECT id as value, name as label FROM pur_suppliers";
            $data = $this->db->query($sql)->fetchAll();

            $options = [];
            foreach ($data as $key => $value) {
                # code...
                $options[] = [
                    'value' => $value['value'],
                    'label' => $value['label']
                ];
            }
            $this->content['suppliers'] = $options;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    

    public function getSuppliers ()
    {
        if ($this->userHasPermission()) {
            $this->content['suppliers'] = Suppliers::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getSuppliersOrders () {
        if ($this->userHasPermission()) {
            $sql = "SELECT id as value, name as label FROM pur_suppliers  WHERE active is true";
            $data = $this->db->query($sql)->fetchAll();
            $this->content['suppliers'] = $data;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getSupplier ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['supplier'] = Suppliers::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getSuppliersByPagination ()
    {
        $request = $this->request->getPost();
        if ($this->userHasPermission()){
            $response = $this->getGridSQL($request['branch'],$request);
            $this->content['suppliers'] = $response['data'];
            $this->content['suppliersCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    public function getGridSQL ($branch,$request) {
        $where = 'WHERE s.id > 0 ';
        if ($branch == 'TODOS') {} else if($branch == ''){}else {$where .= " AND s.branch_id = $branch";}
        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];        
        if (!empty($filter)){
            $where .= " AND ( s.serial::text ILIKE '%".$filter."%' OR s.name ILIKE '%".$filter."%')";
        }
        if (!empty($pagination['sortBy'])) {
            $sortBy .= " ORDER BY s." . trim($pagination['sortBy']);
        } else {
            $sortBy .= " ORDER BY cast(s.serial as int)";
        }
        $desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage'];

        $sql = "SELECT count(s.id) AS count
                FROM pur_suppliers AS s
                INNER JOIN wms_branch_offices AS b
                ON s.branch_id = b.id
            {$where}";
         $suppliersCount = $this->db->query($sql)->fetchAll();
         $sql = "SELECT s.*, b.name as branch_name
         FROM pur_suppliers AS s
         LEFT JOIN wms_branch_offices AS b 
         ON s.branch_id = b.id
          {$where} {$sortBy} {$desc} {$offset} {$limit} ;";
        $data = $this->db->query($sql)->fetchAll();
        $response = array('data' => $data, 'rowCounts' => $suppliersCount[0]['count']);
        return $response;
    }
    public function getOptions () {
        $sql = "SELECT id, name FROM pur_suppliers WHERE active ORDER BY name ASC;";
        $types = $this->db->query($sql)->fetchAll();
        
        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['name']
            ];
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }
    public function getCsvSuppliers ($branchOfficeId) {
        $content = $this->content;
        $where = "WHERE s.active = true";
        if ($branchOfficeId == 'TODOS') {} else if(empty($branchOfficeId)){}else {$where .= " AND s.branch_id = $branchOfficeId";}
        $sql ="SELECT b.name as branch_office_name, s.serial, s.active as status, s.name as supplier_name , s.contact_phone as phone, s.street, s.suburb,s.municipality,s.state,s.zip_code,s.rfc,s.email
                FROM pur_suppliers AS s
                INNER JOIN wms_branch_offices AS b
                ON b.id = s.branch_id
                {$where}
                ORDER BY s.id";
        $suppliers = $this->db->query($sql)->fetchAll();
                
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, ['SERIAL','SUCURSAL','NOMBRE DEL PROVEEDOR','ESTATUS','TELEFONO','CALLE','COLONIA','MUNICIPIO','ESTADO','CP','RFC','CORREO'], ',');
        if (count($suppliers)) {
            foreach ($suppliers as $sp) {
                fputcsv($fp, [
                    $sp['serial'],
                    utf8_decode($sp['branch_office_name']),
                    utf8_decode($sp['supplier_name']),
                    ($sp['status'] == 1 ? 'ACTIVO' : ($sp['status'] == 2 ? 'INACTIVO' : 'OTRO')),
                    $sp['phone'],
                    utf8_decode($sp['street']),
                    utf8_decode($sp['suburb']),
                    utf8_decode($sp['municipality']),
                    utf8_decode($sp['state']),
                    ($sp['zip_code']),
                    ($sp['rfc']),
                    ($sp['email']),
                ], ',');
            }
            $content['result'] = 'success';
        }
        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Proveedores-' . date('Y-m-d') . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }
    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $supplier = Suppliers::findFirst("account_id = $actualAccount AND serial = '".$request['serial']."'");

                if ($supplier) {
                    $this->content['message'] = Message::success('El código ingresado ya se encuentra registrado en otro proveedor.');
                } else {
                    $supplier = new Suppliers();
                    $supplier->setTransaction($tx);
                    $supplier->name = strtoupper($request['name']);
                    $supplier->serial = strtoupper($request['serial']);
                    $supplier->contact_name = strtoupper($request['contact_name']);
                    if (isset($request['contact_phone']) && is_numeric($request['contact_phone'])) {
                        $supplier->contact_phone = intval($request['contact_phone']);
                    }
                    if (isset($request['contact_phone_two']) && is_numeric($request['contact_phone_two'])) {
                        $supplier->contact_phone_two = intval($request['contact_phone_two']);
                    }
                    $supplier->tradename = strtoupper($request['tradename']);
                    $supplier->street = strtoupper($request['street']);
                    $supplier->outdoor_number = strtoupper($request['outdoor_number']);
                    $supplier->indoor_number = strtoupper($request['indoor_number']);
                    $supplier->suburb = strtoupper($request['suburb']);
                    $supplier->municipality = strtoupper($request['municipality']);
                    $supplier->state = strtoupper($request['state']);
                    $supplier->country = strtoupper($request['country']);
                    $supplier->credit_days = intval($request['credit_days']);
                    if (isset($request['zip_code']) && is_numeric($request['zip_code'])) {
                        $supplier->zip_code = intval($request['zip_code']);
                    }
                    $supplier->rfc = strtoupper($request['rfc']);
                    $supplier->term = strtoupper($request['term']);
                    $supplier->payment_method = strtoupper($request['payment_method']);
                    $supplier->currency = strtoupper($request['currency']);
                    $supplier->active = $request['active'];
                    $supplier->account_id = $actualAccount;
                    $supplier->email = strtolower($request['email']);
                    $supplier->email2 = strtolower($request['email2']);
                    $supplier->branch_id = 9;

                    if ($supplier->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El proveedor ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($supplier);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el proveedor.');
                        $tx->rollback();
                    }
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $supplier = Suppliers::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                if ($supplier) {
                    $supplier->setTransaction($tx);
                    $supplier->name = strtoupper($request['name']);
                    $supplier->serial = strtoupper($request['serial']);
                    $supplier->contact_name = strtoupper($request['contact_name']);
                    if (isset($request['contact_phone']) && is_numeric($request['contact_phone'])) {
                        $supplier->contact_phone = intval($request['contact_phone']);
                    }
                    if (isset($request['contact_phone_two']) && is_numeric($request['contact_phone_two'])) {
                        $supplier->contact_phone_two = intval($request['contact_phone_two']);
                    }
                    $supplier->tradename = strtoupper($request['tradename']);
                    $supplier->street = strtoupper($request['street']);
                    $supplier->outdoor_number = strtoupper($request['outdoor_number']);
                    $supplier->indoor_number = strtoupper($request['indoor_number']);
                    $supplier->suburb = strtoupper($request['suburb']);
                    $supplier->municipality = strtoupper($request['municipality']);
                    $supplier->state = strtoupper($request['state']);
                    $supplier->country = strtoupper($request['country']);
                    $supplier->credit_days = intval($request['credit_days']);
                    if (isset($request['zip_code']) && is_numeric($request['zip_code'])) {
                        $supplier->zip_code = intval($request['zip_code']);
                    }
                    $supplier->rfc = strtoupper($request['rfc']);
                    $supplier->term = strtoupper($request['term']);
                    $supplier->payment_method = strtoupper($request['payment_method']);
                    $supplier->currency = strtoupper($request['currency']);
                    $supplier->active = $request['active'];
                    $supplier->account_id = $actualAccount;
                    $supplier->email = strtolower($request['email']);
                    $supplier->email2 = strtolower($request['email2']);
                    $supplier->branch_id = $request['branch_id'];

                    if ($supplier->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El proveedor ha sido modificado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($supplier);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el proveedor.');
                        $tx->rollback();
                    }
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $supplier = Suppliers::findFirst($id);

                if ($supplier) {
                    $supplier->setTransaction($tx);

                    if ($supplier->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El proveedor ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($supplier);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el proveedor.');
                        }
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El proveedor no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 or role_id = 22 or role_id = 20  OR role_id = 17 OR role_id = 22 OR role_id = 28)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
    public function changePhoto ()
    {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $suppliers = Suppliers::findFirst($request['id']);
                if ($suppliers) {
                    $upload_dir = $_SERVER["DOCUMENT_ROOT"] . '/public/assets/images/suppliers/';
                    if (!is_dir($upload_dir))  {
                        mkdir($upload_dir, 0777);
                    }
                    $fullPath = '';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        $this->content['file'] = $file;
                        $this->content['fileExtension'] = $file->getExtension();
                        $fileName = $request['id'] . '.' . $file->getExtension();
                        $fullPath = $upload_dir . $fileName;
                        $this->content['fullPath'] = $fullPath;
                        if ($suppliers->photo != null && file_exists($upload_dir.$suppliers->photo)) {
                            @unlink($upload_dir.$suppliers->photo);
                        }
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $suppliers->setTransaction($tx);
                        $suppliers->photo = $fileName;
                        if ($suppliers->update()) {
                            $file->moveTo($fullPath);
                            $tx->commit();
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La fotografía ha sido registrada exitosamente.');
                        } else {
                            $this->content['result'] = false;
                            $this->content['message'] = Message::error('Error al registrar la fotografía.');
                        }
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No se ha encontrado el cliente.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }

        $this->response->setJsonContent($this->content);
    }
    public function getPdf ()
    {

        $sql ="SELECT b.name as branch_office_name, s.serial, s.active as status, s.name as supplier_name , s.contact_phone as phone, 
                s.contact_phone_two as phone2, s.street, s.suburb,s.municipality,s.state,s.zip_code,s.rfc,s.email,s.photo
                FROM pur_suppliers AS s
                INNER JOIN wms_branch_offices AS b
                ON b.id = s.branch_id
                ORDER BY s.id";
        $suppliers = $this->db->query($sql)->fetchAll();
        $pdf = new PDFS('L','mm','Letter');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial','',8);
        $pdf->SetDrawColor(4, 26, 131);
        $pdf->Ln();
        if (count($suppliers) > 0) {
            $pdf->SetAligns(array('C', 'L', 'L', 'R', 'L', 'L','L','L','C','L','L'));
            $fill = false;
            $fillG = 1;
            $totalQty = 0;
            foreach ($suppliers as $sp) {

                $pdf->Row(array(
                $sp['serial'],
                utf8_decode($sp['supplier_name']),
                ($sp['status'] == 1 ? 'ACTIVO' : ($sp['status'] == 2 ? 'INACTIVO' : 'OTRO')),
                $sp['phone'],
                utf8_decode($sp['street']),
                utf8_decode($sp['suburb']),
                utf8_decode($sp['municipality']),
                utf8_decode($sp['state']),
                ($sp['zip_code']),
                ($sp['rfc']),
                ($sp['email'])),$fillG);
                if($fillG === 1){
                    $fillG = 2;
                }else{
                    $fillG = 1;
                }
            }
        }

        $pdf->SetTitle("Reporte de Proveedor",true);
        $pdf->Output('I', "Reporte de Proveedor.pdf", true);
        $response = new Phalcon\Http\Response();
        $response->setHeader('Content-Type", "application/pdf');
        $response->setHeader('Content-Disposition', 'inline; filename="Reporte de Proveedor.pdf"');
        return $response;
    }
}
    class PDFS extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $operator;
    var $status;
    var $startDate;
    var $endingDate;
    var $drawEdge = true;
    var $fillCell = false;

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo.png';
        $this->Image($img,10,10,50,0,'PNG');
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial','',18);
        $this->SetY($this->GetY()+20);
        $this->SetX(($this->GetPageWidth() / 2)-30);
        $this->MultiCell(195, 6, utf8_decode("Lista de proveedores"), 0, 'J', false);
        $this->SetY($this->GetY());
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial','',8);
        $this->SetDrawColor(4, 26, 131);
        $this->SetFont('Arial','',8);
        $this->SetWidths(array(15,55, 16, 25, 25,20, 25, 15, 15, 25, 25));
        $this->SetAligns(array('C',  'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
        $this->SetHeight(6);
        $this->SetDrawEdge(false);
        $one=array(128,179,240);
        $coords=array(0,0,1,0);
        $this->LinearGradient(10,$this->GetY() + 1,262,5,array($one,$one),$coords);
        $this->Row(array('SERIAL','PROVEEDOR','ESTATUS','TEL. OFICINA','CALLE','COLONIA','MUNICIPIO','ESTADO','CP','RFC','CORREO'),3);
    }

    function Footer()
    {
        $this->SetY(200);
        $this->SetX(($this->GetPageWidth() / 2));
        $this->Cell(6, 6, "www.empresa.mx", 0, 0, 'C', false);
        $this->SetFont('Arial', '', 10);
        $this->SetY(274);
        $this->SetFillColor(4, 26, 131);
        $this->SetTextColor(255);
        $this->Rect(0,268,216,190,'DF');
        $this->Cell(0,0,utf8_decode('Página '.$this->PageNo().' de {nb}'),0,0,'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths=$w;
    }

    function SetAligns($a)
    {
        $this->aligns=$a;
    }

    function SetHeight($h)
    {
        $this->height=$h;
    }

    function SetOperator($o)
    {
        $this->operator = $o;
    }

    function SetStatus($s)
    {
        $this->status = (!is_null($s) && strcasecmp($s, 'NULL') != 0) ? $s : 'PRODUCIENDO Y TERMINADO';
    }

    function SetStartDate($sd)
    {
        $this->startDate = (!is_null($sd) && strcasecmp($sd, 'NULL') != 0) ? $sd : 'EL PRIMER LAMINADO';
    }

    function SetEndingDate($ed)
    {
        $this->endingDate = (!is_null($ed) && strcasecmp($ed, 'NULL') != 0) ? $ed : 'EL ÚLTIMO LAMINADO';
    }

    function SetDrawEdge($de)
    {
        $this->drawEdge=$de;
    }

    function SetFill($f)
    {
        $this->fill=$f;
    }

    function Row($data,$fill)
    {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=$this->height*$nb;
        $this->CheckPageBreak($h);
        $pink=array(255,255,255);
        $one=array(200,220,240);
        $coords=array(0,0,1,0);
        
        if($fill ===1){
            $this->LinearGradient(10,$this->GetY() + 1,262,$h,array($one,$one),$coords);
        }else if($fill ===2){
            $this->LinearGradient(10,$this->GetY() + 1,262,$h,array($pink,$pink),$coords);
        }
        
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $f=false;
            $x=$this->GetX();
            $y=$this->GetY();
            if ($this->drawEdge) {
                $this->Rect($x,$y,$w,$h);
            }
            $this->MultiCell($w,$this->height,$data[$i],0,$a,$f);
            $this->SetXY($x+$w,$y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt)
    {
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
    protected $gradients = array();

    function LinearGradient($x, $y, $w, $h, $colors, $coords=array(0,0,1,0)){
        $this->Clip($x,$y,$w,$h);
        $this->Gradient(2,$colors,$coords);
    }

    function RadialGradient($x, $y, $w, $h, $col1=array(), $col2=array(), $coords=array(0.5,0.5,0.5,0.5,1)){
        $this->Clip($x,$y,$w,$h);
        $this->Gradient(3,$col1,$col2,$coords);
    }

    function CoonsPatchMesh($x, $y, $w, $h, $col1=array(), $col2=array(), $col3=array(), $col4=array(), $coords=array(0.00,0.0,0.33,0.00,0.67,0.00,1.00,0.00,1.00,0.33,1.00,0.67,1.00,1.00,0.67,1.00,0.33,1.00,0.00,1.00,0.00,0.67,0.00,0.33), $coords_min=0, $coords_max=1){
        $this->Clip($x,$y,$w,$h);        
        $n = count($this->gradients)+1;
        $this->gradients[$n]['type']=6; //coons patch mesh
        //check the coords array if it is the simple array or the multi patch array
        if(!isset($coords[0]['f'])){
            //simple array -> convert to multi patch array
            if(!isset($col1[1]))
                $col1[1]=$col1[2]=$col1[0];
            if(!isset($col2[1]))
                $col2[1]=$col2[2]=$col2[0];
            if(!isset($col3[1]))
                $col3[1]=$col3[2]=$col3[0];
            if(!isset($col4[1]))
                $col4[1]=$col4[2]=$col4[0];
            $patch_array[0]['f']=0;
            $patch_array[0]['points']=$coords;
            $patch_array[0]['colors'][0]['r']=$col1[0];
            $patch_array[0]['colors'][0]['g']=$col1[1];
            $patch_array[0]['colors'][0]['b']=$col1[2];
            $patch_array[0]['colors'][1]['r']=$col2[0];
            $patch_array[0]['colors'][1]['g']=$col2[1];
            $patch_array[0]['colors'][1]['b']=$col2[2];
            $patch_array[0]['colors'][2]['r']=$col3[0];
            $patch_array[0]['colors'][2]['g']=$col3[1];
            $patch_array[0]['colors'][2]['b']=$col3[2];
            $patch_array[0]['colors'][3]['r']=$col4[0];
            $patch_array[0]['colors'][3]['g']=$col4[1];
            $patch_array[0]['colors'][3]['b']=$col4[2];
        }
        else{
            //multi patch array
            $patch_array=$coords;
        }
        $bpcd=65535; //16 BitsPerCoordinate
        //build the data stream
        $this->gradients[$n]['stream']='';
        for($i=0;$i<count($patch_array);$i++){
            $this->gradients[$n]['stream'].=chr($patch_array[$i]['f']); //start with the edge flag as 8 bit
            for($j=0;$j<count($patch_array[$i]['points']);$j++){
                //each point as 16 bit
                $patch_array[$i]['points'][$j]=(($patch_array[$i]['points'][$j]-$coords_min)/($coords_max-$coords_min))*$bpcd;
                if($patch_array[$i]['points'][$j]<0) $patch_array[$i]['points'][$j]=0;
                if($patch_array[$i]['points'][$j]>$bpcd) $patch_array[$i]['points'][$j]=$bpcd;
                $this->gradients[$n]['stream'].=chr(floor($patch_array[$i]['points'][$j]/256));
                $this->gradients[$n]['stream'].=chr(floor($patch_array[$i]['points'][$j]%256));
            }
            for($j=0;$j<count($patch_array[$i]['colors']);$j++){
                //each color component as 8 bit
                $this->gradients[$n]['stream'].=chr($patch_array[$i]['colors'][$j]['r']);
                $this->gradients[$n]['stream'].=chr($patch_array[$i]['colors'][$j]['g']);
                $this->gradients[$n]['stream'].=chr($patch_array[$i]['colors'][$j]['b']);
            }
        }
        //paint the gradient
        $this->_out('/Sh'.$n.' sh');
        //restore previous Graphic State
        $this->_out('Q');
    }

    function Clip($x,$y,$w,$h){
        //save current Graphic State
        $s='q';
        //set clipping area
        $s.=sprintf(' %.2F %.2F %.2F %.2F re W n', $x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, -$h*$this->k);
        //set up transformation matrix for gradient
        $s.=sprintf(' %.3F 0 0 %.3F %.3F %.3F cm', $w*$this->k, $h*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k);
        $this->_out($s);
    }

    function Gradient($type, $colors, $coords){
        $n = count($this->gradients)+1;
        $this->gradients[$n]['type']=$type;
        $i = 1;
        foreach ($colors as $color ) {
            if(!isset($color[1]))
            $color[1]=$color[2]=$color[0];
            $this->gradients[$n]['col'.$i]=sprintf('%.3F %.3F %.3F',($color[0]/255),($color[1]/255),($color[2]/255));
            $i++;
        }
        /*if(!isset($col1[1]))
            $col1[1]=$col1[2]=$col1[0];
        $this->gradients[$n]['col1']=sprintf('%.3F %.3F %.3F',($col1[0]/255),($col1[1]/255),($col1[2]/255));
        if(!isset($col2[1]))
            $col2[1]=$col2[2]=$col2[0];
        $this->gradients[$n]['col2']=sprintf('%.3F %.3F %.3F',($col2[0]/255),($col2[1]/255),($col2[2]/255));*/
        $this->gradients[$n]['coords']=$coords;
        //paint the gradient
        $this->_out('/Sh'.$n.' sh');
        //restore previous Graphic State
        $this->_out('Q');
    }

    function _putshaders(){
        foreach($this->gradients as $id=>$grad){  
            if($grad['type']==2 || $grad['type']==3){
                $this->_newobj();
                $this->_put('<<');
                $this->_put('/FunctionType 2');
                $this->_put('/Domain [0.0 1.0]');
                $this->_put('/C0 ['.$grad['col1'].']');
                $this->_put('/C1 ['.$grad['col2'].']');
                $this->_put('/N 1');
                $this->_put('>>');
                $this->_put('endobj');
                $f1=$this->n;
            }
            
            $this->_newobj();
            $this->_put('<<');
            $this->_put('/ShadingType '.$grad['type']);
            $this->_put('/ColorSpace /DeviceRGB');
            if($grad['type']=='2'){
                $this->_put(sprintf('/Coords [%.3F %.3F %.3F %.3F]',$grad['coords'][0],$grad['coords'][1],$grad['coords'][2],$grad['coords'][3]));
                $this->_put('/Function '.$f1.' 0 R');
                $this->_put('/Extend [true true] ');
                $this->_put('>>');
            }
            elseif($grad['type']==3){
                //x0, y0, r0, x1, y1, r1
                //at this time radius of inner circle is 0
                $this->_put(sprintf('/Coords [%.3F %.3F 0 %.3F %.3F %.3F]',$grad['coords'][0],$grad['coords'][1],$grad['coords'][2],$grad['coords'][3],$grad['coords'][4]));
                $this->_put('/Function '.$f1.' 0 R');
                $this->_put('/Extend [true true] ');
                $this->_put('>>');
            }
            elseif($grad['type']==6){
                $this->_put('/BitsPerCoordinate 16');
                $this->_put('/BitsPerComponent 8');
                $this->_put('/Decode[0 1 0 1 0 1 0 1 0 1]');
                $this->_put('/BitsPerFlag 8');
                $this->_put('/Length '.strlen($grad['stream']));
                $this->_put('>>');
                $this->_putstream($grad['stream']);
            }
            $this->_put('endobj');
            $this->gradients[$id]['id']=$this->n;
        }
    }

    function _putresourcedict(){
        parent::_putresourcedict();
        $this->_put('/Shading <<');
        foreach($this->gradients as $id=>$grad)
             $this->_put('/Sh'.$id.' '.$grad['id'].' 0 R');
        $this->_put('>>');
    }

    function _putresources(){
        $this->_putshaders();
        parent::_putresources();
    }
}
