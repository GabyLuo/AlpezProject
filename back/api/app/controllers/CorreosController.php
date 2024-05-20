<?php

use Phalcon\Mvc\Controller;

require_once __DIR__ . './../../app/routes.php';
if (!defined('EMAILTEMPLATES')) {
    define('EMAILTEMPLATES', __DIR__ . './../../public/templateMail/');
}

class CorreosController extends Controller
{
    //Prueba Pablo entrada beta
    public $content = ['result' => 'error', 'message' => ''];

    public function deleteReport()
    {
        $phat = $_SERVER["DOCUMENT_ROOT"] . '/public/station/';
        $filename = 'Captura de información.pdf';
        if (file_exists($phat . $filename)) {
            @unlink($phat . $filename);
        }

        $phat = $_SERVER["DOCUMENT_ROOT"] . '/public/station/';
        $filename = 'Reporte de Existencias.pdf';
        if (file_exists($phat . $filename)) {
            @unlink($phat . $filename);
        }
    }

    public function getData()
    {
        $data2 = "SELECT p.id, bo.name as station, bo.cluster_id, ss.name as cluster_name, l.name AS product_name,TRUNC((sum(ps.stock))::numeric,2) as stock,
        ms.stock as pstock, ms.min_operation, ms.capacity as max_operation,  ps.product_id, p.name as product_call 
        from v_product_stock_price ps
             JOIN wms_storages AS s ON s.id = ps.storage_id 
             JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
             LEFT JOIN sys_supercluster AS ss ON ss.id = bo.cluster_id
             JOIN wms_products AS p ON p.id = ps.product_id
             JOIN wms_lines AS l ON l.id = p.line_id
             JOIN wms_categories AS c ON c.id = l.category_id
             JOIN wms_marks AS ma ON p.mark_id = ma.id
             join wms_products_minimum_stock as ms on p.id = ms.product_id and ms.branch_offices_id = bo.id
          group by p.id, bo.name, bo.cluster_id, ss.name, l.category_id, c.code, c.name,ps.product_id, p.code, p.name, p.line_id, 
          l.code, l.name,p.active,p.old_code,ma.name,ps.price,s.name,ms.stock,ms.min_operation, ms.capacity
          order by cluster_name, station ASC";
        $data_products = $this->db->query($data2)->fetchAll();
        return $data_products;
    }

    public function getData2()
    {
        $data = "WITH cc AS (
            SELECT    distinct b.id, MAX (m.folio) as movement, MAX (m.status) AS status, MAX(m.date) as date,
                   current_date::date - MAX(m.date::date) AS days, ss.name as cluster_name
             FROM wms_movements AS m
                 INNER JOIN wms_storages AS s ON m.storage_id = s.id
                 INNER JOIN wms_branch_offices as b ON b.id = s.branch_office_id
				 LEFT JOIN sys_supercluster AS ss ON ss.id = b.cluster_id
				WHERE m.status = 'EJECUTADO'
            GROUP BY b.id, ss.name)
            SELECT b.id AS branch_office,
                b.name as branch_office_name,
                cc.movement, cc.status,
               TO_CHAR(cc.date, 'dd/mm/yyyy') as date,
                    cc.days -1 as days, cc.cluster_name as cluster_name
            FROM wms_branch_offices AS b
                INNER JOIN cc ON cc.id = b.id
				where cc.days > 0 AND cc.status = 'EJECUTADO'
           GROUP BY b.id, cc.days, cc.movement, cc.status, cc.date, cc.cluster_name
           order by cluster_name, branch_office_name  asc";
        $dataES = $this->db->query($data)->fetchAll();
        return $dataES;
    }


    public function savePdf()
    {
        $this->deleteReport();

        $pdf = $this->generatePdf();
        $fileName = __DIR__ . './../../public/station/';
        $fileName .= "Reporte de Existencias.pdf";
        $pdf->Output('F', $fileName, true);

        $pdf = $this->generatePdf2();
        $fileName = __DIR__ . './../../public/station/';
        $fileName .= "Captura de información.pdf";
        $pdf->Output('F', $fileName, true);
    }

    public function generatePDF2()
    {

        $widths = array(45, 45, 45, 30, 35);
        $aligns = array('C', 'C', 'C', 'C', 'C');

        $pdf = new mailPDF2();
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';

        $pdf->encabezadoDriver();
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetWidths($widths);
        $pdf->SetAligns($aligns);
        $pdf->SetTextColor(0);
        $pdf->SetXY(8, 42);

        $fill = false;
        $i = 1;

        $dataStock = $this->getData2();
        foreach ($dataStock as $d) {
            if ($pdf->getY() >= $pdf->GetPageHeight() - 28) {
                $pdf->encabezadoDriver();
                $pdf->SetFont('Arial', '', 7);
                $pdf->SetWidths($widths);
                $pdf->SetAligns($aligns);
                $pdf->SetTextColor(0);
                $pdf->SetXY(8, 42);
            }
            if ($d['days'] > 0) {
                $pdf->SetX(8);
                $pdf->SetFillColor(200, 220, 240);
                $pdf->SetFont('Arial', '', 10);
                $pdf->Row(array(utf8_decode($d['cluster_name']), utf8_decode(utf8_encode($d['branch_office_name'])), utf8_decode($d['date']), utf8_decode($d['movement']), utf8_decode($d['days'])), $fill);
            }
        }
        $pdf->SetTitle("Sin datos actualizados", true);
        return $pdf;
    }

    public function generatePDF()
    {
        $sql = "SELECT id, name FROM wms_branch_offices where id > 0 order by name";
        $dataStation = $this->db->query($sql)->fetchAll();
        $widths = array(45, 40, 60, 40, 40, 40);
        $aligns = array('C', 'C', 'C', 'R', 'R', 'R');

        $pdf = new mailPDF();
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';

        $pdf->encabezadoDriver();
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetWidths($widths);
        $pdf->SetAligns($aligns);
        $pdf->SetTextColor(0);
        $pdf->SetXY(8, 42);

        $fill = true;
        $i = 1;
        $pdf->SetHeight(5);
        //foreach ($dataStation as $key => $dat) {
            //$station_id = $dat['id'];
            $dataStock = $this->getData();
            foreach ($dataStock as $d) {
                // var_dump($row);
                //135, 180, 223 
                if ($pdf->getY() >= $pdf->GetPageHeight() - 28) {
                    $pdf->encabezadoDriver();
                    $pdf->SetFont('Arial', '', 7);
                    $pdf->SetWidths($widths);
                    $pdf->SetAligns($aligns);
                    $pdf->SetTextColor(0);
                    $pdf->SetXY(8, 42);
                }
                $multi_value = $d['stock'] * 100;
                $percent = 0;
                if ($multi_value != 0 && $d['max_operation'] != 0) {
                    $percent = $multi_value / $d['max_operation'];
                    $percent = number_format(floatval($percent), 2, '.', '');
                }
                if ($percent <= 70) {
                    $pdf->SetX(8);
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->Row(array(utf8_decode($d['cluster_name']), utf8_decode(utf8_encode($d['station'])), utf8_decode($d['product_name']), utf8_decode($d['max_operation']), $d['stock'], $percent . ' %'), true);
                }
            }
        //}
        $pdf->SetTitle("Alerta de existencias", true);
        return $pdf;
    }

    public function sendPdfToProvider()
    {

        $user = array_values(json_decode('[
            {"email":"maria@wasp.mx", "language":"en"}
        ]', true));
        /* $user = array_values(json_decode('[
            {"email":"maria@wasp.mx", "language":"en"},
            {"email":"enrique@wasp.mx", "language":"en"},
            {"email":"gabriela@wasp.mx", "language":"en"},
            {"email":"lucio@wasp.mx", "language":"en"}
        ]', true)); */
        $fileName = __DIR__ . './../../public/station/';
        $mailsEs = [];

        $this->savePdf();

        foreach ($user as $u) {
            array_push($mailsEs, $u['email']);
        }

        // crea primero la carpeta y despues crea el PDF
        $date = new DateTime();
        $xml = file_get_contents(EMAILTEMPLATES . 'stations.xml');
        $xml = str_replace("{##DATE##}", $date->format('d/m/Y'), $xml);
        $transport = (new Swift_SmtpTransport('smtp.gmail.com', '587', 'tls'))
            ->setUsername('noreply@wasp.mx')
            ->setPassword('noreply2022!');
        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($transport);
        // Create a message
        $message = (new Swift_Message('Alerta Estaciones ALPEZ'))
            ->setFrom(['noreply@wasp.mx' => 'ERP Alpez'])
            ->setTo($mailsEs)
            ->setBody($xml, 'text/html')
            ->attach(Swift_Attachment::fromPath($fileName . 'Reporte de Existencias.pdf')->setFilename('Reporte de Existencias.pdf'))
            ->attach(Swift_Attachment::fromPath($fileName . 'Captura de información.pdf')->setFilename('Captura de información.pdf'));
        // Send the message
        $mailer->send($message);
    }
}

class mailPDF extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $drawEdge = true;

    function encabezadoDriver()
    {
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo_alpez_bn.png';

        $nowDate = new DateTime();

        $dateShow = $nowDate->format('d/m/Y');


        $this->AliasNbPages();
        $this->AddPage('L', 'Letter');
        $this->SetLineWidth(0.2);
        $this->SetFont('Arial', '', 20);
        $this->SetTextColor(0);
        $this->SetX($this->GetX() + 70);
        $this->Cell(65, 15, utf8_decode("ALERTA EXISTENCIAS INFERIORES A 70%"), 0, 'C', false);
        if (file_exists($logo)) {
            $this->Image($logo, 5, 8, 65, 0, 'PNG');
        }
        $this->SetFont('Arial', 'B', 10);

        $this->SetXY(241, 20);
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, $dateShow);


        $this->SetXY(8, 35);
        //30,136,229
        $this->SetFillColor(76, 175, 80);
        $this->SetTextColor(255);
        $this->SetDrawColor(76, 175, 80);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B', 10);
        // Header
        $x = 168;
        $i = 0;
        $header = array(utf8_decode('CLUSTER'), utf8_decode('ESTACIÓN'), utf8_decode('PRODUCTO'), utf8_decode('CAPACIDAD'), utf8_decode('EXISTENCIA'), utf8_decode('PORCENTAJE'));
        $w = array(45, 40, 60, 40, 40, 40);

        foreach ($header as $col) {
            $this->Cell($w[$i], 7, $col, 1, 0, 'C', true);
            $i++;
        }
    }

    function Footer()
    {

        $this->SetFont('Arial', '', 10);
        $this->SetY(207);
        $this->SetFillColor(76, 175, 80);
        $this->SetTextColor(255);
        $this->Rect(0, 208, 279.4, 190, 'DF');
        $this->Cell(0, 10, utf8_decode('PÁGINA ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function SetFill($f)
    {
        $this->fill = $f;
    }

    function SetHeight($h)
    {
        $this->height = $h;
    }



    function Row($data, $fill)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 6 * $nb;
        $this->CheckPageBreak($h);
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = $this->aligns[$i];
            $x = $this->GetX();
            $y = $this->GetY();
            if ($fill)
                if ($i == 5) {
                    if ($data[$i] < 10) {
                        $this->SetTextColor(255);
                        $this->SetFillColor(239, 83, 80);
                        $this->Rect($x, $y, $w, $h, 'DF');
                    } else {
                        $this->SetTextColor(0);
                        $this->Rect($x, $y, $w, $h);
                    }
                } else {
                    $this->SetTextColor(0);
                    $this->Rect($x, $y, $w, $h);
                }
            else
                $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 6, utf8_decode($data[$i]), 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }


    function CheckPageBreak($h)
    {
        if (($this->GetY() + $h) > $this->PageBreakTrigger) {
            $this->AddPage('L', 'Letter');
            $this->SetXY(8, 25);
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}
class mailPDF2 extends FPDF
{
    var $widths;
    var $aligns;

    function encabezadoDriver()
    {
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo_alpez_bn.png';

        $nowDate = new DateTime();

        $dateShow = $nowDate->format('d/m/Y');


        $this->AliasNbPages();
        $this->AddPage('P', 'Letter');
        $this->SetLineWidth(0.2);
        $this->SetFont('Arial', '', 15);
        $this->SetTextColor(0);
        $this->SetX($this->GetX() + 65);
        $this->Cell(65, 18, utf8_decode("CAPTURA DE INFORMACIÓN"), 0, 'C', false);
        /*  $this->SetXY(75, 10);
        $this->SetFont('Arial', '', 15);
        $this->Cell(65, 30, utf8_decode("Estaciones sin datos actualizados"), 0, 'C', false); */
        if (file_exists($logo)) {
            $this->Image($logo, 5, 8, 60, 0, 'PNG');
        }
        $this->SetFont('Arial', 'B', 10);

        $this->SetXY(185, 20);
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, $dateShow);


        $this->SetXY(8, 35);
        //30,136,229
        $this->SetFillColor(76, 175, 80);
        $this->SetTextColor(255);
        $this->SetDrawColor(76, 175, 80);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B', 10);
        // Header
        $x = 168;
        $i = 0;
        $header = array(utf8_decode('CLUSTER'),utf8_decode('ESTACIÓN'), utf8_decode('ULTIMO MOVIMIENTO'), utf8_decode('FOLIO'), utf8_decode('DIAS ATRASADOS'));
        $w = array(45, 45, 45, 30, 35);

        foreach ($header as $col) {
            $this->Cell($w[$i], 7, $col, 1, 0, 'C', true);
            $i++;
        }
    }

    function Footer()
    {

        $this->SetFont('Arial', '', 10);
        $this->SetY(270);
        $this->SetFillColor(76, 175, 80);
        $this->SetTextColor(255);
        $this->Rect(0, 270, 279.4, 190, 'DF');
        $this->Cell(0, 10, utf8_decode('PÁGINA ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data, $fill)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 6 * $nb;
        $this->CheckPageBreak($h);
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = $this->aligns[$i];
            $x = $this->GetX();
            $y = $this->GetY();
            if ($fill)
                $this->Rect($x, $y, $w, $h, 'DF');
            else
                $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 6, utf8_decode($data[$i]), 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if (($this->GetY() + $h) > $this->PageBreakTrigger) {
            $this->AddPage('L', 'Letter');
            $this->SetXY(8, 25);
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}
