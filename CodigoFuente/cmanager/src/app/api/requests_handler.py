import requests
import json
from ..constants import JSON_HEADERS

def do_get(url, timeout=0):
  r = None
  if url == '':
    return r
  try:
    if timeout > 0:
      r = requests.get(url, timeout=timeout)
    else:
      r = requests.get(url)
  except requests.exceptions.RequestException as e:
    print(e)
  return r

def do_post(url, payload, headers=JSON_HEADERS, timeout=0):
  r = None
  if url == '':
    return r
  try:
    if timeout > 0:
      r = requests.post(url, data=json.dumps(payload), headers=headers, timeout=timeout)
    else:
      r = requests.post(url, data=json.dumps(payload), headers=headers)
  except requests.exceptions.RequestException as e:
    print(e)
  return r

def do_put(url, payload=None, headers=JSON_HEADERS, timeout=0):
  r = None
  if url == '':
    return r
  try:
    if payload:
      if timeout > 0:
        r = requests.put(url, data=json.dumps(payload), headers=headers, timeout=timeout)
      else:
        r = requests.put(url, data=json.dumps(payload), headers=headers)
    else:
      if timeout > 0:
        r = requests.put(url, timeout=timeout)
      else:
        r = requests.put(url)
  except requests.exceptions.RequestException as e:
    print(e)
  return r

def do_delete(url, payload=None, headers=JSON_HEADERS, timeout=0):
  r = None
  if url == '':
    return r
  try:
    if payload:
      if timeout > 0:
        r = requests.delete(url, data=json.dumps(payload), headers=headers, timeout=timeout)
      else:
        r = requests.delete(url, data=json.dumps(payload), headers=headers)
    else:
      if timeout > 0:
        r = requests.delete(url, timeout=timeout)
      else:
        r = requests.delete(url)
  except requests.exceptions.RequestException as e:
    print(e)
  return r