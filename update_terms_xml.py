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
#    code = urllib.parse.quote_plus(code)
    url = get_base_url() + '/acq/licenses/' + code + '?apikey=' + get_key()
    headers = {"Content-Type": "application/xml"}
    r = requests.put(url,data=ET.tostring(license),headers={"Content-Type" : "application/xml"})
    print (r.content)
    if r.status_code == 200:
        logging.info('Success update for: ' + url)
    else:
    #    print(ET.tostring(r.content))
        logging.info('Failed to post: ' + code)

# Returns the JSON formatted license
def get_license(code):
    url = get_base_url() + '/acq/licenses/' + code + '?apikey=' + get_key()
    print (url)
    r = requests.get(url)
    if r.status_code != 200:
        logging.info('Failed to get: ' + url)
    else:
        return ET.fromstring(r.content)

# Create error log file for downlaod
def check_for_errors(license,code):
#    print (license['web_service_result']['errorList']['error']['errorCode'])
    with open("error_log.csv", "a") as myfile:
        myfile.write(datetime.datetime.now().strftime("%Y-%m-%d %H:%M") + ' ' +  code + ', ' + license.find('errorList/error/errorMessage').text + '\n')

# Updates the term field from Excel file
def update_terms(license, terms):
    print (ET.tostring(license))
    xml_terms = license.find("terms")
    for row in terms[1:].itertuples():
        match_found = False
        if str(row[2]) != 'nan' and str(row[2]) != 'NaN' and str(row[2]) != '' and str(row[2]) != 'Please choose a value':
            if type(row[2]) is str:
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
                #term_value.set('desc', row[2])
    print (ET.tostring(license))
    return license

# Read new licenses csv file
def read_csv(terms):
    df = pd.read_excel(terms)
    #print (df['LicenseCode'])
    code = df.iloc[0,1]
    license = get_license(code)
    if license is not None:
        xml = update_terms(license, df)
        put_license(xml,code)

# Get logging and configuration
logging.basicConfig(filename='error_report.log',level=logging.DEBUG)
config = configparser.ConfigParser()
config.read(sys.argv[1])

licenses = sys.argv[2]
read_csv(licenses)
