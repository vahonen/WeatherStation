#! /usr/bin/python
# -*- coding: cp1252 -*-

import Adafruit_DHT as dht
import time
import os
import MySQLdb
import urllib2

def read_parameter(curs, parameter_name):
    sql_query = "SELECT value FROM parameters WHERE name = %s"
    
    success = False
    value = "0"

    try:      
        curs.execute (sql_query, (parameter_name))
        results = curs.fetchall()
        for row in results:
            value = row[0]
        success = True
    except:
        print( "Virhe: parametria ei voitu lukea")

    return success, value

def main():

    config = open("api_key","r")
    api_key = config.readline()[:-1]
    config.close()
    
    default_interval = 900 #oletuksena 15 minuutin periodi
    enabled = 1
    interval = default_interval
    
    while True:
        
        while enabled == 1:
            humidity, temperature = dht.read_retry(dht.DHT22, 23)
            temperature = round(temperature, 1)
            humidity = round(humidity, 1)    
            # muodostetaan yhteys LAMP-serverin tietokantaan
            db = MySQLdb.connect(host = "192.168.0.112", user = "testaaja", passwd = "salasana", db = "dht")
            curs=db.cursor()
    
            date_ = (os.popen('date +%Y-%m-%d').read())
            time_ =  (os.popen('date +%T').read())

            # poistetaan rivinvaihdot
            date_  = date_[:-1] 
            time_  = time_[:-1]

            # testitulostusta:
            print("")
            print("date %s" % date_)
            print("time %s" % time_)
            print("temperature %s" % temperature)
            print("humidity %s" % humidity)

            sql_query = "INSERT INTO data_table (date, time, temperature, humidity) VALUES (%s, %s, %s, %s)"

             # tietokantaan talletus
            try:
                curs.execute(sql_query, (date_, time_, temperature, humidity))
                db.commit()
                print("Data talletettu tietokantaan")

            except:
                print( "Virhe: dataa ei talletettu tietokantaan")
                db.rollback()

            # ThingSpeakiin vienti
            try:
        
                f = urllib2.urlopen("https://api.thingspeak.com/update?api_key=%s&field1=%s&field2=%s" % (api_key, temperature, humidity))
                f.close()
                print("Data lähetetty ThingSpeakiin")

            except:
                print( "Virhe avattaessa url:ia")
            
            success, interval_ = read_parameter(curs, "interval")
            if success:
                interval = int(interval_)
            else:
                interval = 900
            
            success, enabled_ = read_parameter(curs, "enabled")
            if success:
                enabled = int(enabled_)
            else:
                enabled = 1

            print("success: %s interval: %s" % (success, interval))
            print("success: %s enabled: %s" % (success, enabled))
        
            counter = 0
            read_counter = 0
            while counter < interval and enabled == 1:
                time.sleep(1)
                counter += 1
                read_counter += 1

                if read_counter > 10:
                    success, enabled_ = read_parameter(curs, "enabled")
                    if success:
                        enabled = int(enabled_)
                    else:
                        enabled = 1
                    read_counter = 0

        time.sleep(10)
        
        db = MySQLdb.connect(host = "192.168.0.112", user = "testaaja", passwd = "salasana", db = "dht")
        curs=db.cursor()
        
        success, enabled_ = read_parameter(curs, "enabled")
        if success:
            enabled = int(enabled_)
        else:
            enabled = 1

        if enabled == 1:
            success, interval_ = read_parameter(curs, "interval")
            if success:
                interval = int(interval_)
            else:
                interval = default_interval
            
if __name__ == "__main__":
    main()
