<?php

use Phalcon\Mvc\Controller;

class ProductionIncentivesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getIncentives ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT CONCAT(e.name,' ',e.paternal,' ',e.mathers) AS employee, p.name AS position, pr.name AS product, TO_CHAR(l.scheduled_start_date, 'dd/mm/yyyy') AS date,
            (lw.time_job/60) AS parts_time, (SELECT hp.factor FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS factor, lw.qty AS qty,
            (SELECT hp.minimal FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS min_job, (SELECT hp.minimal*lw.qty FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS efficiency
            FROM prd_lots AS l
            INNER JOIN prd_lot_works AS lw ON lw.lot_id = l.id
            INNER JOIN  hrs_employees AS e  ON lw.employee_id = e.id
            INNER JOIN hrs_positions AS p ON e.position_id = p.id
            INNER JOIN wms_products AS pr ON l.product_id = pr.id
            ORDER BY e.name ASC";

            $data = $this->db->query($sql)->fetchAll();
            $this->content['employees'] = $data;
            $this->content['result'] = true;
            $this->response->setJsonContent($this->content);   
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getIncentivesFilter ($employee, $start, $end)
    {
        if (!empty($employee) || !empty($start) || !empty($end)) {
            $whereEmployee = "";
            $whereAnd = "";
            $whereDate = "";
            if ($employee != 'null') {
                $whereEmployee = " e.id = ".intval($employee);
            }
            if ($employee != 'null' && $start != 'null') {
                $whereAnd = " AND ";
            }
            if ($start != 'null') {
                $whereDate = "DATE(l.scheduled_start_date) BETWEEN '".$start."' and now() ";
            }
            if ($start != 'null' && $end != 'null') {
                $whereDate = "DATE(l.scheduled_start_date) BETWEEN '".$start."' and '".$end."'";
            }
            
        }
        if ($this->userHasPermission()) {
            $sql = "SELECT CONCAT(e.name,' ',e.paternal,' ',e.mathers) AS employee, p.name AS position, pr.name AS product, TO_CHAR(l.scheduled_start_date, 'dd/mm/yyyy') AS date,
            (lw.time_job/60) AS parts_time, (SELECT hp.factor FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS factor, lw.qty AS qty,
            (SELECT hp.minimal FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS min_job, (SELECT hp.minimal*lw.qty FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS efficiency
            FROM prd_lots AS l
            INNER JOIN prd_lot_works AS lw ON lw.lot_id = l.id
            INNER JOIN  hrs_employees AS e  ON lw.employee_id = e.id
            INNER JOIN hrs_positions AS p ON e.position_id = p.id
            INNER JOIN wms_products AS pr ON l.product_id = pr.id
            WHERE $whereEmployee $whereAnd $whereDate
            ORDER BY e.name ASC";

            $data = $this->db->query($sql)->fetchAll();
            $this->content['employees'] = $data;
            $this->content['result'] = true;
            $this->response->setJsonContent($this->content);   
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getIncentivesCsv ($employee, $start, $end) {
        $data = [];
        if ($employee == 'null' && $start == 'null' && $end == 'null') {
            $sql = "SELECT CONCAT(e.name,' ',e.paternal,' ',e.mathers) AS employee, p.name AS position, pr.name AS product, TO_CHAR(l.scheduled_start_date, 'dd/mm/yyyy') AS date,
            (lw.time_job/60) AS parts_time, (SELECT hp.factor FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS factor, lw.qty AS qty,
            (SELECT hp.minimal FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS min_job, (SELECT hp.minimal*lw.qty FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS efficiency
            FROM prd_lots AS l
            INNER JOIN prd_lot_works AS lw ON lw.lot_id = l.id
            INNER JOIN  hrs_employees AS e  ON lw.employee_id = e.id
            INNER JOIN hrs_positions AS p ON e.position_id = p.id
            INNER JOIN wms_products AS pr ON l.product_id = pr.id
            ORDER BY e.name ASC";
            $data = $this->db->query($sql)->fetchAll();
        } else {
            $whereEmployee = "";
            $whereAnd = "";
            $whereDate = "";
            if ($employee != 'null') {
                $whereEmployee = " e.id = ".intval($employee);
            }
            if ($employee != 'null' && $start != 'null') {
                $whereAnd = " AND ";
            }
            if ($start != 'null') {
                $whereDate = "DATE(l.scheduled_start_date) BETWEEN '".$start."' and now() ";
            }
            if ($start != 'null' && $end != 'null') {
                $whereDate = "DATE(l.scheduled_start_date) BETWEEN '".$start."' and '".$end."'";
            }
            $sql = "SELECT CONCAT(e.name,' ',e.paternal,' ',e.mathers) AS employee, p.name AS position, pr.name AS product, TO_CHAR(l.scheduled_start_date, 'dd/mm/yyyy') AS date,
            (lw.time_job/60) AS parts_time, (SELECT hp.factor FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS factor, lw.qty AS qty,
            (SELECT hp.minimal FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS min_job, (SELECT hp.minimal*lw.qty FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS efficiency
            FROM prd_lots AS l
            INNER JOIN prd_lot_works AS lw ON lw.lot_id = l.id
            INNER JOIN  hrs_employees AS e  ON lw.employee_id = e.id
            INNER JOIN hrs_positions AS p ON e.position_id = p.id
            INNER JOIN wms_products AS pr ON l.product_id = pr.id
            WHERE $whereEmployee $whereAnd $whereDate
            ORDER BY e.name ASC";
            $data = $this->db->query($sql)->fetchAll();
        }

        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, ['PUESTO','EMPLEADO','PRODUCTO','EQUIPO','FECHA','PEIZAS','PZS X MIN','MIN.TRAB','FACTOR','INCENTIVO','EFICIENCIA'], ',');
        if (count($data)) {
            foreach ($data as $d) {
                fputcsv($fp, [
                    utf8_decode($d['position']),
                    utf8_decode($d['employee']),
                    utf8_decode($d['product']),
                    "",
                    utf8_decode($d['date']),
                    $d['qty'],
                    $d['parts_time'],
                    $d['min_job'],
                    $d['factor'],
                    $d['factor']*$d['qty'],
                    $d['efficiency']."%",
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
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Incentivos-Producción.'.'.csv');
        $this->response->setContent($output);
        $this->response->send();

    }

    public function getIncentivesPdf ($employee, $start, $end) {
        if ($employee == 'null' && $start == 'null' && $end == 'null') {
        
        } else {
            $whereEmployee = "";
            $whereAnd = "";
            $whereDate = "";
            $showStartDate = "";
            $showEndDate = "";
            $nowDate = new DateTime();
            if ($employee != 'null') {
                $whereEmployee = " e.id = ".intval($employee);
                $showEndDate = $nowDate->format('d/m/Y');
                $showStartDate = "01/".$nowDate->format('m/Y');
                $queryStartDate = $nowDate->format('Y-m-d');
                $queryEndDate = $nowDate->format('Y-m')."-01";
                $whereAnd = " AND ";
                $whereDate = "DATE(l.scheduled_start_date) BETWEEN '".$queryEndDate."' and '".$queryStartDate."'";
            }
            if ($employee != 'null' && $start != 'null') {
                $whereAnd = " AND ";
            }
            if ($start != 'null') {
                $whereDate = "DATE(l.scheduled_start_date) BETWEEN '".$start."' and now() ";
                $dateVal =new DateTime($start);
                $showStartDate = $dateVal->format('d/m/Y');
                $showEndDate = $nowDate->format('d/m/Y');
            }
            if ($start != 'null' && $end != 'null') {
                $whereDate = "DATE(l.scheduled_start_date) BETWEEN '".$start."' and '".$end."'";
                $dateVal =new DateTime($start);
                $showStartDate = $dateVal->format('d/m/Y');
                $endate = new DateTime($end);
                $showEndDate = $endate->format('d/m/Y');
            }
            $sql = "SELECT CONCAT(e.name,' ',e.paternal,' ',e.mathers) AS employee, p.name AS position, pr.name AS product, TO_CHAR(l.scheduled_start_date, 'dd/mm/yyyy') AS date,
            (lw.time_job/60) AS parts_time, (SELECT hp.factor FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS factor, lw.qty AS qty,
            (SELECT hp.minimal FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS min_job, (SELECT hp.minimal*lw.qty FROM wms_handiwork_products AS hp WHERE hp.product_id = l.product_id LIMIT 1) AS efficiency
            FROM prd_lots AS l
            INNER JOIN prd_lot_works AS lw ON lw.lot_id = l.id
            INNER JOIN  hrs_employees AS e  ON lw.employee_id = e.id
            INNER JOIN hrs_positions AS p ON e.position_id = p.id
            INNER JOIN wms_products AS pr ON l.product_id = pr.id
            WHERE $whereEmployee $whereAnd $whereDate
            ORDER BY e.name ASC";
            $data = $this->db->query($sql)->fetchAll();
            $employee = Employees::findFirst(intval($employee));

            $pdf = new PDFIncentive('L','mm','Letter');
            $pdf->AliasNbPages();
            $pdf->SetAutoPageBreak(true, 20);
            $pdf->SetFont('Arial','',10);
            $pdf->AddPage();
            $pdf->Ln();
            $pdf->SetFont('Arial','B',16);
            $pdf->Cell(80,10,utf8_decode($employee->name.' '.$employee->paternal.' '.$employee->mathers),0,1,'L');
            $pdf->SetFont('Arial','',10);
            $pdf->SetXY($pdf->GetX()+192, $pdf->GetY()-35);
            $pdf->Cell(40,10,"INICIO: ".$showStartDate,0,0,'L');
            $pdf->Cell(80,10,"FIN: ".$showEndDate,0,0,'L');

            $pdf->SetY($pdf->GetY()+30);
            $pdf->Ln();
            $pdf->SetFont('Arial','',11);
            $pdf->SetWidths(array(50, 35, 20, 20, 21, 22, 22, 22, 24, 24));
            $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
            $pdf->SetHeight(6);
            $pdf->SetDrawEdge(false);
            $pdf->SetFill(array(true, true, true, true, true, true, true, true, true, true));
            $pdf->SetFillColor(249, 203, 16);
            $pdf->Row(array(utf8_decode('PUESTO'), utf8_decode('PRODUCTO'), utf8_decode('EQUIPO'), utf8_decode('FECHA'),utf8_decode('PIEZAS'),utf8_decode('PZS X MIN'),utf8_decode('MIN.TRAB'),utf8_decode('FACTOR'),utf8_decode('INCENTIVO'),utf8_decode('EFICIENCIA')));
            $pdf->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
            $fill = false;
            $totalWeight = 0;

            foreach ($data as $d) {
                $pdf->SetFillColor(245, 235, 35);
                $pdf->SetFont('Arial','',8);
                $pdf->SetFill(array($fill, $fill, $fill, $fill, $fill, $fill, $fill, $fill, $fill, $fill));
                $pdf->Row(array(utf8_decode($d['position']),utf8_decode($d['product']),utf8_decode(""),utf8_decode($d['date']),number_format($d['qty'],4),number_format($d['parts_time'],4),number_format($d['min_job'],4),number_format($d['factor'],4),number_format($d['factor']*$d['qty'],4),number_format($d['efficiency'],2)."%"));
                $fill = !$fill;
            }
            /*$fill = false;
            $pdf->SetFill(array($fill, $fill, $fill, $fill, $fill, $fill, $fill, $fill, $fill, $fill));
            $pdf->Row(array(utf8_decode(""),utf8_decode(""),utf8_decode(""),utf8_decode(""),number_format($d['qty'],4),number_format($d['parts_time'],4),number_format($d['min_job'],4),number_format($d['factor'],4),number_format($d['factor']*$d['qty'],4),number_format($d['efficiency'],2)."%"));
            */
            $pdf->SetTitle("Packing List Lote #",true);
            $pdf->Output('I', "Packing List Lote #", true);

            $response = new Phalcon\Http\Response();
            $response->setHeader("Content-Type", "application/pdf");
            $response->setHeader("Content-Disposition", 'inline; filename="Packing List Lote #"');
            return $response;
        
        }
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 4 OR role_id = 7) 
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
}

class PDFIncentive extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $orderNumber;
    var $drawEdge = true;
    var $fillCell = false;

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo.png';
        $this->Image($img,10,5,45,0,'PNG');
        $this->SetFont('Arial','B',17);
        $this->SetX($this->GetX()+60);
        $this->MultiCell(130, 12, utf8_decode("Incentivos de producción"), 0, 'C', false);
        /*$this->SetFont('Arial','B',14);
        $this->SetX($this->GetX()+153);
        $this->Cell(130, 2, utf8_decode("MANO DE OBRA"), 0, 'C', false);
        $this->Cell(130, 10, 0, 'C', false);*/
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(260);
        $this->Cell(195, 6, "www.panelw.com", 0, 0, 'C', false);
        $this->SetFont('Arial', '', 10);
        $this->SetY(274);
        $this->SetFillColor(249, 203, 16);
        $this->SetTextColor(0);
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

    function SetOrderNumber($o)
    {
        $this->orderNumber=$o;
    }

    function SetDrawEdge($de)
    {
        $this->drawEdge=$de;
    }

    function SetFill($f)
    {
        $this->fill=$f;
    }

    function Row($data)
    {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=$this->height*$nb;
        $this->CheckPageBreak($h);
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $f=isset($this->fill[$i]) ? $this->fill[$i] : false;
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
}