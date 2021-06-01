<?php
/**
 * Created by PhpStorm.
 * User: vlyalko
 * Date: 12/12/19
 * Time: 10:34 PM
 */

namespace Drupal\samhsa_pep_pdf_printing;

/**
 * Class PosFPDF.
 *
 * Overrides some empty functions in \FPDF, like Header() and Footer().
 *
 * @package Drupal\samhsa_pep_pdf_printing
 */
class PepFPDF extends \FPDF {

    protected $NewPageGroup = false;   // variable indicating whether a new group was requested
    protected $PageGroups = array();   // variable containing the number of pages of the groups
    protected $CurrPageGroup;          // variable containing the alias of the current page group

    // create a new page group; call this before calling AddPage()
    public function StartPageGroup()
    {
        $this->NewPageGroup = true;
    }

    // current page in the group
    public function GroupPageNo()
    {
        return $this->PageGroups[$this->CurrPageGroup];
    }

    // alias of the current page group -- will be replaced by the total number of pages in this group
    public function PageGroupAlias()
    {
        return $this->CurrPageGroup;
    }


    private $contentSection = NULL;

    private $yTopItems = 0;

    private $headerText = [
        "Substance Abuse and Mental Health Services",
        "c/o STOPSO",
        "45110 Ocean Ct., Suite 110",
        "Sterling, VA 20166",
    ];

    private $returnedAddress = [
        "Substance Abuse and Mental Health Services",
        "c/o STOPSO",
        "45110 Ocean Ct., Suite 110",
        "Sterling, VA 20166",
    ];

    private $footerText = "We hope you are satisfied with your order. To order additional materials, please visit <a href = '>https://store.samhsa.gov' target = '_BLANK'>https://store.samhsa.gov</a>. We look forward serving you again.";

    private $infoText = 'The enclosed materials are provided in response for your request for information.';

    private $addressLineSteps = 7;

    private $order = NULL;

    private $previousOrderId;

    private $isReturnedOrder = FALSE;

    /**
     * Set content section.
     *
     * @param string $value
     *   Value to be set.
     */
    public function setContentSection($value) {
        $this->contentSection = $value;
    }

    /**
     * Set Order values.
     *
     * @param string $value
     *   Value to be set.
     */
    public function setOrder($value) {
        $this->order = $value;
    }

    /**
     * Set the flag indicating whether the Order ia a returned one..
     *
     * @param string $value
     *   Value to be set.
     */
    public function setIsReturnedOrder($value) {
        $this->isReturnedOrder = $value;
    }

    /**
     * Get yTopItems.
     *
     * @return float
     *   yTopItems value.
     */
    public function getYTopItems() {
        return $this->yTopItems;
    }

    /**
     * Get footerText.
     *
     * @return string
     *   footerText value.
     */
    public function getFooterText() {
        return $this->footerText;
    }

    /**
     * Get returnedAddress.
     *
     * @return array
     *   returnedAddress value.
     */
    public function getReturnedAddress() {
        return $this->returnedAddress;
    }

    /**
     * Formats the long text into multiline cell.
     *
     * @param number $c_width
     *   Width of the Cell.
     * @param number $c_height
     *   Height of the Cell
     * @param number X
     *   SetX
     * @param string $text to format
     *   Text to format
     * @param number $border
     *  Indicates if borders must be drawn around the cell. The value can be either a number:
     *      0: no border
     *      1: frame
     *      or a string containing some or all of the following characters (in any order):
     *      L: left
     *      T: top
     *      R: right
     *      B: bottom
     * @param number $ln
     *   Indicates where the current position should go after the call
     * @param Char $align
     *   Allows to center or align the text. Possible values are:
     *      0: to the right
     *      1: to the beginning of the next line
     *      2: below
     * @param $fill
     *  Indicates if the cell background must be painted (true) or transparent (false). Default value: false.
     * @return array
     *   The formatted multiline cell.
     */
    public function vcell($c_width,$c_height,$x_axis,$text,$border,$ln, $align, $fill, $max_char_len = 75){
        $w_w=$c_height/3;
        $w_w_1=$w_w+2;
        $w_w1=$w_w+$w_w+$w_w+3;
        // $w_w2=$w_w+$w_w+$w_w+$w_w+3;// for 3 rows wrap
        $len=strlen($text);// check the length of the cell and splits the text into $max_char_len character each and saves in a array
        if($len > $max_char_len){
            $w_text=str_split($text,$max_char_len);// splits the text into length of $max_char_len and saves in a array since we need wrap cell of two cell we took $w_text[0], $w_text[1] alone.
            // if we need wrap cell of 3 row then we can go for    $w_text[0],$w_text[1],$w_text[2]
            $this->SetX($x_axis);
            $this->Cell($c_width,$w_w_1,$w_text[0],$border,$ln,$align,$fill);
            $this->SetX($x_axis);
            $this->Cell($c_width,$w_w1,$w_text[1],$border,$ln,$align,$fill);
            //$this->SetX($x_axis);
            // $this->Cell($c_width,$w_w2,$w_text[2],'','','');// for 3 rows wrap but increase the $c_height it is very important.
            $this->SetX($x_axis);
            $this->Cell($c_width,$c_height,'',$border,$ln,$align,$fill);
        }
        else{
            $this->SetX($x_axis);
            $this->Cell($c_width,$c_height,$text,$border,$ln,$align,$fill);
        }
    }

    /**
     * {@inheritdoc}
     */
    function Header() {
        $file = drupal_get_path('module', 'samhsa_pep_pdf_printing') . '/images/none.png';
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Image($file, 0, 0, 0);

        if ($this->contentSection == 'summarypage') {
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 6, 'Page '.$this->GroupPageNo().' of '.$this->PageGroupAlias(), 0, 0, 'C');
        }

        if (isset($GLOBALS["order_info"]) && is_array($GLOBALS["order_info"])) {
            $order_info = $GLOBALS["order_info"];
            $this->previousOrderId = $order_info[0];
            // Address.
            $shipping = \Drupal::service('samhsa_pep_pdf_printing.label')->getShipping($order_info[0]);
            if($shipping) {
                $address_value = $shipping->get('address')->getValue();
                $address = array_shift($address_value);
                $address_lines = $this->formatLabelLines($address);
                $address_text = "";
                foreach ($address_lines as $address_line) {
                    $address_text .= wordwrap($address_line, 35, chr(10)) . chr(10);
                }

                // Header starts //
                $this->SetFont('Arial', '', 12);
                $this->SetY($this->GetY() + 15);
                $margin = 25;
                $this->SetLeftMargin($margin - 10);
                $this->SetRightMargin($margin - 10);
                //$this->SetTopMargin(50);
                $x = $this->GetX();
                $y = $this->GetY();
                $this->MultiCell(120, 5, $address_text, 0, 'l', false);
                $this->SetXY($x + 120, $y);
                $this->MultiCell(120, 5, $address_text, 0, 'l', false);

                $this->SetY($this->GetY() + 50);

                $this->SetLeftMargin($margin - 15);
                $this->SetRightMargin($margin - 15);
                $x = $this->GetX();
                $y = $this->GetY();
                $this->Cell(25, 10, 'Order: ' . $order_info[0], 1, 'l', false);
                $this->SetXY($x + 25, $y);
                $this->Cell(50, 10, 'Order Date: ' . $order_info[2], 1, 'l', false);
                $this->SetXY($x + 75, $y);
                $this->Cell(55, 10, 'Source: ' . $order_info[1], 1, 'l', false);
                $this->SetXY($x + 130, $y);
                $this->Cell(30, 10, 'Weight: ' . $order_info[3], 1, 'l', false);
            }
        }
        $this->SetY($this->GetY() + 15);
        if ($this->contentSection == 'cover') {
            /*$y = $this->GetY();
            $page_width = $this->GetPageWidth();
            $this->setX(0);

            $y += 15;
            $this->SetY($y);
            $this->SetFont('Arial', '', 12);
            $margin = ($page_width - 150) / 2;
            $this->setX($margin);
            $this->Cell(7, 10, '', 0, 0, 'C');
            $this->Cell(25, 10, 'Order ID', 0, 0, 'C');
            $this->Cell(57, 10, 'Name', 0, 0, 'L');
            $this->Cell(60, 10, 'Batched', 0, 1, 'C');
            $y += 9;
            $this->Line($margin, $y, $margin + 150, $y);*/
        }
        else {
            $margin = 10;
            $this->SetLeftMargin($margin);
            $this->SetRightMargin($margin);
            $full_page_width = $this->GetPageWidth();
            $page_width = $full_page_width - ($margin * 2);

            if ($this->isReturnedOrder) {
                $this->SetFont('Arial', 'B', 30);
                $this->Cell($page_width, 10, 'Returned Order', 0, 1, 'C');
            }

            // Items.
            /*$this->SetY($this->GetY() + 15);
            $this->setX($margin);
            $this->SetFont('Arial', 'B', 12);
            $title_w = $page_width - 60;
            $this->Cell(40, 10, 'Publication', 0, 0, 'L');
            $this->Cell($title_w, 10, 'Title', 0, 0, 'L');
            $this->Cell(20, 10, 'Quantity', 0, 1, 'R');
            $y = $this->GetY();
            $this->Line($margin, $y, $full_page_width - $margin, $y);*/

        }
    }

    /**
     * Formats the array with the address lines.
     *
     * @param array $shipping
     *   Shipping information.
     *
     * @return array
     *   The lines with the shipping.
     */
    private function formatLabelLines($shipping) {
        $country = \Drupal::service('country_manager')->getList()[$shipping['country_code']]->__toString();
        $result = [
            $shipping['given_name'] . chr(32) . $shipping['family_name'],
            $shipping['organization'],
            $shipping['address_line1'],
            $shipping['address_line2'],
            $shipping['locality'] . ', ' . $shipping['administrative_area'] . chr(32) . $shipping['postal_code'],
            $country,
        ];
        $result = array_values(array_filter($result, [$this, 'filterLines']));
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    function Footer() {
        $y = $this->GetPageHeight() - 16;
        $w = $this->GetPageHeight();
        $this->Line(0, $y, $w, $y);
        $this->SetY(-15);
        $this->SetFont('Arial', '', 12);
        $page_width = $this->GetPageWidth();
        if ($this->contentSection == 'cover') {
            $this->Cell(0, 10, 'For internal use only', 0, 0, 'C');
        } else if ($this->contentSection == 'summaryfooter') {
            $this->Cell(0, 10, 'For internal use only', 0, 0, 'C');
        } else if ($this->contentSection == 'invoice') {
            $this->Cell(0, 10, 'SAMHSA Publications', 0, 0, 'L');
            $this->SetY(-15);

            $this->SetFont('Arial', '', 12);
            //$this->Cell(0, 10, 'Page ' . $this->PageNo() . " of {nb}", 0, 0, 'C');
            //$this->Cell(0,10,'Page '.$this->PageNo().' of {nb}',0,0,'C');
            $this->Cell(0, 6, 'Page '.$this->GroupPageNo().' of '.$this->PageGroupAlias(), 0, 0, 'C');

            $this->SetFont('Arial', '', 8);
            $this->Cell(0, 10, date('m-d-y h:i A') , 0, 0, 'R');
        }
    }


    public function WordWrap(&$text, $maxwidth) {
        $text = trim($text);
        if ($text === '') {
            return 0;
        }
        $space = $this->GetStringWidth(' ');
        $lines = explode("\n", $text);
        $text = '';
        $count = 0;

        foreach ($lines as $line) {
            $words = preg_split('/ +/', $line);
            $width = 0;

            foreach ($words as $word) {
                $wordwidth = $this->GetStringWidth($word);
                if ($wordwidth > $maxwidth) {
                    for ($i = 0; $i < strlen($word); $i++) {
                        $wordwidth = $this->GetStringWidth(substr($word, $i, 1));
                        if ($width + $wordwidth <= $maxwidth) {
                            $width += $wordwidth;
                            $text .= substr($word, $i, 1);
                        }
                        else {
                            $width = $wordwidth;
                            $text = rtrim($text) . "\n" . substr($word, $i, 1);
                            $count++;
                        }
                    }
                }
                elseif ($width + $wordwidth <= $maxwidth) {
                    $width += $wordwidth + $space;
                    $text .= $word . ' ';
                }
                else {
                    $width = $wordwidth + $space;
                    $text = rtrim($text) . "\n" . $word . ' ';
                    $count++;
                }
            }
            $text = rtrim($text) . "\n";
            $count++;
        }
        $text = rtrim($text);
        return $count;
    }

    function WriteHTML($html) {
        //HTML parser
        $html = strip_tags($html, "<b><u><i><a><img><p><br><strong><em><font><tr><blockquote>"); //supprime tous les tags sauf ceux reconnus
        $html = str_replace("\n", ' ', $html); //remplace retour à la ligne par un espace
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE); //éclate la chaîne avec les balises
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                //Text
                $this->Write(5, stripslashes($this->txtentities($e)));
            }
            else {
                //Tag
                if ($e[0] == '/') {
                    $this->CloseTag(strtoupper(substr($e, 1)));
                }
                else {
                    //Extract attributes
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = [];
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3)) {
                            $attr[strtoupper($a3[1])] = $a3[2];
                        }
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    function PutLink($URL, $txt) {
        //Put a hyperlink
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', TRUE);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', FALSE);
        $this->SetTextColor(0);
    }

    function SetStyle($tag, $enable) {
        //Modify style and select corresponding font
        if (!property_exists($this, $tag)) {
            $this->$tag = 0;
        }
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (['B', 'I', 'U'] as $s) {
            if (property_exists($this, $s) && $this->$s > 0) {
                $style .= $s;
            }
        }
        $this->SetFont('', $style);
    }

    function txtentities($html) {
        $trans = get_html_translation_table(HTML_ENTITIES);
        $trans = array_flip($trans);
        return strtr($html, $trans);
    }

    function CloseTag($tag) {
        //Closing tag
        if ($tag == 'STRONG') {
            $tag = 'B';
        }
        if ($tag == 'EM') {
            $tag = 'I';
        }
        if ($tag == 'B' || $tag == 'I' || $tag == 'U') {
            $this->SetStyle($tag, FALSE);
        }
        if ($tag == 'A') {
            $this->HREF = '';
        }
        if ($tag == 'FONT') {
            if ($this->issetcolor == TRUE) {
                $this->SetTextColor(0);
            }
            if ($this->issetfont) {
                $this->SetFont('arial');
                $this->issetfont = FALSE;
            }
        }
    }

    function OpenTag($tag, $attr) {
        //Opening tag
        switch ($tag) {
            case 'STRONG':
                $this->SetStyle('B', TRUE);
                break;
            case 'EM':
                $this->SetStyle('I', TRUE);
                break;
            case 'B':
            case 'I':
            case 'U':
                $this->SetStyle($tag, TRUE);
                break;
            case 'A':
                if(isset($attr['HREF'])) {
                    $this->HREF = $attr['HREF'];
                }
                break;
            case 'IMG':
                if (isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                    if (!isset($attr['WIDTH'])) {
                        $attr['WIDTH'] = 0;
                    }
                    if (!isset($attr['HEIGHT'])) {
                        $attr['HEIGHT'] = 0;
                    }
                    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
                }
                break;
            case 'TR':
            case 'BLOCKQUOTE':
            case 'BR':
                $this->Ln(5);
                break;
            case 'P':
                $this->Ln(10);
                break;
            case 'FONT':
                if (isset($attr['COLOR']) && $attr['COLOR'] != '') {
                    $coul = hex2dec($attr['COLOR']);
                    $this->SetTextColor($coul['R'], $coul['V'], $coul['B']);
                    $this->issetcolor = TRUE;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                    $this->SetFont(strtolower($attr['FACE']));
                    $this->issetfont = TRUE;
                }
                break;
        }
    }

    /**
     * Callback for array_filter().
     *
     * Removes empty elements and other undesired values.
     *
     * @param string $element
     *   Element to be checked.
     *
     * @return bool
     *   Whether or not the string should be kept.
     */
    private function filterLines($element) {
        $element = trim($element);
        if (!$element) {
            return FALSE;
        }
        elseif (empty($element)) {
            return FALSE;
        }
        elseif ($element == '- None -' || $element == '_none') {
            return FALSE;
        }
        else {
            return TRUE;
        }
    }

    function fixtags($text){
        $text = htmlspecialchars($text);
        $text = preg_replace("/=/", "=\"\"", $text);
        $text = preg_replace("/&quot;/", "&quot;\"", $text);
        $tags = "/&lt;(\/|)(\w*)(\ |)(\w*)([\\\=]*)(?|(\")\"&quot;\"|)(?|(.*)?&quot;(\")|)([\ ]?)(\/|)&gt;/i";
        $replacement = "<$1$2$3$4$5$6$7$8$9$10>";
        $text = preg_replace($tags, $replacement, $text);
        $text = preg_replace("/=\"\"/", "=", $text);
        return $text;
    }

    function _beginpage($orientation, $size, $rotation)
    {
        parent::_beginpage($orientation, $size, $rotation);
        if($this->NewPageGroup)
        {
            // start a new group
            $n = sizeof($this->PageGroups)+1;
            $alias = "{nb$n}";
            $this->PageGroups[$alias] = 1;
            $this->CurrPageGroup = $alias;
            $this->NewPageGroup = false;
        }
        elseif($this->CurrPageGroup)
            $this->PageGroups[$this->CurrPageGroup]++;
    }

    function _putpages()
    {
        $nb = $this->page;
        if (!empty($this->PageGroups))
        {
            // do page number replacement
            foreach ($this->PageGroups as $k => $v)
            {
                for ($n = 1; $n <= $nb; $n++)
                {
                    $this->pages[$n] = str_replace($k, $v, $this->pages[$n]);
                }
            }
        }
        parent::_putpages();
    }


}
