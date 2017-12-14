#!/usr/bin/env python3.5

import sys
import csv
import logging
import configparser
import pandas as pd
import numpy
import requests
import xml.etree.ElementTree as ET
import datetime
import html
import urllib.parse

# Returns the API key
def get_key():
    return config.get('Params', 'apikey')

# Returns the Alma API base URL
def get_base_url():
    return config.get('Params', 'baseurl')

# Places PUT request for license to Alma
def put_license(license,code):
    url = get_base_url() + '/acq/licenses/' + code + '?apikey=' + get_key()
    headers = {"Content-Type": "application/xml"}
    r = requests.put(url,data=ET.tostring(license),headers=headers)
    if r.status_code == 200:
        logging.info('Success update for: ' + url)
        print ('Success')
    else:
        logging.info('Failed to post: ' + code)
        print ('PostError')

# Returns the XML formatted license
def get_license(code):
    url = get_base_url() + '/acq/licenses/' + code + '?apikey=' + get_key()
    r = requests.get(url)
    if r.status_code != 200:
        logging.info('Failed to get: ' + url)
    else:
        return ET.fromstring(r.content)

# Create error log file for downlaod
def check_for_errors(license,code):
    with open("error_log.csv", "a") as myfile:
        myfile.write(datetime.datetime.now().strftime("%Y-%m-%d %H:%M") + ' ' +  code + ', ' + license.find('errorList/error/errorMessage').text + '\n')

# Updates the term field from Excel file
def update_terms(license, terms):
    xml_terms = license.find("terms")
    for row in terms[1:].itertuples():
        match_found = False
        if str(row[2]) != 'nan' and str(row[2]) != 'NaN' and str(row[2]) != '' and str(row[2]) != 'Please choose a value':
            if type(row[2]) is str and str(row[2]) in ('No', 'Yes', 'Not Applicable', 'Uninterpreted', 'Permitted' , 'Prohibited', 'Silent', 'Calendar day', 'Month', 'Business day', 'Week', 'Automatic','Explicit'):
                val = row[2].upper()
            else:
                val = str(row[2])
            for t in xml_terms.findall('term'):
                if t.find('code').text == row[3].upper():
                    t.find('value').text = val
                    match_found = True
            if not match_found:
                term = ET.SubElement(xml_terms, 'term')
                term_code = ET.SubElement(term,'code')
                term_code.text = row[3].upper()
                term_code.set('desc',row[3])
                term_value = ET.SubElement(term,'value')
                term_value.text = val
    return license

# Read new licenses csv file
def read_csv(terms):
    df = pd.read_excel(terms)
    if df.iloc[0,0] != 'License code:':
    	print ('FormatError')
    else:
    	code = df.iloc[0,1]
    	license = get_license(code)
    	if license is not None:
    		xml = update_terms(license, df)
    		put_license(xml,code)
    	else:
    		print ('CodeError')
			
# Get logging and configuration, config file now in a hardcoded file.
logging.basicConfig(filename='error_report.log',level=logging.DEBUG)
config = configparser.ConfigParser()
#config.read(sys.argv[1])
config.read('config.txt')
licenses = sys.argv[1]
read_csv(licenses)
