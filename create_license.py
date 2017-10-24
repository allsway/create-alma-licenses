#!/usr/bin/env python3.5

import sys
import pandas as pd
import numpy as np
import xlrd
import csv
import json
import dateparser
import configparser
import logging
import requests

# Returns the API key
def get_key():
    return config.get('Params', 'apikey')

# Returns the Alma API base URL
def get_base_url():
    return config.get('Params', 'baseurl')

def post_license(json, code):
    url = get_base_url() + '/acq/licenses?apikey=' + get_key()
    print (url)
    headers = {"Content-Type": "application/json"}
    r = requests.post(url,data=json,headers=headers)
    print (r.content)
    if r.status_code == 200:
        print (r.content)
        logging.info('Success update for: ' + url)
    else:
        print (r.content)
        logging.info('Failed to post: ' + code)


def make_license(row):
    start_date = dateparser.parse(row[5]).strftime('%Y-%m-%d')
    license = {
        'code' : row[0],
        'name' : row[1],
        'type' : {'value' : row[2].upper(), 'desc' : row[2]},
        'status': {'value': row[3].upper(), 'desc' : row[3]},
        'licensor' : {'value' : row[4].upper(), 'desc': row[4]},
        'location' : {'value' : row[6].upper(), 'desc' : row[5]},
        'start_date': start_date + 'Z',
        'review_status': {'value': row[7].upper(), 'desc' : row[7]}
    }
    print (json.dumps(license))
    post_license(json.dumps(license),row[0])

def read_csv(licenses):
    f  = open(licenses,'rt')
    try:
        reader = csv.reader(f)
        header = next(reader)
        for row in reader:
            make_license(row)
    finally:
        f.close()


logging.basicConfig(filename='status.log',level=logging.DEBUG)
config = configparser.ConfigParser()
config.read(sys.argv[1])

licenses = sys.argv[2]
read_csv(licenses)
