

<html>
<!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>

 
  <meta charset="utf-8">
  <title>Alma+</title>
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
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">

  <!-- Favicon
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <!--link rel="icon" type="image/png" href="images/favicon.png"-->

   <body>
        <div class="container">

    		<div class="row">
    		
      			<div class="one-half column" style="margin-top: 20%">
				    <form action = "" method = "POST" enctype = "multipart/form-data" id="updatesubmission">
					  <div class="row" id="updatesubmissionarea">
						 <label for="kbartfile">Update license terms for one license:</label>
							 <input class="button-primary"  type = "file" name = "image" id="update" /><br />
							 <input name="submitbutton" class="button-primary"  type = "submit"/>
						 	
						<div id="updateloading" style="display:none"> 
							<img src="css/ajax-loader.gif"/>
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
			
			$("form#updatesubmission").submit(function(e) {
			if ($("#update").val() === '')
			{
				alert('Upload an Excel file');
			}
			else
			{
				$("#updateloading").show();
				var url = "license_upload.php"; // the script where you handle the form input.
				console.log(url);
				$.ajax({
					url: url,
					type: 'POST',
					data: new FormData(this),
					processData: false,
					contentType: false,
					success: function(data) {
						console.log(data);
						data = data.trim();
						if (data === 'Success') {
							var str = '<label for="downloadfile">Your license terms have successfully been updated!</label><a href="license_terms.php"><input value="Upload another file" type="button"></a>'
							
						}
						else {
							var str = '<label for="downloadfile">There was a problem with your license file.  Please try again. </label><a href="license_terms.php"><input value="Upload another file" type="button"></a>'

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
