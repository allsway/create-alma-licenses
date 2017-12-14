
<html>
<!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>

 
  <meta charset="utf-8">
  <title>Create and upload licenses</title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile Specific Metas
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">

  <!-- CSS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="stylesheet" href="../css/normalize.css">
  <link rel="stylesheet" href="../css/skeleton.css">

  <!-- Favicon
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <!--link rel="icon" type="image/png" href="images/favicon.png"-->

   <body>
        <div class="container">

    		<div class="row">
    		
      			<div class="one-half column" style="margin-top: 20%">
      			  <p> Update and create Alma licenses </p>

				  <form action ="" method = "POST" enctype = "multipart/form-data" id="createsubmission">
					  <div class="row" id="createsubmissionarea">
						 <label for="kbartfile">Create new licenses from a CSV file:</label>
							 <input class="button-primary"  type = "file" name = "image" id="create" /><br />
							 <input name="submitbutton" class="button-primary"  type = "submit"/>
						 	
						<div id="createloading" style="display:none"> 
							<img src="../css/ajax-loader.gif"/>
						</div>

					</div>	
					 <div id="createdownload"></div>
			
				  </form>
				    <form action = "" method = "POST" enctype = "multipart/form-data" id="updatesubmission">
					  <div class="row" id="updatesubmissionarea">
						 <label for="kbartfile">Update license terms for one license:</label>
							 <input class="button-primary"  type = "file" name = "image" id="update" /><br />
							 <input name="submitbutton" class="button-primary"  type = "submit"/>
						 	
						<div id="updateloading" style="display:none"> 
							<img src="../css/ajax-loader.gif"/>
						</div>

					</div>	
					 <div id="updatedownload"></div>
			
				  </form>
      			</div>
      		</div>
      	</div>
   </body>
   
   <script type="text/javascript">

	 $(document).ready(function()
	{
			
		$("form#createsubmission").submit(function(e) {
			if ($("#create").val() === '')
			{
				alert('Upload an Excel file');
			}
			else
			{
				$("#createloading").show();
				console.log(formData);
				var url = "license_create.php"; // the script where you handle the form input.

				$.ajax({
					url: url,
					type: 'POST',
					data: new FormData(this),
					processData: false,
					contentType: false,
					success: function(data) {
						console.log(data);
						if (data === 'Error') {
							console.log(data);
							var str = '<label for="downloadfile">Sorry, there was something invalid in new licenses file. Please try again. </label><a  href="' + data + '" target="_blank" name="download"><a href="handle_licenses.php"><input value="Upload another file" type="button"></a>'
						}
						else {
							var str = '<label for="downloadfile">Your licenses have successfully been created</label><a  href="' + data + '" target="_blank" name="download"><input class="button-primary" value="Download File" type="button"></a> <a href="handle_licenses.php"><input value="Upload another file" type="button"></a>'

						}
						console.log(data);
						$("#createsubmissionarea").hide();
						if ($("#createdownload").html() === ""){
							$("#createloading").hide();
							$("#createdownload").append(str);
						}
					}
	
					});
	
			}
				e.preventDefault();
				});
			
			
			$("form#updatesubmission").submit(function(e) {
			if ($("#update").val() === '')
			{
				alert('Upload an Excel file');
			}
			else
			{
				$("#loading").show();
				var url = "license_upload.php"; // the script where you handle the form input.

				$.ajax({
					url: url,
					type: 'POST',
					data: new FormData(this),
					processData: false,
					contentType: false,
					success: function(data) {
						console.log(data);
						data = data.trim();
						if (data === 'FormatError') {
							var str = '<label for="downloadfile">The uploaded file was not in the right format, please check the file</label><a href="handle_licenses.php"><input value="Try again" type="button"></a>'
							
						}
						else if (data === 'CodeError'){
							var str = '<label for="downloadfile">The uploaded file does not have an accurate license code</label><a href="handle_licenses.php"><input value="Try again" type="button"></a>'
							
						}
						else if (data === 'PostError'){
							var str = '<label for="downloadfile">There was a problem placing an update request to Alma with this file.</label><a href="handle_licenses.php"><input value="Try again" type="button"></a>'
							
						}
						else {
							var str = '<label for="downloadfile">Your license terms have successfully been updated:</label><a href="handle_licenses.php"><input value="Upload another file" type="button"></a>'

						}
						$("#updatesubmissionarea").hide();
						if ($("#updatedownload").html() === ""){
							$("#updateloading").hide();
							$("#updatedownload").append(str);
						}
					}
	
					});
	
			}
				e.preventDefault();
				});
			
	});
</script>
</html>
