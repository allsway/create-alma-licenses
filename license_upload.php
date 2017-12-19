<?php


  # Allows us to read and write Excel
    include 'home/ssinick/PHPExcel/Classes/PHPExcel/IOFactory.php';

   if(isset($_FILES['image'])){

      $errors= array();
      $file_name = $_FILES['image']['name'];
      $file_size = $_FILES['image']['size'];
      $file_tmp = $_FILES['image']['tmp_name'];
      $file_type = $_FILES['image']['type'];
      $file_ext=strtolower(end(explode('.',$_FILES['image']['name'])));


      if($file_size > 2097152) {
         $errors[]='File size is too large';
      }

      # Figure out how to choose API key
      if(empty($errors)==true) {
         $file_name = preg_replace('/[^A-Za-z0-9\-\.]/', '', $file_name);
         move_uploaded_file($file_tmp,"files/".$file_name);
     $file_path =  getcwd() . '/files/' . $file_name .'';

      # Makes a PUT request for our updated license
      function put_license($baseurl,$key,$code,$data){
        $data = makexml($data);
        $url = $baseurl . '/acq/licenses/' . $code . '?apikey=' . $key;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/xml"));
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        try
        {
          $xml = new SimpleXMLElement($response);
          check_errors($xml);
          #return $xml;
        }
        catch(Exception $exception)
        {
          echo $exception;
          exit;
        }
      }

      # Returns the license XML from the Alma API
      function get_license($code,$baseurl,$key){
        $url = $baseurl . '/acq/licenses/' . $code . '?apikey=' . $key;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/xml"));
        $response = curl_exec($curl);
        curl_close($curl);
        try
        {
          $xml = new SimpleXMLElement($response);
          return $xml;
        }
        catch(Exception $exception)
        {
          echo $exception;
          exit;
        }
      }


      function check_errors($xml){
        if($xml->errorsExist == "true")
        {
          echo "Error";
        }
        else {
            echo "Success";
        }
      }

      # Converts our simpleXML back to XML
      function makexml($xml)
      {
        $doc = new DOMDocument();
        $doc->formatOutput = TRUE;
        $doc->loadXML($xml->asXML());
        $return_xml = $doc->saveXML();
        return $return_xml;
      }

      # Adds the XML term node with the updated term info
      function add_term($license,$code,$value)
      {
          $terms = $license->terms;
        $new_term = $terms->addChild("term");
        $new_code = $new_term->addChild("code",strtoupper($code));
        $new_code->addAttribute("desc",$code);
        $new_value = $new_term->addChild("value",$value);
        $new_value->addAttribute("desc",$value);
        return $terms;
      }

      # Updates the license XML data with the updated/new terms
      function update_terms($license,$new_terms){
        $terms = $license->xpath('//license/terms/term');
        $caps =array('No', 'Yes', 'Not Applicable', 'Uninterpreted', 'Permitted' , 'Prohibited', 'Silent', 'Calendar day', 'Month', 'Business day', 'Week', 'Automatic','Explicit');

        foreach ( $new_terms as $key=>$value){
          if (is_string($value) && in_array($value,$caps))
          {
            $value = strtoupper($value);
          }
          $match_found = false;
          if (isset($value) && $value != 'Please choose a value'){
            # Check and see if the value already exists in the XML
            for($i = 0; $i<count($terms); $i++)
            {
              if ($terms[$i]->code == $key)
              {
                $match_found = true;
                $terms[$i]->value = $value;
              }
            }
            if (!$match_found)
            {
               add_term($license,$key,$value);
            }
          }
        }
        return $license;
      }

      #Parses the license terms Excel spreadsheet
      function read_csv($licenses,$key,$baseurl){

        //  Read your Excel workbook
        try {
          $excel_data = array();
          $objPHPExcel = PHPExcel_IOFactory::load($licenses);
          $worksheet = $objPHPExcel->getSheet(0);
          $worksheetTitle     = $worksheet->getTitle();
          $highestRow         = $worksheet->getHighestRow(); // e.g. 10
          $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
          $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
          $nrColumns = ord($highestColumn) - 64;
          for ($row = 1; $row <= $highestRow; ++ $row) {
            $cell_1 = $worksheet->getCellByColumnAndRow(1, $row);
            $response = $cell_1->getValue();
            $cell_2 = $worksheet->getCellByColumnAndRow(2, $row);
            $code = $cell_2->getValue();
            if ($response != 'Enter License term values here:' && $code != 'LicenseCode'){
              $excel_data[$code] = $response;
            }
            if ($code == 'LicenseCode'){
              $license_code = $response;
            }
          }
            $license_xml = get_license($license_code,$baseurl,$key);
            if (isset($license_xml)){
              $updated_license = update_terms($license_xml,$excel_data);
              put_license( $baseurl,$key,$license_code,$updated_license);
            }
            else{
              echo 'Error';
            }
        }
        catch(Exception $e) {
          die('Error loading file "'.pathinfo($licenses,PATHINFO_BASENAME).'": '.$e->getMessage());
        }
      }

      $ini_array = parse_ini_file("create-alma-licenses/config.ini");
      $key = $ini_array['apikey'];
      $baseurl = $ini_array['baseurl'];
      $licenses = $file_path;

      read_csv($licenses,$key,$baseurl);



      }
      else{
         echo 'Error opening file';
      }
   }
?>
