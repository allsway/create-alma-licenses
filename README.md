# create-alma-licenses
Creates/updates licenses in Alma from an excel file 

#### license_terms.php
Front end for uploading an Excel file of license terms

#### license_upload.php
Handles the uploaded excel file, calls the Alma API and retrieves the license XML, updates the XML based on the uploaded terms and places PUT request to Alma NZ.  

#### config.ini
Required file including the Alma base URL and the relevant API Key
```
apikey='keyhere'
baseurl='urlhere'
```

