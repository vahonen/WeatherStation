#! /usr/bin/python
# -*- coding: cp1252 -*-

import Adafruit_DHT as dht
import time
import os
import urllib2


def main():
    
    GPIO_PIN = 23
    DEFAULT_INTERVAL = 10 # 10 sekuntia
    
    enabled = 1
  
    f = open("api_key","r")
    WRITE_KEY = f.readline()[:-1]
    f.close()
    
    while True:
        while enabled == 1:
            humidity, temperature = dht.read_retry(dht.DHT22, GPIO_PIN)
            temperature = round(temperature, 1)
            humidity = round(humidity, 1)    
    
            date_ = (os.popen('date +%Y-%m-%d').read())
            time_ =  (os.popen('date +%T').read())

            # poistetaan rivinvaihdot
            date_  = date_[:-1] 
            time_  = time_[:-1]

            # testitulostusta:
           
            print("\ndate %s" % date_)
            print("time %s" % time_)
            print("temperature %s" % temperature)
            print("humidity %s" % humidity)

           
           
            # ThingSpeakiin vienti
            try:
        
                f = urllib2.urlopen("https://api.thingspeak.com/update?api_key=%s&field1=%s&field2=%s" % (WRITE_KEY, temperature, humidity))
                f.close()
                print("Data lähetetty ThingSpeakiin")

            except:
                print( "Virhe avattaessa url:ia")

            time.sleep(DEFAULT_INTERVAL)
if __name__ == "__main__":
    main()
