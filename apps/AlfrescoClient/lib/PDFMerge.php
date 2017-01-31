<?php
define('FPDF_FONTPATH',sfConfig::get('sf_lib_dir').'/FPDF/font/');

class PDFMerge extends fpdi {

    private $files = array();

    public function __construct($orientation='P',$unit='mm',$format='A4') {
        parent::fpdi($orientation,$unit,$format);
    }

    public function setFiles($files) {
        $this->files = $files;
    }
    
    public function addFile($file) {
        $this->files[] = $file;
    }

    public function merge() {
        $this->SetDisplayMode("default","single");

        foreach($this->files AS $file) {
            $pagecount = $this->setSourceFile($file);
            $pageFormats = array();
            for ($i = 1; $i <= $pagecount; $i++) {
                 $tplidx = $this->ImportPage($i);
                 
                 $format = $this->getTemplateSize($tplidx);
                 $pageFormats[$i] = $format;
            }

            for ($i = 1; $i <= $pagecount; $i++) {
                $this->AddPage("P",array($pageFormats[$i]["w"],$pageFormats[$i]["h"]));
                $tplidx = $this->ImportPage($i);
                
                $this->useTemplate($tplidx,0,0,$pageFormats[$i]["w"],$pageFormats[$i]["h"],true);
            }
        }
    }

}