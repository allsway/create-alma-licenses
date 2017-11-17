#!/usr/bin/env python3.5

import sys
import csv
import json
import logging
import configparser
import pandas as pd
import numpy
import requests
import xml.etree.ElementTree as ET
import datetime

# Returns the API key
def get_key():
    return config.get('Params', 'apikey')

# Returns the Alma API base URL
def get_base_url():
    return config.get('Params', 'baseurl')

# Places PUT request for license to Alma
def put_license(license,code):
    url = get_base_url() + '/acq/licenses/' + code + '?apikey=' + get_key()
    headers = {"Content-Type": "application/json"}
    print (json.dumps(license))
    r = requests.put(url,data=json.dumps(license),headers=headers)
    print (r.content)
    if r.status_code == 200:
        logging.info('Success update for: ' + url)
    else:
        check_for_errors(json.loads(r.content.decode('utf-8')),code)
        logging.info('Failed to post: ' + code)

# Returns the JSON formatted license
def get_license(code):
    url = get_base_url() + '/acq/licenses/' + code + '?apikey=' + get_key() + '&format=json'
    print (url)
    r = requests.get(url)
    if r.status_code != 200:
        logging.info('Failed to get: ' + url)
    else:
        return json.loads(r.content.decode('utf-8'))

# Create error log file for downlaod
def check_for_errors(license,code):
    print (license['web_service_result']['errorList']['error']['errorCode'])
    with open("error_log.csv", "a") as myfile:
        myfile.write(datetime.datetime.now().strftime("%Y-%m-%d %H:%M") + ' ' +  code + ', ' + license['web_service_result']['errorList']['error']['errorCode'] + '\n')

# Updates the term field from Excel file
def update_terms(license, terms):
    term = []
    """for row in terms[1:].itertuples():
        values = {'code': {}, 'value' : {}}
        if str(row[2]) != 'nan' and str(row[2]) != 'NaN' and str(row[2]) != '' and str(row[2]) != 'Please choose a value':
            print (str(row[2]))
            values['code']['value'] =  row[3].upper()
            values['code']['desc'] = row[3]
            values['value']['value'] = row[2].upper()
            values['value']['desc'] = row[2]
            term.append(values)
    license['term'] = term"""
    print (license)
    return license

# Read new licenses csv file
def read_csv(terms):
    df = pd.read_excel(terms)
    #print (df['LicenseCode'])
    code = df.iloc[0,1]
    license = get_license(code)
    if license is not None:
        json = update_terms(license, df)
        put_license(json,code)

# Get logging and configuration
logging.basicConfig(filename='error_report.log',level=logging.DEBUG)
config = configparser.ConfigParser()
config.read(sys.argv[1])

licenses = sys.argv[2]
read_csv(licenses)
