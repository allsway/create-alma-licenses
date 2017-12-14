<?php
   if(isset($_FILES['image'])){
   	  if (isset($_POST['parseparams'])) {
		$include_parse = 1;
	  }
	  else {
	  	$include_parse = 0;
	  }
      $errors= array();
      $file_name = $_FILES['image']['name'];
      $file_size = $_FILES['image']['size'];
      $file_tmp = $_FILES['image']['tmp_name'];
      $file_type = $_FILES['image']['type'];
      $file_ext=strtolower(end(explode('.',$_FILES['image']['name'])));
      
      
      if($file_size > 2097152) {
         $errors[]='File size must be exactely 2 MB';
      }
      
      if(empty($errors)==true) {
      	 $file_name = preg_replace('/[^A-Za-z0-9\-]/', '', $file_name);
         move_uploaded_file($file_tmp,"files/".$file_name);
         $command = escapeshellcmd('python3.5 ./convert_to_alma.py files/"' . $file_name .'" ' . $include_parse );
		 $output = shell_exec($command);
		 if (strlen($output) == 54){
		 	echo $output;
		 }
		 else { 
		 	echo 'Error';
		 }
		 //shell_exec('echo "'. $command .'" >> error_file.log');
         
      }
      else{
         print_r($errors);
      }
   }
?>